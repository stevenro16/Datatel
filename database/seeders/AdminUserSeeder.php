<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@datatel.local'],
            [
                'name' => 'DataTel Admin',
                'password' => Hash::make('changeme123!'),
                'role' => 'admin',
                'status' => 'active',
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
