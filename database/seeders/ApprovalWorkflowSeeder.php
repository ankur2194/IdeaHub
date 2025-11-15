<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ApprovalWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default single-level workflow for all ideas
        ApprovalWorkflow::create([
            'name' => 'Standard Approval',
            'description' => 'Default single-level approval by admins and department heads',
            'category_id' => null,
            'min_budget' => null,
            'max_budget' => null,
            'approval_levels' => [
                [
                    'level' => 1,
                    'approver_roles' => ['admin', 'department_head'],
                    'require_all' => false, // Any one approver can approve
                ],
            ],
            'is_active' => true,
            'is_default' => true,
            'priority' => 1,
        ]);

        // Two-level workflow for high-budget ideas
        ApprovalWorkflow::create([
            'name' => 'High Budget Approval',
            'description' => 'Two-level approval for ideas with budget > $10,000',
            'category_id' => null,
            'min_budget' => 10000,
            'max_budget' => null,
            'approval_levels' => [
                [
                    'level' => 1,
                    'approver_roles' => ['department_head'],
                    'require_all' => false,
                ],
                [
                    'level' => 2,
                    'approver_roles' => ['admin'],
                    'require_all' => true, // All admins must approve
                ],
            ],
            'is_active' => true,
            'is_default' => false,
            'priority' => 10, // Higher priority than default
        ]);

        // Three-level workflow for strategic category
        $strategicCategory = Category::where('slug', 'strategic')->first();
        if ($strategicCategory) {
            ApprovalWorkflow::create([
                'name' => 'Strategic Ideas Workflow',
                'description' => 'Three-level approval for strategic initiatives',
                'category_id' => $strategicCategory->id,
                'min_budget' => null,
                'max_budget' => null,
                'approval_levels' => [
                    [
                        'level' => 1,
                        'approver_roles' => ['team_lead'],
                        'require_all' => false,
                    ],
                    [
                        'level' => 2,
                        'approver_roles' => ['department_head'],
                        'require_all' => false,
                    ],
                    [
                        'level' => 3,
                        'approver_roles' => ['admin'],
                        'require_all' => true,
                    ],
                ],
                'is_active' => true,
                'is_default' => false,
                'priority' => 15, // Highest priority
            ]);
        }
    }
}
