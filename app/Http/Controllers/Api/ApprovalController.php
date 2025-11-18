<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Idea;
use App\Services\ApprovalWorkflowService;
use App\Services\NotificationService;
use App\Services\PointsService;
use App\Events\IdeaApproved;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Display approvals for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Approval::with(['idea.user', 'idea.category', 'approver'])
            ->where('approver_id', Auth::id());

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Validate pagination limit to prevent excessive resource usage
        $perPage = $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100)); // Clamp between 1 and 100

        $approvals = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }

    /**
     * Store a newly created approval request.
     */
    public function store(Request $request): JsonResponse
    {
        // Only admins and department heads can create approval requests
        if (!Auth::user()->isAdmin() && !Auth::user()->isDepartmentHead()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create approval requests',
            ], 403);
        }

        $validated = $request->validate([
            'idea_id' => 'required|exists:ideas,id',
            'approver_id' => 'required|exists:users,id',
            'level' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $approval = Approval::create([
            'idea_id' => $validated['idea_id'],
            'approver_id' => $validated['approver_id'],
            'level' => $validated['level'] ?? 1,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        $approval->load(['idea', 'approver']);

        return response()->json([
            'success' => true,
            'message' => 'Approval request created successfully',
            'data' => $approval,
        ], 201);
    }

    /**
     * Display the specified approval.
     */
    public function show(Approval $approval): JsonResponse
    {
        $approval->load(['idea.user', 'idea.category', 'approver']);

        return response()->json([
            'success' => true,
            'data' => $approval,
        ]);
    }

    /**
     * Approve an idea.
     */
    public function approve(Request $request, Approval $approval, ApprovalWorkflowService $workflowService, NotificationService $notificationService, PointsService $pointsService, \App\Services\GamificationService $gamificationService): JsonResponse
    {
        // Check authorization
        if ($approval->approver_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to approve this request',
            ], 403);
        }

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This approval has already been processed',
            ], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        // Process approval through workflow service
        $result = $workflowService->processApproval(
            $approval,
            'approved',
            $validated['notes'] ?? null
        );

        $idea = $approval->idea->fresh();

        // Send notifications based on workflow result
        if ($result['final_status'] === 'approved') {
            // Idea is fully approved
            $notificationService->notifyIdeaApproved($idea);
            $pointsService->awardIdeaApproved($idea->user);
            $gamificationService->trackIdeaApproved($idea->user);

            // Broadcast idea approved event
            broadcast(new IdeaApproved($idea));

            $message = 'Idea fully approved! (+50 points, +100 XP for author)';
        } elseif ($result['next_level']) {
            // Move to next approval level
            if (!empty($result['pending_approvals'])) {
                foreach ($result['pending_approvals'] as $nextApproval) {
                    $notificationService->notifyApprovalRequest($nextApproval);
                }
            }
            $message = "Approval recorded. Moving to level {$result['next_level']}.";
        } else {
            $message = 'Approval recorded. Waiting for other approvers at this level.';
        }

        $approval->load(['idea', 'approver']);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'approval' => $approval,
                'workflow_status' => $result,
                'idea_status' => $idea->status,
            ],
        ]);
    }

    /**
     * Reject an idea.
     */
    public function reject(Request $request, Approval $approval, ApprovalWorkflowService $workflowService, NotificationService $notificationService): JsonResponse
    {
        // Check authorization
        if ($approval->approver_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject this request',
            ], 403);
        }

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This approval has already been processed',
            ], 400);
        }

        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        // Process rejection through workflow service
        $result = $workflowService->processApproval(
            $approval,
            'rejected',
            $validated['notes']
        );

        $idea = $approval->idea->fresh();

        // Notify idea author of rejection
        $notificationService->notifyIdeaRejected($idea, $validated['notes']);

        $approval->load(['idea', 'approver']);

        return response()->json([
            'success' => true,
            'message' => 'Idea rejected',
            'data' => [
                'approval' => $approval,
                'workflow_status' => $result,
                'idea_status' => $idea->status,
            ],
        ]);
    }

    /**
     * Get pending approvals count.
     */
    public function pending(): JsonResponse
    {
        $count = Approval::where('approver_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }

    /**
     * Get workflow status for an idea.
     */
    public function workflowStatus(Idea $idea, ApprovalWorkflowService $workflowService): JsonResponse
    {
        $status = $workflowService->getWorkflowStatus($idea);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Remove the specified approval.
     */
    public function destroy(Approval $approval): JsonResponse
    {
        // Only admins can delete approvals
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete approvals',
            ], 403);
        }

        $approval->delete();

        return response()->json([
            'success' => true,
            'message' => 'Approval deleted successfully',
        ]);
    }
}
