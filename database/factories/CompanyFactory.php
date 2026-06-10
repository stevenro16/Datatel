<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => fake()->company(),
            'status'     => 'active',
            'created_by' => User::factory()->admin(),
        ];
    }
}
