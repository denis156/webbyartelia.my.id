<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Support;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportFactory extends Factory
{
    protected $model = Support::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'ticket_number' => 'TIC-' . strtoupper(uniqid()),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => 'open',
            'category' => $this->faker->randomElement(['technical', 'billing', 'general', 'bug', 'feature_request']),
            'attachment_path' => null,
            'resolved_at' => null,
            'resolved_by' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => User::factory()->admin(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }
}
