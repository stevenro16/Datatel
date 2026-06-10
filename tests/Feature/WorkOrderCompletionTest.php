<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employee = User::factory()->employee()->create();
        $this->admin    = User::factory()->admin()->create();
    }

    private function assignedWorkOrder(string $state = 'scheduled'): WorkOrder
    {
        $workOrder = WorkOrder::factory()->{$state}()->create();
        $workOrder->assignments()->create([
            'user_id'     => $this->employee->id,
            'assigned_by' => $this->admin->id,
        ]);

        return $workOrder;
    }

    private function signaturePayload(): array
    {
        return [
            'signer_name'    => 'Pat Customer',
            'signature_data' => 'data:image/png;base64,' . base64_encode('fake-png-bytes'),
        ];
    }

    private function cleanupSignatureFile(WorkOrder $workOrder): void
    {
        $sig = WorkOrderSignature::where('work_order_id', $workOrder->id)->first();
        if ($sig) {
            @unlink(storage_path('app/signatures/work-orders/' . $sig->signature_path));
        }
    }

    public function test_assigned_employee_can_complete_a_scheduled_work_order(): void
    {
        $workOrder = $this->assignedWorkOrder();

        $this->actingAs($this->employee)
            ->post(route('employee.work-orders.complete', $workOrder), $this->signaturePayload())
            ->assertRedirect();

        $workOrder->refresh();
        $this->assertSame(WorkOrder::STATUS_SERVICES_PERFORMED, $workOrder->status);

        $signature = $workOrder->completionSignature;
        $this->assertNotNull($signature);
        $this->assertSame('Pat Customer', $signature->signer_name);
        $this->assertSame($this->employee->id, $signature->collected_by);

        // Every status change writes an audit row.
        $this->assertDatabaseHas('work_order_history', [
            'work_order_id' => $workOrder->id,
            'changed_by'    => $this->employee->id,
            'field_name'    => 'status',
            'old_value'     => WorkOrder::STATUS_SCHEDULED,
            'new_value'     => WorkOrder::STATUS_SERVICES_PERFORMED,
        ]);

        $this->cleanupSignatureFile($workOrder);
    }

    public function test_completing_twice_does_not_create_a_second_signature(): void
    {
        $workOrder = $this->assignedWorkOrder();

        $this->actingAs($this->employee)
            ->post(route('employee.work-orders.complete', $workOrder), $this->signaturePayload());

        $this->actingAs($this->employee)
            ->post(route('employee.work-orders.complete', $workOrder), $this->signaturePayload())
            ->assertRedirect(route('employee.work-orders.show', $workOrder));

        $this->assertSame(1, WorkOrderSignature::where('work_order_id', $workOrder->id)->count());

        $this->cleanupSignatureFile($workOrder);
    }

    public function test_completing_an_unscheduled_order_backfills_a_scheduled_step(): void
    {
        // Order is Triaged (never scheduled). Completing it should record a
        // Scheduled step first, then Services Performed — two status history rows.
        $workOrder = WorkOrder::factory()->create(['status' => WorkOrder::STATUS_TRIAGED]);
        $workOrder->assignments()->create([
            'user_id'     => $this->employee->id,
            'assigned_by' => $this->admin->id,
        ]);

        $this->actingAs($this->employee)
            ->post(route('employee.work-orders.complete', $workOrder), $this->signaturePayload())
            ->assertRedirect();

        $this->assertSame(WorkOrder::STATUS_SERVICES_PERFORMED, $workOrder->fresh()->status);

        $this->assertDatabaseHas('work_order_history', [
            'work_order_id' => $workOrder->id,
            'field_name'    => 'status',
            'old_value'     => WorkOrder::STATUS_TRIAGED,
            'new_value'     => WorkOrder::STATUS_SCHEDULED,
        ]);
        $this->assertDatabaseHas('work_order_history', [
            'work_order_id' => $workOrder->id,
            'field_name'    => 'status',
            'old_value'     => WorkOrder::STATUS_SCHEDULED,
            'new_value'     => WorkOrder::STATUS_SERVICES_PERFORMED,
        ]);

        $this->cleanupSignatureFile($workOrder);
    }

    public function test_completing_a_scheduled_order_does_not_backfill(): void
    {
        $workOrder = $this->assignedWorkOrder(); // scheduled

        $this->actingAs($this->employee)
            ->post(route('employee.work-orders.complete', $workOrder), $this->signaturePayload())
            ->assertRedirect();

        // No Scheduled back-fill row should exist (it was already scheduled).
        $this->assertDatabaseMissing('work_order_history', [
            'work_order_id' => $workOrder->id,
            'field_name'    => 'status',
            'new_value'     => WorkOrder::STATUS_SCHEDULED,
        ]);
        $this->assertSame(WorkOrder::STATUS_SERVICES_PERFORMED, $workOrder->fresh()->status);

        $this->cleanupSignatureFile($workOrder);
    }

    public function test_unassigned_employee_cannot_complete_a_work_order(): void
    {
        $workOrder = WorkOrder::factory()->scheduled()->create();

        $this->actingAs($this->employee)
            ->post(route('employee.work-orders.complete', $workOrder), $this->signaturePayload())
            ->assertForbidden();

        $this->assertSame(WorkOrder::STATUS_SCHEDULED, $workOrder->fresh()->status);
        $this->assertSame(0, WorkOrderSignature::count());
    }

    public function test_canceled_work_order_cannot_be_completed(): void
    {
        $workOrder = $this->assignedWorkOrder('canceled');

        $this->actingAs($this->employee)
            ->post(route('employee.work-orders.complete', $workOrder), $this->signaturePayload());

        $this->assertSame(WorkOrder::STATUS_CANCELED, $workOrder->fresh()->status);
        $this->assertSame(0, WorkOrderSignature::count());
    }

    public function test_invalid_signature_data_is_rejected(): void
    {
        $workOrder = $this->assignedWorkOrder();

        $this->actingAs($this->employee)
            ->post(route('employee.work-orders.complete', $workOrder), [
                'signer_name'    => 'Pat Customer',
                'signature_data' => 'data:image/png;base64,!!!not-base64!!!',
            ])
            ->assertSessionHasErrors('signature_data');

        $this->assertSame(WorkOrder::STATUS_SCHEDULED, $workOrder->fresh()->status);
    }

    public function test_work_orders_receive_sequential_numbers(): void
    {
        $first  = WorkOrder::factory()->create();
        $second = WorkOrder::factory()->create();

        $this->assertSame($first->wo_number + 1, $second->wo_number);
        $this->assertSame('WO-' . $first->wo_number, $first->woLabel());
    }
}
