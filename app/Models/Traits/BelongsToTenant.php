<?php

namespace App\Models\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the belongs to tenant trait
     */
    public static function bootBelongsToTenant(): void
    {
        // Automatically set tenant_id when creating a model
        static::creating(function (Model $model) {
            if (! $model->tenant_id && $tenantId = self::getCurrentTenantId()) {
                $model->tenant_id = $tenantId;
            }
        });

        // Global scope to filter by current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenantId = self::getCurrentTenantId()) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            }
        });
    }

    /**
     * Get the tenant relationship
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the current tenant ID from the session or request
     */
    protected static function getCurrentTenantId(): ?int
    {
        // Get from session or app container
        return app('current_tenant_id') ?? session('tenant_id');
    }

    /**
     * Scope query without tenant filtering
     */
    public function scopeWithoutTenant(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('tenant');
    }

    /**
     * Scope query for a specific tenant
     */
    public function scopeForTenant(Builder $builder, int $tenantId): Builder
    {
        return $builder->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }

    /**
     * Scope query for all tenants
     */
    public function scopeAllTenants(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('tenant');
    }
}
