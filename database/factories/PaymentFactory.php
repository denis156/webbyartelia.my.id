<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'invoice_id' => Invoice::factory(),
            'payment_number' => 'PAY-' . strtoupper(uniqid()),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer']),
            'status' => fake()->randomElement(['pending', 'verified', 'rejected']),
            'payment_proof' => null,
            'payment_notes' => fake()->optional()->sentence(),
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }
}
