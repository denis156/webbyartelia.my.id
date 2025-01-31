<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake('id_ID')->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake('id_ID')->phoneNumber(),
            'address' => fake('id_ID')->address(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
            'role' => 'client',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'client',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
