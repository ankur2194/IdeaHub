<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
        'usage_count',
    ];

    /**
     * The ideas that belong to the tag.
     */
    public function ideas(): BelongsToMany
    {
        return $this->belongsToMany(Idea::class)->withTimestamps();
    }

    /**
     * Scope a query to order tags by popularity.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }
}
