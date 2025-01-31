<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Support;
use Illuminate\Database\Eloquent\Factories\Factory;


class SupportFactory extends Factory
{
    protected $model = Support::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'ticket_number' => 'TIC-' . strtoupper(uniqid()),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => fake()->randomElement(['open', 'in_progress', 'resolved', 'closed']),
            'category' => fake()->randomElement(['technical', 'billing', 'general', 'bug', 'feature_request']),
            'attachment_path' => null,
            'resolved_at' => null,
            'resolved_by' => null,
        ];
    }
}
