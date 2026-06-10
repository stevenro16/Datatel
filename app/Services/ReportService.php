<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\ServiceType;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the data sets behind the admin Reports section. Each public method
 * returns plain arrays/collections shaped for a print-oriented Blade view.
 *
 * Completion/billing timestamps are derived from work_order_history /
 * invoice_history (field_name = 'status'), since there are no dedicated
 * completed_at / paid_at columns.
 */
class ReportService
{
    /** Work-order statuses that mean the order is still open (not closed out). */
    private const OPEN_STATUSES = [
        WorkOrder::STATUS_NEW,
        WorkOrder::STATUS_TRIAGED,
        WorkOrder::STATUS_SCHEDULED,
        WorkOrder::STATUS_AWAITING_FEEDBACK,
        WorkOrder::STATUS_SERVICES_PERFORMED,
        WorkOrder::STATUS_INVOICE_PREPARED,
        WorkOrder::STATUS_BILLED,
    ];

    /** Invoice statuses that represent money not yet collected/closed. */
    private const RECEIVABLE_STATUSES = [
        Invoice::STATUS_ISSUED,
        Invoice::STATUS_PAYMENT_RECEIVED,
    ];

    // ─────────────────────────────────────────────────────────────────────────
    //  Work Orders
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Every work order created in the period, with status/urgency breakdowns.
     */
    public function workOrderSummary(Carbon $from, Carbon $to): array
    {
        $orders = WorkOrder::with(['customer', 'company', 'serviceTypes', 'assignedEmployees'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $completedIds = $this->completedWorkOrderIds($from, $to);

        return [
            'orders'       => $orders,
            'total'        => $orders->count(),
            'byStatus'     => $this->countByLabel($orders, fn ($o) => $o->statusLabel()),
            'byUrgency'    => $this->countByLabel($orders, fn ($o) => $o->urgencyLabel()),
            'completedNum' => $orders->whereIn('id', $completedIds)->count(),
        ];
    }

    /**
     * Snapshot of all open work orders, aged from creation date, grouped by status.
     */
    public function workOrderAging(): array
    {
        $orders = WorkOrder::with(['customer', 'assignedEmployees'])
            ->whereIn('status', self::OPEN_STATUSES)
            ->orderBy('created_at')
            ->get();

        $now = Carbon::now();
        $rows = $orders->map(function (WorkOrder $o) use ($now) {
            return [
                'order'         => $o,
                'ageDays'       => (int) $o->created_at->diffInDays($now),
                'scheduledDays' => $o->scheduled_at ? (int) $now->diffInDays($o->scheduled_at, false) : null,
            ];
        });

        // Group by status label, preserving the lifecycle order.
        $groups = [];
        foreach (self::OPEN_STATUSES as $status) {
            $statusRows = $rows->filter(fn ($r) => $r['order']->status === $status)
                ->sortByDesc('ageDays')
                ->values();
            if ($statusRows->isNotEmpty()) {
                $label = WorkOrder::STATUS_LABELS[$status] ?? $status;
                $groups[$label] = [
                    'rows'   => $statusRows,
                    'count'  => $statusRows->count(),
                    'oldest' => $statusRows->max('ageDays'),
                    'avgAge' => round($statusRows->avg('ageDays'), 1),
                ];
            }
        }

        return [
            'groups'     => $groups,
            'totalOpen'  => $rows->count(),
            'oldestDays' => $rows->max('ageDays') ?? 0,
            'avgAgeDays' => $rows->isNotEmpty() ? round($rows->avg('ageDays'), 1) : 0,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Invoicing
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * All invoices created in the period, with per-status totals.
     */
    public function invoiceRegister(Carbon $from, Carbon $to): array
    {
        $invoices = Invoice::with(['workOrder.customer', 'workOrder.company'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $byStatus = [];
        foreach ($invoices->groupBy('status') as $status => $group) {
            $byStatus[$this->invoiceStatusLabel($status)] = [
                'count'  => $group->count(),
                'total'  => (float) $group->sum(fn ($i) => (float) $i->total),
            ];
        }

        return [
            'invoices'  => $invoices,
            'count'     => $invoices->count(),
            'subtotal'  => (float) $invoices->sum(fn ($i) => (float) $i->subtotal),
            'taxTotal'  => (float) $invoices->sum(fn ($i) => (float) $i->tax_amount),
            'discount'  => (float) $invoices->sum(fn ($i) => (float) $i->discount),
            'grandTotal'=> (float) $invoices->sum(fn ($i) => (float) $i->total),
            'byStatus'  => $byStatus,
        ];
    }

    /**
     * Outstanding receivables (issued + payment-received), aged by due date
     * into standard buckets. Snapshot as of now.
     */
    public function accountsReceivable(): array
    {
        $invoices = Invoice::with(['workOrder.customer'])
            ->whereIn('status', self::RECEIVABLE_STATUSES)
            ->orderBy('due_date')
            ->get();

        $today   = Carbon::today();
        $buckets = [
            'Current'      => ['count' => 0, 'total' => 0.0, 'rows' => collect()],
            '1–30 Days'    => ['count' => 0, 'total' => 0.0, 'rows' => collect()],
            '31–60 Days'   => ['count' => 0, 'total' => 0.0, 'rows' => collect()],
            '61–90 Days'   => ['count' => 0, 'total' => 0.0, 'rows' => collect()],
            'Over 90 Days' => ['count' => 0, 'total' => 0.0, 'rows' => collect()],
        ];

        $rows = $invoices->map(function (Invoice $inv) use ($today) {
            $ref      = $inv->due_date ? Carbon::parse($inv->due_date) : Carbon::parse($inv->created_at);
            $pastDue  = $ref->lt($today) ? (int) $ref->diffInDays($today) : 0;
            $bucket   = match (true) {
                $pastDue <= 0  => 'Current',
                $pastDue <= 30 => '1–30 Days',
                $pastDue <= 60 => '31–60 Days',
                $pastDue <= 90 => '61–90 Days',
                default        => 'Over 90 Days',
            };

            return [
                'invoice' => $inv,
                'pastDue' => $pastDue,
                'bucket'  => $bucket,
                'amount'  => (float) $inv->total,
            ];
        });

        foreach ($rows as $r) {
            $buckets[$r['bucket']]['count']++;
            $buckets[$r['bucket']]['total'] += $r['amount'];
            $buckets[$r['bucket']]['rows']->push($r);
        }

        return [
            'buckets'     => $buckets,
            'rows'        => $rows->sortByDesc('pastDue')->values(),
            'totalOwed'   => (float) $rows->sum('amount'),
            'count'       => $rows->count(),
            'pastDueOwed' => (float) $rows->where('pastDue', '>', 0)->sum('amount'),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Technicians
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Per-technician productivity: assigned/open/completed work orders, hours
     * logged, and average days-to-complete over the period.
     */
    public function technicianProductivity(Carbon $from, Carbon $to): array
    {
        $techs = User::where('role', User::ROLE_EMPLOYEE)
            ->orderBy('name')
            ->get();

        $completedIds  = $this->completedWorkOrderIds($from, $to);
        $completedMap  = $this->completionTimestamps($from, $to); // woId => Carbon
        $woCreatedMap  = WorkOrder::whereIn('id', $completedIds)->pluck('created_at', 'id');

        $rows = $techs->map(function (User $tech) use ($from, $to, $completedIds, $completedMap, $woCreatedMap) {
            $completedForTech = $tech->assignedWorkOrders()
                ->whereIn('work_orders.id', $completedIds->all() ?: [0])
                ->pluck('work_orders.id');

            // Average days-to-complete across this tech's completed orders.
            $durations = $completedForTech->map(function ($woId) use ($completedMap, $woCreatedMap) {
                $done    = $completedMap[$woId] ?? null;
                $created = $woCreatedMap[$woId] ?? null;
                if (!$done || !$created) {
                    return null;
                }
                return Carbon::parse($created)->diffInDays(Carbon::parse($done));
            })->filter(fn ($d) => $d !== null);

            $minutes = TimeEntry::where('user_id', $tech->id)
                ->whereNotNull('clocked_out_at')
                ->whereBetween('clocked_in_at', [$from, $to])
                ->get()
                ->sum(fn (TimeEntry $e) => $e->totalMinutes() ?? 0);

            $openAssigned = $tech->assignedWorkOrders()
                ->whereIn('status', self::OPEN_STATUSES)
                ->count();

            return [
                'tech'        => $tech,
                'openOrders'  => $openAssigned,
                'completed'   => $completedForTech->count(),
                'hours'       => round($minutes / 60, 1),
                'avgDays'     => $durations->isNotEmpty() ? round($durations->avg(), 1) : null,
            ];
        });

        return [
            'rows'          => $rows,
            'totalCompleted'=> (int) $rows->sum('completed'),
            'totalHours'    => round($rows->sum('hours'), 1),
        ];
    }

    /**
     * Detailed labor log: every closed time entry in the period, grouped by tech.
     */
    public function technicianTime(Carbon $from, Carbon $to, ?int $techId = null): array
    {
        $entries = TimeEntry::with(['user', 'workOrder.customer'])
            ->whereNotNull('clocked_out_at')
            ->whereBetween('clocked_in_at', [$from, $to])
            ->when($techId, fn ($q) => $q->where('user_id', $techId))
            ->orderBy('clocked_in_at')
            ->get()
            ->filter(fn (TimeEntry $e) => $e->user && $e->user->role === User::ROLE_EMPLOYEE);

        $groups = [];
        foreach ($entries->groupBy('user_id') as $entriesForTech) {
            $tech = $entriesForTech->first()->user;
            $groups[] = [
                'tech'       => $tech,
                'entries'    => $entriesForTech->values(),
                'totalHours' => round($entriesForTech->sum(fn ($e) => $e->totalMinutes() ?? 0) / 60, 1),
            ];
        }

        return [
            'groups'     => collect($groups)->sortBy(fn ($g) => $g['tech']->name)->values(),
            'totalHours' => round($entries->sum(fn ($e) => $e->totalMinutes() ?? 0) / 60, 1),
            'entryCount' => $entries->count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Customers & Companies
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Per-customer activity statement for the period plus current balance owed.
     */
    public function customerStatement(Carbon $from, Carbon $to, ?int $customerId = null): array
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)
            ->when($customerId, fn ($q) => $q->where('id', $customerId))
            ->orderBy('name')
            ->get();

        $completedIds = $this->completedWorkOrderIds($from, $to);

        $rows = $customers->map(function (User $cust) use ($from, $to, $completedIds) {
            $woIds = WorkOrder::where('customer_id', $cust->id)->pluck('id');

            $periodWoCount = WorkOrder::where('customer_id', $cust->id)
                ->whereBetween('created_at', [$from, $to])
                ->count();

            $periodInvoices = Invoice::whereIn('work_order_id', $woIds)
                ->whereBetween('created_at', [$from, $to])
                ->get();

            $invoiced    = (float) $periodInvoices->sum(fn ($i) => (float) $i->total);
            $paid        = (float) $periodInvoices->whereIn('status', [Invoice::STATUS_PAYMENT_RECEIVED, Invoice::STATUS_COMPLETED])->sum(fn ($i) => (float) $i->total);

            $outstanding = (float) Invoice::whereIn('work_order_id', $woIds)
                ->whereIn('status', self::RECEIVABLE_STATUSES)
                ->get()->sum(fn ($i) => (float) $i->total);

            return [
                'customer'    => $cust,
                'company'     => $cust->companies->first(),
                'woCount'     => $periodWoCount,
                'completed'   => $woIds->intersect($completedIds)->count(),
                'invoiced'    => $invoiced,
                'paid'        => $paid,
                'outstanding' => $outstanding,
            ];
        })->filter(function ($r) use ($customerId) {
            // When listing everyone, hide customers with no activity and nothing owed.
            return $customerId !== null || $r['woCount'] > 0 || $r['invoiced'] > 0 || $r['outstanding'] > 0;
        })->values();

        return [
            'rows'            => $rows,
            'single'          => $customerId !== null ? $customers->first() : null,
            'totalInvoiced'   => (float) $rows->sum('invoiced'),
            'totalPaid'       => (float) $rows->sum('paid'),
            'totalOutstanding'=> (float) $rows->sum('outstanding'),
        ];
    }

    /**
     * Per-company performance for the period.
     */
    public function companyPerformance(Carbon $from, Carbon $to, ?int $companyId = null): array
    {
        $companies = Company::when($companyId, fn ($q) => $q->where('id', $companyId))
            ->orderBy('name')
            ->get();

        $completedIds = $this->completedWorkOrderIds($from, $to);

        $rows = $companies->map(function (Company $company) use ($from, $to, $completedIds) {
            $woIds = WorkOrder::where('company_id', $company->id)->pluck('id');

            $periodWos = WorkOrder::where('company_id', $company->id)
                ->whereBetween('created_at', [$from, $to])
                ->get();

            $periodInvoices = Invoice::whereIn('work_order_id', $woIds)
                ->whereBetween('created_at', [$from, $to])
                ->get();

            return [
                'company'     => $company,
                'woCount'     => $periodWos->count(),
                'open'        => $periodWos->whereIn('status', self::OPEN_STATUSES)->count(),
                'completed'   => $woIds->intersect($completedIds)->count(),
                'revenue'     => (float) $periodInvoices->sum(fn ($i) => (float) $i->total),
                'outstanding' => (float) Invoice::whereIn('work_order_id', $woIds)
                                    ->whereIn('status', self::RECEIVABLE_STATUSES)
                                    ->get()->sum(fn ($i) => (float) $i->total),
            ];
        })->filter(fn ($r) => $companyId !== null || $r['woCount'] > 0 || $r['revenue'] > 0 || $r['outstanding'] > 0)
          ->values();

        return [
            'rows'             => $rows,
            'single'           => $companyId !== null ? $companies->first() : null,
            'totalWorkOrders'  => (int) $rows->sum('woCount'),
            'totalCompleted'   => (int) $rows->sum('completed'),
            'totalRevenue'     => (float) $rows->sum('revenue'),
            'totalOutstanding' => (float) $rows->sum('outstanding'),
        ];
    }

    /**
     * Service catalog usage: how often each service appears on work orders in
     * the period, with completed counts and list value (price × usage).
     */
    public function serviceCatalogUsage(Carbon $from, Carbon $to): array
    {
        $services     = ServiceType::orderBy('sort_order')->orderBy('name')->get();
        $completedIds = $this->completedWorkOrderIds($from, $to);

        $rows = $services->map(function (ServiceType $svc) use ($from, $to, $completedIds) {
            $woIds = $svc->workOrders()
                ->whereBetween('work_orders.created_at', [$from, $to])
                ->pluck('work_orders.id');

            $price = (float) ($svc->default_unit_price ?? 0);

            return [
                'service'    => $svc,
                'usage'      => $woIds->count(),
                'completed'  => $woIds->intersect($completedIds)->count(),
                'price'      => $price,
                'listValue'  => $price * $woIds->count(),
            ];
        })->sortByDesc('usage')->values();

        return [
            'rows'           => $rows,
            'totalUsage'     => (int) $rows->sum('usage'),
            'totalListValue' => (float) $rows->sum('listValue'),
            'activeCount'    => $services->where('is_active', true)->count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** IDs of work orders that reached "completed" within the period. */
    private function completedWorkOrderIds(Carbon $from, Carbon $to): Collection
    {
        return WorkOrderHistory::where('field_name', 'status')
            ->where('new_value', WorkOrder::STATUS_COMPLETED)
            ->whereBetween('changed_at', [$from, $to])
            ->pluck('work_order_id')
            ->unique()
            ->values();
    }

    /** Map of work_order_id => completion Carbon (latest) within the period. */
    private function completionTimestamps(Carbon $from, Carbon $to): array
    {
        return WorkOrderHistory::where('field_name', 'status')
            ->where('new_value', WorkOrder::STATUS_COMPLETED)
            ->whereBetween('changed_at', [$from, $to])
            ->orderBy('changed_at')
            ->get()
            ->keyBy('work_order_id')
            ->map(fn ($h) => Carbon::parse($h->changed_at))
            ->all();
    }

    private function countByLabel(Collection $items, callable $labeler): array
    {
        $out = [];
        foreach ($items as $item) {
            $label = $labeler($item);
            $out[$label] = ($out[$label] ?? 0) + 1;
        }
        arsort($out);
        return $out;
    }

    private function invoiceStatusLabel(string $status): string
    {
        return match ($status) {
            Invoice::STATUS_DRAFT            => 'Draft',
            Invoice::STATUS_ISSUED           => 'Issued',
            Invoice::STATUS_PAYMENT_RECEIVED => 'Payment Received',
            Invoice::STATUS_COMPLETED        => 'Completed',
            Invoice::STATUS_CANCELED         => 'Canceled',
            default                          => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
