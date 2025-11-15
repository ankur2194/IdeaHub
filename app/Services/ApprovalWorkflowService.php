<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalWorkflow;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Support\Collection;

class ApprovalWorkflowService
{
    /**
     * Initialize approval workflow for an idea.
     */
    public function initializeWorkflow(Idea $idea): Collection
    {
        $workflow = $this->determineWorkflow($idea);

        if (!$workflow) {
            // No workflow found, use default single-level approval
            return $this->createDefaultApprovals($idea);
        }

        return $this->createWorkflowApprovals($idea, $workflow);
    }

    /**
     * Determine which workflow to use for an idea.
     */
    protected function determineWorkflow(Idea $idea): ?ApprovalWorkflow
    {
        // Find workflows that match the idea's criteria
        $workflows = ApprovalWorkflow::active()
            ->byPriority()
            ->get();

        foreach ($workflows as $workflow) {
            // Category match
            if ($workflow->category_id && $workflow->category_id !== $idea->category_id) {
                continue;
            }

            // Budget match (if idea has budget field)
            if ($workflow->min_budget || $workflow->max_budget) {
                $ideaBudget = $idea->budget ?? 0;

                if ($workflow->min_budget && $ideaBudget < $workflow->min_budget) {
                    continue;
                }

                if ($workflow->max_budget && $ideaBudget > $workflow->max_budget) {
                    continue;
                }
            }

            // If all criteria match, use this workflow
            return $workflow;
        }

        // Return default workflow if exists
        return ApprovalWorkflow::active()
            ->where('is_default', true)
            ->first();
    }

    /**
     * Create approval requests based on workflow definition.
     */
    protected function createWorkflowApprovals(Idea $idea, ApprovalWorkflow $workflow): Collection
    {
        $approvals = collect();
        $levels = $workflow->approval_levels;

        foreach ($levels as $levelData) {
            $level = $levelData['level'];
            $approverRoles = $levelData['approver_roles'] ?? [];
            $approverIds = $levelData['approver_ids'] ?? [];
            $requireAll = $levelData['require_all'] ?? false;

            // Get approvers by roles
            if (!empty($approverRoles)) {
                $roleApprovers = User::whereIn('role', $approverRoles)
                    ->where('is_active', true)
                    ->get();

                foreach ($roleApprovers as $approver) {
                    $approval = Approval::create([
                        'idea_id' => $idea->id,
                        'approver_id' => $approver->id,
                        'level' => $level,
                        'status' => 'pending',
                    ]);
                    $approvals->push($approval);
                }
            }

            // Get specific approvers by ID
            if (!empty($approverIds)) {
                foreach ($approverIds as $approverId) {
                    $approval = Approval::create([
                        'idea_id' => $idea->id,
                        'approver_id' => $approverId,
                        'level' => $level,
                        'status' => 'pending',
                    ]);
                    $approvals->push($approval);
                }
            }
        }

        return $approvals;
    }

    /**
     * Create default single-level approvals (admins and department heads).
     */
    protected function createDefaultApprovals(Idea $idea): Collection
    {
        $approvals = collect();
        $approvers = User::whereIn('role', ['admin', 'department_head'])
            ->where('is_active', true)
            ->get();

        foreach ($approvers as $approver) {
            $approval = Approval::create([
                'idea_id' => $idea->id,
                'approver_id' => $approver->id,
                'level' => 1,
                'status' => 'pending',
            ]);
            $approvals->push($approval);
        }

        return $approvals;
    }

    /**
     * Process approval and trigger next level if needed.
     */
    public function processApproval(Approval $approval, string $action, ?string $notes = null): array
    {
        $idea = $approval->idea;

        // Update the approval
        $approval->update([
            'status' => $action,
            'notes' => $notes,
            $action === 'approved' ? 'approved_at' : 'rejected_at' => now(),
        ]);

        if ($action === 'rejected') {
            // If rejected, reject the entire idea
            $idea->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            return [
                'final_status' => 'rejected',
                'next_level' => null,
                'pending_approvals' => [],
            ];
        }

        // Check if this level is complete
        $currentLevel = $approval->level;
        $levelApprovals = Approval::where('idea_id', $idea->id)
            ->where('level', $currentLevel)
            ->get();

        $allApproved = $levelApprovals->every(fn($a) => $a->status === 'approved');
        $anyRejected = $levelApprovals->contains(fn($a) => $a->status === 'rejected');

        if ($anyRejected) {
            $idea->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            return [
                'final_status' => 'rejected',
                'next_level' => null,
                'pending_approvals' => [],
            ];
        }

        if (!$allApproved) {
            // Still waiting for other approvals at this level
            return [
                'final_status' => 'pending',
                'next_level' => $currentLevel,
                'pending_approvals' => $levelApprovals->where('status', 'pending'),
            ];
        }

        // Current level is complete, check for next level
        $nextLevelApprovals = Approval::where('idea_id', $idea->id)
            ->where('level', $currentLevel + 1)
            ->pending()
            ->get();

        if ($nextLevelApprovals->isEmpty()) {
            // No more levels, idea is fully approved
            $idea->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return [
                'final_status' => 'approved',
                'next_level' => null,
                'pending_approvals' => [],
            ];
        }

        // Move to next level
        $idea->update(['status' => 'under_review']);

        return [
            'final_status' => 'under_review',
            'next_level' => $currentLevel + 1,
            'pending_approvals' => $nextLevelApprovals,
        ];
    }

    /**
     * Get approval workflow status for an idea.
     */
    public function getWorkflowStatus(Idea $idea): array
    {
        $approvals = Approval::where('idea_id', $idea->id)
            ->with('approver')
            ->orderBy('level')
            ->get();

        $levels = $approvals->groupBy('level');
        $currentLevel = 1;

        $levelStatuses = [];
        foreach ($levels as $level => $levelApprovals) {
            $approved = $levelApprovals->where('status', 'approved')->count();
            $rejected = $levelApprovals->where('status', 'rejected')->count();
            $pending = $levelApprovals->where('status', 'pending')->count();
            $total = $levelApprovals->count();

            $levelStatus = 'pending';
            if ($rejected > 0) {
                $levelStatus = 'rejected';
            } elseif ($approved === $total) {
                $levelStatus = 'approved';
                $currentLevel = $level + 1;
            } elseif ($approved > 0) {
                $levelStatus = 'in_progress';
            }

            $levelStatuses[] = [
                'level' => $level,
                'status' => $levelStatus,
                'approved' => $approved,
                'rejected' => $rejected,
                'pending' => $pending,
                'total' => $total,
                'approvals' => $levelApprovals,
            ];
        }

        return [
            'current_level' => $currentLevel,
            'total_levels' => $levels->count(),
            'levels' => $levelStatuses,
            'overall_status' => $idea->status,
        ];
    }
}
