<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('payment_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer'])->default('cash');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('payment_proof')->nullable();
            $table->text('payment_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
