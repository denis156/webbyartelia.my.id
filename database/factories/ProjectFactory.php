<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'approved', 'in_progress', 'completed']);
        $startDate = null;
        $completionDate = null;
        $progress = 0;

        if ($status !== 'pending') {
            $startDate = now()->subDays(rand(1, 30));
            if ($status === 'completed') {
                $completionDate = now();
                $progress = 100;
            } else if ($status === 'in_progress') {
                $progress = rand(10, 90);
            }
        }

        return [
            'user_id' => User::factory(),
            'project_name' => fake()->catchPhrase(),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(1000000, 50000000),
            'status' => $status,
            'progress' => $progress,
            'start_date' => $startDate,
            'deadline' => $startDate ? now()->addDays(rand(30, 90)) : null,
            'completion_date' => $completionDate,
            'requirements' => fake()->paragraphs(3, true),
            'attachment_path' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'start_date' => now(),
            'progress' => 0,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'start_date' => now()->subDays(rand(1, 30)),
            'progress' => rand(10, 90),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'start_date' => now()->subDays(rand(31, 60)),
            'completion_date' => now(),
            'progress' => 100,
        ]);
    }
}
