<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id'        => User::factory()->customer(),
            'status'             => WorkOrder::STATUS_NEW,
            'urgency'            => WorkOrder::URGENCY_ROUTINE,
            'description'        => fake()->sentence(8),
            'site_street'        => fake()->streetAddress(),
            'site_city'          => fake()->city(),
            'site_state'         => 'NC',
            'site_zip'           => fake()->numerify('#####'),
            'site_contact_name'  => fake()->name(),
            'site_contact_phone' => fake()->numerify('###-###-####'),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => WorkOrder::STATUS_SCHEDULED,
            'scheduled_at' => now()->addDay()->setTime(9, 0),
        ]);
    }

    public function servicesPerformed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrder::STATUS_SERVICES_PERFORMED,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrder::STATUS_COMPLETED,
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrder::STATUS_CANCELED,
        ]);
    }
}
