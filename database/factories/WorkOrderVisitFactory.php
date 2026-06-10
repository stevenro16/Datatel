<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use App\Models\WorkOrderVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderVisit>
 */
class WorkOrderVisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_order_id'             => WorkOrder::factory()->scheduled(),
            'scheduled_at'              => now()->addDay()->setTime(9, 0),
            'duration_estimate_minutes' => 120,
            'confirmation_status'       => WorkOrderVisit::CONFIRMATION_PENDING,
        ];
    }
}
