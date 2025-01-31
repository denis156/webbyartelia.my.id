<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;


class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        $startDate = fake()->dateTimeBetween('-6 months', '+1 month');
        $deadline = fake()->dateTimeBetween($startDate, '+1 year');

        return [
            'user_id' => User::factory(),
            'project_name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 1000, 50000),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'in_progress', 'completed', 'cancelled']),
            'progress' => fake()->numberBetween(0, 100),
            'start_date' => $startDate,
            'deadline' => $deadline,
            'completion_date' => null,
            'rejection_reason' => null,
            'requirements' => fake()->paragraphs(3, true),
            'attachment_path' => null,
        ];
    }
}
