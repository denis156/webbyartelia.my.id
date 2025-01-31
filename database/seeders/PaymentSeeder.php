<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Ambil semua invoice yang belum lunas
        $invoices = Invoice::whereIn('status', ['sent', 'partially_paid'])->get();

        foreach ($invoices as $invoice) {
            // Buat 1-3 pembayaran untuk setiap invoice
            $paymentCount = rand(1, 3);

            for ($i = 0; $i < $paymentCount; $i++) {
                // Hitung sisa yang perlu dibayar
                $remainingToPay = $invoice->total_amount - $invoice->paid_amount;

                if ($remainingToPay <= 0) {
                    continue;
                }

                // Tentukan jumlah pembayaran (antara 10% - 100% dari sisa)
                $paymentAmount = $remainingToPay * (rand(10, 100) / 100);
                $paymentAmount = round($paymentAmount, 2);

                // Buat payment
                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_number' => 'PAY-' . strtoupper(uniqid()),
                    'amount' => $paymentAmount,
                    'payment_method' => fake()->randomElement(['cash', 'bank_transfer']),
                    'status' => fake()->randomElement(['pending', 'verified', 'rejected']),
                    'payment_notes' => fake()->optional()->sentence(),
                    'payment_proof' => null,
                ]);

                // Jika payment verified, update verified_by dan invoice
                if ($payment->status === 'verified') {
                    $payment->update([
                        'verified_by' => $admin->id,
                        'verified_at' => now(),
                    ]);

                    // Update invoice paid amount dan status
                    $newPaidAmount = $invoice->paid_amount + $paymentAmount;
                    $newRemainingAmount = $invoice->total_amount - $newPaidAmount;

                    $invoice->update([
                        'paid_amount' => $newPaidAmount,
                        'remaining_amount' => $newRemainingAmount,
                        'status' => $newPaidAmount >= $invoice->total_amount ? 'paid' : 'partially_paid'
                    ]);
                }
                // Jika payment rejected, tambahkan rejection reason
                elseif ($payment->status === 'rejected') {
                    $payment->update([
                        'rejection_reason' => fake()->sentence()
                    ]);
                }
            }
        }
    }
}
