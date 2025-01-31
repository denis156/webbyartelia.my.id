<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            Log::warning('No admin user found. Creating default admin...');
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);
        }

        $approvedProjects = Project::whereIn('status', ['approved', 'in_progress', 'completed'])->get();

        if ($approvedProjects->isEmpty()) {
            Log::warning('No approved projects found for creating invoices.');
            return;
        }

        foreach ($approvedProjects as $project) {
            try {
                $baseAmount = $project->price;
                $paymentType = $this->getRandomPaymentType();
                $taxAmount = rand(10, 12);

                // Calculate amounts
                $taxValue = $baseAmount * ($taxAmount / 100);
                $totalAmount = $baseAmount + $taxValue;

                if ($paymentType === 'partial') {
                    $totalAmount = $totalAmount * 0.5;
                }

                $invoiceNumber = $this->generateInvoiceNumber();

                $invoice = Invoice::create([
                    'project_id' => $project->id,
                    'invoice_number' => $invoiceNumber,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'remaining_amount' => $totalAmount,
                    'payment_type' => $paymentType,
                    'status' => 'sent',
                    'issue_date' => now(),
                    'due_date' => now()->addDays(14),
                    'tax_amount' => $taxAmount,
                    'notes' => 'Invoice dibuat otomatis oleh sistem',
                    'created_by' => $admin->id,
                    'sent_at' => now(),
                ]);

                Log::info("Created invoice: {$invoiceNumber} for project: {$project->project_name}");
            } catch (\Exception $e) {
                Log::error("Failed to create invoice for project {$project->id}: " . $e->getMessage());
                continue;
            }
        }
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('ymd');
        $number = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        while (Invoice::where('invoice_number', $prefix . '-' . $number)->exists()) {
            $number = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '-' . $number;
    }

    private function getRandomPaymentType(): string
    {
        return (rand(1, 100) <= 70) ? 'full' : 'partial';
    }
}
