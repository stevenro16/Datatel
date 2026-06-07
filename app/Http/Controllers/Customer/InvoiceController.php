<?php

namespace App\Http\Controllers\Customer;

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
        $user = auth()->user();
        $view = in_array($request->get('view'), ['active', 'completed', 'all'])
            ? $request->get('view') : 'active';

        $workOrderIds = WorkOrder::where('customer_id', $user->id)->pluck('id');

        $query = Invoice::whereIn('work_order_id', $workOrderIds)
            ->with(['workOrder']);

        if ($view === 'active') {
            $query->whereIn('status', [
                Invoice::STATUS_DRAFT,
                Invoice::STATUS_ISSUED,
                Invoice::STATUS_PAYMENT_RECEIVED,
            ]);
        } elseif ($view === 'completed') {
            $query->where('status', Invoice::STATUS_COMPLETED);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('customer.invoices.index', compact('invoices', 'view'));
    }

    public function show(Invoice $invoice)
    {
        abort_if($invoice->workOrder->customer_id !== auth()->id(), 403);
        $invoice->load(['workOrder.visits', 'lineItems']);
        return view('customer.invoices.show', compact('invoice'));
    }

    public function printView(Invoice $invoice)
    {
        abort_if($invoice->workOrder->customer_id !== auth()->id(), 403);
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

    public function submitPayment(Invoice $invoice)
    {
        abort_if($invoice->workOrder->customer_id !== auth()->id(), 403);
        abort_if($invoice->status !== Invoice::STATUS_ISSUED, 422);

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
            'work_order_id' => $invoice->work_order_id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'invoice_status',
            'old_value'     => Invoice::STATUS_ISSUED,
            'new_value'     => Invoice::STATUS_PAYMENT_RECEIVED,
            'comment'       => "Customer confirmed payment submitted for {$invoiceNum}.",
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Thank you! Your payment confirmation has been received. We will verify and complete your order shortly.');
    }
}
