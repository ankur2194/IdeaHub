<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflow extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'category_id',
        'min_budget',
        'max_budget',
        'approval_levels',
        'is_active',
        'is_default',
        'priority',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approval_levels' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'min_budget' => 'integer',
            'max_budget' => 'integer',
            'priority' => 'integer',
        ];
    }

    /**
     * Get the category that owns the workflow.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include active workflows.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get workflows ordered by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
