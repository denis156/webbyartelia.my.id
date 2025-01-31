<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            Log::warning('No admin found for payment verification. Skipping payment seeding.');
            return;
        }

        $invoices = Invoice::where('status', 'sent')->get();

        if ($invoices->isEmpty()) {
            Log::info('No sent invoices found for payment seeding.');
            return;
        }

        foreach ($invoices as $invoice) {
            try {
                if (rand(1, 100) <= 70) { // 70% chance of having payment
                    DB::transaction(function () use ($invoice, $admin) {
                        $isFullPayment = rand(1, 100) <= 60; // 60% chance of full payment
                        $amount = $isFullPayment ?
                            $invoice->total_amount :
                            ($invoice->total_amount * rand(30, 70) / 100); // 30-70% of total

                        $payment = Payment::create([
                            'invoice_id' => $invoice->id,
                            'payment_number' => $this->generatePaymentNumber(),
                            'amount' => $amount,
                            'payment_method' => $this->getRandomPaymentMethod(),
                            'status' => 'verified',
                            'payment_notes' => $this->generatePaymentNotes($amount),
                            'verified_by' => $admin->id,
                            'verified_at' => now(),
                        ]);

                        // Update invoice
                        $invoice->paid_amount = $amount;
                        $invoice->remaining_amount = $invoice->total_amount - $amount;
                        $invoice->status = $amount >= $invoice->total_amount ? 'paid' : 'partially_paid';
                        $invoice->paid_at = now();
                        $invoice->save();

                        Log::info("Created payment: {$payment->payment_number} for invoice: {$invoice->invoice_number}");
                    });
                }
            } catch (\Exception $e) {
                Log::error("Failed to create payment for invoice {$invoice->id}: " . $e->getMessage());
                continue;
            }
        }
    }

    private function generatePaymentNumber(): string
    {
        $prefix = 'PAY-' . date('ymd');
        $number = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        while (Payment::where('payment_number', $prefix . '-' . $number)->exists()) {
            $number = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '-' . $number;
    }

    private function getRandomPaymentMethod(): string
    {
        $methods = [
            'bank_transfer' => 70,
            'cash' => 30,
        ];

        $rand = rand(1, array_sum($methods));
        $current = 0;

        foreach ($methods as $method => $weight) {
            $current += $weight;
            if ($rand <= $current) {
                return $method;
            }
        }

        return 'bank_transfer';
    }

    private function generatePaymentNotes(float $amount): string
    {
        $templates = [
            "Pembayaran invoice sebesar Rp%s telah diterima",
            "Pembayaran diterima melalui %s sejumlah Rp%s",
            "Konfirmasi pembayaran Rp%s sudah diverifikasi",
        ];

        $template = $templates[array_rand($templates)];
        $formattedAmount = number_format($amount, 0, ',', '.');

        return sprintf($template, $formattedAmount);
    }
}
