<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Idea;
use App\Services\NotificationService;
use App\Services\PointsService;
use App\Events\IdeaCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IdeaController extends Controller
{
    /**
     * Display a listing of ideas.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Idea::with(['user', 'category', 'tags'])
            ->withCount(['comments', 'approvals']);

        // Filter by status
        if ($request->has('status')) {
            $query->status($request->status);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by user/author
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by tags (multiple tags support)
        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tags.id', $tags);
            });
        }

        // Date range filtering
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by title or description (enhanced)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort column to prevent SQL injection
        $allowedSorts = ['created_at', 'updated_at', 'likes_count', 'comments_count', 'views_count', 'title'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Validate pagination limit to prevent excessive resource usage
        $perPage = $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100)); // Clamp between 1 and 100

        $ideas = $query->paginate($perPage);

        // Add 'liked' attribute to each idea (optimized to prevent N+1 query)
        if (Auth::check()) {
            $userId = Auth::id();

            // Get all liked idea IDs in a single query
            $likedIdeaIds = DB::table('idea_likes')
                ->where('user_id', $userId)
                ->whereIn('idea_id', $ideas->pluck('id'))
                ->pluck('idea_id')
                ->toArray();

            $ideas->getCollection()->transform(function ($idea) use ($likedIdeaIds) {
                $idea->liked = in_array($idea->id, $likedIdeaIds);
                return $idea;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $ideas,
        ]);
    }

    /**
     * Store a newly created idea.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'is_anonymous' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'attachments' => 'nullable|array|max:5',
            // Security: ZIP files removed to prevent executable content uploads
            // For production, consider implementing virus scanning (e.g., ClamAV)
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif',
        ]);

        // Handle file uploads with security checks
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Additional security: Validate file extension matches MIME type
                $extension = $file->getClientOriginalExtension();
                $mimeType = $file->getMimeType();

                // Sanitize filename to prevent path traversal
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                $filename = time() . '_' . Str::random(10) . '_' . $safeName . '.' . $extension;

                $path = $file->storeAs('idea-attachments', $filename, 'public');

                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $idea = Idea::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'] ?? null,
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'status' => 'draft',
            'attachments' => !empty($attachmentPaths) ? $attachmentPaths : null,
        ]);

        // Attach tags if provided
        if (isset($validated['tags'])) {
            $idea->tags()->attach($validated['tags']);
        }

        $idea->load(['user', 'category', 'tags']);

        // Broadcast idea created event
        broadcast(new IdeaCreated($idea));

        return response()->json([
            'success' => true,
            'message' => 'Idea created successfully',
            'data' => $idea,
        ], 201);
    }

    /**
     * Display the specified idea.
     */
    public function show(Idea $idea)
    {
        // Increment view count
        $idea->increment('views_count');

        $idea->load(['user', 'category', 'tags', 'comments.user', 'approvals.approver']);

        // Add 'liked' attribute
        if (Auth::check()) {
            $idea->liked = $idea->likedBy()->where('user_id', Auth::id())->exists();
        }

        return response()->json([
            'success' => true,
            'data' => $idea,
        ]);
    }

    /**
     * Update the specified idea.
     */
    public function update(Request $request, Idea $idea)
    {
        // Check authorization
        if ($idea->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this idea',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'nullable|exists:categories,id',
            'is_anonymous' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip',
            'remove_attachments' => 'nullable|array',
        ]);

        // Handle new file uploads
        $existingAttachments = $idea->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('idea-attachments', $filename, 'public');

                $existingAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        // Handle attachment removal
        if ($request->has('remove_attachments')) {
            $removeIndexes = $request->input('remove_attachments');
            foreach ($removeIndexes as $index) {
                if (isset($existingAttachments[$index])) {
                    // Delete file from storage
                    Storage::disk('public')->delete($existingAttachments[$index]['path']);
                    unset($existingAttachments[$index]);
                }
            }
            $existingAttachments = array_values($existingAttachments); // Re-index array
        }

        $updateData = array_filter($validated, function ($key) {
            return !in_array($key, ['tags', 'attachments', 'remove_attachments']);
        }, ARRAY_FILTER_USE_KEY);

        $updateData['attachments'] = !empty($existingAttachments) ? $existingAttachments : null;

        $idea->update($updateData);

        // Sync tags if provided
        if (isset($validated['tags'])) {
            $idea->tags()->sync($validated['tags']);
        }

        $idea->load(['user', 'category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Idea updated successfully',
            'data' => $idea,
        ]);
    }

    /**
     * Remove the specified idea.
     */
    public function destroy(Idea $idea)
    {
        // Check authorization
        if ($idea->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this idea',
            ], 403);
        }

        // Delete associated attachments from storage
        if ($idea->attachments) {
            foreach ($idea->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $idea->delete();

        return response()->json([
            'success' => true,
            'message' => 'Idea deleted successfully',
        ]);
    }

    /**
     * Submit an idea for approval.
     */
    public function submit(Idea $idea, PointsService $pointsService, NotificationService $notificationService, \App\Services\ApprovalWorkflowService $workflowService, \App\Services\GamificationService $gamificationService)
    {
        if ($idea->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to submit this idea',
            ], 403);
        }

        if ($idea->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft ideas can be submitted',
            ], 400);
        }

        $idea->update([
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        // Award points for idea submission
        $pointsService->awardIdeaSubmitted($idea->user);

        // Track gamification (XP, badges, levels)
        $gamificationService->trackIdeaSubmitted($idea->user);

        // Initialize approval workflow
        $idea->load('user', 'category');
        $approvals = $workflowService->initializeWorkflow($idea);

        // Notify first-level approvers
        $firstLevelApprovals = $approvals->where('level', 1);
        foreach ($firstLevelApprovals as $approval) {
            $notificationService->notifyApprovalRequest($approval);
        }

        return response()->json([
            'success' => true,
            'message' => "Idea submitted for approval (+10 points, +20 XP). {$approvals->count()} approver(s) notified.",
            'data' => [
                'idea' => $idea,
                'approvals_created' => $approvals->count(),
                'first_level_approvers' => $firstLevelApprovals->count(),
            ],
        ]);
    }

    /**
     * Like or unlike an idea.
     */
    public function like(Idea $idea, \App\Services\LikeService $likeService, PointsService $pointsService, \App\Services\GamificationService $gamificationService)
    {
        $user = Auth::user();

        // Use LikeService to handle like/unlike logic
        $result = $likeService->likeIdea($idea, $user->id);

        // Award/deduct points and gamification XP
        if ($result['liked']) {
            // Like: award points to idea author
            if ($idea->user) {
                $pointsService->awardLikeReceived($idea->user);
                $gamificationService->trackLikeReceived($idea->user);
            }

            // Track like given by current user
            $gamificationService->trackLikeGiven($user);

            $liked = true;
            $message = $result['message'];
        } else {
            // Unlike: deduct points from idea author
            // Note: XP is not deducted as it's a progression system
            if ($idea->user) {
                $pointsService->deductLikeRemoved($idea->user);
            }

            $liked = false;
            $message = $result['message'];
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'liked' => $liked,
                'likes_count' => $result['likes_count'],
            ],
        ]);
    }
}
