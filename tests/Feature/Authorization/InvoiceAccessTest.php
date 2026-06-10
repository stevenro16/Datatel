<?php

namespace Tests\Feature\Authorization;

use App\Models\Invoice;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Invoice show/print are scoped to the work order's customer.
 */
class InvoiceAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $otherCustomer;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner         = User::factory()->customer()->create();
        $this->otherCustomer = User::factory()->customer()->create();

        $workOrder = WorkOrder::factory()
            ->servicesPerformed()
            ->for($this->owner, 'customer')
            ->create();

        $this->invoice = Invoice::factory()->issued()->create([
            'work_order_id' => $workOrder->id,
        ]);
    }

    public function test_owner_can_view_their_invoice(): void
    {
        $this->actingAs($this->owner)
            ->get(route('portal.invoices.show', $this->invoice))
            ->assertOk();
    }

    public function test_owner_can_print_their_invoice(): void
    {
        $this->actingAs($this->owner)
            ->get(route('portal.invoices.print', $this->invoice))
            ->assertOk();
    }

    public function test_other_customer_cannot_view_the_invoice(): void
    {
        $this->actingAs($this->otherCustomer)
            ->get(route('portal.invoices.show', $this->invoice))
            ->assertForbidden();
    }

    public function test_other_customer_cannot_print_the_invoice(): void
    {
        $this->actingAs($this->otherCustomer)
            ->get(route('portal.invoices.print', $this->invoice))
            ->assertForbidden();
    }

    public function test_other_customer_cannot_submit_payment_on_the_invoice(): void
    {
        $this->actingAs($this->otherCustomer)
            ->post(route('portal.invoices.submit-payment', $this->invoice))
            ->assertForbidden();

        $this->assertSame(Invoice::STATUS_ISSUED, $this->invoice->fresh()->status);
    }
}
