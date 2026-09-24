<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@kostsaas.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'status_verification' => 'verified',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
    }
}
