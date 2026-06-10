<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end proof that the shared x-wo.urgency-badge component renders the
 * canonical colours/label from the model on a real portal page.
 */
class UrgencyBadgeRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_show_renders_emergency_badge_from_model_palette(): void
    {
        $customer  = User::factory()->customer()->create();
        $workOrder = WorkOrder::factory()->for($customer, 'customer')->create([
            'urgency' => WorkOrder::URGENCY_EMERGENCY,
        ]);

        $response = $this->actingAs($customer)->get(route('portal.work-orders.show', $workOrder));

        $response->assertOk();
        $response->assertSee('Emergency');
        // Canonical emergency palette from WorkOrder::URGENCY_COLORS
        $response->assertSee('#fee2e2', false);
        $response->assertSee('#991b1b', false);
    }
}
