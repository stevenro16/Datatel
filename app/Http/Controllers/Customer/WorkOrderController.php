<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreWorkOrderRequest;
use App\Http\Requests\Customer\UpdateWorkOrderRequest;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\InvoiceHistory;
use App\Models\WorkOrder;
use App\Models\WorkOrderAttachment;
use App\Models\WorkOrderHistory;
use App\Models\WorkOrderNote;
use App\Models\WorkOrderVisit;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $stats = [
            'open'      => WorkOrder::where('customer_id', $user->id)
                            ->whereNotIn('status', [WorkOrder::STATUS_COMPLETED, WorkOrder::STATUS_CANCELED])->count(),
            'feedback'  => WorkOrder::where('customer_id', $user->id)
                            ->where(fn($q) => $q
                                ->where('status', WorkOrder::STATUS_AWAITING_FEEDBACK)
                                ->orWhere('confirmation_status', WorkOrder::CONFIRMATION_PENDING)
                            )->count(),
            'to_sign'   => WorkOrder::where('customer_id', $user->id)
                            ->where('status', WorkOrder::STATUS_BILLED)->count(),
            'completed' => WorkOrder::where('customer_id', $user->id)
                            ->where('status', WorkOrder::STATUS_COMPLETED)
                            ->whereMonth('updated_at', now()->month)->count(),
        ];

        // Auto-select the most actionable filter when the user hasn't picked one
        if (!$request->has('filter')) {
            if ($stats['feedback'] > 0)  $filter = 'feedback';
            elseif ($stats['to_sign'] > 0) $filter = 'to_sign';
            else $filter = 'open';
        } else {
            $filter = $request->input('filter', 'open');
        }

        $query = WorkOrder::where('customer_id', $user->id)->with('serviceTypes');

        match ($filter) {
            'feedback'  => $query->where(fn($q) => $q
                                ->where('status', WorkOrder::STATUS_AWAITING_FEEDBACK)
                                ->orWhere('confirmation_status', WorkOrder::CONFIRMATION_PENDING)),
            'to_sign'   => $query->where('status', WorkOrder::STATUS_BILLED),
            'completed' => $query->where('status', WorkOrder::STATUS_COMPLETED),
            'all'       => null,
            default     => $query->whereNotIn('status', [WorkOrder::STATUS_COMPLETED, WorkOrder::STATUS_CANCELED]),
        };

        $query->orderByRaw('scheduled_at IS NULL, scheduled_at ASC')->orderBy('created_at', 'desc');

        $orders = $query->paginate(15)->withQueryString();

        $customerId  = $user->id;
        $pageIds     = $orders->pluck('id')->toArray();
        $unreadWoIds = [];
        if (!empty($pageIds)) {
            $unreadWoIds = WorkOrderNote::whereIn('work_order_id', $pageIds)
                ->where('visibility', 'customer')
                ->where('user_id', '!=', $customerId)
                ->whereNull('read_at')
                ->pluck('work_order_id')
                ->unique()
                ->flip()
                ->toArray();
        }

        return view('customer.work-orders.index', compact('orders', 'stats', 'filter', 'unreadWoIds'));
    }

    public function create()
    {
        $user                 = auth()->user();
        $serviceTypes         = ServiceType::where('is_active', true)->orderBy('name')->get();
        $defaultDate          = now()->addDays(3)->format('Y-m-d');
        $sites                = CustomerAddress::forUser($user)->where('is_active', true)->orderByDesc('is_default')->orderBy('label')->get();
        $defaultSite          = $sites->firstWhere('is_default', true);
        $customerAvailDefaults = $user->preferred_availability;

        $siteAccountAddress = null;
        $company = $user->companies()->first();
        if ($company?->address_street) {
            $siteAccountAddress = implode(', ', array_filter([
                $company->address_street,
                $company->address_city,
                trim(($company->address_state ?? '') . ' ' . ($company->address_zip ?? '')),
            ]));
        }
        if (!$siteAccountAddress && $user->home_street) {
            $siteAccountAddress = implode(', ', array_filter([
                $user->home_street,
                $user->home_city,
                trim(($user->home_state ?? '') . ' ' . ($user->home_zip ?? '')),
            ]));
        }
        $sitePriorAddresses = WorkOrder::where('customer_id', $user->id)
            ->whereNotNull('site_street')
            ->where('site_street', '!=', '')
            ->orderByDesc('created_at')
            ->select('site_street')
            ->distinct()
            ->pluck('site_street')
            ->toArray();

        return view('customer.work-orders.create', compact('serviceTypes', 'defaultDate', 'sites', 'defaultSite', 'user', 'customerAvailDefaults', 'siteAccountAddress', 'sitePriorAddresses'));
    }

    public function store(StoreWorkOrderRequest $request)
    {
        $data = $request->validated();

        $availability = $this->parseAvailability($data['preferred_availability'] ?? null);

        $user = auth()->user();
        if ($request->boolean('save_phone_as_default') && !empty($data['site_contact_phone']) && !$user->phone) {
            $user->update(['phone' => $data['site_contact_phone']]);
        }
        if ($request->boolean('update_customer_defaults')) {
            $user->update(['preferred_availability' => $availability ?: null]);
        }

        $order = WorkOrder::create([
            'customer_id'            => auth()->id(),
            'status'                 => WorkOrder::STATUS_NEW,
            'urgency'                => $data['urgency'],
            'description'            => $data['description'],
            'equipment_details'      => $data['equipment_details'] ?? null,
            'site_street'            => $data['site_street'] ?? null,
            'site_contact_name'      => $data['site_contact_name'] ?? null,
            'site_contact_phone'     => $data['site_contact_phone'] ?? null,
            'preferred_date'         => $data['preferred_date'] ?? null,
            'preferred_availability' => $availability ?: null,
        ]);

        if (!empty($data['service_ids'])) {
            $order->serviceTypes()->sync($data['service_ids']);
        }

        $this->saveFiles($request, $order, 'photos');
        $this->saveFiles($request, $order, 'documents');

        return redirect()->route('portal.work-orders.show', $order)
            ->with('success', 'Work order submitted. We\'ll be in touch shortly.');
    }

    public function show(WorkOrder $workOrder)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);

        WorkOrderNote::where('work_order_id', $workOrder->id)
            ->where('visibility', 'customer')
            ->where('user_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $workOrder->load([
            'serviceTypes',
            'attachments',
            'notes'              => fn($q) => $q->where('visibility', 'customer'),
            'notes.author',
            'assignments.employee',
            'invoices.lineItems',
            'invoices.history',
            'completionSignature.collectedBy',
            'visits.techs.user',
            'visits.signature',
            'visits.timeEntries',
        ]);
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('name')->get();

        $user = auth()->user();
        $siteAccountAddress = null;
        $company = $user->companies()->first();
        if ($company?->address_street) {
            $siteAccountAddress = implode(', ', array_filter([
                $company->address_street,
                $company->address_city,
                trim(($company->address_state ?? '') . ' ' . ($company->address_zip ?? '')),
            ]));
        }
        if (!$siteAccountAddress && $user->home_street) {
            $siteAccountAddress = implode(', ', array_filter([
                $user->home_street,
                $user->home_city,
                trim(($user->home_state ?? '') . ' ' . ($user->home_zip ?? '')),
            ]));
        }
        $sitePriorAddresses = WorkOrder::where('customer_id', $user->id)
            ->where('id', '!=', $workOrder->id)
            ->whereNotNull('site_street')
            ->where('site_street', '!=', '')
            ->orderByDesc('created_at')
            ->select('site_street')
            ->distinct()
            ->pluck('site_street')
            ->toArray();

        return view('customer.work-orders.show', compact('workOrder', 'serviceTypes', 'siteAccountAddress', 'sitePriorAddresses'));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder)
    {
        $data = $request->validated();

        $scalarFields = ['description', 'equipment_details', 'urgency', 'site_street', 'site_contact_name', 'site_contact_phone'];

        foreach ($scalarFields as $field) {
            $old = $workOrder->$field ?? null;
            $new = $data[$field] ?? null;
            if ($old !== $new) {
                WorkOrderHistory::create([
                    'work_order_id' => $workOrder->id,
                    'changed_by'    => auth()->id(),
                    'field_name'    => $field,
                    'old_value'     => $old,
                    'new_value'     => $new,
                    'changed_at'    => now(),
                ]);
            }
        }

        $oldDate = $workOrder->preferred_date?->format('Y-m-d');
        $newDate = $data['preferred_date'] ?? null;
        if ($oldDate !== $newDate) {
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'preferred_date',
                'old_value'     => $oldDate,
                'new_value'     => $newDate,
                'changed_at'    => now(),
            ]);
        }

        $oldServices = $workOrder->serviceTypes->pluck('name')->sort()->join(', ');
        $workOrder->serviceTypes()->sync($data['service_ids'] ?? []);
        $newServices = $workOrder->fresh()->serviceTypes->pluck('name')->sort()->join(', ');
        if ($oldServices !== $newServices) {
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'services',
                'old_value'     => $oldServices ?: null,
                'new_value'     => $newServices ?: null,
                'changed_at'    => now(),
            ]);
        }

        $newAvailability = $this->parseAvailability($data['preferred_availability'] ?? null) ?: null;
        $oldAvailJson    = $workOrder->preferred_availability ? json_encode($workOrder->preferred_availability) : null;
        $newAvailJson    = $newAvailability ? json_encode($newAvailability) : null;
        if ($oldAvailJson !== $newAvailJson) {
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'preferred_availability',
                'old_value'     => $oldAvailJson,
                'new_value'     => $newAvailJson,
                'changed_at'    => now(),
            ]);
        }
        $data['preferred_availability'] = $newAvailability;

        $workOrder->update(collect($data)->except(['service_ids', 'update_customer_defaults'])->toArray());

        if ($request->boolean('update_customer_defaults')) {
            auth()->user()->update(['preferred_availability' => $newAvailability]);
        }

        return back()->with('success', 'Work order updated.');
    }

    public function addAttachment(Request $request, WorkOrder $workOrder)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);
        abort_if(!in_array($workOrder->status, [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED]), 403);

        $request->validate([
            'photos'      => 'nullable|array',
            'photos.*'    => 'file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'documents'   => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,txt|max:20480',
        ]);

        $existing      = $workOrder->attachments()->get();
        $photoCount    = $existing->filter(fn($a) => str_starts_with($a->mime_type, 'image/'))->count();
        $docCount      = $existing->filter(fn($a) => !str_starts_with($a->mime_type, 'image/'))->count();
        $newPhotos     = $request->file('photos', []);
        $newDocs       = $request->file('documents', []);

        if ($photoCount + count($newPhotos) > 3) {
            return back()->withErrors(['photos' => 'You can only have up to 3 photos per work order.']);
        }
        if ($docCount + count($newDocs) > 3) {
            return back()->withErrors(['documents' => 'You can only have up to 3 documents per work order.']);
        }

        $this->storeFiles($newPhotos, $workOrder);
        $this->storeFiles($newDocs, $workOrder);

        return back()->with('success', 'Files added.');
    }

    public function removeAttachment(WorkOrder $workOrder, WorkOrderAttachment $attachment)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);
        abort_if($attachment->work_order_id !== $workOrder->id, 403);
        abort_if(!in_array($workOrder->status, [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED]), 403);

        $path = storage_path('app/uploads/work-orders/' . $workOrder->id . '/' . $attachment->stored_name);
        if (file_exists($path)) {
            unlink($path);
        }
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

    public function addNote(Request $request, WorkOrder $workOrder)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);
        abort_if($workOrder->status === WorkOrder::STATUS_CANCELED, 422);

        $data = $request->validate([
            'body' => 'required|string|min:3|max:2000',
        ]);

        WorkOrderNote::create([
            'work_order_id' => $workOrder->id,
            'user_id'       => auth()->id(),
            'body'          => $data['body'],
            'visibility'    => 'customer',
        ]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'note',
            'old_value'     => null,
            'new_value'     => $data['body'],
            'comment'       => 'Customer added a note.',
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Note added.');
    }

    public function confirmVisit(WorkOrder $workOrder)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);
        abort_if($workOrder->confirmation_status !== WorkOrder::CONFIRMATION_PENDING, 422);

        $oldStatus = $workOrder->status;

        $workOrder->update([
            'confirmation_status' => WorkOrder::CONFIRMATION_CONFIRMED,
            'status'              => WorkOrder::STATUS_SCHEDULED,
        ]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'confirmation_status',
            'old_value'     => WorkOrder::CONFIRMATION_PENDING,
            'new_value'     => WorkOrder::CONFIRMATION_CONFIRMED,
            'comment'       => 'Customer confirmed the scheduled visit.',
            'changed_at'    => now(),
        ]);

        if ($oldStatus !== WorkOrder::STATUS_SCHEDULED) {
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => $oldStatus,
                'new_value'     => WorkOrder::STATUS_SCHEDULED,
                'comment'       => 'Automatically advanced when customer confirmed the visit.',
                'changed_at'    => now(),
            ]);
        }

        return back();
    }

    public function declineVisit(Request $request, WorkOrder $workOrder)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);
        abort_if($workOrder->confirmation_status !== WorkOrder::CONFIRMATION_PENDING, 422);

        $data = $request->validate([
            'decline_reason' => 'nullable|string|max:1000',
        ]);

        $workOrder->update(['confirmation_status' => WorkOrder::CONFIRMATION_DECLINED]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'confirmation_status',
            'old_value'     => WorkOrder::CONFIRMATION_PENDING,
            'new_value'     => WorkOrder::CONFIRMATION_DECLINED,
            'comment'       => $data['decline_reason'] ?? null,
            'changed_at'    => now(),
        ]);

        return back();
    }

    public function confirmVisitByCustomer(WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);
        abort_if($visit->work_order_id !== $workOrder->id, 403);
        abort_if($visit->confirmation_status !== WorkOrderVisit::CONFIRMATION_PENDING, 422);

        $visit->update([
            'confirmation_status' => WorkOrderVisit::CONFIRMATION_CONFIRMED,
            'confirmed_by'        => auth()->id(),
            'confirmed_at'        => now(),
        ]);
        $workOrder->syncConfirmationStatus();

        // Advance WO status if still pre-scheduled
        $oldStatus = $workOrder->status;
        if (in_array($oldStatus, [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED])) {
            $workOrder->update(['status' => WorkOrder::STATUS_SCHEDULED]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => $oldStatus,
                'new_value'     => WorkOrder::STATUS_SCHEDULED,
                'comment'       => 'Automatically advanced when customer confirmed the visit.',
                'changed_at'    => now(),
            ]);
        }

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'visit_confirmation',
            'old_value'     => WorkOrderVisit::CONFIRMATION_PENDING,
            'new_value'     => WorkOrderVisit::CONFIRMATION_CONFIRMED,
            'comment'       => 'Customer confirmed visit on ' . $visit->scheduled_at->format('M j, Y \a\t g:i A') . '.',
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Visit confirmed for ' . $visit->scheduled_at->format('l, F j, Y') . '.');
    }

    public function declineVisitByCustomer(Request $request, WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);
        abort_if($visit->work_order_id !== $workOrder->id, 403);
        abort_if($visit->confirmation_status !== WorkOrderVisit::CONFIRMATION_PENDING, 422);

        $data = $request->validate(['decline_reason' => 'nullable|string|max:1000']);

        $visit->update(['confirmation_status' => WorkOrderVisit::CONFIRMATION_DECLINED]);
        $workOrder->syncConfirmationStatus();

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'visit_confirmation',
            'old_value'     => WorkOrderVisit::CONFIRMATION_PENDING,
            'new_value'     => WorkOrderVisit::CONFIRMATION_DECLINED,
            'comment'       => 'Customer declined visit on ' . $visit->scheduled_at->format('M j, Y \a\t g:i A') . ($data['decline_reason'] ? ': ' . $data['decline_reason'] : ''),
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Reschedule request submitted for visit on ' . $visit->scheduled_at->format('l, F j, Y') . '.');
    }

    public function cancel(Request $request, WorkOrder $workOrder)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);
        abort_if(in_array($workOrder->status, [
            WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED,
            WorkOrder::STATUS_BILLED,
            WorkOrder::STATUS_COMPLETED,
            WorkOrder::STATUS_CANCELED,
        ]), 422);

        $data = $request->validate(['cancel_reason' => 'nullable|string|max:2000']);

        $reason  = trim($data['cancel_reason'] ?? '');
        $comment = $reason
            ? 'Customer requested cancellation of scheduled visit: ' . $reason
            : 'Customer requested cancellation of scheduled visit. No instructions provided — team will follow up on the next business day.';

        $old = $workOrder->status;
        $workOrder->update([
            'status'      => WorkOrder::STATUS_AWAITING_FEEDBACK,
            'cancel_reason' => $reason ?: null,
            'canceled_by'   => auth()->id(),
        ]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'status',
            'old_value'     => $old,
            'new_value'     => WorkOrder::STATUS_AWAITING_FEEDBACK,
            'comment'       => $comment,
            'changed_at'    => now(),
        ]);

        $msg = $reason
            ? 'Your cancellation request has been submitted. We will review your instructions and be in touch.'
            : 'Your cancellation request has been submitted. We will follow up with you on the next business day.';

        return redirect()->route('portal.work-orders.show', $workOrder);
    }

    public function submitPayment(WorkOrder $workOrder)
    {
        abort_if($workOrder->customer_id !== auth()->id(), 403);

        $invoice = $workOrder->invoice;
        abort_if(!$invoice || $invoice->status !== Invoice::STATUS_ISSUED, 422);

        $invoice->update(['status' => Invoice::STATUS_PAYMENT_RECEIVED]);

        $invoiceNum = 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);

        InvoiceHistory::create([
            'invoice_id' => $invoice->id,
            'changed_by' => auth()->id(),
            'field_name' => 'status',
            'old_value'  => Invoice::STATUS_ISSUED,
            'new_value'  => Invoice::STATUS_PAYMENT_RECEIVED,
            'comment'    => 'Customer confirmed payment was submitted.',
            'changed_at' => now(),
        ]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'invoice_status',
            'old_value'     => Invoice::STATUS_ISSUED,
            'new_value'     => Invoice::STATUS_PAYMENT_RECEIVED,
            'comment'       => "Customer confirmed payment submitted for {$invoiceNum}.",
            'changed_at'    => now(),
        ]);

        return back();
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

    private function saveFiles(Request $request, WorkOrder $order, string $field): void
    {
        $this->storeFiles($request->file($field, []), $order);
    }

    private function storeFiles(array $files, WorkOrder $order): void
    {
        if (empty($files)) {
            return;
        }

        $dir = storage_path('app/uploads/work-orders/' . $order->id);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $mimeType     = $file->getClientMimeType();
            $sizeBytes    = $file->getSize();
            $stored       = Str::uuid() . '.' . $file->getClientOriginalExtension();

            $file->move($dir, $stored);

            WorkOrderAttachment::create([
                'work_order_id' => $order->id,
                'uploaded_by'   => auth()->id(),
                'original_name' => $originalName,
                'stored_name'   => $stored,
                'mime_type'     => $mimeType,
                'size_bytes'    => $sizeBytes,
            ]);
        }
    }
}
