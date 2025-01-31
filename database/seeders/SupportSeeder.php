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
        $admin = User::where('role', 'admin')->first();
        $ticketCounter = 1;

        foreach ($projects as $project) {
            $numSupports = rand(0, 2);

            for ($i = 0; $i < $numSupports; $i++) {
                // Buat ticket number yang dijamin unik menggunakan counter
                $ticketNumber = sprintf('TIC-%s-%04d', date('Ymd'), $ticketCounter++);

                $support = Support::factory()->create([
                    'user_id' => $project->user_id,
                    'project_id' => $project->id,
                    'ticket_number' => $ticketNumber,
                ]);

                // Buat replies untuk support ticket ini
                // Reply dari client
                SupportReply::factory(rand(1, 3))->create([
                    'support_id' => $support->id,
                    'user_id' => $project->user_id,
                    'is_internal' => false,
                ]);

                // Reply dari admin
                SupportReply::factory(rand(1, 3))->create([
                    'support_id' => $support->id,
                    'user_id' => $admin->id,
                    'is_internal' => false,
                ]);
            }
        }
    }
}
