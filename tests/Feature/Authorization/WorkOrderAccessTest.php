<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Role-separation matrix for work order detail routes.
 */
class WorkOrderAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $otherCustomer;
    private User $assignedEmployee;
    private User $unassignedEmployee;
    private User $admin;
    private WorkOrder $workOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner              = User::factory()->customer()->create();
        $this->otherCustomer      = User::factory()->customer()->create();
        $this->assignedEmployee   = User::factory()->employee()->create();
        $this->unassignedEmployee = User::factory()->employee()->create();
        $this->admin              = User::factory()->admin()->create();

        $this->workOrder = WorkOrder::factory()
            ->for($this->owner, 'customer')
            ->create();

        $this->workOrder->assignments()->create([
            'user_id'     => $this->assignedEmployee->id,
            'assigned_by' => $this->admin->id,
        ]);
    }

    // ── Customer portal ──────────────────────────────────────────────────────

    public function test_owner_can_view_their_work_order(): void
    {
        $this->actingAs($this->owner)
            ->get(route('portal.work-orders.show', $this->workOrder))
            ->assertOk();
    }

    public function test_other_customer_cannot_view_the_work_order(): void
    {
        $this->actingAs($this->otherCustomer)
            ->get(route('portal.work-orders.show', $this->workOrder))
            ->assertForbidden();
    }

    public function test_other_customer_cannot_update_the_work_order(): void
    {
        $this->actingAs($this->otherCustomer)
            ->patch(route('portal.work-orders.update', $this->workOrder), [
                'description' => 'hijacked description',
            ])
            ->assertForbidden();

        $this->assertNotSame('hijacked description', $this->workOrder->fresh()->description);
    }

    public function test_employee_cannot_use_customer_portal_routes(): void
    {
        $this->actingAs($this->assignedEmployee)
            ->get(route('portal.work-orders.show', $this->workOrder))
            ->assertForbidden();
    }

    // ── Employee portal ──────────────────────────────────────────────────────

    public function test_assigned_employee_can_view_work_order(): void
    {
        $this->actingAs($this->assignedEmployee)
            ->get(route('employee.work-orders.show', $this->workOrder))
            ->assertOk();
    }

    public function test_unassigned_employee_cannot_view_work_order(): void
    {
        $this->actingAs($this->unassignedEmployee)
            ->get(route('employee.work-orders.show', $this->workOrder))
            ->assertForbidden();
    }

    public function test_customer_cannot_use_employee_routes(): void
    {
        $this->actingAs($this->owner)
            ->get(route('employee.work-orders.show', $this->workOrder))
            ->assertForbidden();
    }

    // ── Admin portal ─────────────────────────────────────────────────────────

    public function test_admin_can_view_any_work_order(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.work-orders.show', $this->workOrder))
            ->assertOk();
    }

    public function test_customer_cannot_use_admin_routes(): void
    {
        $this->actingAs($this->owner)
            ->get(route('admin.work-orders.show', $this->workOrder))
            ->assertForbidden();
    }

    public function test_employee_cannot_use_admin_routes(): void
    {
        $this->actingAs($this->assignedEmployee)
            ->get(route('admin.work-orders.show', $this->workOrder))
            ->assertForbidden();
    }
}
