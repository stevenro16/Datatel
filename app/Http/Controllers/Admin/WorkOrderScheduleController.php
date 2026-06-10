<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use App\Models\WorkOrderVisit;
use App\Models\WorkOrderVisitTech;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Admin scheduling, visit management, and confirmation workflows for work orders.
 * Split out of WorkOrderController to keep each controller cohesive (M2.4).
 */
class WorkOrderScheduleController extends Controller
{
    public function requestConfirmation(WorkOrder $workOrder)
    {
        // Also push down to any upcoming visits that haven't been confirmed yet
        $workOrder->visits()
            ->where('scheduled_at', '>=', now())
            ->whereNull('confirmation_status')
            ->update(['confirmation_status' => WorkOrderVisit::CONFIRMATION_PENDING]);

        $workOrder->update(['confirmation_status' => WorkOrder::CONFIRMATION_PENDING]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'confirmation_status',
            'old_value'     => null,
            'new_value'     => WorkOrder::CONFIRMATION_PENDING,
            'comment'       => 'Admin requested customer confirmation of scheduled visit.',
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Customer confirmation request sent.');
    }

    public function overrideConfirmation(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate([
            'override_reason' => 'required|string|min:5|max:1000',
        ]);

        $oldStatus = $workOrder->status;
        $oldConfirmation = $workOrder->confirmation_status;

        $preScheduledStatuses = [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED];
        $advanceStatus = in_array($oldStatus, $preScheduledStatuses);

        $updates = ['confirmation_status' => WorkOrder::CONFIRMATION_CONFIRMED];
        if ($advanceStatus) {
            $updates['status'] = WorkOrder::STATUS_SCHEDULED;
        }

        $workOrder->update($updates);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'confirmation_status',
            'old_value'     => $oldConfirmation,
            'new_value'     => WorkOrder::CONFIRMATION_CONFIRMED,
            'comment'       => 'Admin verified visit directly. ' . $data['override_reason'],
            'changed_at'    => now(),
        ]);

        if ($advanceStatus) {
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => $oldStatus,
                'new_value'     => WorkOrder::STATUS_SCHEDULED,
                'comment'       => 'Status advanced to Scheduled after admin verified the visit.',
                'changed_at'    => now(),
            ]);
        }

        $msg = $advanceStatus
            ? 'Visit marked as verified. Work order moved to Scheduled.'
            : 'Visit marked as verified.';

        return back()->with('success', $msg);
    }

    public function updateSchedule(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate([
            'scheduled_date'            => 'required|date',
            'scheduled_time'            => 'required|date_format:H:i',
            'duration_estimate_minutes' => 'nullable|integer|min:15|max:480',
            'comment'                   => 'nullable|string|max:500',
            'employees_managed'         => 'nullable|boolean',
            'keep_employees'            => 'nullable|array',
            'keep_employees.*'          => 'integer|exists:users,id',
            'confirmation_action'       => 'nullable|in:confirmed,request',
        ]);

        $old   = $workOrder->scheduled_at?->format('Y-m-d H:i');
        $newDt = \Carbon\Carbon::parse($data['scheduled_date'] . ' ' . $data['scheduled_time']);

        $oldConfirmation = $workOrder->confirmation_status;

        $workOrder->update([
            'scheduled_at'              => $newDt,
            'duration_estimate_minutes' => $data['duration_estimate_minutes'] ?? null,
        ]);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'scheduled_at',
            'old_value'     => $old,
            'new_value'     => $newDt->format('Y-m-d H:i'),
            'comment'       => $data['comment'] ?? null,
            'changed_at'    => now(),
        ]);

        // If the tech checkbox column was shown, unassign any techs that were left unchecked
        if ($request->input('employees_managed') == '1') {
            $keepIds = collect($data['keep_employees'] ?? []);
            $toRemove = $workOrder->assignments()
                ->whereNotIn('user_id', $keepIds->all())
                ->with('employee')
                ->get();

            foreach ($toRemove as $assignment) {
                WorkOrderHistory::create([
                    'work_order_id' => $workOrder->id,
                    'changed_by'    => auth()->id(),
                    'field_name'    => 'employee',
                    'new_value'     => $assignment->employee->name . ' unassigned',
                    'comment'       => 'Removed via schedule save (unchecked on tech selector).',
                    'changed_at'    => now(),
                ]);
                $assignment->delete();
            }
        }

        $confirmMsg = '';
        $confirmAction = $data['confirmation_action'] ?? null;

        if ($confirmAction === 'confirmed') {
            $workOrder->update(['confirmation_status' => WorkOrder::CONFIRMATION_CONFIRMED]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'confirmation_status',
                'old_value'     => $oldConfirmation,
                'new_value'     => WorkOrder::CONFIRMATION_CONFIRMED,
                'comment'       => 'Admin marked visit as confirmed.',
                'changed_at'    => now(),
            ]);
            $confirmMsg = ' Visit marked as confirmed.';

            if (in_array($workOrder->status, [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED])) {
                $oldStatus = $workOrder->status;
                $workOrder->update(['status' => WorkOrder::STATUS_SCHEDULED]);
                WorkOrderHistory::create([
                    'work_order_id' => $workOrder->id,
                    'changed_by'    => auth()->id(),
                    'field_name'    => 'status',
                    'old_value'     => $oldStatus,
                    'new_value'     => WorkOrder::STATUS_SCHEDULED,
                    'comment'       => 'Status advanced to Scheduled after admin confirmed the visit.',
                    'changed_at'    => now(),
                ]);
            }
        } elseif ($confirmAction === 'request') {
            $workOrder->update(['confirmation_status' => WorkOrder::CONFIRMATION_PENDING]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'confirmation_status',
                'old_value'     => $oldConfirmation,
                'new_value'     => WorkOrder::CONFIRMATION_PENDING,
                'comment'       => 'Admin requested customer confirmation of scheduled visit.',
                'changed_at'    => now(),
            ]);
            $confirmMsg = ' Confirmation request sent to customer.';
        }

        return back()->with('success', 'Visit scheduled for '.$newDt->format('M j, Y \a\t g:i A').'.'.$confirmMsg);
    }

    public function techSchedule(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate([
            'date'       => 'required|date',
            'tech_ids'   => 'nullable|array',
            'tech_ids.*' => 'integer|exists:users,id',
            'visit_id'   => 'nullable|integer',
        ]);
        $date    = $data['date'];
        $visitId = $data['visit_id'] ?? null;

        // Use explicitly requested tech IDs if provided; otherwise fall back to WO assignments
        $ids = !empty($data['tech_ids'])
            ? collect($data['tech_ids'])->map(fn($id) => (int) $id)
            : $workOrder->assignments()->pluck('user_id');

        if ($ids->isEmpty()) {
            return response()->json([]);
        }

        // Collect all visits on that date for any WO assigned to one of these techs.
        // A visit "belongs to" a tech if either:
        //   (a) the visit has explicit visit-level techs and this tech is one of them, OR
        //   (b) the visit has no visit-level techs and the tech is on the WO's assignments.
        // When editing a specific visit, exclude only that visit — not the rest of its work order.
        $visits = WorkOrderVisit::with(['workOrder.assignments', 'techs'])
            ->whereDate('scheduled_at', $date)
            ->when($visitId, fn($q) => $q->where('id', '!=', $visitId))
            ->whereHas('workOrder', function ($q) {
                $q->whereNotIn('status', [WorkOrder::STATUS_CANCELED, WorkOrder::STATUS_COMPLETED]);
            })
            ->where(function ($q) use ($ids) {
                $q->whereHas('techs', fn ($t) => $t->whereIn('user_id', $ids))
                  ->orWhere(function ($q2) use ($ids) {
                      $q2->whereDoesntHave('techs')
                         ->whereHas('workOrder.assignments', fn ($aq) => $aq->whereIn('user_id', $ids));
                  });
            })
            ->get();

        $techs = User::whereIn('id', $ids)->orderBy('name')->get();

        $result = $techs->map(fn($tech) => [
            'id'     => $tech->id,
            'name'   => $tech->name,
            'orders' => $visits
                ->filter(function ($v) use ($tech) {
                    // Visit-level techs take precedence; fall back to WO-level assignments
                    if ($v->techs->isNotEmpty()) {
                        return $v->techs->contains('user_id', $tech->id);
                    }
                    return $v->workOrder->assignments->contains('user_id', $tech->id);
                })
                ->map(fn($v) => [
                    'id'        => $v->workOrder->id,
                    'wo_number' => $v->workOrder->wo_number,
                    'time'      => $v->scheduled_at->format('g:i A'),
                    'start_h'   => (int)$v->scheduled_at->format('G'),
                    'start_m'   => (int)$v->scheduled_at->format('i'),
                    'duration'  => $v->duration_estimate_minutes ?? 60,
                    'address'   => $v->workOrder->site_street ?? '',
                ])
                ->values(),
        ]);

        return response()->json($result);
    }

    public function travelTime(Request $request, WorkOrder $workOrder)
    {
        $from   = trim($request->get('from', ''));
        $techId = (string) $request->get('tech_id', '');
        $to     = trim($workOrder->site_street ?? '');
        $key    = config('services.ors.key');

        if (!$from || !$to || !$techId || !$key) {
            return response()->json(['error' => 'unavailable'], 422);
        }

        $formatText = function (int $minutes): string {
            $h = intdiv($minutes, 60);
            $m = $minutes % 60;
            return $h > 0 ? $h . ' hr' . ($m ? ' ' . $m . ' min' : '') : $m . ' min';
        };

        // Return stored result if this tech's from address hasn't changed
        $cache = $workOrder->travel_time_cache ?? [];
        if (isset($cache[$techId]) && $cache[$techId]['from'] === $from) {
            return response()->json([
                'minutes' => $cache[$techId]['minutes'],
                'text'    => $formatText($cache[$techId]['minutes']),
                'cached'  => true,
            ]);
        }

        try {
            // Geocode both addresses, caching results for 24 hours to save quota
            $geocode = function (string $address) use ($key): ?array {
                return cache()->remember('ors_geo_' . md5($address), 86400, function () use ($address, $key) {
                    $res = Http::timeout(5)->get('https://api.openrouteservice.org/geocode/search', [
                        'api_key' => $key,
                        'text'    => $address,
                        'size'    => 1,
                    ]);
                    $features = $res->json('features');
                    return !empty($features) ? $features[0]['geometry']['coordinates'] : null;
                });
            };

            $fromCoords = $geocode($from);
            $toCoords   = $geocode($to);

            if (!$fromCoords || !$toCoords) {
                return response()->json(['error' => 'geocode failed'], 422);
            }

            // Get driving duration from ORS Directions API
            $dir = Http::timeout(8)
                ->withHeaders(['Authorization' => $key, 'Content-Type' => 'application/json'])
                ->post('https://api.openrouteservice.org/v2/directions/driving-car', [
                    'coordinates' => [$fromCoords, $toCoords],
                ]);

            $seconds = $dir->json('routes.0.summary.duration');
            if ($seconds === null) {
                return response()->json(['error' => 'route failed'], 422);
            }

            $minutes = (int) round($seconds / 60);

            // Persist per-tech so future modal opens skip the API call
            $cache[$techId] = ['from' => $from, 'minutes' => $minutes];
            $workOrder->update(['travel_time_cache' => $cache]);

            return response()->json(['minutes' => $minutes, 'text' => $formatText($minutes)]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'unavailable'], 422);
        }
    }

    public function storeVisit(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate([
            'scheduled_date'            => 'required|date',
            'scheduled_time'            => 'required|date_format:H:i',
            'duration_estimate_minutes' => 'nullable|integer|min:15|max:480',
            'notes'                     => 'nullable|string|max:1000',
            'confirmation_action'       => 'nullable|in:confirmed,request',
        ]);

        $scheduledAt = \Carbon\Carbon::parse($data['scheduled_date'] . ' ' . $data['scheduled_time']);

        $visit = WorkOrderVisit::create([
            'work_order_id'             => $workOrder->id,
            'scheduled_at'              => $scheduledAt,
            'duration_estimate_minutes' => $data['duration_estimate_minutes'] ?? null,
            'notes'                     => $data['notes'] ?? null,
            'created_by'                => auth()->id(),
        ]);

        $workOrder->syncScheduledAt();

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'visit',
            'new_value'     => 'Visit scheduled: ' . $scheduledAt->format('M j, Y \a\t g:i A'),
            'changed_at'    => now(),
        ]);

        if ($request->input('employees_managed') == '1') {
            $techIds = collect($request->input('keep_employees', []))->map(fn($id) => (int) $id);
        } else {
            $techIds = $workOrder->assignments->pluck('user_id');
        }

        foreach ($techIds as $userId) {
            WorkOrderVisitTech::firstOrCreate(
                ['visit_id' => $visit->id, 'user_id' => $userId],
                ['assigned_by' => auth()->id()]
            );
        }

        $this->applyConfirmationAction($workOrder, $data['confirmation_action'] ?? null, $visit);

        return back()->with('success', 'Visit scheduled for ' . $scheduledAt->format('M j, Y \a\t g:i A') . '.');
    }

    public function updateVisit(Request $request, WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($visit->work_order_id !== $workOrder->id, 403);

        $data = $request->validate([
            'scheduled_date'            => 'required|date',
            'scheduled_time'            => 'required|date_format:H:i',
            'duration_estimate_minutes' => 'nullable|integer|min:15|max:480',
            'notes'                     => 'nullable|string|max:1000',
            'confirmation_action'       => 'nullable|in:confirmed,request',
        ]);

        $old         = $visit->scheduled_at->format('M j, Y \a\t g:i A');
        $scheduledAt = \Carbon\Carbon::parse($data['scheduled_date'] . ' ' . $data['scheduled_time']);

        $visit->update([
            'scheduled_at'              => $scheduledAt,
            'duration_estimate_minutes' => $data['duration_estimate_minutes'] ?? null,
            'notes'                     => $data['notes'] ?? null,
        ]);

        $workOrder->syncScheduledAt();

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'visit',
            'old_value'     => $old,
            'new_value'     => 'Visit rescheduled: ' . $scheduledAt->format('M j, Y \a\t g:i A'),
            'changed_at'    => now(),
        ]);

        if ($request->input('employees_managed') == '1') {
            $techIds = collect($request->input('keep_employees', []))->map(fn($id) => (int) $id);

            // Remove techs unchecked for this visit
            $visit->techs()->whereNotIn('user_id', $techIds->all())->delete();

            // Add newly checked techs
            foreach ($techIds as $userId) {
                WorkOrderVisitTech::firstOrCreate(
                    ['visit_id' => $visit->id, 'user_id' => $userId],
                    ['assigned_by' => auth()->id()]
                );
            }
        } elseif ($visit->techs()->doesntExist()) {
            // No explicit visit techs yet — seed from WO assignments
            foreach ($workOrder->assignments->pluck('user_id') as $userId) {
                WorkOrderVisitTech::firstOrCreate(
                    ['visit_id' => $visit->id, 'user_id' => $userId],
                    ['assigned_by' => auth()->id()]
                );
            }
        }

        $this->applyConfirmationAction($workOrder, $data['confirmation_action'] ?? null, $visit);

        return back()->with('success', 'Visit updated to ' . $scheduledAt->format('M j, Y \a\t g:i A') . '.');
    }

    public function destroyVisit(WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($visit->work_order_id !== $workOrder->id, 403);

        $label = $visit->scheduled_at->format('M j, Y \a\t g:i A');
        $visit->delete();
        $workOrder->syncScheduledAt();

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'visit',
            'old_value'     => 'Visit removed: ' . $label,
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Visit on ' . $label . ' removed.');
    }

    public function requestVisitConfirmation(WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($visit->work_order_id !== $workOrder->id, 403);

        $old = $visit->confirmation_status;
        $visit->update(['confirmation_status' => WorkOrderVisit::CONFIRMATION_PENDING]);
        $workOrder->syncConfirmationStatus();

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'visit_confirmation',
            'old_value'     => $old,
            'new_value'     => WorkOrderVisit::CONFIRMATION_PENDING,
            'comment'       => 'Admin requested customer confirmation for visit on ' . $visit->scheduled_at->format('M j, Y \a\t g:i A') . '.',
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Customer confirmation requested for visit on ' . $visit->scheduled_at->format('M j, Y') . '.');
    }

    public function adminConfirmVisit(Request $request, WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($visit->work_order_id !== $workOrder->id, 403);

        $data = $request->validate([
            'override_reason' => 'nullable|string|max:1000',
        ]);

        $old = $visit->confirmation_status;
        $visit->update([
            'confirmation_status' => WorkOrderVisit::CONFIRMATION_CONFIRMED,
            'confirmed_by'        => auth()->id(),
            'confirmed_at'        => now(),
        ]);
        $workOrder->syncConfirmationStatus();

        $comment = 'Admin verified visit on ' . $visit->scheduled_at->format('M j, Y \a\t g:i A') . '.';
        if (!empty($data['override_reason'])) {
            $comment .= ' ' . $data['override_reason'];
        }

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'visit_confirmation',
            'old_value'     => $old,
            'new_value'     => WorkOrderVisit::CONFIRMATION_CONFIRMED,
            'comment'       => $comment,
            'changed_at'    => now(),
        ]);

        // Advance WO status if still pre-scheduled
        if (in_array($workOrder->status, [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED])) {
            $oldStatus = $workOrder->status;
            $workOrder->update(['status' => WorkOrder::STATUS_SCHEDULED]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => $oldStatus,
                'new_value'     => WorkOrder::STATUS_SCHEDULED,
                'comment'       => 'Status advanced to Scheduled after admin verified the visit.',
                'changed_at'    => now(),
            ]);
        }

        return back()->with('success', 'Visit on ' . $visit->scheduled_at->format('M j, Y') . ' marked as verified.');
    }

    private function applyConfirmationAction(WorkOrder $workOrder, ?string $action, ?WorkOrderVisit $visit = null): void
    {
        if (!$action) {
            return;
        }

        $oldConfirmation = $workOrder->confirmation_status;

        if ($action === 'confirmed') {
            if ($visit) {
                $visit->update([
                    'confirmation_status' => WorkOrderVisit::CONFIRMATION_CONFIRMED,
                    'confirmed_by'        => auth()->id(),
                    'confirmed_at'        => now(),
                ]);
                $workOrder->syncConfirmationStatus();
            } else {
                $workOrder->update(['confirmation_status' => WorkOrder::CONFIRMATION_CONFIRMED]);
            }
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'confirmation_status',
                'old_value'     => $oldConfirmation,
                'new_value'     => WorkOrder::CONFIRMATION_CONFIRMED,
                'comment'       => 'Admin marked visit as confirmed.',
                'changed_at'    => now(),
            ]);
            if (in_array($workOrder->status, [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED])) {
                $oldStatus = $workOrder->status;
                $workOrder->update(['status' => WorkOrder::STATUS_SCHEDULED]);
                WorkOrderHistory::create([
                    'work_order_id' => $workOrder->id,
                    'changed_by'    => auth()->id(),
                    'field_name'    => 'status',
                    'old_value'     => $oldStatus,
                    'new_value'     => WorkOrder::STATUS_SCHEDULED,
                    'comment'       => 'Status advanced to Scheduled after admin confirmed the visit.',
                    'changed_at'    => now(),
                ]);
            }
        } elseif ($action === 'request') {
            if ($visit) {
                $visit->update(['confirmation_status' => WorkOrderVisit::CONFIRMATION_PENDING]);
                $workOrder->syncConfirmationStatus();
            } else {
                $workOrder->update(['confirmation_status' => WorkOrder::CONFIRMATION_PENDING]);
            }
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'confirmation_status',
                'old_value'     => $oldConfirmation,
                'new_value'     => WorkOrder::CONFIRMATION_PENDING,
                'comment'       => 'Admin requested customer confirmation of scheduled visit.',
                'changed_at'    => now(),
            ]);
        }
    }
}
