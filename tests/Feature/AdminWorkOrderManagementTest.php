<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_work_order(): void
    {
        $admin    = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this->actingAs($admin)
            ->post(route('admin.work-orders.store'), [
                'customer_id' => $customer->id,
                'urgency'     => WorkOrder::URGENCY_ROUTINE,
                'description' => 'Pull four new Cat6 drops to the east wing.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('work_orders', [
            'customer_id' => $customer->id,
            'created_by'  => $admin->id,
            'urgency'     => WorkOrder::URGENCY_ROUTINE,
        ]);
    }

    public function test_admin_create_requires_a_customer(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.work-orders.store'), [
                'urgency' => WorkOrder::URGENCY_ROUTINE,
            ])
            ->assertSessionHasErrors('customer_id');
    }

    public function test_non_admin_cannot_create_a_work_order(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->post(route('admin.work-orders.store'), [
                'customer_id' => $customer->id,
                'urgency'     => WorkOrder::URGENCY_ROUTINE,
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_work_order_fields(): void
    {
        $admin     = User::factory()->admin()->create();
        $workOrder = WorkOrder::factory()->create([
            'status'  => WorkOrder::STATUS_TRIAGED,
            'urgency' => WorkOrder::URGENCY_ROUTINE,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.work-orders.update', $workOrder), [
                'status'      => WorkOrder::STATUS_TRIAGED,
                'urgency'     => WorkOrder::URGENCY_URGENT,
                'description' => 'Updated scope of work for the east wing.',
            ])
            ->assertRedirect();

        $this->assertSame(WorkOrder::URGENCY_URGENT, $workOrder->fresh()->urgency);
    }
}
