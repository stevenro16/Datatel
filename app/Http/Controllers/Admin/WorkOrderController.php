<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkOrderRequest;
use App\Http\Requests\Admin\UpdateWorkOrderRequest;
use App\Models\CustomerAddress;
use App\Models\TimeEntry;
use App\Models\WorkOrder;
use App\Models\WorkOrderAttachment;
use App\Models\WorkOrderHistory;
use App\Models\WorkOrderNote;
use App\Models\WorkOrderVisit;
use App\Models\User;
use App\Models\AdminSetting;
use App\Models\Invoice;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        // Counts for every queue pill — computed before any filters
        $activeStatuses = [
            WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED, WorkOrder::STATUS_SCHEDULED,
            WorkOrder::STATUS_AWAITING_FEEDBACK, WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED, WorkOrder::STATUS_BILLED,
        ];
        $queueCounts = [
            'all'                  => WorkOrder::whereIn('status', $activeStatuses)->count(),
            'new'                  => WorkOrder::where('status', WorkOrder::STATUS_NEW)->count(),
            'pending_confirmation' => WorkOrder::whereIn('status', $activeStatuses)
                                        ->whereHas('visits', fn($v) => $v->where('confirmation_status', WorkOrderVisit::CONFIRMATION_PENDING))
                                        ->count(),
            'scheduled'            => WorkOrder::where('status', WorkOrder::STATUS_SCHEDULED)->count(),
            'prepare_invoice'      => WorkOrder::where(function ($q) {
                                        $q->where('status', WorkOrder::STATUS_SERVICES_PERFORMED)
                                          ->orWhere('needs_invoice', true);
                                    })->count(),
            'confirm_payment'      => WorkOrder::where('status', WorkOrder::STATUS_BILLED)->count(),
            'unread'               => (int) DB::table('work_order_notes as n')
                                        ->join('users as u', 'u.id', '=', 'n.user_id')
                                        ->whereNull('n.read_at')
                                        ->where('u.role', '!=', 'admin')
                                        ->distinct()
                                        ->count('n.work_order_id'),
            'completed'            => WorkOrder::where('status', WorkOrder::STATUS_COMPLETED)->count(),
        ];

        $validQueues = ['all', 'new', 'pending_confirmation', 'scheduled', 'prepare_invoice', 'confirm_payment', 'unread', 'completed'];
        $queue = in_array($request->input('queue'), $validQueues) ? $request->input('queue') : null;

        // Default landing: walk the saved priority order and pick the first queue with items
        if ($queue === null && !$request->filled('search')) {
            $savedOrder   = AdminSetting::get('work_queue_order', 'all,new,pending_confirmation,scheduled,prepare_invoice,confirm_payment,unread');
            $priorityKeys = array_filter(array_map('trim', explode(',', $savedOrder)));
            $default      = 'new';
            foreach ($priorityKeys as $k) {
                if (in_array($k, $validQueues) && $k !== 'all' && ($queueCounts[$k] ?? 0) > 0) {
                    $default = $k;
                    break;
                }
            }
            return redirect()->route('admin.work-orders.index', ['queue' => $default]);
        }

        $isSearching = $request->filled('search');
        $fullSearch  = $isSearching && $request->boolean('full_search');

        $query = WorkOrder::with([
            'customer',
            'customer.companies' => fn($q) => $q->wherePivot('is_primary', true)->wherePivot('status', 'active'),
            'serviceTypes',
            'assignments.employee',
            'visits',
        ]);

        if (!$isSearching) {
            // No search active — apply queue-specific filter
            match ($queue) {
                'new'                  => $query->where('status', WorkOrder::STATUS_NEW),
                'pending_confirmation' => $query->whereIn('status', $activeStatuses)
                                               ->whereHas('visits', fn($v) => $v->where('confirmation_status', WorkOrderVisit::CONFIRMATION_PENDING)),
                'scheduled'            => $query->where('status', WorkOrder::STATUS_SCHEDULED),
                'prepare_invoice'      => $query->where(function ($q) {
                                              $q->where('status', WorkOrder::STATUS_SERVICES_PERFORMED)
                                                ->orWhere('needs_invoice', true);
                                          }),
                'confirm_payment'      => $query->where('status', WorkOrder::STATUS_BILLED),
                'unread'               => $query->whereHas('notes', fn($q) =>
                                              $q->whereNull('read_at')
                                                ->whereHas('author', fn($u) => $u->where('role', '!=', 'admin'))),
                'completed'            => $query->where('status', WorkOrder::STATUS_COMPLETED),
                default                => $query->whereIn('status', $activeStatuses),
            };
        } elseif (!$fullSearch) {
            // Searching active work orders only (default)
            $query->whereIn('status', $activeStatuses);
        }
        // fullSearch=true: no status filter — search all work orders including completed/canceled

        if ($isSearching) {
            $search    = trim($request->search);
            $stripped  = preg_replace('/^WO?-?/i', '', $search);
            $searchNum = ctype_digit($stripped) && $stripped !== '' ? (int) $stripped : 0;

            // Invoice number: i42, inv42, inv-42, INV-0042 → resolve to work_order_id
            $invoiceWoId = null;
            if (preg_match('/^i(?:nv-?)?0*(\d+)$/i', $search, $m)) {
                $invoiceWoId = Invoice::where('id', (int) $m[1])->value('work_order_id');
            }

            $query->where(function ($q) use ($search, $searchNum, $invoiceWoId) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', '%'.$search.'%')
                       ->orWhere('phone', 'like', '%'.$search.'%');
                })
                ->orWhereHas('customer.companies', function ($cq) use ($search) {
                    $cq->where('name', 'like', '%'.$search.'%')
                       ->orWhere('phone', 'like', '%'.$search.'%');
                })
                ->orWhere('site_contact_phone', 'like', '%'.$search.'%')
                ->orWhere('site_contact_name', 'like', '%'.$search.'%');

                if ($searchNum) {
                    $q->orWhere('wo_number', $searchNum);
                }
                if ($invoiceWoId) {
                    $q->orWhere('id', $invoiceWoId);
                }
            });
        }

        $allowedSorts = ['wo_number', 'urgency', 'status', 'created_at', 'updated_at', 'scheduled_at'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $dir  = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $orders  = $query->paginate(20)->withQueryString();

        $pageIds = $orders->pluck('id')->toArray();
        $unreadWoIds = [];
        if (!empty($pageIds)) {
            $unreadWoIds = DB::table('work_order_notes as n')
                ->join('users as u', 'u.id', '=', 'n.user_id')
                ->whereIn('n.work_order_id', $pageIds)
                ->whereNull('n.read_at')
                ->where('u.role', '!=', 'admin')
                ->pluck('n.work_order_id')
                ->unique()
                ->flip()
                ->toArray();
        }

        if ($request->header('X-List-Request')) {
            return view('admin.work-orders._list', compact('orders', 'queue', 'sort', 'dir', 'unreadWoIds'));
        }

        return view('admin.work-orders.index', compact('orders', 'queue', 'queueCounts', 'sort', 'dir', 'unreadWoIds'));
    }

    public function create()
    {
        return redirect()->route('admin.work-orders.index');
    }

    public function store(StoreWorkOrderRequest $request)
    {
        $data = $request->validated();

        $scheduledAt  = null;
        if (!empty($data['scheduled_date'])) {
            $scheduledAt = $data['scheduled_date'] . ' ' . ($data['scheduled_time'] ?? '00:00') . ':00';
        }

        $availability = $this->parseAvailability($data['preferred_availability'] ?? null);

        $order = WorkOrder::create([
            'customer_id'            => $data['customer_id'],
            'urgency'                => $data['urgency'],
            'status'                 => WorkOrder::STATUS_NEW,
            'created_by'             => auth()->id(),
            'description'            => $data['description'] ?? null,
            'equipment_details'      => $data['equipment_details'] ?? null,
            'site_street'            => $data['site_street'] ?? null,
            'site_contact_name'      => $data['site_contact_name'] ?? null,
            'site_contact_phone'     => $data['site_contact_phone'] ?? null,
            'preferred_date'         => $data['preferred_date'] ?? null,
            'preferred_availability' => $availability ?: null,
            'scheduled_at'           => $scheduledAt,
        ]);

        if (!empty($data['service_ids'])) {
            $order->serviceTypes()->sync($data['service_ids']);
        }

        return redirect()->route('admin.work-orders.show', $order)
            ->with('success', 'Work order '.$order->woLabel().' created.')
            ->with('auto_edit', true);
    }

    private function parseAvailability(?string $json): ?array
    {
        if (!$json) return null;
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) return null;

        $validDays  = ['monday','tuesday','wednesday','thursday','friday','saturday'];
        $validTimes = ['morning','lunch','afternoon'];
        $result = [];

        foreach ($validDays as $day) {
            if (isset($decoded[$day]) && is_array($decoded[$day])) {
                $times = array_values(array_intersect($decoded[$day], $validTimes));
                if ($times) $result[$day] = $times;
            }
        }

        return $result ?: null;
    }

    public function show(Request $request, WorkOrder $workOrder)
    {
        // Track which activity the admin came from so the back button returns to the right place.
        // The originating page passes ?from=<source>&from_id=<id>; we cache it per-WO in the session
        // so it survives refreshes and back()-redirects from actions on the show page itself.
        $allowedFrom = ['work-orders', 'dashboard', 'calendar', 'invoice', 'company', 'customer'];
        $from        = $request->query('from');
        $fromId      = $request->query('from_id');
        $sessionKey  = 'wo_back_' . $workOrder->id;

        if ($from && in_array($from, $allowedFrom, true)) {
            session()->put($sessionKey, ['from' => $from, 'from_id' => $fromId]);
        }
        $back = session($sessionKey, ['from' => 'work-orders', 'from_id' => null]);

        [$backUrl, $backLabel] = match ($back['from']) {
            'dashboard' => [route('admin.dashboard'), 'Dashboard'],
            'calendar'  => [route('admin.calendar'),  'Calendar'],
            'invoice'   => [
                $back['from_id'] ? route('admin.invoices.show', $back['from_id']) : route('admin.invoices.index'),
                'Invoice',
            ],
            'company'   => [
                $back['from_id'] ? route('admin.analytics.companies', ['company_id' => $back['from_id']]) : route('admin.analytics.companies'),
                'Company Analytics',
            ],
            'customer'  => [
                $back['from_id'] ? route('admin.analytics.customers', ['customer_id' => $back['from_id']]) : route('admin.analytics.customers'),
                'Customer Analytics',
            ],
            default     => [route('admin.work-orders.index'), 'Work Orders'],
        };

        WorkOrderNote::where('work_order_id', $workOrder->id)
            ->whereIn('user_id', User::where('role', '!=', User::ROLE_ADMIN)->select('id'))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $workOrder->load(['customer.companies', 'serviceTypes', 'assignments.employee', 'notes.author', 'history', 'attachments', 'completionSignature.collectedBy', 'invoice', 'invoices', 'visits.techs.user', 'visits.signature.collectedBy', 'visits.timeEntries.user']);
        $employees      = User::where('role', User::ROLE_EMPLOYEE)->orderBy('name')->get();
        $serviceTypes   = ServiceType::where('is_active', true)->orderBy('name')->get();
        $pullableStatuses = [
            WorkOrder::STATUS_SCHEDULED, WorkOrder::STATUS_AWAITING_FEEDBACK,
            WorkOrder::STATUS_SERVICES_PERFORMED, WorkOrder::STATUS_INVOICE_PREPARED,
            WorkOrder::STATUS_BILLED, WorkOrder::STATUS_COMPLETED,
        ];
        $completedCount = WorkOrder::where('customer_id', $workOrder->customer_id)
                            ->where('id', '!=', $workOrder->id)
                            ->whereIn('status', $pullableStatuses)->count();

        $customerHasConfirmed = WorkOrder::where('customer_id', $workOrder->customer_id)
                            ->where('confirmation_status', WorkOrder::CONFIRMATION_CONFIRMED)
                            ->exists();

        $siteAccountAddress = null;
        $customer = $workOrder->customer;
        $company  = $customer->companies->first();
        if ($company?->address_street) {
            $siteAccountAddress = implode(', ', array_filter([
                $company->address_street,
                $company->address_city,
                trim(($company->address_state ?? '') . ' ' . ($company->address_zip ?? '')),
            ]));
        }
        if (!$siteAccountAddress && $customer->home_street) {
            $siteAccountAddress = implode(', ', array_filter([
                $customer->home_street,
                $customer->home_city,
                trim(($customer->home_state ?? '') . ' ' . ($customer->home_zip ?? '')),
            ]));
        }
        $sitePriorAddresses = WorkOrder::where('customer_id', $customer->id)
            ->where('id', '!=', $workOrder->id)
            ->whereNotNull('site_street')
            ->where('site_street', '!=', '')
            ->orderByDesc('created_at')
            ->select('site_street', 'site_city', 'site_state', 'site_zip')
            ->limit(5)
            ->get()
            ->unique('site_street');

        $companyAddresses = $company
            ? CustomerAddress::where('company_id', $company->id)
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('label')
                ->get()
            : collect();

        // Merge company addresses + prior WO addresses, deduped by normalized street
        $addressSuggestions = collect();
        $seen = [];
        foreach ($companyAddresses as $ca) {
            $key = strtolower(trim($ca->street));
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $addressSuggestions->push([
                    'source'     => 'company',
                    'label'      => $ca->label ?: null,
                    'address'    => $ca->formattedAddress(),
                    'street'     => $ca->street,
                    'city'       => $ca->city,
                    'state'      => $ca->state,
                    'zip'        => $ca->zip,
                    'is_default' => $ca->is_default,
                ]);
            }
        }
        foreach ($sitePriorAddresses as $wo) {
            $key = strtolower(trim($wo->site_street));
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $addressSuggestions->push([
                    'source'     => 'customer',
                    'label'      => null,
                    'address'    => collect([$wo->site_street, $wo->site_city, trim(($wo->site_state ?? '').' '.($wo->site_zip ?? ''))])->filter()->join(', '),
                    'street'     => $wo->site_street,
                    'city'       => $wo->site_city,
                    'state'      => $wo->site_state,
                    'zip'        => $wo->site_zip,
                    'is_default' => false,
                ]);
            }
        }

        $previousOrders = $completedCount > 0
            ? WorkOrder::where('customer_id', $workOrder->customer_id)
                ->where('id', '!=', $workOrder->id)
                ->whereIn('status', $pullableStatuses)
                ->orderByDesc('updated_at')
                ->limit(20)
                ->get(['id', 'wo_number', 'status', 'description', 'equipment_details', 'site_street', 'site_contact_name', 'site_contact_phone', 'updated_at'])
            : collect();

        $openRelated = WorkOrder::where('customer_id', $workOrder->customer_id)
            ->where('id', '!=', $workOrder->id)
            ->whereNotIn('status', [WorkOrder::STATUS_COMPLETED, WorkOrder::STATUS_CANCELED])
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get(['id', 'wo_number', 'status', 'description', 'updated_at']);

        if ($openRelated->count() < 3) {
            $completedRelated = WorkOrder::where('customer_id', $workOrder->customer_id)
                ->where('id', '!=', $workOrder->id)
                ->where('status', WorkOrder::STATUS_COMPLETED)
                ->orderByDesc('updated_at')
                ->limit(3 - $openRelated->count())
                ->get(['id', 'wo_number', 'status', 'description', 'updated_at']);
            $relatedOrders = $openRelated->concat($completedRelated);
        } else {
            $relatedOrders = $openRelated;
        }

        $timeEntries   = TimeEntry::where('work_order_id', $workOrder->id)->get();
        $siteArrival   = $timeEntries->min('clocked_in_at');
        $siteDeparture = $timeEntries->filter(fn($t) => $t->clocked_out_at)->max('clocked_out_at');

        return view('admin.work-orders.show', compact('workOrder', 'employees', 'serviceTypes', 'completedCount', 'customerHasConfirmed', 'siteAccountAddress', 'sitePriorAddresses', 'addressSuggestions', 'previousOrders', 'relatedOrders', 'siteArrival', 'siteDeparture', 'companyAddresses', 'backUrl', 'backLabel'));
    }

    public function edit(WorkOrder $workOrder)
    {
        $customers    = User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('name')->get();

        $customer = $workOrder->customer->load(['companies.sites' => fn($q) => $q->where('is_active', true)->orderByDesc('is_default')]);

        $suggestions = collect();

        // Company registered sites
        foreach ($customer->companies as $company) {
            // Company main address
            if ($company->address_street) {
                $addr = collect([$company->address_street, $company->address_city, trim(($company->address_state ?? '') . ' ' . ($company->address_zip ?? ''))])->filter()->join(', ');
                $suggestions->push(['label' => $company->name . ' (main)', 'street' => $addr, 'contact_name' => null, 'contact_phone' => null]);
            }
            // Company service sites (CustomerAddress)
            foreach ($company->sites as $site) {
                if ($site->street) {
                    $addr = collect([$site->street, $site->city, trim(($site->state ?? '') . ' ' . ($site->zip ?? ''))])->filter()->join(', ');
                    $lbl  = $company->name . ($site->label ? ' — ' . $site->label : ' (site)');
                    $suggestions->push(['label' => $lbl, 'street' => $addr, 'contact_name' => null, 'contact_phone' => null]);
                }
            }
        }

        // Prior work order addresses for this customer
        WorkOrder::where('customer_id', $customer->id)
            ->where('id', '!=', $workOrder->id)
            ->whereNotNull('site_street')
            ->where('site_street', '!=', '')
            ->orderByDesc('created_at')
            ->select('site_street', 'site_contact_name', 'site_contact_phone')
            ->get()
            ->unique('site_street')
            ->each(fn($wo) => $suggestions->push([
                'label'         => 'Previous order',
                'street'        => $wo->site_street,
                'contact_name'  => $wo->site_contact_name,
                'contact_phone' => $wo->site_contact_phone,
            ]));

        $siteSuggestions  = $suggestions->unique('street')->values();
        $customerCompanies = $customer->companies;

        return view('admin.work-orders.edit', compact('workOrder', 'customers', 'serviceTypes', 'siteSuggestions', 'customerCompanies'));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder)
    {
        $data = $request->validated();

        $data['preferred_availability'] = $this->parseAvailability($data['preferred_availability'] ?? null) ?: null;

        $updates = collect($data)->except(['scheduled_date', 'scheduled_time', 'service_ids', 'update_customer_defaults'])->toArray();

        // Only overwrite scheduled_at when the form explicitly submitted a scheduled_date field
        if ($request->has('scheduled_date')) {
            $scheduledAt = null;
            if (!empty($data['scheduled_date'])) {
                $scheduledAt = $data['scheduled_date'] . ' ' . ($data['scheduled_time'] ?? '00:00') . ':00';
            }
            $updates['scheduled_at'] = $scheduledAt;

            $oldScheduledAt = $workOrder->scheduled_at ? $workOrder->scheduled_at->format('Y-m-d H:i') : null;
            $newScheduledAt = $scheduledAt ? \Carbon\Carbon::parse($scheduledAt)->format('Y-m-d H:i') : null;

            if ($oldScheduledAt !== $newScheduledAt) {
                WorkOrderHistory::create([
                    'work_order_id' => $workOrder->id,
                    'changed_by'    => auth()->id(),
                    'field_name'    => 'scheduled_at',
                    'old_value'     => $oldScheduledAt,
                    'new_value'     => $newScheduledAt,
                    'changed_at'    => now(),
                ]);
            }
        }

        $workOrder->update($updates);

        if (array_key_exists('service_ids', $data)) {
            $workOrder->serviceTypes()->sync($data['service_ids'] ?? []);
        }

        if ($request->boolean('update_customer_defaults')) {
            $workOrder->customer->update([
                'preferred_availability' => $data['preferred_availability'],
            ]);
        }

        // Auto-advance New → Triaged when a description has been provided
        $workOrder->refresh();
        if ($workOrder->status === WorkOrder::STATUS_NEW && !empty($workOrder->description)) {
            $workOrder->update(['status' => WorkOrder::STATUS_TRIAGED]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => WorkOrder::STATUS_NEW,
                'new_value'     => WorkOrder::STATUS_TRIAGED,
                'comment'       => 'Auto-advanced to Triaged when details were saved with a description.',
                'changed_at'    => now(),
            ]);
        }

        return redirect()->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Work order updated.');
    }

    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();
        return redirect()->route('admin.work-orders.index')
            ->with('success', 'Work order deleted.');
    }

    public function storeNote(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate([
            'body'       => 'required|string|min:1',
            'visibility' => 'required|in:internal,customer',
        ]);

        $workOrder->notes()->create([
            'user_id'    => auth()->id(),
            'body'       => $data['body'],
            'visibility' => $data['visibility'],
        ]);

        return back()->with('success', 'Note added.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $valid = implode(',', [
            WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED, WorkOrder::STATUS_SCHEDULED,
            WorkOrder::STATUS_AWAITING_FEEDBACK, WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED, WorkOrder::STATUS_BILLED,
            WorkOrder::STATUS_COMPLETED, WorkOrder::STATUS_CANCELED,
        ]);

        $data = $request->validate([
            'status'  => 'required|in:'.$valid,
            'comment' => 'nullable|string|max:1000',
        ]);

        $old = $workOrder->status;
        $new = $data['status'];

        if ($old === $new) {
            return back();
        }

        $workOrder->update(['status' => $new]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'status',
            'old_value'     => $old,
            'new_value'     => $new,
            'comment'       => $data['comment'] ?? null,
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Status updated to "'.str_replace('_', ' ', $new).'".');
    }

    public function updateUrgency(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate([
            'urgency' => 'required|in:routine,urgent,emergency',
        ]);

        $old = $workOrder->urgency;
        $new = $data['urgency'];

        if ($old === $new) {
            return response()->json(['ok' => true]);
        }

        $workOrder->update(['urgency' => $new]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'urgency',
            'old_value'     => $old,
            'new_value'     => $new,
            'changed_at'    => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function addAttachment(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'photos'      => 'nullable|array',
            'photos.*'    => 'file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'documents'   => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,txt|max:20480',
        ]);

        $dir = storage_path('app/uploads/work-orders/' . $workOrder->id);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        foreach (array_merge($request->file('photos', []), $request->file('documents', [])) as $file) {
            $originalName = $file->getClientOriginalName();
            $mimeType     = $file->getClientMimeType();
            $sizeBytes    = $file->getSize();
            $stored       = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $stored);
            WorkOrderAttachment::create([
                'work_order_id' => $workOrder->id,
                'uploaded_by'   => auth()->id(),
                'original_name' => $originalName,
                'stored_name'   => $stored,
                'mime_type'     => $mimeType,
                'size_bytes'    => $sizeBytes,
            ]);
        }

        return back()->with('success', 'Files uploaded.');
    }

    public function removeAttachment(WorkOrder $workOrder, WorkOrderAttachment $attachment)
    {
        abort_if($attachment->work_order_id !== $workOrder->id, 403);

        $path = storage_path('app/uploads/work-orders/' . $workOrder->id . '/' . $attachment->stored_name);
        if (file_exists($path)) {
            unlink($path);
        }
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

}
