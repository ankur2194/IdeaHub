<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Idea;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Category $category;
    protected Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::factory()->create();
        $this->tag = Tag::factory()->create();
    }

    /**
     * Test listing ideas returns paginated results.
     */
    public function test_list_ideas_returns_paginated_results(): void
    {
        Idea::factory()->count(20)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/ideas');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'title', 'description', 'status', 'user', 'category'],
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /**
     * Test filtering ideas by status.
     */
    public function test_can_filter_ideas_by_status(): void
    {
        Idea::factory()->create(['status' => 'draft']);
        Idea::factory()->create(['status' => 'pending']);
        Idea::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ideas?status=pending');

        $response->assertStatus(200);

        $ideas = $response->json('data.data');
        $this->assertCount(1, $ideas);
        $this->assertEquals('pending', $ideas[0]['status']);
    }

    /**
     * Test filtering ideas by category.
     */
    public function test_can_filter_ideas_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Idea::factory()->create(['category_id' => $category1->id]);
        Idea::factory()->create(['category_id' => $category2->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/ideas?category_id={$category1->id}");

        $response->assertStatus(200);

        $ideas = $response->json('data.data');
        $this->assertCount(1, $ideas);
        $this->assertEquals($category1->id, $ideas[0]['category_id']);
    }

    /**
     * Test searching ideas by title or description.
     */
    public function test_can_search_ideas(): void
    {
        Idea::factory()->create(['title' => 'Dark Mode Feature']);
        Idea::factory()->create(['description' => 'Implement dark mode']);
        Idea::factory()->create(['title' => 'Other Feature']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ideas?search=dark');

        $response->assertStatus(200);

        $ideas = $response->json('data.data');
        $this->assertGreaterThanOrEqual(2, count($ideas));
    }

    /**
     * Test sorting ideas.
     */
    public function test_can_sort_ideas(): void
    {
        Idea::factory()->create(['likes_count' => 10, 'created_at' => now()->subDays(2)]);
        Idea::factory()->create(['likes_count' => 5, 'created_at' => now()->subDay()]);
        Idea::factory()->create(['likes_count' => 15, 'created_at' => now()]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ideas?sort_by=likes_count&sort_order=desc');

        $response->assertStatus(200);

        $ideas = $response->json('data.data');
        $this->assertEquals(15, $ideas[0]['likes_count']);
    }

    /**
     * Test creating an idea successfully.
     */
    public function test_user_can_create_idea(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ideas', [
                'title' => 'New Feature Idea',
                'description' => 'Detailed description of the feature',
                'category_id' => $this->category->id,
                'is_anonymous' => false,
                'tags' => [$this->tag->id],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Idea created successfully',
                'data' => [
                    'title' => 'New Feature Idea',
                    'status' => 'draft',
                ],
            ]);

        $this->assertDatabaseHas('ideas', [
            'title' => 'New Feature Idea',
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);
    }

    /**
     * Test creating idea without optional fields.
     */
    public function test_user_can_create_idea_without_optional_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ideas', [
                'title' => 'Simple Idea',
                'description' => 'Simple description',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('ideas', [
            'title' => 'Simple Idea',
            'category_id' => null,
        ]);
    }

    /**
     * Test creating idea fails without required fields.
     */
    public function test_create_idea_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ideas', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description']);
    }

    /**
     * Test creating idea fails with invalid category.
     */
    public function test_create_idea_fails_with_invalid_category(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ideas', [
                'title' => 'Test Idea',
                'description' => 'Test description',
                'category_id' => 99999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    /**
     * Test viewing idea increments view count.
     */
    public function test_viewing_idea_increments_view_count(): void
    {
        $idea = Idea::factory()->create(['views_count' => 0]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/ideas/{$idea->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $idea->id,
                    'views_count' => 1,
                ],
            ]);

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'views_count' => 1,
        ]);
    }

    /**
     * Test user can update their own idea.
     */
    public function test_user_can_update_own_idea(): void
    {
        $idea = Idea::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/ideas/{$idea->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Idea updated successfully',
            ]);

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * Test user cannot update another user's idea.
     */
    public function test_user_cannot_update_another_users_idea(): void
    {
        $otherUser = User::factory()->create();
        $idea = Idea::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/ideas/{$idea->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to update this idea',
            ]);
    }

    /**
     * Test admin can update any idea.
     */
    public function test_admin_can_update_any_idea(): void
    {
        $idea = Idea::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/ideas/{$idea->id}", [
                'title' => 'Admin Updated Title',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'title' => 'Admin Updated Title',
        ]);
    }

    /**
     * Test updating idea with tags.
     */
    public function test_can_update_idea_with_tags(): void
    {
        $idea = Idea::factory()->create(['user_id' => $this->user->id]);
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/ideas/{$idea->id}", [
                'tags' => [$tag1->id, $tag2->id],
            ]);

        $response->assertStatus(200);

        $this->assertEquals(2, $idea->fresh()->tags()->count());
    }

    /**
     * Test user can delete their own idea.
     */
    public function test_user_can_delete_own_idea(): void
    {
        $idea = Idea::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/ideas/{$idea->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Idea deleted successfully',
            ]);

        $this->assertSoftDeleted('ideas', ['id' => $idea->id]);
    }

    /**
     * Test user cannot delete another user's idea.
     */
    public function test_user_cannot_delete_another_users_idea(): void
    {
        $otherUser = User::factory()->create();
        $idea = Idea::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/ideas/{$idea->id}");

        $response->assertStatus(403);
    }

    /**
     * Test admin can delete any idea.
     */
    public function test_admin_can_delete_any_idea(): void
    {
        $idea = Idea::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/ideas/{$idea->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('ideas', ['id' => $idea->id]);
    }

    /**
     * Test user can submit their draft idea.
     */
    public function test_user_can_submit_draft_idea(): void
    {
        $idea = Idea::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/ideas/{$idea->id}/submit");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Idea submitted for approval',
            ]);

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'status' => 'pending',
        ]);
        $this->assertNotNull($idea->fresh()->submitted_at);
    }

    /**
     * Test user cannot submit another user's idea.
     */
    public function test_user_cannot_submit_another_users_idea(): void
    {
        $otherUser = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/ideas/{$idea->id}/submit");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to submit this idea',
            ]);
    }

    /**
     * Test cannot submit non-draft idea.
     */
    public function test_cannot_submit_non_draft_idea(): void
    {
        $idea = Idea::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/ideas/{$idea->id}/submit");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Only draft ideas can be submitted',
            ]);
    }

    /**
     * Test user can like an idea.
     */
    public function test_user_can_like_idea(): void
    {
        $idea = Idea::factory()->create(['likes_count' => 5]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/ideas/{$idea->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Idea liked',
                'data' => [
                    'likes_count' => 6,
                ],
            ]);

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'likes_count' => 6,
        ]);
    }

    /**
     * Test unauthenticated user cannot access ideas.
     */
    public function test_unauthenticated_user_cannot_access_ideas(): void
    {
        $response = $this->getJson('/api/ideas');
        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot create idea.
     */
    public function test_unauthenticated_user_cannot_create_idea(): void
    {
        $response = $this->postJson('/api/ideas', [
            'title' => 'Test',
            'description' => 'Test',
        ]);

        $response->assertStatus(401);
    }
}
