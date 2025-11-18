<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DashboardWidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemWidgets = [
            // Ideas Category - Stats Cards
            [
                'name' => 'Total Ideas',
                'type' => 'stats_card',
                'category' => 'ideas',
                'description' => 'Total number of ideas submitted',
                'is_system' => true,
                'config' => [
                    'metric' => 'count',
                    'aggregation' => 'count',
                ],
            ],
            [
                'name' => 'Pending Ideas',
                'type' => 'stats_card',
                'category' => 'ideas',
                'description' => 'Ideas awaiting review',
                'is_system' => true,
                'config' => [
                    'status' => 'pending',
                    'metric' => 'count',
                    'aggregation' => 'count',
                ],
            ],
            [
                'name' => 'Approved Ideas',
                'type' => 'stats_card',
                'category' => 'ideas',
                'description' => 'Ideas that have been approved',
                'is_system' => true,
                'config' => [
                    'status' => 'approved',
                    'metric' => 'count',
                    'aggregation' => 'count',
                ],
            ],
            [
                'name' => 'Implemented Ideas',
                'type' => 'stats_card',
                'category' => 'ideas',
                'description' => 'Ideas that have been implemented',
                'is_system' => true,
                'config' => [
                    'status' => 'implemented',
                    'metric' => 'count',
                    'aggregation' => 'count',
                ],
            ],

            // Ideas Category - Charts
            [
                'name' => 'Ideas by Status',
                'type' => 'pie',
                'category' => 'ideas',
                'description' => 'Distribution of ideas by status',
                'is_system' => true,
                'config' => [
                    'aggregation' => 'count',
                    'group_by' => 'status',
                ],
            ],
            [
                'name' => 'Ideas Trend (30 Days)',
                'type' => 'line',
                'category' => 'ideas',
                'description' => 'Trend of idea submissions over the last 30 days',
                'is_system' => true,
                'config' => [
                    'time_range' => '30d',
                    'aggregation' => 'count',
                    'group_by' => 'date',
                ],
            ],
            [
                'name' => 'Ideas by Category',
                'type' => 'bar',
                'category' => 'ideas',
                'description' => 'Number of ideas per category',
                'is_system' => true,
                'config' => [
                    'aggregation' => 'count',
                    'group_by' => 'category',
                    'limit' => 10,
                ],
            ],
            [
                'name' => 'Recent Ideas',
                'type' => 'list',
                'category' => 'ideas',
                'description' => 'Most recently submitted ideas',
                'is_system' => true,
                'config' => [
                    'limit' => 10,
                    'order_by' => 'created_at',
                    'order' => 'desc',
                ],
            ],
            [
                'name' => 'Top Ideas by Likes',
                'type' => 'table',
                'category' => 'ideas',
                'description' => 'Ideas with the most likes',
                'is_system' => true,
                'config' => [
                    'limit' => 10,
                    'order_by' => 'likes_count',
                    'order' => 'desc',
                ],
            ],

            // Users Category
            [
                'name' => 'Total Users',
                'type' => 'stats_card',
                'category' => 'users',
                'description' => 'Total number of registered users',
                'is_system' => true,
                'config' => [
                    'metric' => 'count',
                    'aggregation' => 'count',
                ],
            ],
            [
                'name' => 'Active Users',
                'type' => 'stats_card',
                'category' => 'users',
                'description' => 'Number of active users',
                'is_system' => true,
                'config' => [
                    'metric' => 'count',
                    'aggregation' => 'count',
                    'filter' => 'is_active',
                ],
            ],
            [
                'name' => 'User Growth',
                'type' => 'area',
                'category' => 'users',
                'description' => 'User registration trend',
                'is_system' => true,
                'config' => [
                    'time_range' => '90d',
                    'aggregation' => 'count',
                    'group_by' => 'date',
                ],
            ],
            [
                'name' => 'Top Contributors',
                'type' => 'table',
                'category' => 'users',
                'description' => 'Users with most ideas submitted',
                'is_system' => true,
                'config' => [
                    'limit' => 10,
                    'order_by' => 'ideas_submitted',
                    'order' => 'desc',
                ],
            ],
            [
                'name' => 'Leaderboard',
                'type' => 'list',
                'category' => 'users',
                'description' => 'Top users by points',
                'is_system' => true,
                'config' => [
                    'limit' => 10,
                    'order_by' => 'points',
                    'order' => 'desc',
                ],
            ],

            // Approvals Category
            [
                'name' => 'Pending Approvals',
                'type' => 'stats_card',
                'category' => 'approvals',
                'description' => 'Number of pending approvals',
                'is_system' => true,
                'config' => [
                    'status' => 'pending',
                    'metric' => 'count',
                    'aggregation' => 'count',
                ],
            ],
            [
                'name' => 'Approvals by Status',
                'type' => 'pie',
                'category' => 'approvals',
                'description' => 'Distribution of approvals by status',
                'is_system' => true,
                'config' => [
                    'aggregation' => 'count',
                    'group_by' => 'status',
                ],
            ],
            [
                'name' => 'Approval Queue',
                'type' => 'table',
                'category' => 'approvals',
                'description' => 'Ideas awaiting approval',
                'is_system' => true,
                'config' => [
                    'status' => 'pending',
                    'limit' => 15,
                    'order_by' => 'created_at',
                    'order' => 'asc',
                ],
            ],
            [
                'name' => 'Recent Approvals',
                'type' => 'list',
                'category' => 'approvals',
                'description' => 'Recently processed approvals',
                'is_system' => true,
                'config' => [
                    'limit' => 10,
                    'order_by' => 'updated_at',
                    'order' => 'desc',
                ],
            ],

            // Analytics Category
            [
                'name' => 'Engagement Rate',
                'type' => 'stats_card',
                'category' => 'analytics',
                'description' => 'Overall platform engagement metrics',
                'is_system' => true,
                'config' => [
                    'metric' => 'engagement_rate',
                    'aggregation' => 'avg',
                ],
            ],
            [
                'name' => 'Total Comments',
                'type' => 'stats_card',
                'category' => 'analytics',
                'description' => 'Total number of comments',
                'is_system' => true,
                'config' => [
                    'metric' => 'count',
                    'aggregation' => 'count',
                ],
            ],
            [
                'name' => 'Activity Trend',
                'type' => 'line',
                'category' => 'analytics',
                'description' => 'Platform activity over time',
                'is_system' => true,
                'config' => [
                    'time_range' => '30d',
                    'aggregation' => 'count',
                    'group_by' => 'date',
                    'metrics' => ['ideas', 'comments', 'approvals'],
                ],
            ],
            [
                'name' => 'Department Activity',
                'type' => 'bar',
                'category' => 'analytics',
                'description' => 'Idea submissions by department',
                'is_system' => true,
                'config' => [
                    'aggregation' => 'count',
                    'group_by' => 'department',
                    'limit' => 10,
                ],
            ],
        ];

        foreach ($systemWidgets as $widget) {
            \App\Models\DashboardWidget::create($widget);
        }

        $this->command->info('Created '.count($systemWidgets).' system widget templates');
    }
}
