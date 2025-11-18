<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboard extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'tenant_id',
        'name',
        'slug',
        'widgets',
        'layout',
        'is_default',
        'is_shared',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'widgets' => 'array',
            'layout' => 'array',
            'is_default' => 'boolean',
            'is_shared' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the dashboard.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set this dashboard as the default for the user.
     */
    public function setAsDefault(): void
    {
        // Remove default from other user's dashboards
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Add a widget to the dashboard.
     */
    public function addWidget(array $widgetConfig): void
    {
        $widgets = $this->widgets ?? [];
        $widgets[] = $widgetConfig;
        $this->update(['widgets' => $widgets]);
    }

    /**
     * Remove a widget from the dashboard.
     */
    public function removeWidget(string $widgetId): void
    {
        $widgets = $this->widgets ?? [];
        $widgets = array_filter($widgets, fn ($widget) => $widget['id'] !== $widgetId);
        $this->update(['widgets' => array_values($widgets)]);
    }

    /**
     * Update a widget's configuration.
     */
    public function updateWidget(string $widgetId, array $newConfig): void
    {
        $widgets = $this->widgets ?? [];
        $widgets = array_map(function ($widget) use ($widgetId, $newConfig) {
            if ($widget['id'] === $widgetId) {
                return array_merge($widget, $newConfig);
            }

            return $widget;
        }, $widgets);
        $this->update(['widgets' => $widgets]);
    }

    /**
     * Update the dashboard layout.
     */
    public function updateLayout(array $layout): void
    {
        $this->update(['layout' => $layout]);
    }

    /**
     * Scope to get default dashboard for a user.
     */
    public function scopeDefault($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Scope to get shared dashboards.
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }
}
