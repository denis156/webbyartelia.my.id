<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'address',
        'profile_photo',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function supports(): HasMany
    {
        return $this->hasMany(Support::class);
    }

    public function supportReplies(): HasMany
    {
        return $this->hasMany(SupportReply::class);
    }

    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('role', 'admin');
    }

    public function scopeClient(Builder $query): Builder
    {
        return $query->where('role', 'client');
    }

    // Helper Methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }
}
