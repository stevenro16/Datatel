<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards S6: the customer edit endpoint must never let the customer move their
 * own work order through the status machine or reassign ownership, even though
 * those columns are in WorkOrder::$fillable. The protection is the validation
 * allow-list — these tests fail loudly if a future edit widens it.
 */
class WorkOrderMassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_change_status_via_update(): void
    {
        $customer  = User::factory()->customer()->create();
        $workOrder = WorkOrder::factory()->for($customer, 'customer')->create([
            'status' => WorkOrder::STATUS_NEW,
        ]);

        $this->actingAs($customer)
            ->patch(route('portal.work-orders.update', $workOrder), [
                'description' => 'A valid description that is long enough.',
                'urgency'     => WorkOrder::URGENCY_ROUTINE,
                'status'      => WorkOrder::STATUS_COMPLETED,
                'confirmation_status' => WorkOrder::CONFIRMATION_CONFIRMED,
            ])
            ->assertRedirect();

        $workOrder->refresh();
        $this->assertSame(WorkOrder::STATUS_NEW, $workOrder->status);
        $this->assertNotSame(WorkOrder::CONFIRMATION_CONFIRMED, $workOrder->confirmation_status);
    }

    public function test_customer_cannot_reassign_ownership_via_update(): void
    {
        $customer  = User::factory()->customer()->create();
        $victim    = User::factory()->customer()->create();
        $workOrder = WorkOrder::factory()->for($customer, 'customer')->create([
            'status' => WorkOrder::STATUS_NEW,
        ]);

        $this->actingAs($customer)
            ->patch(route('portal.work-orders.update', $workOrder), [
                'description' => 'A valid description that is long enough.',
                'urgency'     => WorkOrder::URGENCY_ROUTINE,
                'customer_id' => $victim->id,
                'created_by'  => $victim->id,
            ])
            ->assertRedirect();

        $this->assertSame($customer->id, $workOrder->fresh()->customer_id);
    }
}
