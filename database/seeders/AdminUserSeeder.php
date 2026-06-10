<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_SEED_PASSWORD');

        if (!$password) {
            // The well-known default is only acceptable in local/testing environments.
            if (!app()->environment('local', 'testing')) {
                throw new RuntimeException(
                    'Set ADMIN_SEED_PASSWORD in .env before seeding the admin user outside local/testing.'
                );
            }
            $password = 'changeme123!';
        }

        User::firstOrCreate(
            ['email' => 'admin@datatel.local'],
            [
                'name' => 'DataTel Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
