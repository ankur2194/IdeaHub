<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Quick Win', 'slug' => 'quick-win', 'color' => '#10b981'],
            ['name' => 'High Impact', 'slug' => 'high-impact', 'color' => '#f59e0b'],
            ['name' => 'Low Cost', 'slug' => 'low-cost', 'color' => '#3b82f6'],
            ['name' => 'Innovation', 'slug' => 'innovation', 'color' => '#8b5cf6'],
            ['name' => 'Urgent', 'slug' => 'urgent', 'color' => '#ef4444'],
            ['name' => 'Long-term', 'slug' => 'long-term', 'color' => '#6366f1'],
            ['name' => 'Revenue Growth', 'slug' => 'revenue-growth', 'color' => '#059669'],
            ['name' => 'Customer Focused', 'slug' => 'customer-focused', 'color' => '#ec4899'],
            ['name' => 'Digital Transformation', 'slug' => 'digital-transformation', 'color' => '#8b5cf6'],
            ['name' => 'Automation', 'slug' => 'automation', 'color' => '#6366f1'],
            ['name' => 'Mobile', 'slug' => 'mobile', 'color' => '#3b82f6'],
            ['name' => 'AI/ML', 'slug' => 'ai-ml', 'color' => '#8b5cf6'],
            ['name' => 'UX/UI', 'slug' => 'ux-ui', 'color' => '#ec4899'],
            ['name' => 'Security', 'slug' => 'security', 'color' => '#ef4444'],
            ['name' => 'Scalability', 'slug' => 'scalability', 'color' => '#10b981'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
