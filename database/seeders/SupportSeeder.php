<?php

namespace Database\Seeders;

use App\Models\Support;
use App\Models\SupportReply;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $adminId = User::where('role', 'admin')->first()->id;

        foreach ($projects as $project) {
            // Create 0-3 support tickets for each project
            Support::factory()
                ->count(rand(0, 3))
                ->create([
                    'user_id' => $project->user_id,
                    'project_id' => $project->id
                ])
                ->each(function ($support) use ($adminId, $project) {
                    // Create 1-5 replies for each support ticket
                    SupportReply::factory()
                        ->count(rand(1, 5))
                        ->create([
                            'support_id' => $support->id,
                            'user_id' => rand(0, 1) ? $adminId : $project->user_id
                        ]);

                    // 60% chance of being resolved
                    if (rand(1, 100) <= 60) {
                        $support->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                            'resolved_by' => $adminId
                        ]);
                    }
                });
        }
    }
}
