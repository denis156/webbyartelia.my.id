<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin Artelia',
            'email' => 'admin@artelia.dev',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone_number' => '081234567890',
            'address' => 'Jakarta, Indonesia',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Clients
        User::factory(10)->create([
            'role' => 'client',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
