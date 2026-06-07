<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerAnalyticsController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $query = User::where('role', 'customer')->where('status', 'active');

        // Work order number pattern: W42, WO42, WO-42, #42
        if (preg_match('/^(?:w(?:o)?[-\s]?|#)(\d+)$/i', $q, $m)) {
            $woId        = (int) $m[1];
            $customerIds = WorkOrder::where('id', $woId)->pluck('customer_id');
            $query->whereIn('id', $customerIds);
        } else {
            // Expand company matches (by name or phone) to all their active members
            $companyMemberIds = CompanyMember::whereIn(
                'company_id',
                Company::where('status', 'active')
                    ->where(fn($c) => $c->where('name', 'like', "%{$q}%")
                                        ->orWhere('phone', 'like', "%{$q}%"))
                    ->select('id')
            )->where('status', 'active')->pluck('user_id');

            $query->where(function ($inner) use ($q, $companyMemberIds) {
                $inner->where('name',  'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
                if ($companyMemberIds->isNotEmpty()) {
                    $inner->orWhereIn('id', $companyMemberIds);
                }
            });
        }

        $results = $query
            ->with(['companies' => fn($c) => $c->wherePivot('status', 'active')->wherePivot('is_primary', true)])
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json($results->map(fn($u) => [
            'id'      => $u->id,
            'name'    => $u->name,
            'email'   => $u->email,
            'phone'   => $u->phone,
            'company' => $u->companies->first()?->name,
        ]));
    }

    public function customer(Request $request)
    {
        $customers = User::where('role', 'customer')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'title']);

        $customer = null;
        $company  = null;
        $ytd      = $request->boolean('ytd', false);

        if ($request->filled('customer_id')) {
            $customer = User::where('role', 'customer')
                ->with(['companies' => fn($q) => $q->withPivot('is_primary', 'status')])
                ->findOrFail($request->integer('customer_id'));

            $company = $customer->companies()
                ->wherePivot('is_primary', true)
                ->wherePivot('status', 'active')
                ->first();
        }

        $companies        = Company::where('status', 'active')->orderBy('name')->get();
        $currentCompanyId = $customer ? $customer->companyMemberships()->where('status', 'active')->value('company_id') : null;

        if (!$customer) {
            return view('admin.analytics.customer', compact('customers', 'customer', 'company', 'ytd', 'companies', 'currentCompanyId'));
        }

        $woBase  = WorkOrder::where('customer_id', $customer->id);
        $invBase = Invoice::whereHas('workOrder', fn($q) => $q->where('customer_id', $customer->id));

        if ($ytd) {
            $woBase->whereYear('created_at', now()->year);
            $invBase->whereYear('created_at', now()->year);
        }

        $recentWorkOrders = (clone $woBase)
            ->with('serviceTypes:id,name')
            ->latest()
            ->limit(10)
            ->get();

        $topInvoices = (clone $invBase)
            ->with('workOrder:id')
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELED])
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $totalCompleted = (clone $woBase)
            ->where('status', WorkOrder::STATUS_COMPLETED)
            ->count();

        $metrics = (clone $invBase)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELED])
            ->selectRaw('AVG(total) as avg_total, MAX(total) as max_total, SUM(total) as sum_total')
            ->first();

        $avgInvoice     = round($metrics->avg_total    ?? 0, 2);
        $highestInvoice = round($metrics->max_total     ?? 0, 2);
        $totalRevenue   = round($metrics->sum_total     ?? 0, 2);

        $pendingPaymentOrders = (clone $woBase)
            ->whereHas('invoices', fn($q) => $q->where('status', Invoice::STATUS_ISSUED))
            ->count();

        $uncollectedRevenue = round(
            (clone $invBase)->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])->sum('total'),
            2
        );

        $hasPastDueInvoice = (clone $invBase)
            ->where('status', Invoice::STATUS_ISSUED)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->exists();

        $companyMembers = $company
            ? $company->members()
                ->where('company_members.status', 'active')
                ->orderBy('users.name')
                ->get(['users.id', 'users.name', 'users.email', 'users.phone', 'users.title'])
            : collect();

        $sites = CustomerAddress::where(function ($q) use ($customer, $company) {
                $q->where('user_id', $customer->id);
                if ($company) {
                    $q->orWhere('company_id', $company->id);
                }
            })
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        return view('admin.analytics.customer', compact(
            'customers', 'customer', 'company', 'ytd',
            'recentWorkOrders', 'topInvoices',
            'totalCompleted', 'avgInvoice', 'highestInvoice', 'totalRevenue',
            'pendingPaymentOrders', 'uncollectedRevenue', 'hasPastDueInvoice',
            'companyMembers', 'sites',
            'companies', 'currentCompanyId'
        ));
    }
}
