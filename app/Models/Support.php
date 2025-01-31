<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Support extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_id',
        'ticket_number',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'attachment_path',
        'resolved_at',
        'resolved_by'
    ];

    protected $casts = [
        'resolved_at' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportReply::class);
    }

    // Scopes
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    // Helper Methods
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function markAsResolved(int $resolvedBy): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($support) {
            if (empty($support->ticket_number)) {
                $support->ticket_number = 'TIC-' . strtoupper(uniqid());
            }
        });
    }
}

class SupportReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'support_id',
        'user_id',
        'message',
        'attachment_path',
        'is_internal'
    ];

    protected $casts = [
        'is_internal' => 'boolean'
    ];

    // Relationships
    public function support(): BelongsTo
    {
        return $this->belongsTo(Support::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('is_internal', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_internal', false);
    }

    // Helper Methods
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }
}
