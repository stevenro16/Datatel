<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    const TECH_COLORS = [
        '#2E86C1', '#E74C3C', '#27AE60', '#F39C12',
        '#8E44AD', '#16A085', '#D35400', '#6D4C41',
        '#0097A7', '#C0392B',
    ];

    public function index(Request $request)
    {
        $view   = in_array($request->get('view'), ['day', 'week', 'month']) ? $request->get('view') : 'week';
        $wkDays = in_array((int) $request->get('days'), [5, 7]) ? (int) $request->get('days') : 5;
        $anchor = $request->filled('date') ? Carbon::parse($request->get('date')) : Carbon::today();

        [$start, $end, $prev, $next, $label] = $this->range($view, $anchor, $wkDays);

        $scheduled = WorkOrderVisit::whereHas('workOrder', fn($q) => $q->whereIn('status', [
                WorkOrder::STATUS_TRIAGED,
                WorkOrder::STATUS_SCHEDULED,
                WorkOrder::STATUS_AWAITING_FEEDBACK,
                WorkOrder::STATUS_SERVICES_PERFORMED,
            ]))
            ->with(['workOrder.customer', 'workOrder.serviceTypes', 'workOrder.assignments.employee', 'techUsers', 'signature'])
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at')
            ->get();

        $employees = User::where('role', User::ROLE_EMPLOYEE)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $techColors = [];
        foreach ($employees as $i => $emp) {
            $techColors[$emp->id] = self::TECH_COLORS[$i % count(self::TECH_COLORS)];
        }

        return view('admin.calendar', compact(
            'scheduled', 'employees', 'techColors',
            'view', 'anchor', 'start', 'end', 'prev', 'next', 'label', 'wkDays'
        ));
    }

    private function range(string $view, Carbon $anchor, int $wkDays = 5): array
    {
        switch ($view) {
            case 'day':
                $start = $anchor->copy()->startOfDay();
                $end   = $anchor->copy()->endOfDay();
                $prev  = $anchor->copy()->subDay()->format('Y-m-d');
                $next  = $anchor->copy()->addDay()->format('Y-m-d');
                $label = $anchor->format('l, F j, Y');
                break;

            case 'month':
                $start = $anchor->copy()->startOfMonth()->startOfDay();
                $end   = $anchor->copy()->endOfMonth()->endOfDay();
                $prev  = $anchor->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $next  = $anchor->copy()->addMonth()->startOfMonth()->format('Y-m-d');
                $label = $anchor->format('F Y');
                break;

            default: // week
                if ($wkDays === 5) {
                    $start = $anchor->copy()->startOfWeek(Carbon::MONDAY);
                    $end   = $start->copy()->addDays(4)->endOfDay();
                    $prev  = $start->copy()->subWeek()->format('Y-m-d');
                    $next  = $start->copy()->addWeek()->format('Y-m-d');
                } else {
                    $start = $anchor->copy()->startOfWeek(Carbon::SUNDAY);
                    $end   = $anchor->copy()->endOfWeek(Carbon::SATURDAY)->endOfDay();
                    $prev  = $start->copy()->subDay()->format('Y-m-d');
                    $next  = $end->copy()->addDay()->format('Y-m-d');
                }
                $label = $start->format('M j') . ' – ' . $end->format('M j, Y');
                break;
        }

        return [$start, $end, $prev, $next, $label];
    }
}
