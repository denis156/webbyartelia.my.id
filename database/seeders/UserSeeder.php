<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin first
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@artelia.dev',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create clients
        \App\Models\User::factory(5)->create(['role' => 'client']);
    }
}

