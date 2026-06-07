<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use App\Models\WorkOrderSignature;
use App\Models\WorkOrderVisit;
use App\Models\WorkOrderVisitSignature;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function show(WorkOrder $workOrder)
    {
        abort_if(
            !$workOrder->assignments()->where('user_id', auth()->id())->exists(),
            403
        );

        $workOrder->load([
            'customer',
            'serviceTypes',
            'assignments.employee',
            'notes.author',
            'attachments',
            'history.changedBy',
            'completionSignature.collectedBy',
        ]);

        $timeEntry = TimeEntry::where('work_order_id', $workOrder->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('employee.work-orders.show', compact('workOrder', 'timeEntry'));
    }

    public function recordArrival(Request $request, WorkOrder $workOrder)
    {
        abort_if(
            !$workOrder->assignments()->where('user_id', auth()->id())->exists(),
            403
        );

        $data = $request->validate(['clocked_in_at' => 'required|date']);

        TimeEntry::updateOrCreate(
            ['work_order_id' => $workOrder->id, 'user_id' => auth()->id()],
            ['clocked_in_at' => $data['clocked_in_at']]
        );

        return back()->with('success', 'Arrival time recorded.');
    }

    public function recordDeparture(Request $request, WorkOrder $workOrder)
    {
        abort_if(
            !$workOrder->assignments()->where('user_id', auth()->id())->exists(),
            403
        );

        $data = $request->validate(['clocked_out_at' => 'required|date']);

        $entry = TimeEntry::where('work_order_id', $workOrder->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if (!$entry) {
            return back()->withErrors(['clocked_out_at' => 'Please record your arrival time first.']);
        }

        $entry->update(['clocked_out_at' => $data['clocked_out_at']]);

        return back()->with('success', 'Departure time recorded.');
    }

    public function confirmCustomer(Request $request, WorkOrder $workOrder)
    {
        abort_if(
            !$workOrder->assignments()->where('user_id', auth()->id())->exists(),
            403
        );

        $data = $request->validate([
            'confirmation_note' => 'required|string|min:5|max:1000',
        ]);

        $oldStatus       = $workOrder->status;
        $oldConfirmation = $workOrder->confirmation_status;

        $preScheduledStatuses = [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED];
        $shouldAdvance = in_array($oldStatus, $preScheduledStatuses);

        $updates = ['confirmation_status' => WorkOrder::CONFIRMATION_CONFIRMED];
        if ($shouldAdvance) {
            $updates['status'] = WorkOrder::STATUS_SCHEDULED;
        }

        $workOrder->update($updates);

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'confirmation_status',
            'old_value'     => $oldConfirmation,
            'new_value'     => WorkOrder::CONFIRMATION_CONFIRMED,
            'comment'       => 'Field tech confirmed visit with customer. ' . $data['confirmation_note'],
            'changed_at'    => now(),
        ]);

        if ($shouldAdvance) {
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => $oldStatus,
                'new_value'     => WorkOrder::STATUS_SCHEDULED,
                'comment'       => 'Status advanced to Scheduled after field tech confirmed visit with customer.',
                'changed_at'    => now(),
            ]);
        }

        $msg = $shouldAdvance
            ? $workOrder->woLabel() . ' confirmed and marked Scheduled.'
            : $workOrder->woLabel() . ' visit confirmed.';

        return back()->with('success', $msg);
    }

    public function storeNote(Request $request, WorkOrder $workOrder)
    {
        abort_if(
            !$workOrder->assignments()->where('user_id', auth()->id())->exists(),
            403
        );

        $data = $request->validate([
            'body'       => 'required|string|min:1',
            'visibility' => 'required|in:internal,customer',
        ]);

        $workOrder->notes()->create([
            'user_id'    => auth()->id(),
            'body'       => $data['body'],
            'visibility' => $data['visibility'],
        ]);

        return back()->with('success', 'Note added.');
    }

    public function complete(Request $request, WorkOrder $workOrder)
    {
        abort_if(
            !$workOrder->assignments()->where('user_id', auth()->id())->exists(),
            403
        );
        if (in_array($workOrder->status, [
            WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED,
            WorkOrder::STATUS_BILLED,
            WorkOrder::STATUS_COMPLETED,
            WorkOrder::STATUS_CANCELED,
        ])) {
            return redirect()->route('employee.work-orders.show', $workOrder)
                ->with('info', 'This work order has already been marked complete.');
        }

        $data = $request->validate([
            'signer_name'    => 'required|string|max:255',
            'signature_data' => 'required|string',
        ]);

        // Decode and save the signature image
        $imageData   = preg_replace('/^data:image\/\w+;base64,/', '', $data['signature_data']);
        $imageBinary = base64_decode($imageData);

        if (!$imageBinary) {
            return back()->withErrors(['signature_data' => 'Invalid signature. Please sign again.']);
        }

        $sigDir = storage_path('app/signatures/work-orders');
        if (!is_dir($sigDir)) {
            mkdir($sigDir, 0775, true);
        }

        $filename = 'wo-' . $workOrder->id . '-' . time() . '.png';
        file_put_contents($sigDir . '/' . $filename, $imageBinary);

        // Create signature record
        WorkOrderSignature::create([
            'work_order_id'  => $workOrder->id,
            'signer_name'    => $data['signer_name'],
            'signature_path' => $filename,
            'collected_by'   => auth()->id(),
            'ip_address'     => $request->ip(),
            'signed_at'      => now(),
        ]);

        // Update status
        $oldStatus = $workOrder->status;
        $workOrder->update(['status' => WorkOrder::STATUS_SERVICES_PERFORMED]);

        // Record in history
        $signedAt = now()->format('M j, Y \a\t g:i A');
        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'status',
            'old_value'     => $oldStatus,
            'new_value'     => WorkOrder::STATUS_SERVICES_PERFORMED,
            'comment'       => "Work completed. Customer signature collected from \"{$data['signer_name']}\" on {$signedAt}.",
            'changed_at'    => now(),
        ]);

        return back()->with('success', 'Work marked as complete and customer signature recorded.');
    }

    // ── Per-visit methods ────────────────────────────────────────────────────────

    public function showVisit(WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($visit->work_order_id !== $workOrder->id, 403);
        abort_if(!$this->employeeCanAccessVisit($workOrder, $visit), 403);

        $workOrder->load(['customer', 'serviceTypes', 'notes.author', 'visits.techs.user', 'visits.signature']);
        $visit->load(['techs.user', 'signature.collectedBy']);

        $timeEntry = TimeEntry::where('visit_id', $visit->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('employee.work-orders.visit', compact('workOrder', 'visit', 'timeEntry'));
    }

    public function recordVisitArrival(Request $request, WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($visit->work_order_id !== $workOrder->id, 403);
        abort_if(!$this->employeeCanAccessVisit($workOrder, $visit), 403);

        $data = $request->validate(['clocked_in_at' => 'required|date']);

        TimeEntry::updateOrCreate(
            ['work_order_id' => $workOrder->id, 'visit_id' => $visit->id, 'user_id' => auth()->id()],
            ['clocked_in_at' => $data['clocked_in_at']]
        );

        return back()->with('success', 'Arrival recorded.');
    }

    public function recordVisitDeparture(Request $request, WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($visit->work_order_id !== $workOrder->id, 403);
        abort_if(!$this->employeeCanAccessVisit($workOrder, $visit), 403);

        $data = $request->validate(['clocked_out_at' => 'required|date']);

        $entry = TimeEntry::where('visit_id', $visit->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if (!$entry) {
            return back()->withErrors(['clocked_out_at' => 'Please record your arrival time first.']);
        }

        $entry->update(['clocked_out_at' => $data['clocked_out_at']]);

        return back()->with('success', 'Departure recorded.');
    }

    public function completeVisit(Request $request, WorkOrder $workOrder, WorkOrderVisit $visit)
    {
        abort_if($visit->work_order_id !== $workOrder->id, 403);
        abort_if(!$this->employeeCanAccessVisit($workOrder, $visit), 403);

        if ($visit->signature) {
            return back()->with('info', 'This visit has already been signed.');
        }

        $data = $request->validate([
            'signer_name'    => 'required|string|max:255',
            'signature_data' => 'required|string',
        ]);

        $imageData   = preg_replace('/^data:image\/\w+;base64,/', '', $data['signature_data']);
        $imageBinary = base64_decode($imageData);

        if (!$imageBinary) {
            return back()->withErrors(['signature_data' => 'Invalid signature. Please sign again.']);
        }

        $sigDir = storage_path('app/signatures/work-orders');
        if (!is_dir($sigDir)) {
            mkdir($sigDir, 0775, true);
        }

        $filename = 'wo-' . $workOrder->id . '-v' . $visit->id . '-' . time() . '.png';
        file_put_contents($sigDir . '/' . $filename, $imageBinary);

        WorkOrderVisitSignature::create([
            'visit_id'       => $visit->id,
            'work_order_id'  => $workOrder->id,
            'signer_name'    => $data['signer_name'],
            'signature_path' => $filename,
            'collected_by'   => auth()->id(),
            'ip_address'     => $request->ip(),
            'signed_at'      => now(),
        ]);

        // Signature acts as implicit customer verification — auto-confirm if still pending
        if ($visit->confirmation_status !== WorkOrderVisit::CONFIRMATION_CONFIRMED) {
            $oldConf = $visit->confirmation_status;
            $visit->update(['confirmation_status' => WorkOrderVisit::CONFIRMATION_CONFIRMED]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'confirmation_status',
                'old_value'     => $oldConf,
                'new_value'     => WorkOrderVisit::CONFIRMATION_CONFIRMED,
                'comment'       => "Visit on {$visit->scheduled_at->format('M j, Y')} auto-verified: signature collected from \"{$data['signer_name']}\".",
                'changed_at'    => now(),
            ]);
        }

        // Advance WO status to services_performed if not already at/past that stage
        $pastStatuses = [
            WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED,
            WorkOrder::STATUS_BILLED,
            WorkOrder::STATUS_COMPLETED,
            WorkOrder::STATUS_CANCELED,
        ];
        if (!in_array($workOrder->status, $pastStatuses)) {
            $oldStatus = $workOrder->status;
            $workOrder->update(['status' => WorkOrder::STATUS_SERVICES_PERFORMED]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'status',
                'old_value'     => $oldStatus,
                'new_value'     => WorkOrder::STATUS_SERVICES_PERFORMED,
                'comment'       => "Visit on {$visit->scheduled_at->format('M j, Y')} completed. Signature collected from \"{$data['signer_name']}\".",
                'changed_at'    => now(),
            ]);
        } else {
            // WO already has at least one invoice — flag it so admin sees it needs another
            $workOrder->update(['needs_invoice' => true]);
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'changed_by'    => auth()->id(),
                'field_name'    => 'note',
                'old_value'     => null,
                'new_value'     => null,
                'comment'       => "Visit on {$visit->scheduled_at->format('M j, Y')} completed. Signature collected from \"{$data['signer_name']}\". Flagged for additional invoice.",
                'changed_at'    => now(),
            ]);
        }

        return back()->with('success', 'Visit signed and marked complete.');
    }

    private function employeeCanAccessVisit(WorkOrder $workOrder, WorkOrderVisit $visit): bool
    {
        // Visit-level assignment takes precedence; fall back to WO-level for visits without explicit techs
        if ($visit->techs()->exists()) {
            return $visit->techs()->where('user_id', auth()->id())->exists();
        }
        return $workOrder->assignments()->where('user_id', auth()->id())->exists();
    }
}
