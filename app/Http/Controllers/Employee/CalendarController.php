<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssignment;
use App\Models\WorkOrderVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $view = $request->input('view', 'day');

        try {
            $focusDate = Carbon::parse($request->input('date', today()->toDateString()))->startOfDay();
        } catch (\Exception $e) {
            $focusDate = today()->startOfDay();
        }

        // Visits where: this employee is explicitly assigned via visit-level techs,
        // OR the visit has no explicit techs and this employee is assigned at the WO level.
        $baseVisitQuery = fn () => WorkOrderVisit::where(function ($q) use ($user) {
            $q->whereHas('techs', fn ($t) => $t->where('user_id', $user->id))
              ->orWhere(fn ($q2) => $q2
                  ->whereDoesntHave('techs')
                  ->whereHas('workOrder.assignments', fn ($a) => $a->where('user_id', $user->id))
              );
        })
        ->whereHas('workOrder', fn ($q) => $q->where('status', '!=', WorkOrder::STATUS_CANCELED))
        ->with(['workOrder.customer.companies', 'workOrder.serviceTypes', 'techs.user', 'signature']);

        $assignedIds = WorkOrderAssignment::where('user_id', $user->id)->pluck('work_order_id');

        if ($view === 'week') {
            $weekStart = $focusDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();

            $weekVisits = $baseVisitQuery()
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->orderBy('scheduled_at')
                ->get();

            $days = collect(range(0, 6))->map(fn ($i) => [
                'date'   => $weekStart->copy()->addDays($i),
                'visits' => $weekVisits->filter(
                    fn ($v) => $v->scheduled_at->isSameDay($weekStart->copy()->addDays($i))
                )->values(),
            ]);

            $todayVisits = $weekVisits->filter(fn ($v) => $v->scheduled_at->isSameDay($focusDate))->values();

            $orders = WorkOrder::whereIn('id', $assignedIds)
                ->where('status', '!=', WorkOrder::STATUS_CANCELED)
                ->with(['customer.companies', 'serviceTypes'])
                ->get();

            return view('employee.calendar', compact('view', 'focusDate', 'weekStart', 'weekEnd', 'days', 'todayVisits', 'orders'));
        }

        // Day view
        $visits = $baseVisitQuery()
            ->whereDate('scheduled_at', $focusDate)
            ->orderBy('scheduled_at')
            ->get();

        $orders = WorkOrder::whereIn('id', $assignedIds)
            ->where('status', '!=', WorkOrder::STATUS_CANCELED)
            ->with(['customer.companies', 'serviceTypes'])
            ->get();

        return view('employee.calendar', compact('view', 'focusDate', 'visits', 'orders'));
    }
}
