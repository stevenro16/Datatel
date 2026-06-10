<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkOrderAttachment>
 */
class WorkOrderAttachmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'uploaded_by'   => User::factory()->customer(),
            'original_name' => 'site-photo.jpg',
            'stored_name'   => Str::uuid() . '.jpg',
            'mime_type'     => 'image/jpeg',
            'size_bytes'    => fake()->numberBetween(10_000, 500_000),
        ];
    }
}
