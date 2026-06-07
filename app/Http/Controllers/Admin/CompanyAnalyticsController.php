<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Models\WorkOrderVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyAnalyticsController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Company::where('status', 'active')
            ->where(fn($c) => $c
                ->where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('owner_name', 'like', "%{$q}%")
            )
            ->withCount(['members' => fn($q) => $q->wherePivot('status', 'active')])
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'phone', 'email', 'owner_name', 'address_city', 'address_state']);

        return response()->json($results->map(fn($c) => [
            'id'           => $c->id,
            'name'         => $c->name,
            'phone'        => $c->phone,
            'email'        => $c->email,
            'owner_name'   => $c->owner_name,
            'city'         => $c->address_city,
            'state'        => $c->address_state,
            'member_count' => $c->members_count,
        ]));
    }

    public function index(Request $request)
    {
        $company = null;
        $ytd     = $request->boolean('ytd', false);

        if ($request->filled('company_id')) {
            $company = Company::where('status', 'active')
                ->with([
                    'members'  => fn($q) => $q->wherePivot('status', 'active')->orderBy('users.name'),
                    'sites'    => fn($q) => $q->where('is_active', true)->orderByDesc('is_default')->orderBy('label'),
                ])
                ->findOrFail($request->integer('company_id'));
        }

        if (!$company) {
            return view('admin.analytics.company', compact('company', 'ytd'));
        }

        $memberIds = $company->members->pluck('id');

        $woBase  = WorkOrder::whereIn('customer_id', $memberIds);
        $invBase = Invoice::whereHas('workOrder', fn($q) => $q->whereIn('customer_id', $memberIds));

        if ($ytd) {
            $woBase->whereYear('created_at', now()->year);
            $invBase->whereYear('created_at', now()->year);
        }

        $totalWorkOrders  = (clone $woBase)->count();
        $totalCompleted   = (clone $woBase)->where('status', WorkOrder::STATUS_COMPLETED)->count();
        $activeWorkOrders = (clone $woBase)
            ->whereNotIn('status', [WorkOrder::STATUS_COMPLETED, WorkOrder::STATUS_CANCELED])
            ->count();

        $invoiceMetrics = (clone $invBase)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELED])
            ->selectRaw('SUM(total) as sum_total')
            ->first();

        $totalRevenue = round($invoiceMetrics->sum_total ?? 0, 2);

        $uncollectedRevenue = round(
            (clone $invBase)
                ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])
                ->sum('total'),
            2
        );

        $needsConfirmation = (clone $woBase)
            ->whereHas('visits', fn($v) => $v->where('confirmation_status', WorkOrderVisit::CONFIRMATION_PENDING))
            ->count();

        $needsInvoice = (clone $woBase)
            ->where('status', WorkOrder::STATUS_SERVICES_PERFORMED)
            ->count();

        $awaitingPayment = (clone $invBase)
            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])
            ->count();

        $pastDueCount = (clone $invBase)
            ->where('status', Invoice::STATUS_ISSUED)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $hasPendingFollowUp = $needsConfirmation > 0 || $needsInvoice > 0 || $pastDueCount > 0;

        $memberCompletedCounts = WorkOrder::whereIn('customer_id', $memberIds)
            ->where('status', WorkOrder::STATUS_COMPLETED)
            ->selectRaw('customer_id, COUNT(*) as cnt')
            ->groupBy('customer_id')
            ->pluck('cnt', 'customer_id');

        $recentWorkOrders = (clone $woBase)
            ->with(['serviceTypes:id,name', 'customer:id,name'])
            ->latest()
            ->limit(15)
            ->get();

        $openInvoices = (clone $invBase)
            ->with(['workOrder:id,customer_id', 'workOrder.customer:id,name'])
            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])
            ->orderBy('due_date')
            ->limit(15)
            ->get();

        return view('admin.analytics.company', compact(
            'company', 'ytd',
            'totalWorkOrders', 'totalCompleted', 'activeWorkOrders',
            'totalRevenue', 'uncollectedRevenue',
            'needsConfirmation', 'needsInvoice', 'awaitingPayment', 'pastDueCount',
            'hasPendingFollowUp',
            'recentWorkOrders', 'openInvoices',
            'memberCompletedCounts',
        ));
    }
}
