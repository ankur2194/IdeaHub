<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Idea;
use App\Services\NotificationService;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IdeaController extends Controller
{
    /**
     * Display a listing of ideas.
     */
    public function index(Request $request)
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

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $ideas = $query->paginate($request->get('per_page', 15));

        // Add 'liked' attribute to each idea
        if (Auth::check()) {
            $userId = Auth::id();
            $ideas->getCollection()->transform(function ($idea) use ($userId) {
                $idea->liked = $idea->likedBy()->where('user_id', $userId)->exists();
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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip',
        ]);

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
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
    public function submit(Idea $idea, PointsService $pointsService, NotificationService $notificationService)
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

        // Notify approvers
        $idea->load('user', 'category');
        $notificationService->notifyIdeaSubmitted($idea);

        return response()->json([
            'success' => true,
            'message' => 'Idea submitted for approval (+10 points)',
            'data' => $idea,
        ]);
    }

    /**
     * Like or unlike an idea.
     */
    public function like(Idea $idea, PointsService $pointsService)
    {
        $user = Auth::user();

        // Check if user already liked this idea
        if ($idea->likedBy()->where('user_id', $user->id)->exists()) {
            // Unlike: remove the like
            $idea->likedBy()->detach($user->id);
            $idea->decrement('likes_count');

            // Deduct points from idea author
            if ($idea->user) {
                $pointsService->deductLikeRemoved($idea->user);
            }

            $liked = false;
            $message = 'Idea unliked';
        } else {
            // Like: add the like
            $idea->likedBy()->attach($user->id);
            $idea->increment('likes_count');

            // Award points to idea author
            if ($idea->user) {
                $pointsService->awardLikeReceived($idea->user);
            }

            $liked = true;
            $message = 'Idea liked';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'liked' => $liked,
                'likes_count' => $idea->likes_count,
            ],
        ]);
    }
}
