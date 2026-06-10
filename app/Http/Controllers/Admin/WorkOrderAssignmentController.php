<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use Illuminate\Http\Request;

/**
 * Admin employee-assignment endpoints for work orders.
 * Split out of WorkOrderController to keep each controller cohesive (M2.4).
 */
class WorkOrderAssignmentController extends Controller
{
    public function assignEmployee(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);

        if (!$workOrder->assignments()->where('user_id', $data['user_id'])->exists()) {
            $workOrder->assignments()->create([
                'user_id'     => $data['user_id'],
                'assigned_by' => auth()->id(),
            ]);

            $employee = User::find($data['user_id']);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'employee',
                'new_value'     => $employee->name . ' assigned',
                'changed_at'    => now(),
            ]);

            if ($workOrder->status === WorkOrder::STATUS_NEW) {
                $workOrder->update(['status' => WorkOrder::STATUS_TRIAGED]);
                WorkOrderHistory::create([
                    'work_order_id' => $workOrder->id,
                    'changed_by'    => auth()->id(),
                    'field_name'    => 'status',
                    'old_value'     => WorkOrder::STATUS_NEW,
                    'new_value'     => WorkOrder::STATUS_TRIAGED,
                    'comment'       => 'Auto-advanced to Triaged on employee assignment.',
                    'changed_at'    => now(),
                ]);
            }
        }

        return back()->with('success', 'Employee assigned.');
    }

    public function unassignEmployee(WorkOrder $workOrder, User $user)
    {
        $workOrder->assignments()->where('user_id', $user->id)->get()->each->delete();

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'employee',
            'new_value'     => $user->name . ' unassigned',
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Employee removed.');
    }
}
