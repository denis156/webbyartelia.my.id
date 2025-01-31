<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        $totalAmount = fake()->randomFloat(2, 1000, 50000);
        $paidAmount = fake()->randomFloat(2, 0, $totalAmount);
        $remainingAmount = $totalAmount - $paidAmount;
        $issueDate = fake()->dateTimeBetween('-3 months', 'now');
        $dueDate = fake()->dateTimeBetween($issueDate, '+2 months');

        // Pastikan ada admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        return [
            'project_id' => Project::factory(),
            'invoice_number' => 'INV-' . date('ymd') . '-' . fake()->unique()->randomNumber(5),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_type' => fake()->randomElement(['full', 'partial']),
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'partially_paid', 'cancelled', 'overdue']),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'tax_amount' => fake()->randomFloat(2, 0, 20),
            'notes' => fake()->optional()->paragraph(),
            'created_by' => $admin->id, // Pastikan selalu ada created_by
            'sent_at' => null,
            'paid_at' => null,
            'cancelled_at' => null,
        ];
    }
}

