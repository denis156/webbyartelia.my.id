<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_name',
        'description',
        'price',
        'status',
        'progress',
        'start_date',
        'deadline',
        'completion_date',
        'rejection_reason',
        'requirements',
        'attachment_path'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'progress' => 'integer',
        'start_date' => 'date',
        'deadline' => 'date',
        'completion_date' => 'date',
        'attachment_path' => 'array',
    ];

    public function getAttachmentPathAttribute($value)
    {
        if (empty($value)) return [];
        return array_map(function ($path) {
            return str_replace('public/', '', $path);
        }, json_decode($value, true) ?? []);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supports(): HasMany
    {
        return $this->hasMany(Support::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    // Helper Methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function canBeUpdated(): bool
    {
        return in_array($this->status, ['approved', 'in_progress']);
    }

    public function calculateProgress(): int
    {
        return $this->updates()->max('new_progress') ?? 0;
    }

    public function getRemainingDays(): int
    {
        if (!$this->deadline) {
            return 0;
        }
        return now()->diffInDays($this->deadline, false);
    }

    public function getTotalPaid(): float
    {
        return $this->invoices()
            ->whereIn('status', ['paid', 'partially_paid'])
            ->sum('paid_amount');
    }

    public function getRemainingPayment(): float
    {
        return $this->price - $this->getTotalPaid();
    }
}
