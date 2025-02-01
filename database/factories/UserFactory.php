<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role' => fake()->randomElement(['admin', 'client']),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'avatar_url' => null,
            'email_verified_at' => now(),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
