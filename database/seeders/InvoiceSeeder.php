<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan events sementara
        Invoice::unsetEventDispatcher();

        try {
            DB::transaction(function() {
                // Pastikan ada admin
                $admin = User::where('role', 'admin')->first();
                if (!$admin) {
                    $admin = User::create([
                        'name' => 'Admin User',
                        'email' => 'admin@example.com',
                        'password' => bcrypt('password'),
                        'role' => 'admin',
                    ]);
                }

                // Ambil semua project
                $projects = Project::all();

                foreach ($projects as $project) {
                    // Buat 1-2 invoice per project
                    $count = rand(1, 2);

                    for ($i = 0; $i < $count; $i++) {
                        $baseAmount = $project->price;
                        $taxPercent = rand(0, 20);
                        $taxAmount = ($baseAmount * $taxPercent) / 100;
                        $totalAmount = $baseAmount + $taxAmount;

                        DB::table('invoices')->insert([
                            'project_id' => $project->id,
                            'invoice_number' => 'INV-' . date('ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                            'total_amount' => $totalAmount,
                            'paid_amount' => 0,
                            'remaining_amount' => $totalAmount,
                            'payment_type' => 'full',
                            'status' => 'draft',
                            'issue_date' => now(),
                            'due_date' => now()->addDays(30),
                            'tax_amount' => $taxPercent,
                            'created_by' => $admin->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            });
        } finally {
            // Aktifkan kembali events
            Invoice::setEventDispatcher(
                app('events')
            );
        }
    }
}
