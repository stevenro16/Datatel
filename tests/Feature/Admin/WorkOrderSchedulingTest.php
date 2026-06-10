<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behavioral baseline for the admin scheduling / visit / confirmation / status
 * endpoints. These must pass identically before and after the controller split
 * (M2.4), so they double as the safety net for that refactor.
 */
class WorkOrderSchedulingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function workOrder(string $state = 'create'): WorkOrder
    {
        return $state === 'create'
            ? WorkOrder::factory()->create(['status' => WorkOrder::STATUS_TRIAGED])
            : WorkOrder::factory()->{$state}()->create();
    }

    private function visitFor(WorkOrder $workOrder): WorkOrderVisit
    {
        return WorkOrderVisit::factory()->create(['work_order_id' => $workOrder->id]);
    }

    // ── Status & urgency ─────────────────────────────────────────────────────

    public function test_update_status_changes_status_and_logs_history(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.status', $workOrder), [
                'status'  => WorkOrder::STATUS_SCHEDULED,
                'comment' => 'Booked in.',
            ])
            ->assertRedirect();

        $this->assertSame(WorkOrder::STATUS_SCHEDULED, $workOrder->fresh()->status);
        $this->assertDatabaseHas('work_order_history', [
            'work_order_id' => $workOrder->id,
            'field_name'    => 'status',
            'new_value'     => WorkOrder::STATUS_SCHEDULED,
        ]);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.status', $workOrder), ['status' => 'bogus'])
            ->assertSessionHasErrors('status');
    }

    public function test_update_urgency_returns_json_and_persists(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->post(route('admin.work-orders.urgency', $workOrder), [
                'urgency' => WorkOrder::URGENCY_EMERGENCY,
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(WorkOrder::URGENCY_EMERGENCY, $workOrder->fresh()->urgency);
    }

    // ── Scheduling ───────────────────────────────────────────────────────────

    public function test_update_schedule_sets_scheduled_at(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.schedule', $workOrder), [
                'scheduled_date'      => now()->addDays(3)->toDateString(),
                'scheduled_time'      => '09:30',
                'confirmation_action' => 'confirmed',
            ])
            ->assertRedirect();

        $workOrder->refresh();
        $this->assertNotNull($workOrder->scheduled_at);
        $this->assertSame(WorkOrder::CONFIRMATION_CONFIRMED, $workOrder->confirmation_status);
    }

    public function test_tech_schedule_returns_json(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->getJson(route('admin.work-orders.tech-schedule', $workOrder) . '?date=' . now()->toDateString())
            ->assertOk();
    }

    public function test_travel_time_is_unavailable_without_api_key(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->getJson(route('admin.work-orders.travel-time', $workOrder) . '?from=123+Main+St&tech_id=1')
            ->assertStatus(422);
    }

    // ── Visits ───────────────────────────────────────────────────────────────

    public function test_store_visit_creates_a_visit(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.visits.store', $workOrder), [
                'scheduled_date' => now()->addDay()->toDateString(),
                'scheduled_time' => '10:00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('work_order_visits', ['work_order_id' => $workOrder->id]);
    }

    public function test_update_visit_reschedules(): void
    {
        $workOrder = $this->workOrder();
        $visit     = $this->visitFor($workOrder);

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->patch(route('admin.work-orders.visits.update', [$workOrder, $visit]), [
                'scheduled_date' => now()->addDays(2)->toDateString(),
                'scheduled_time' => '14:00',
            ])
            ->assertRedirect();

        $this->assertSame('14:00', $visit->fresh()->scheduled_at->format('H:i'));
    }

    public function test_destroy_visit_removes_it(): void
    {
        $workOrder = $this->workOrder();
        $visit     = $this->visitFor($workOrder);

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->delete(route('admin.work-orders.visits.destroy', [$workOrder, $visit]))
            ->assertRedirect();

        $this->assertDatabaseMissing('work_order_visits', ['id' => $visit->id]);
    }

    public function test_visit_routes_reject_mismatched_work_order(): void
    {
        $workOrder = $this->workOrder();
        $other     = $this->workOrder();
        $visit     = $this->visitFor($other);

        $this->actingAs($this->admin)
            ->delete(route('admin.work-orders.visits.destroy', [$workOrder, $visit]))
            ->assertForbidden();
    }

    // ── Confirmation ─────────────────────────────────────────────────────────

    public function test_request_confirmation_sets_pending(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.request-confirmation', $workOrder))
            ->assertRedirect();

        $this->assertSame(WorkOrder::CONFIRMATION_PENDING, $workOrder->fresh()->confirmation_status);
    }

    public function test_override_confirmation_requires_reason(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.override-confirmation', $workOrder), ['override_reason' => 'no'])
            ->assertSessionHasErrors('override_reason');
    }

    public function test_override_confirmation_marks_confirmed(): void
    {
        $workOrder = $this->workOrder();

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.override-confirmation', $workOrder), [
                'override_reason' => 'Spoke with the customer by phone.',
            ])
            ->assertRedirect();

        $this->assertSame(WorkOrder::CONFIRMATION_CONFIRMED, $workOrder->fresh()->confirmation_status);
    }

    public function test_request_visit_confirmation(): void
    {
        $workOrder = $this->workOrder();
        $visit     = $this->visitFor($workOrder);

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.visits.request-confirm', [$workOrder, $visit]))
            ->assertRedirect();

        $this->assertSame(WorkOrderVisit::CONFIRMATION_PENDING, $visit->fresh()->confirmation_status);
    }

    public function test_admin_confirm_visit(): void
    {
        $workOrder = $this->workOrder();
        $visit     = $this->visitFor($workOrder);

        $this->actingAs($this->admin)
            ->from(route('admin.work-orders.show', $workOrder))
            ->post(route('admin.work-orders.visits.admin-confirm', [$workOrder, $visit]), [
                'override_reason' => 'Verified on site.',
            ])
            ->assertRedirect();

        $this->assertSame(WorkOrderVisit::CONFIRMATION_CONFIRMED, $visit->fresh()->confirmation_status);
    }

    public function test_non_admin_cannot_reach_scheduling_endpoints(): void
    {
        $workOrder = $this->workOrder();
        $customer  = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->post(route('admin.work-orders.status', $workOrder), ['status' => WorkOrder::STATUS_SCHEDULED])
            ->assertForbidden();
    }
}
