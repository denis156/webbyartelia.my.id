<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $invoice = Invoice::factory()->create();
        $amount = $invoice->remaining_amount;

        return [
            'invoice_id' => $invoice->id,
            'payment_number' => 'PAY-' . date('ymd') . '-' . strtoupper(uniqid()),
            'amount' => $amount,
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer']),
            'status' => 'pending',
            'payment_proof' => null,
            'payment_notes' => fake()->sentence(),
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_by' => User::factory()->admin(),
            'verified_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
