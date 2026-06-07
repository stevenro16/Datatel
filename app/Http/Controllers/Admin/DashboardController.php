<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\Inquiry;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderVisit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        $activeStatuses = [
            WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED, WorkOrder::STATUS_SCHEDULED,
            WorkOrder::STATUS_AWAITING_FEEDBACK, WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED, WorkOrder::STATUS_BILLED,
        ];

        // Open invoices (issued + payment received = uncollected)
        $openInvRow = Invoice::whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total), 0) as rev')
            ->first();
        $openInvoicesCount   = (int) ($openInvRow->cnt ?? 0);
        $openInvoicesRevenue = (float) ($openInvRow->rev ?? 0);

        // Past due invoices (issued, not yet paid, due date passed)
        $pastDueRow = Invoice::where('status', Invoice::STATUS_ISSUED)
            ->whereNotNull('due_date')
            ->where('due_date', '<', today()->toDateString())
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total), 0) as rev')
            ->first();
        $pastDueCount   = (int) ($pastDueRow->cnt ?? 0);
        $pastDueRevenue = (float) ($pastDueRow->rev ?? 0);

        // Work orders that have at least one visit awaiting confirmation
        $unconfirmedCount = WorkOrder::whereIn('status', $activeStatuses)
            ->whereHas('visits', fn($v) => $v->where('confirmation_status', WorkOrderVisit::CONFIRMATION_PENDING))
            ->count();

        // All active work orders — matches the nav bar badge
        $openWoCount    = WorkOrder::whereIn('status', $activeStatuses)->count();

        // Scheduled today
        $scheduledToday = WorkOrder::whereDate('scheduled_at', today())->count();

        // Weekly calendar: Mon–Fri of the requested week (defaults to current)
        $anchor    = $request->filled('week') ? Carbon::parse($request->input('week')) : Carbon::now();
        $weekStart = $anchor->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $weekStart->copy()->addDays(4)->endOfDay();
        $prevWeek  = $weekStart->copy()->subWeek()->format('Y-m-d');
        $nextWeek  = $weekStart->copy()->addWeek()->format('Y-m-d');
        $weekLabel = $weekStart->format('M j') . ' – ' . $weekEnd->format('M j, Y');

        $allowedSorts = ['wo_number', 'urgency', 'scheduled_at'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'scheduled_at';
        $dir  = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        $calView = in_array($request->input('cal_view'), ['week', 'day']) ? $request->input('cal_view') : 'week';

        $calendarStatuses = [
            WorkOrder::STATUS_TRIAGED, WorkOrder::STATUS_SCHEDULED,
            WorkOrder::STATUS_AWAITING_FEEDBACK, WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED, WorkOrder::STATUS_BILLED,
        ];

        // Week calendar — visits so each block shows visit-level techs
        $weekByDay = WorkOrderVisit::whereHas('workOrder', fn($q) => $q->whereIn('status', $calendarStatuses))
            ->with(['workOrder.customer.companies', 'workOrder.serviceTypes', 'workOrder.assignments.employee', 'techUsers'])
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn($v) => $v->scheduled_at->format('Y-m-d'));

        // Flat sorted visit list for the week schedule table (matches what the calendar shows)
        $urgMap = ['emergency' => 0, 'urgent' => 1, 'routine' => 2];
        $weekVisits = $weekByDay->flatten()->values();
        $weekVisits = match($sort) {
            'wo_number' => $dir === 'desc'
                ? $weekVisits->sortByDesc(fn($v) => $v->workOrder?->wo_number ?? 0)
                : $weekVisits->sortBy(fn($v) => $v->workOrder?->wo_number ?? 0),
            'urgency'   => $dir === 'desc'
                ? $weekVisits->sortByDesc(fn($v) => $urgMap[$v->workOrder?->urgency] ?? 1)
                : $weekVisits->sortBy(fn($v) => $urgMap[$v->workOrder?->urgency] ?? 1),
            default     => $dir === 'desc'
                ? $weekVisits->sortByDesc('scheduled_at')
                : $weekVisits->sortBy('scheduled_at'),
        };

        // Day view data
        $dayAnchor  = $request->filled('day') ? Carbon::parse($request->input('day')) : Carbon::today();
        $prevDay    = $dayAnchor->copy()->subDay()->format('Y-m-d');
        $nextDay    = $dayAnchor->copy()->addDay()->format('Y-m-d');
        $dayLabel   = $dayAnchor->format('l, F j, Y');

        // Day calendar — visits so each block shows visit-level techs
        $dayVisits = WorkOrderVisit::whereHas('workOrder', fn($q) => $q->whereIn('status', $calendarStatuses))
            ->with(['workOrder.customer.companies', 'workOrder.serviceTypes', 'workOrder.assignments.employee', 'techUsers'])
            ->whereNotNull('scheduled_at')
            ->whereDate('scheduled_at', $dayAnchor->toDateString())
            ->orderBy('scheduled_at')
            ->get();

        $calTechColors = ['#2E86C1','#E74C3C','#27AE60','#F39C12','#8E44AD','#16A085','#D35400','#6D4C41','#0097A7','#C0392B'];
        $employees = User::where('role', 'employee')->where('status', 'active')->orderBy('name')->get(['id', 'name', 'profile_photo']);
        $employeeTechColors = [];
        foreach ($employees as $i => $emp) {
            $employeeTechColors[$emp->id] = $calTechColors[$i % count($calTechColors)];
        }

        return view('admin.dashboard', compact(
            'openInvoicesCount', 'openInvoicesRevenue',
            'pastDueCount', 'pastDueRevenue',
            'unconfirmedCount', 'openWoCount', 'scheduledToday',
            'weekStart', 'weekLabel', 'prevWeek', 'nextWeek', 'weekVisits', 'weekByDay',
            'calView', 'dayAnchor', 'prevDay', 'nextDay', 'dayLabel', 'dayVisits',
            'employees', 'employeeTechColors',
            'sort', 'dir'
        ));
    }

    public function navCounts(): JsonResponse
    {
        $activeStatuses = [
            WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED, WorkOrder::STATUS_SCHEDULED,
            WorkOrder::STATUS_AWAITING_FEEDBACK, WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED, WorkOrder::STATUS_BILLED,
        ];

        return response()->json([
            'wo'      => WorkOrder::whereIn('status', $activeStatuses)->count(),
            'invoice' => Invoice::whereIn('status', [
                              Invoice::STATUS_DRAFT,
                              Invoice::STATUS_ISSUED,
                              Invoice::STATUS_PAYMENT_RECEIVED,
                          ])->count(),
            'pending' => User::where('role', 'customer')->where('status', 'pending')->count(),
            'inquiry' => Inquiry::where('status', 'new')->count(),
            'company' => Company::where('status', 'pending')->count()
                         + CompanyMember::where('status', 'pending')
                               ->whereHas('company', fn($q) => $q->where('status', 'active'))
                               ->count(),
        ]);
    }
}
