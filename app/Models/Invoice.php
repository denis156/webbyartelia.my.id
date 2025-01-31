<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'invoice_number',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'payment_type',
        'status',
        'issue_date',
        'due_date',
        'tax_amount',
        'notes',
        'created_by',
        'sent_at',
        'paid_at',
        'cancelled_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    // Konstanta untuk status dan payment_type
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIALLY_PAID = 'partially_paid';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_OVERDUE = 'overdue';

    const PAYMENT_TYPE_FULL = 'full';
    const PAYMENT_TYPE_PARTIAL = 'partial';

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopePartiallyPaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PARTIALLY_PAID);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT)
            ->where('due_date', '<', now());
    }

    // Helper Methods
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_PAID;
    }

    public function isOverdue(): bool
    {
        return $this->due_date < now() && !in_array($this->status, [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    public function calculateRemainingAmount(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function calculateTotalWithTax(): float
    {
        return $this->total_amount + (($this->total_amount * $this->tax_amount) / 100);
    }

    public function updatePaymentStatus(): void
    {
        $totalWithTax = $this->calculateTotalWithTax();

        if ($this->paid_amount >= $totalWithTax) {
            $this->update([
                'status' => self::STATUS_PAID,
                'paid_at' => now(),
                'remaining_amount' => 0
            ]);
        } elseif ($this->paid_amount > 0) {
            $this->update([
                'status' => self::STATUS_PARTIALLY_PAID,
                'remaining_amount' => $totalWithTax - $this->paid_amount
            ]);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            // Set created_by jika belum diset
            $invoice->created_by = Filament::auth()->id();

            DB::transaction(function () use ($invoice) {
                if (empty($invoice->invoice_number)) {
                    // Format: FKR-YYMMDD-RANDOM
                    $prefix = 'FKR-' . date('ymd') . '-';

                    // Generate random number 5 digit
                    $randomNumber = mt_rand(10000, 99999);

                    // Cek dan pastikan number unik
                    while (static::where('invoice_number', $prefix . $randomNumber)->exists()) {
                        $randomNumber = mt_rand(10000, 99999);
                    }

                    $invoice->invoice_number = $prefix . $randomNumber;
                }
            });

            // Perhitungan amount yang lebih akurat
            if ($invoice->project) {
                try {
                    $baseAmount = (float) $invoice->project->price;
                    $taxPercent = (float) ($invoice->tax_amount ?? 0);

                    // Gunakan bcmath untuk perhitungan yang lebih akurat
                    $taxAmount = bcmul($baseAmount, bcdiv($taxPercent, 100, 4), 2);
                    $totalWithTax = bcadd($baseAmount, $taxAmount, 2);

                    $invoice->total_amount = $totalWithTax;
                    $invoice->remaining_amount = $totalWithTax;
                    $invoice->paid_amount = 0;
                } catch (\Exception $e) {
                    Log::error('Error calculating invoice amounts: ' . $e->getMessage());
                    throw $e;
                }
            }
        });

        static::updating(function ($invoice) {
            if ($invoice->isDirty('paid_amount')) {
                $invoice->remaining_amount = bcsub($invoice->total_amount, $invoice->paid_amount, 2);
            }
        });
    }
}
