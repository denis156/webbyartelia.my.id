<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $clients = User::where('role', 'client')->get();

        if ($clients->isEmpty()) {
            Log::warning('No clients found. Creating a default client...');
            $clients = collect([
                User::create([
                    'name' => 'Default Client',
                    'email' => 'client@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'client',
                ])
            ]);
        }

        foreach ($clients as $client) {
            try {
                $projectCount = rand(1, 3);
                for ($i = 0; $i < $projectCount; $i++) {
                    $status = $this->getRandomStatus();
                    $project = Project::create([
                        'user_id' => $client->id,
                        'project_name' => "Project " . fake()->words(3, true),
                        'description' => fake()->paragraph(),
                        'price' => fake()->randomFloat(2, 10000000, 100000000),
                        'start_date' => now(),
                        'end_date' => now()->addMonths(rand(1, 6)),
                        'status' => $status,
                    ]);

                    Log::info("Created project: {$project->project_name} for client: {$client->name}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to create project for client {$client->id}: " . $e->getMessage());
                continue;
            }
        }
    }

    private function getRandomStatus(): string
    {
        $statuses = [
            'pending' => 15,
            'approved' => 35,
            'in_progress' => 35,
            'completed' => 15,
        ];

        return $this->getRandomWeightedElement($statuses);
    }

    private function getRandomWeightedElement(array $weightedValues)
    {
        $rand = rand(1, array_sum($weightedValues));
        $current = 0;
        foreach ($weightedValues as $key => $weight) {
            $current += $weight;
            if ($rand <= $current) {
                return $key;
            }
        }
        return array_key_first($weightedValues);
    }
}
