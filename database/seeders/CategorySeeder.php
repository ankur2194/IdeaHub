<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Product Innovation',
                'slug' => 'product-innovation',
                'description' => 'Ideas for new products or improvements to existing products',
                'color' => '#3b82f6',
                'icon' => 'lightbulb',
                'is_active' => true,
            ],
            [
                'name' => 'Process Improvement',
                'slug' => 'process-improvement',
                'description' => 'Streamline workflows and optimize business processes',
                'color' => '#10b981',
                'icon' => 'cog',
                'is_active' => true,
            ],
            [
                'name' => 'Customer Experience',
                'slug' => 'customer-experience',
                'description' => 'Enhance customer satisfaction and engagement',
                'color' => '#f59e0b',
                'icon' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'Technology & Tools',
                'slug' => 'technology-tools',
                'description' => 'New technologies, software, or tools to improve operations',
                'color' => '#8b5cf6',
                'icon' => 'cpu-chip',
                'is_active' => true,
            ],
            [
                'name' => 'Cost Reduction',
                'slug' => 'cost-reduction',
                'description' => 'Ideas to reduce costs and improve efficiency',
                'color' => '#ef4444',
                'icon' => 'currency-dollar',
                'is_active' => true,
            ],
            [
                'name' => 'Sustainability',
                'slug' => 'sustainability',
                'description' => 'Environmental and sustainable business practices',
                'color' => '#059669',
                'icon' => 'globe-alt',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing & Sales',
                'slug' => 'marketing-sales',
                'description' => 'Strategies to boost marketing effectiveness and sales',
                'color' => '#ec4899',
                'icon' => 'megaphone',
                'is_active' => true,
            ],
            [
                'name' => 'Workplace Culture',
                'slug' => 'workplace-culture',
                'description' => 'Improve employee engagement and workplace environment',
                'color' => '#6366f1',
                'icon' => 'heart',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
