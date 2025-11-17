<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Integration extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'config',
        'is_active',
        'last_sync_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'last_sync_at' => 'datetime',
        ];
    }

    /**
     * Get the integration logs for this integration.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(IntegrationLog::class);
    }

    /**
     * Get recent logs for this integration.
     */
    public function recentLogs(int $limit = 10): HasMany
    {
        return $this->logs()
            ->latest()
            ->limit($limit);
    }

    /**
     * Mark the integration as synced.
     */
    public function markAsSynced(): void
    {
        $this->update(['last_sync_at' => now()]);
    }

    /**
     * Check if the integration is configured properly.
     */
    public function isConfigured(): bool
    {
        return !empty($this->config) && $this->is_active;
    }

    /**
     * Scope a query to only include active integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by integration type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
