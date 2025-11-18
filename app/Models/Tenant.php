<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'database',
        'settings',
        'is_active',
        'trial_ends_at',
        'subscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'trial_ends_at' => 'datetime',
            'subscribed_at' => 'datetime',
        ];
    }

    /**
     * Get the users belonging to this tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the ideas belonging to this tenant
     */
    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }

    /**
     * Get the branding for this tenant
     */
    public function branding(): HasOne
    {
        return $this->hasOne(TenantBranding::class);
    }

    /**
     * Check if tenant is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture() && ! $this->subscribed_at;
    }

    /**
     * Check if tenant subscription has expired
     */
    public function hasExpired(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast() && ! $this->subscribed_at;
    }

    /**
     * Scope to get active tenants only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
