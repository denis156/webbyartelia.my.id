<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $project = Project::factory()->create();
        $paymentType = $this->faker->randomElement(['full', 'partial']);
        $baseAmount = $paymentType === 'full' ? $project->price : ($project->price * 0.5);
        $taxAmount = rand(10, 12);
        $taxValue = $baseAmount * ($taxAmount / 100);
        $totalAmount = $baseAmount + $taxValue;

        return [
            'project_id' => $project->id,
            'invoice_number' => 'INV-' . date('ymd') . '-' . strtoupper(uniqid()),
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'remaining_amount' => $totalAmount,
            'payment_type' => $paymentType,
            'status' => 'draft',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'tax_amount' => $taxAmount,
            'notes' => fake()->sentence(),
            'created_by' => User::factory()->admin(),
            'sent_at' => null,
            'paid_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'sent_at' => now()->subDays(rand(1, 13)),
                'paid_at' => now(),
                'paid_amount' => $attributes['total_amount'],
                'remaining_amount' => 0,
            ];
        });
    }
}
