<?php

namespace App\GraphQL\Queries;

use App\Models\Tag;

class TagResolver
{
    /**
     * Get popular tags.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function popular($_, array $args)
    {
        $limit = $args['limit'] ?? 20;

        return Tag::query()
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();
    }
}
