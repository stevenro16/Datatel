<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** Every report route name under admin.reports.*, with any required params. */
    private const REPORTS = [
        'work-order-summary',
        'work-order-aging',
        'invoice-register',
        'accounts-receivable',
        'technician-productivity',
        'technician-time',
        'customer-statement',
        'company-performance',
        'service-usage',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Seed a realistic slice: a company, customer, tech, work orders (one
     * completed via history), an invoice with a line item, and a time entry.
     */
    private function seedData(): void
    {
        $company  = Company::factory()->create();
        $customer = User::factory()->customer()->create();
        $tech     = User::factory()->employee()->create();

        $open = WorkOrder::factory()->create([
            'customer_id' => $customer->id,
            'company_id'  => $company->id,
            'status'      => WorkOrder::STATUS_SCHEDULED,
        ]);
        $open->assignments()->create(['user_id' => $tech->id, 'assigned_by' => $this->admin->id]);

        $completed = WorkOrder::factory()->completed()->create([
            'customer_id' => $customer->id,
            'company_id'  => $company->id,
        ]);
        $completed->assignments()->create(['user_id' => $tech->id, 'assigned_by' => $this->admin->id]);
        WorkOrderHistory::create([
            'work_order_id' => $completed->id,
            'changed_by'    => $this->admin->id,
            'field_name'    => 'status',
            'old_value'     => WorkOrder::STATUS_SCHEDULED,
            'new_value'     => WorkOrder::STATUS_COMPLETED,
            'changed_at'    => now()->subDay(),
        ]);

        $invoice = Invoice::factory()->issued()->create([
            'work_order_id' => $completed->id,
            'due_date'      => now()->subDays(45)->toDateString(), // past due → A/R aging
            'subtotal'      => 100,
            'tax_amount'    => 7.5,
            'total'         => 107.5,
        ]);
        InvoiceLineItem::factory()->create(['invoice_id' => $invoice->id, 'quantity' => 2, 'unit_price' => 50]);

        TimeEntry::create([
            'user_id'        => $tech->id,
            'work_order_id'  => $completed->id,
            'clocked_in_at'  => now()->subDay()->setTime(9, 0),
            'clocked_out_at' => now()->subDay()->setTime(12, 30),
        ]);
    }

    public function test_reports_index_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Reports')
            ->assertSee('Work Order Summary')
            ->assertSee('Accounts Receivable Aging');
    }

    public function test_every_report_renders_with_data(): void
    {
        $this->seedData();

        foreach (self::REPORTS as $slug) {
            $this->actingAs($this->admin)
                ->get(route("admin.reports.$slug"))
                ->assertOk();
        }
    }

    public function test_every_report_renders_when_empty(): void
    {
        // No seeded data — reports must still render their empty states, not error.
        foreach (self::REPORTS as $slug) {
            $this->actingAs($this->admin)
                ->get(route("admin.reports.$slug"))
                ->assertOk();
        }
    }

    public function test_reports_respect_custom_date_range(): void
    {
        $this->seedData();

        $this->actingAs($this->admin)
            ->get(route('admin.reports.invoice-register', [
                'range' => 'custom',
                'from'  => now()->startOfMonth()->toDateString(),
                'to'    => now()->toDateString(),
            ]))
            ->assertOk();
    }

    public function test_filtered_reports_accept_entity_ids(): void
    {
        $this->seedData();
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $company  = Company::first();
        $tech     = User::where('role', User::ROLE_EMPLOYEE)->first();

        $this->actingAs($this->admin)->get(route('admin.reports.customer-statement', ['customer_id' => $customer->id]))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.company-performance', ['company_id' => $company->id]))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.technician-time', ['tech_id' => $tech->id]))->assertOk();
    }

    public function test_non_admin_cannot_access_reports(): void
    {
        $customer = User::factory()->customer()->create();
        $employee = User::factory()->employee()->create();

        $this->actingAs($customer)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.reports.work-order-summary'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.reports.invoice-register'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
    }
}
