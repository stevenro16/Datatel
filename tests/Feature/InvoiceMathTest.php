<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceHistory;
use App\Models\InvoiceLineItem;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceMathTest extends TestCase
{
    use RefreshDatabase;

    public function test_line_total_is_computed_by_the_database(): void
    {
        $item = InvoiceLineItem::factory()->create([
            'quantity'   => 3,
            'unit_price' => 19.99,
        ]);

        // line_total is a STORED generated column — must come back from the DB.
        $this->assertEqualsWithDelta(59.97, (float) $item->fresh()->line_total, 0.001);
    }

    public function test_line_total_updates_when_quantity_changes(): void
    {
        $item = InvoiceLineItem::factory()->create([
            'quantity'   => 1,
            'unit_price' => 100.00,
        ]);

        $item->update(['quantity' => 4]);

        $this->assertEqualsWithDelta(400.00, (float) $item->fresh()->line_total, 0.001);
    }

    public function test_customer_payment_submission_advances_status_and_writes_history(): void
    {
        $customer  = User::factory()->customer()->create();
        $workOrder = WorkOrder::factory()->servicesPerformed()->for($customer, 'customer')->create();
        $invoice   = Invoice::factory()->issued()->create(['work_order_id' => $workOrder->id]);

        $this->actingAs($customer)
            ->post(route('portal.invoices.submit-payment', $invoice))
            ->assertRedirect();

        $this->assertSame(Invoice::STATUS_PAYMENT_RECEIVED, $invoice->fresh()->status);

        $this->assertDatabaseHas('invoice_history', [
            'invoice_id' => $invoice->id,
            'field_name' => 'status',
            'old_value'  => Invoice::STATUS_ISSUED,
            'new_value'  => Invoice::STATUS_PAYMENT_RECEIVED,
        ]);

        $this->assertDatabaseHas('work_order_history', [
            'work_order_id' => $workOrder->id,
            'field_name'    => 'invoice_status',
            'new_value'     => Invoice::STATUS_PAYMENT_RECEIVED,
        ]);
    }

    public function test_payment_cannot_be_submitted_on_a_draft_invoice(): void
    {
        $customer  = User::factory()->customer()->create();
        $workOrder = WorkOrder::factory()->servicesPerformed()->for($customer, 'customer')->create();
        $invoice   = Invoice::factory()->create(['work_order_id' => $workOrder->id]); // draft

        $this->actingAs($customer)
            ->post(route('portal.invoices.submit-payment', $invoice))
            ->assertStatus(422);

        $this->assertSame(Invoice::STATUS_DRAFT, $invoice->fresh()->status);
        $this->assertSame(0, InvoiceHistory::count());
        $this->assertSame(0, WorkOrderHistory::count());
    }
}
