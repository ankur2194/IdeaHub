<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Display approvals for the current user.
     */
    public function index(Request $request)
    {
        $query = Approval::with(['idea.user', 'idea.category', 'approver'])
            ->where('approver_id', Auth::id());

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $approvals = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }

    /**
     * Store a newly created approval request.
     */
    public function store(Request $request)
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
    public function show(Approval $approval)
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
    public function approve(Request $request, Approval $approval)
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

        $approval->update([
            'status' => 'approved',
            'notes' => $validated['notes'] ?? null,
            'approved_at' => now(),
        ]);

        // Update idea status
        $idea = $approval->idea;
        $idea->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Idea approved successfully',
            'data' => $approval,
        ]);
    }

    /**
     * Reject an idea.
     */
    public function reject(Request $request, Approval $approval)
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

        $approval->update([
            'status' => 'rejected',
            'notes' => $validated['notes'],
            'rejected_at' => now(),
        ]);

        // Update idea status
        $idea = $approval->idea;
        $idea->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Idea rejected',
            'data' => $approval,
        ]);
    }

    /**
     * Get pending approvals count.
     */
    public function pending()
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
     * Remove the specified approval.
     */
    public function destroy(Approval $approval)
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
