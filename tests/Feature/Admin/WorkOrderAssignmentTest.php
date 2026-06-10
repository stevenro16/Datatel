<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behavioral baseline for admin employee-assignment endpoints — safety net for
 * the controller split (M2.4).
 */
class WorkOrderAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_assign_employee_creates_assignment(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => WorkOrder::STATUS_NEW]);
        $employee  = User::factory()->employee()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.assign', $workOrder), ['user_id' => $employee->id])
            ->assertRedirect();

        $this->assertTrue($workOrder->assignments()->where('user_id', $employee->id)->exists());
        // Assigning auto-advances New → Triaged.
        $this->assertSame(WorkOrder::STATUS_TRIAGED, $workOrder->fresh()->status);
    }

    public function test_assigning_same_employee_twice_is_idempotent(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => WorkOrder::STATUS_TRIAGED]);
        $employee  = User::factory()->employee()->create();

        foreach (range(1, 2) as $_) {
            $this->actingAs($this->admin)
                ->from(route('admin.work-orders.show', $workOrder))
                ->post(route('admin.work-orders.assign', $workOrder), ['user_id' => $employee->id]);
        }

        $this->assertSame(1, $workOrder->assignments()->where('user_id', $employee->id)->count());
    }

    public function test_unassign_employee_removes_assignment(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => WorkOrder::STATUS_TRIAGED]);
        $employee  = User::factory()->employee()->create();
        $workOrder->assignments()->create(['user_id' => $employee->id, 'assigned_by' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->delete(route('admin.work-orders.unassign', [$workOrder, $employee]))
            ->assertRedirect();

        $this->assertFalse($workOrder->assignments()->where('user_id', $employee->id)->exists());
    }

    public function test_non_admin_cannot_assign(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $employee  = User::factory()->employee()->create();

        $this->actingAs(User::factory()->customer()->create())
            ->post(route('admin.work-orders.assign', $workOrder), ['user_id' => $employee->id])
            ->assertForbidden();
    }
}
