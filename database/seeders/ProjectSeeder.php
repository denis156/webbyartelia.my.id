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

        foreach ($clients as $client) {
            // Create 1-3 projects for each client
            Project::factory()
                ->count(rand(1, 3))
                ->create([
                    'user_id' => $client->id
                ])
                ->each(function ($project) {
                    // 70% chance of project being in progress or completed
                    if (rand(1, 100) <= 70) {
                        $project->update([
                            'status' => rand(1, 100) <= 50 ? 'in_progress' : 'completed',
                            'progress' => rand(10, 100)
                        ]);
                    }
                });
        }
    }
}
