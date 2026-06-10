<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceLineItem>
 *
 * line_total is a STORED generated column (quantity * unit_price) — never set it here.
 */
class InvoiceLineItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->words(3, true),
            'quantity'    => 1,
            'unit_price'  => fake()->randomFloat(2, 25, 500),
            'sort_order'  => 0,
        ];
    }
}
