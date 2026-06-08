<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Powers the admin "My Analytics" dashboard.
 *
 * Each method returns a plain array shaped for ApexCharts (series + categories)
 * or a flat KPI bundle. All time-series methods gap-fill empty buckets so the
 * x-axis is continuous regardless of underlying data density. Bucketing is done
 * in PHP via CarbonPeriod for full SQLite/MySQL portability.
 */
class AnalyticsService
{
    private const RANGE_PRESETS = ['7d', '30d', 'month', 'ytd', 'custom'];
    private const DEFAULT_RANGE = 'month';

    /**
     * Resolve ?range= and (for 'custom') ?from=/?to= into a Carbon pair + label.
     * Falls back to "This Month" on invalid input.
     */
    public function resolveRange(Request $request): array
    {
        $range = $request->query('range', self::DEFAULT_RANGE);
        if (!in_array($range, self::RANGE_PRESETS, true)) {
            $range = self::DEFAULT_RANGE;
        }

        $today = Carbon::today();

        switch ($range) {
            case '7d':
                $from  = $today->copy()->subDays(6)->startOfDay();
                $to    = $today->copy()->endOfDay();
                $label = 'Last 7 Days';
                break;

            case '30d':
                $from  = $today->copy()->subDays(29)->startOfDay();
                $to    = $today->copy()->endOfDay();
                $label = 'Last 30 Days';
                break;

            case 'ytd':
                $from  = $today->copy()->startOfYear();
                $to    = $today->copy()->endOfDay();
                $label = 'Year to Date';
                break;

            case 'custom':
                try {
                    $from = Carbon::parse($request->query('from'))->startOfDay();
                    $to   = Carbon::parse($request->query('to'))->endOfDay();
                    if ($from->gt($to)) {
                        [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                    }
                    $label = $from->format('M j, Y') . ' – ' . $to->format('M j, Y');
                } catch (\Throwable $e) {
                    $range = self::DEFAULT_RANGE;
                    $from  = $today->copy()->startOfMonth();
                    $to    = $today->copy()->endOfDay();
                    $label = 'This Month';
                }
                break;

            case 'month':
            default:
                $from  = $today->copy()->startOfMonth();
                $to    = $today->copy()->endOfDay();
                $label = 'This Month';
                break;
        }

        return compact('range', 'from', 'to', 'label');
    }

    /**
     * Pick a bucket granularity from the range length.
     */
    public function pickBucket(Carbon $from, Carbon $to): string
    {
        $days = $from->diffInDays($to) + 1;
        return match (true) {
            $days <= 31  => 'day',
            $days <= 120 => 'week',
            default      => 'month',
        };
    }

    /**
     * Top-strip KPI snapshot.
     */
    public function kpis(Carbon $from, Carbon $to): array
    {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $activeWorkOrders = WorkOrder::whereNotIn('status', [
            WorkOrder::STATUS_COMPLETED,
            WorkOrder::STATUS_CANCELED,
        ])->count();

        $scheduledThisWeek = WorkOrder::whereBetween('scheduled_at', [$weekStart, $weekEnd])
            ->whereNotIn('status', [WorkOrder::STATUS_CANCELED, WorkOrder::STATUS_COMPLETED])
            ->count();

        // No completed_at column on work_orders — use updated_at as a proxy for completion time.
        $completedInPeriod = WorkOrder::where('status', WorkOrder::STATUS_COMPLETED)
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $revenueInPeriod = (float) Invoice::whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELED])
            ->whereBetween('created_at', [$from, $to])
            ->sum('total');

        $outstandingRevenue = (float) Invoice::whereIn('status', [
            Invoice::STATUS_ISSUED,
            Invoice::STATUS_PAYMENT_RECEIVED,
        ])->sum('total');

        $pastDueRevenue = (float) Invoice::where('status', Invoice::STATUS_ISSUED)
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::now()->toDateString())
            ->sum('total');

        $newCustomers = User::where('role', User::ROLE_CUSTOMER)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $newCompanies = Company::whereBetween('created_at', [$from, $to])->count();

        return [
            'activeWorkOrders'   => $activeWorkOrders,
            'scheduledThisWeek'  => $scheduledThisWeek,
            'completedInPeriod'  => $completedInPeriod,
            'revenueInPeriod'    => round($revenueInPeriod, 2),
            'outstandingRevenue' => round($outstandingRevenue, 2),
            'pastDueRevenue'     => round($pastDueRevenue, 2),
            'newCustomers'       => $newCustomers,
            'newCompanies'       => $newCompanies,
        ];
    }

    /**
     * WOs created vs completed per bucket — two-series line chart.
     */
    public function workOrderTimeSeries(Carbon $from, Carbon $to, string $bucket): array
    {
        $created   = WorkOrder::whereBetween('created_at', [$from, $to])->pluck('created_at');
        $completed = WorkOrder::where('status', WorkOrder::STATUS_COMPLETED)
            ->whereBetween('updated_at', [$from, $to])
            ->pluck('updated_at');

        $buckets = $this->buildBucketKeys($from, $to, $bucket);
        $created   = $this->bucketize($created,   $bucket, $buckets);
        $completed = $this->bucketize($completed, $bucket, $buckets);

        return [
            'categories' => array_values(array_map(fn($k) => $this->formatBucketLabel($k, $bucket), array_keys($buckets))),
            'series'     => [
                ['name' => 'Created',   'data' => array_values($created)],
                ['name' => 'Completed', 'data' => array_values($completed)],
            ],
            'bucket'     => $bucket,
        ];
    }

    /**
     * WO status distribution for the period — donut.
     */
    public function statusDistribution(Carbon $from, Carbon $to): array
    {
        $rows = WorkOrder::whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('COUNT(*) as n'))
            ->groupBy('status')
            ->pluck('n', 'status');

        $statuses = [
            WorkOrder::STATUS_NEW,
            WorkOrder::STATUS_TRIAGED,
            WorkOrder::STATUS_SCHEDULED,
            WorkOrder::STATUS_AWAITING_FEEDBACK,
            WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED,
            WorkOrder::STATUS_BILLED,
            WorkOrder::STATUS_COMPLETED,
            WorkOrder::STATUS_CANCELED,
        ];

        $labels = [];
        $series = [];
        foreach ($statuses as $s) {
            $count = (int) ($rows[$s] ?? 0);
            if ($count === 0) continue;
            $labels[] = ucwords(str_replace('_', ' ', $s));
            $series[] = $count;
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * Pipeline funnel — current snapshot of WOs at each pre-completed stage.
     * Excludes Canceled. Ignores date range (true pipeline view).
     */
    public function pipelineFunnel(): array
    {
        $rows = WorkOrder::select('status', DB::raw('COUNT(*) as n'))
            ->whereNotIn('status', [WorkOrder::STATUS_CANCELED])
            ->groupBy('status')
            ->pluck('n', 'status');

        $order = [
            WorkOrder::STATUS_NEW                => 'New',
            WorkOrder::STATUS_TRIAGED            => 'Triaged',
            WorkOrder::STATUS_SCHEDULED          => 'Scheduled',
            WorkOrder::STATUS_AWAITING_FEEDBACK  => 'Awaiting Feedback',
            WorkOrder::STATUS_SERVICES_PERFORMED => 'Services Performed',
            WorkOrder::STATUS_INVOICE_PREPARED   => 'Invoice Prepared',
            WorkOrder::STATUS_BILLED             => 'Billed',
            WorkOrder::STATUS_COMPLETED          => 'Completed',
        ];

        $labels = [];
        $series = [];
        foreach ($order as $key => $label) {
            $labels[] = $label;
            $series[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * WO urgency mix for the period — donut.
     */
    public function urgencyMix(Carbon $from, Carbon $to): array
    {
        $rows = WorkOrder::whereBetween('created_at', [$from, $to])
            ->select('urgency', DB::raw('COUNT(*) as n'))
            ->groupBy('urgency')
            ->pluck('n', 'urgency');

        $labels = [];
        $series = [];
        foreach (['routine', 'urgent', 'emergency'] as $u) {
            $n = (int) ($rows[$u] ?? 0);
            if ($n === 0) continue;
            $labels[] = ucfirst($u);
            $series[] = $n;
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * Revenue per bucket (Invoice.created_at) — bar chart.
     */
    public function revenueSeries(Carbon $from, Carbon $to, string $bucket): array
    {
        $rows = Invoice::whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELED])
            ->whereBetween('created_at', [$from, $to])
            ->get(['created_at', 'total']);

        $buckets = $this->buildBucketKeys($from, $to, $bucket);
        foreach ($rows as $row) {
            $key = $this->bucketKey($row->created_at, $bucket);
            if (array_key_exists($key, $buckets)) {
                $buckets[$key] += (float) $row->total;
            }
        }

        return [
            'categories' => array_values(array_map(fn($k) => $this->formatBucketLabel($k, $bucket), array_keys($buckets))),
            'series'     => [
                ['name' => 'Revenue', 'data' => array_values(array_map(fn($v) => round($v, 2), $buckets))],
            ],
            'bucket'     => $bucket,
        ];
    }

    /**
     * Invoice status distribution for the period — donut.
     */
    public function invoiceStatusDistribution(Carbon $from, Carbon $to): array
    {
        $rows = Invoice::whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('COUNT(*) as n'))
            ->groupBy('status')
            ->pluck('n', 'status');

        $order = [
            Invoice::STATUS_DRAFT            => 'Draft',
            Invoice::STATUS_ISSUED           => 'Issued',
            Invoice::STATUS_PAYMENT_RECEIVED => 'Payment Received',
            Invoice::STATUS_COMPLETED        => 'Completed',
            Invoice::STATUS_CANCELED         => 'Canceled',
        ];

        $labels = [];
        $series = [];
        foreach ($order as $key => $label) {
            $n = (int) ($rows[$key] ?? 0);
            if ($n === 0) continue;
            $labels[] = $label;
            $series[] = $n;
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * Top services by WO count in period — horizontal bar.
     */
    public function topServices(Carbon $from, Carbon $to, int $limit = 10): array
    {
        $rows = DB::table('work_order_services as wos')
            ->join('service_types as st', 'st.id', '=', 'wos.service_type_id')
            ->join('work_orders as wo', 'wo.id', '=', 'wos.work_order_id')
            ->whereBetween('wo.created_at', [$from, $to])
            ->select('st.name', DB::raw('COUNT(*) as n'))
            ->groupBy('st.name')
            ->orderByDesc('n')
            ->limit($limit)
            ->get();

        return [
            'categories' => $rows->pluck('name')->all(),
            'series'     => [
                ['name' => 'Work Orders', 'data' => $rows->pluck('n')->map(fn($v) => (int) $v)->all()],
            ],
        ];
    }

    /**
     * New customers per month over the trailing 12 months — line.
     * Ignores the user-selected range filter (long-horizon signal).
     */
    public function newCustomersTrailing12Months(): array
    {
        $end   = Carbon::now()->endOfMonth();
        $start = Carbon::now()->subMonths(11)->startOfMonth();

        $dates = User::where('role', User::ROLE_CUSTOMER)
            ->whereBetween('created_at', [$start, $end])
            ->pluck('created_at');

        $buckets = $this->buildBucketKeys($start, $end, 'month');
        $buckets = $this->bucketize($dates, 'month', $buckets);

        return [
            'categories' => array_values(array_map(fn($k) => $this->formatBucketLabel($k, 'month'), array_keys($buckets))),
            'series'     => [
                ['name' => 'New Customers', 'data' => array_values($buckets)],
            ],
        ];
    }

    /**
     * Per-tech completion leaderboard for the period.
     * Returns ['leaderboard' => [...rows], 'chart' => ['categories' => [...], 'series' => [...]]].
     */
    public function employeePerformance(Carbon $from, Carbon $to): array
    {
        // Pull row-level data once (portable across SQLite/MySQL) and aggregate in PHP.
        $rows = DB::table('work_orders as wo')
            ->join('work_order_assignments as woa', 'woa.work_order_id', '=', 'wo.id')
            ->where('wo.status', WorkOrder::STATUS_COMPLETED)
            ->whereBetween('wo.updated_at', [$from, $to])
            ->select('woa.user_id', 'wo.created_at', 'wo.updated_at')
            ->get();

        $buckets = [];
        foreach ($rows as $r) {
            $uid = (int) $r->user_id;
            if (!isset($buckets[$uid])) {
                $buckets[$uid] = ['completed' => 0, 'days_total' => 0.0, 'last' => null];
            }
            $created = Carbon::parse($r->created_at);
            $updated = Carbon::parse($r->updated_at);
            $buckets[$uid]['completed']++;
            $buckets[$uid]['days_total'] += $created->diffInDays($updated, false);
            if ($buckets[$uid]['last'] === null || $updated->gt($buckets[$uid]['last'])) {
                $buckets[$uid]['last'] = $updated;
            }
        }

        $users = User::whereIn('id', array_keys($buckets))->pluck('name', 'id');

        $leaderboard = collect($buckets)
            ->map(fn($b, $uid) => [
                'user_id'           => (int) $uid,
                'name'              => $users[$uid] ?? 'Unknown',
                'completed'         => $b['completed'],
                'avg_days'          => $b['completed'] > 0 ? round($b['days_total'] / $b['completed'], 1) : null,
                'last_completed_at' => $b['last'],
            ])
            ->sortByDesc('completed')
            ->values()
            ->all();

        return [
            'leaderboard' => $leaderboard,
            'chart'       => [
                'categories' => array_map(fn($r) => $r['name'],      $leaderboard),
                'series'     => [
                    ['name' => 'Completed', 'data' => array_map(fn($r) => $r['completed'], $leaderboard)],
                ],
            ],
        ];
    }

    // ── Internal helpers ──────────────────────────────────────────────

    /**
     * Build an ordered map of bucket-key => 0 spanning [$from, $to].
     */
    private function buildBucketKeys(Carbon $from, Carbon $to, string $bucket): array
    {
        $step = match ($bucket) {
            'day'   => '1 day',
            'week'  => '1 week',
            'month' => '1 month',
            default => '1 day',
        };

        $start = match ($bucket) {
            'week'  => $from->copy()->startOfWeek(Carbon::MONDAY),
            'month' => $from->copy()->startOfMonth(),
            default => $from->copy()->startOfDay(),
        };
        $end = match ($bucket) {
            'week'  => $to->copy()->startOfWeek(Carbon::MONDAY),
            'month' => $to->copy()->startOfMonth(),
            default => $to->copy()->startOfDay(),
        };

        $out = [];
        foreach (CarbonPeriod::create($start, $step, $end) as $date) {
            $out[$this->bucketKey($date, $bucket)] = 0;
        }
        return $out;
    }

    /**
     * Tally an iterable of Carbon dates into the supplied bucket map.
     */
    private function bucketize(iterable $dates, string $bucket, array $buckets): array
    {
        foreach ($dates as $d) {
            $c = $d instanceof Carbon ? $d : Carbon::parse($d);
            $k = $this->bucketKey($c, $bucket);
            if (array_key_exists($k, $buckets)) {
                $buckets[$k]++;
            }
        }
        return $buckets;
    }

    private function bucketKey(Carbon $date, string $bucket): string
    {
        return match ($bucket) {
            'week'  => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function formatBucketLabel(string $key, string $bucket): string
    {
        try {
            return match ($bucket) {
                'week'  => 'Wk of ' . Carbon::parse($key)->format('M j'),
                'month' => Carbon::createFromFormat('Y-m', $key)->format('M Y'),
                default => Carbon::parse($key)->format('M j'),
            };
        } catch (\Throwable $e) {
            return $key;
        }
    }
}
