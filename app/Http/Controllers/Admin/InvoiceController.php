<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Invoice;
use App\Models\InvoiceHistory;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $validTabs  = ['new', 'billed', 'payment_received', 'all_active', 'past_due', 'completed'];
        $search     = trim($request->input('search', ''));
        $customerId = $request->filled('customer_id') ? $request->integer('customer_id') : null;
        $isSearching = $search !== '';
        $fullSearch  = $isSearching && $request->boolean('full_search');

        $allowed = ['id', 'customer_name', 'work_order_id', 'status', 'total', 'due_date', 'created_at'];
        $sort    = in_array($request->get('sort'), $allowed) ? $request->get('sort') : 'created_at';
        $dir     = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        // Per-tab counts (unfiltered — badges always reflect global state)
        $tabCounts = [
            'new'              => Invoice::where('status', Invoice::STATUS_DRAFT)->count(),
            'billed'           => Invoice::where('status', Invoice::STATUS_ISSUED)->count(),
            'payment_received' => Invoice::where('status', Invoice::STATUS_PAYMENT_RECEIVED)->count(),
            'all_active'       => Invoice::whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])->count(),
            'past_due'         => Invoice::where('status', Invoice::STATUS_ISSUED)
                                    ->whereNotNull('due_date')
                                    ->where('due_date', '<', today()->toDateString())
                                    ->count(),
            'completed'        => Invoice::where('status', Invoice::STATUS_COMPLETED)->count(),
        ];

        // Smart default: walk the saved priority order and land on first non-empty tab
        if (!$request->filled('tab') && !$isSearching && !$customerId) {
            $savedOrder   = AdminSetting::get('invoice_queue_order', 'past_due,new,billed,payment_received,all_active,completed');
            $priorityKeys = array_filter(array_map('trim', explode(',', $savedOrder)));
            $default      = 'new';
            foreach ($priorityKeys as $k) {
                if (in_array($k, $validTabs) && $k !== 'all_active' && ($tabCounts[$k] ?? 0) > 0) {
                    $default = $k;
                    break;
                }
            }
            return redirect()->route('admin.invoices.index', ['tab' => $default]);
        }

        $tab = in_array($request->get('tab'), $validTabs) ? $request->get('tab') : 'new';

        $query = Invoice::with(['workOrder.customer', 'workOrder.visits.timeEntries', 'workOrder.visits.signature', 'workOrder.visits.techUsers']);

        if (!$isSearching) {
            // No search — apply tab status filter
            match ($tab) {
                'new'              => $query->where('invoices.status', Invoice::STATUS_DRAFT),
                'billed'           => $query->where('invoices.status', Invoice::STATUS_ISSUED),
                'payment_received' => $query->where('invoices.status', Invoice::STATUS_PAYMENT_RECEIVED),
                'all_active'       => $query->whereIn('invoices.status', [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED]),
                'past_due'         => $query->where('invoices.status', Invoice::STATUS_ISSUED)
                                            ->whereNotNull('invoices.due_date')
                                            ->where('invoices.due_date', '<', today()->toDateString()),
                'completed'        => $query->where('invoices.status', Invoice::STATUS_COMPLETED),
                default            => null,
            };
        } elseif (!$fullSearch) {
            // Searching active invoices only (draft, issued, payment_received)
            $query->whereIn('invoices.status', [
                Invoice::STATUS_DRAFT,
                Invoice::STATUS_ISSUED,
                Invoice::STATUS_PAYMENT_RECEIVED,
            ]);
        }
        // fullSearch=true: no status filter — search all invoices including completed/canceled

        if ($customerId) {
            $query->whereHas('workOrder', fn($q) => $q->where('customer_id', $customerId));
        }

        if ($isSearching) {
            $invId = null;
            if (preg_match('/^(?:inv-?)?0*(\d+)$/i', $search, $m)) {
                $invId = (int) $m[1];
            }
            $woId = null;
            if (preg_match('/^(?:wo-?)?0*(\d+)$/i', $search, $m)) {
                $woId = (int) $m[1];
            }

            $query->where(function ($q) use ($search, $invId, $woId) {
                if ($invId) $q->orWhere('invoices.id', $invId);
                if ($woId)  $q->orWhere('invoices.work_order_id', $woId);
                $q->orWhereHas('workOrder.customer', fn($c) => $c->where('name', 'like', "%{$search}%")
                                                                   ->orWhere('email', 'like', "%{$search}%")
                                                                   ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($sort === 'customer_name') {
            $query->join('work_orders', 'invoices.work_order_id', '=', 'work_orders.id')
                  ->join('users', 'work_orders.customer_id', '=', 'users.id')
                  ->select('invoices.*')
                  ->orderBy('users.name', $dir);
        } else {
            $query->orderBy('invoices.' . $sort, $dir);
        }

        $invoices = $query->paginate(20)->withQueryString();

        if ($request->header('X-List-Request')) {
            return view('admin.invoices._list', compact('invoices', 'tab', 'sort', 'dir', 'search', 'customerId'));
        }

        return view('admin.invoices.index', compact(
            'invoices', 'tab', 'tabCounts', 'sort', 'dir',
            'search', 'customerId', 'fullSearch'
        ));
    }

    public function create(Request $request)
    {
        $settings = [
            'tax_rate_pct'  => (float) AdminSetting::get('default_tax_rate', '0.0750') * 100,
            'payment_terms' => AdminSetting::get('invoice_terms', AdminSetting::get('invoice_payment_terms', 'Net 30')),
            'footer_note'   => AdminSetting::get('invoice_footer', 'Thank you for your business.'),
            'due_days'      => (int) AdminSetting::get('invoice_due_days', '30'),
            'company_name'  => AdminSetting::get('company_name', 'DataTel'),
        ];

        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::with([
                'customer.companies',
                'serviceTypes',
                'completionSignature.collectedBy',
                'visits.signature',
            ])->findOrFail($request->work_order_id);

            $visitInvoiceMap = [];
            Invoice::where('work_order_id', $workOrder->id)
                ->whereNotNull('covered_visit_ids')
                ->get(['id', 'covered_visit_ids'])
                ->each(function ($inv) use (&$visitInvoiceMap) {
                    foreach ((array) $inv->covered_visit_ids as $vid) {
                        $visitInvoiceMap[(int) $vid] = $inv;
                    }
                });

            return view('admin.invoices.create', compact('workOrder', 'settings', 'visitInvoiceMap'));
        }

        $workOrders = WorkOrder::whereIn('status', [
            WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED,
            WorkOrder::STATUS_BILLED,
        ])->with('customer')->get();

        $workOrder = null;
        return view('admin.invoices.create', compact('workOrders', 'workOrder', 'settings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'work_order_id'       => 'required|exists:work_orders,id',
            'covered_visit_ids'   => 'nullable|array',
            'covered_visit_ids.*' => 'integer|exists:work_order_visits,id',
            'due_date'            => 'nullable|date',
            'payment_terms'       => 'nullable|string|max:1000',
            'footer_note'         => 'nullable|string|max:2000',
            'tax_rate_pct'        => 'required|numeric|min:0|max:100',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $taxRate   = round($data['tax_rate_pct'] / 100, 6);
        $subtotal  = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $taxAmount = round($subtotal * $taxRate, 2);
        $total     = round($subtotal + $taxAmount, 2);

        $invoice = Invoice::create([
            'work_order_id'     => $data['work_order_id'],
            'covered_visit_ids' => !empty($data['covered_visit_ids']) ? $data['covered_visit_ids'] : null,
            'status'            => Invoice::STATUS_DRAFT,
            'due_date'          => $data['due_date'] ?? null,
            'payment_terms'     => $data['payment_terms'] ?? null,
            'footer_note'       => $data['footer_note'] ?? null,
            'tax_rate'          => $taxRate,
            'subtotal'          => $subtotal,
            'tax_amount'        => $taxAmount,
            'total'             => $total,
            'created_by'        => auth()->id(),
        ]);

        foreach ($data['items'] as $i => $item) {
            $invoice->lineItems()->create([
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'sort_order'  => $i,
            ]);
        }

        InvoiceHistory::create([
            'invoice_id' => $invoice->id,
            'changed_by' => auth()->id(),
            'field_name' => 'status',
            'old_value'  => null,
            'new_value'  => Invoice::STATUS_DRAFT,
            'comment'    => 'Invoice created.',
            'changed_at' => now(),
        ]);

        $invoiceNum = 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
        $workOrder  = $invoice->workOrder;

        if ($workOrder->status === WorkOrder::STATUS_SERVICES_PERFORMED) {
            $workOrder->update(['status' => WorkOrder::STATUS_INVOICE_PREPARED, 'needs_invoice' => false]);
            WorkOrderHistory::create([
                'work_order_id' => $invoice->work_order_id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => WorkOrder::STATUS_SERVICES_PERFORMED,
                'new_value'     => WorkOrder::STATUS_INVOICE_PREPARED,
                'comment'       => "Invoice {$invoiceNum} created.",
                'changed_at'    => now(),
            ]);
        } else {
            $workOrder->update(['needs_invoice' => false]);
            WorkOrderHistory::create([
                'work_order_id' => $invoice->work_order_id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'note',
                'old_value'     => null,
                'new_value'     => null,
                'comment'       => "Additional invoice {$invoiceNum} created.",
                'changed_at'    => now(),
            ]);
        }

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['workOrder.customer', 'workOrder.visits.signature', 'workOrder.invoices', 'lineItems', 'signature', 'history.changedBy']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function printView(Invoice $invoice)
    {
        $invoice->load([
            'workOrder.customer',
            'workOrder.serviceTypes',
            'workOrder.completionSignature',
            'lineItems',
            'history',
        ]);

        $completedAt = $invoice->history
            ->where('field_name', 'status')
            ->where('new_value', 'completed')
            ->first()?->changed_at;

        $num      = 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
        $subTotal = (float)($invoice->subtotal  ?? $invoice->lineItems->sum(fn($i) => $i->quantity * $i->unit_price));
        $taxAmt   = (float)($invoice->tax_amount ?? round($subTotal * (float)($invoice->tax_rate ?? 0), 2));
        $total    = (float)($invoice->total     ?? round($subTotal + $taxAmt, 2));
        $company  = [
            'name'    => AdminSetting::get('company_name', 'DataTel'),
            'phone'   => AdminSetting::get('company_phone', ''),
            'email'   => AdminSetting::get('company_email', ''),
            'address' => AdminSetting::get('company_address', ''),
        ];

        return view('customer.invoices.print', compact(
            'invoice', 'num', 'subTotal', 'taxAmt', 'total', 'completedAt', 'company'
        ));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['lineItems', 'workOrder.customer.companies']);

        $customer = $invoice->workOrder?->customer;
        $company  = $customer?->companies->firstWhere('pivot.is_primary', true)
                    ?? $customer?->companies->first();

        $customerStats = null;
        if ($customer) {
            $custInvoices = Invoice::whereHas('workOrder', fn($q) => $q->where('customer_id', $customer->id))
                ->get(['id', 'status', 'total', 'covered_visit_ids']);

            $customerStats = [
                'collected'      => (float) $custInvoices->where('status', Invoice::STATUS_COMPLETED)->sum('total'),
                'unpaid'         => (float) $custInvoices->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])->sum('total'),
                'visitsBilled'   => $custInvoices->whereNotIn('status', [Invoice::STATUS_CANCELED])
                                        ->flatMap(fn($i) => (array) ($i->covered_visit_ids ?? []))
                                        ->unique()->count(),
                'completedCount' => $custInvoices->where('status', Invoice::STATUS_COMPLETED)->count(),
                'openCount'      => $custInvoices->whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])->count(),
            ];
        }

        return view('admin.invoices.edit', compact('invoice', 'company', 'customerStats'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->status, [
            Invoice::STATUS_PAYMENT_RECEIVED,
            Invoice::STATUS_COMPLETED,
            Invoice::STATUS_CANCELED,
        ])) {
            return back()->withErrors(['edit' => 'This invoice cannot be edited once payment has been received.']);
        }

        $data = $request->validate([
            'due_date'            => 'nullable|date',
            'payment_terms'       => 'nullable|string|max:2000',
            'footer_note'         => 'nullable|string|max:2000',
            'tax_rate_pct'        => 'nullable|numeric|min:0|max:100',
            'items'               => 'nullable|array',
            'items.*.description' => 'required_with:items|string|max:500',
            'items.*.quantity'    => 'required_with:items|numeric|min:0',
            'items.*.unit_price'  => 'required_with:items|numeric|min:0',
        ]);

        $updates = [
            'due_date'      => $data['due_date'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'footer_note'   => $data['footer_note'] ?? null,
        ];

        if (!empty($data['items'])) {
            $invoice->lineItems()->delete();
            $subtotal = 0;
            foreach ($data['items'] as $i => $item) {
                $invoice->lineItems()->create([
                    'description' => $item['description'],
                    'quantity'    => (float) $item['quantity'],
                    'unit_price'  => (float) $item['unit_price'],
                    'sort_order'  => $i,
                ]);
                $subtotal = round($subtotal + ((float)$item['quantity'] * (float)$item['unit_price']), 2);
            }
            $taxRate   = isset($data['tax_rate_pct']) ? round((float)$data['tax_rate_pct'] / 100, 6) : (float)$invoice->tax_rate;
            $taxAmount = round($subtotal * $taxRate, 2);
            $updates  += [
                'subtotal'   => $subtotal,
                'tax_rate'   => $taxRate,
                'tax_amount' => $taxAmount,
                'total'      => round($subtotal + $taxAmount, 2),
            ];
        }

        $invoice->update($updates);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validStatuses = [
            Invoice::STATUS_DRAFT,
            Invoice::STATUS_ISSUED,
            Invoice::STATUS_PAYMENT_RECEIVED,
            Invoice::STATUS_COMPLETED,
            Invoice::STATUS_CANCELED,
        ];

        $data = $request->validate([
            'status'                   => 'required|string|in:' . implode(',', $validStatuses),
            'cancel_reason'            => 'nullable|string|max:1000',
            'comment'                  => 'nullable|string|max:1000',
            'also_complete_work_order' => 'nullable|boolean',
            'transaction_reference'    => 'nullable|string|max:100',
        ]);

        if ($data['status'] === Invoice::STATUS_CANCELED && empty(trim($data['cancel_reason'] ?? ''))) {
            return back()->withErrors(['cancel_reason' => 'A cancellation reason is required.'])->withInput();
        }

        $oldStatus  = $invoice->status;
        $invoiceNum = 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);

        $updateData = ['status' => $data['status']];
        if ($data['status'] === Invoice::STATUS_CANCELED) {
            $updateData['cancel_reason'] = trim($data['cancel_reason']);
        }
        if ($data['status'] === Invoice::STATUS_COMPLETED && !empty($data['transaction_reference'])) {
            $updateData['transaction_reference'] = trim($data['transaction_reference']);
        }
        $invoice->update($updateData);

        // Write invoice audit trail
        InvoiceHistory::create([
            'invoice_id' => $invoice->id,
            'changed_by' => auth()->id(),
            'field_name' => 'status',
            'old_value'  => $oldStatus,
            'new_value'  => $data['status'],
            'comment'    => $data['status'] === Invoice::STATUS_CANCELED
                ? trim($data['cancel_reason'])
                : ($data['comment'] ?? null),
            'changed_at' => now(),
        ]);

        // Work order side-effects
        if ($data['status'] === Invoice::STATUS_ISSUED) {
            $workOrder   = $invoice->workOrder;
            $oldWoStatus = $workOrder->status;
            $workOrder->update(['status' => WorkOrder::STATUS_BILLED]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => $oldWoStatus,
                'new_value'     => WorkOrder::STATUS_BILLED,
                'comment'       => "Invoice {$invoiceNum} issued to customer.",
                'changed_at'    => now(),
            ]);
        }

        if ($data['status'] === Invoice::STATUS_COMPLETED && !empty($data['also_complete_work_order'])) {
            $workOrder   = $invoice->workOrder;
            $oldWoStatus = $workOrder->status;
            $workOrder->update(['status' => WorkOrder::STATUS_COMPLETED]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => $oldWoStatus,
                'new_value'     => WorkOrder::STATUS_COMPLETED,
                'comment'       => "Marked complete from invoice {$invoiceNum}.",
                'changed_at'    => now(),
            ]);
        }

        $statusLabel = match($data['status']) {
            Invoice::STATUS_ISSUED           => 'Issued',
            Invoice::STATUS_PAYMENT_RECEIVED => 'Payment Received',
            Invoice::STATUS_COMPLETED        => 'Completed',
            Invoice::STATUS_CANCELED         => 'Canceled',
            default                          => 'Draft',
        };

        return back()->with('success', 'Invoice status updated to "' . $statusLabel . '".');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted.');
    }
}
