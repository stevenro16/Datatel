<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory()->servicesPerformed(),
            'created_by'    => User::factory()->admin(),
            'status'        => Invoice::STATUS_DRAFT,
            'subtotal'      => 0,
            'tax_rate'      => 0.0750,
            'tax_amount'    => 0,
            'discount'      => 0,
            'total'         => 0,
            'payment_terms' => 'Net 30',
            'due_date'      => now()->addDays(30)->toDateString(),
        ];
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Invoice::STATUS_ISSUED,
        ]);
    }
}
