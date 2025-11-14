<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        ]);

        $idea = Idea::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'] ?? null,
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'status' => 'draft',
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
        ]);

        $idea->update($validated);

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

        $idea->delete();

        return response()->json([
            'success' => true,
            'message' => 'Idea deleted successfully',
        ]);
    }

    /**
     * Submit an idea for approval.
     */
    public function submit(Idea $idea)
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

        return response()->json([
            'success' => true,
            'message' => 'Idea submitted for approval',
            'data' => $idea,
        ]);
    }

    /**
     * Like an idea.
     */
    public function like(Idea $idea)
    {
        $idea->increment('likes_count');

        return response()->json([
            'success' => true,
            'message' => 'Idea liked',
            'data' => ['likes_count' => $idea->likes_count],
        ]);
    }
}
