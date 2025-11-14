<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Idea $idea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->idea = Idea::factory()->create();
    }

    /**
     * Test listing comments for an idea.
     */
    public function test_can_list_comments_for_idea(): void
    {
        Comment::factory()->count(5)->create([
            'idea_id' => $this->idea->id,
            'parent_id' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/ideas/{$this->idea->id}/comments");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'content', 'user', 'idea_id'],
                    ],
                ],
            ]);
    }

    /**
     * Test listing comments only returns top-level comments.
     */
    public function test_list_comments_only_returns_top_level_comments(): void
    {
        $parentComment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'parent_id' => null,
        ]);

        Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'parent_id' => $parentComment->id, // Reply
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/ideas/{$this->idea->id}/comments");

        $response->assertStatus(200);

        $comments = $response->json('data.data');
        $this->assertCount(1, $comments); // Only top-level comment
    }

    /**
     * Test user can create a comment.
     */
    public function test_user_can_create_comment(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/comments', [
                'idea_id' => $this->idea->id,
                'content' => 'This is a great idea!',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => [
                    'content' => 'This is a great idea!',
                    'idea_id' => $this->idea->id,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'idea_id' => $this->idea->id,
            'user_id' => $this->user->id,
            'content' => 'This is a great idea!',
        ]);
    }

    /**
     * Test creating comment increments idea comment count.
     */
    public function test_creating_comment_increments_idea_comment_count(): void
    {
        $initialCount = $this->idea->comments_count;

        $this->actingAs($this->user)
            ->postJson('/api/comments', [
                'idea_id' => $this->idea->id,
                'content' => 'Test comment',
            ]);

        $this->assertEquals($initialCount + 1, $this->idea->fresh()->comments_count);
    }

    /**
     * Test user can create a reply to a comment.
     */
    public function test_user_can_create_reply_to_comment(): void
    {
        $parentComment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/comments', [
                'idea_id' => $this->idea->id,
                'content' => 'Reply to comment',
                'parent_id' => $parentComment->id,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'parent_id' => $parentComment->id,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'parent_id' => $parentComment->id,
            'content' => 'Reply to comment',
        ]);
    }

    /**
     * Test create comment fails without required fields.
     */
    public function test_create_comment_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/comments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['idea_id', 'content']);
    }

    /**
     * Test create comment fails with invalid idea_id.
     */
    public function test_create_comment_fails_with_invalid_idea_id(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/comments', [
                'idea_id' => 99999,
                'content' => 'Test comment',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['idea_id']);
    }

    /**
     * Test create comment fails with invalid parent_id.
     */
    public function test_create_comment_fails_with_invalid_parent_id(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/comments', [
                'idea_id' => $this->idea->id,
                'content' => 'Test comment',
                'parent_id' => 99999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    /**
     * Test user can view a comment.
     */
    public function test_user_can_view_comment(): void
    {
        $comment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                ],
            ]);
    }

    /**
     * Test user can update their own comment.
     */
    public function test_user_can_update_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'user_id' => $this->user->id,
            'content' => 'Original content',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/comments/{$comment->id}", [
                'content' => 'Updated content',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comment updated successfully',
                'data' => [
                    'content' => 'Updated content',
                    'is_edited' => true,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
            'is_edited' => true,
        ]);

        $this->assertNotNull($comment->fresh()->edited_at);
    }

    /**
     * Test user cannot update another user's comment.
     */
    public function test_user_cannot_update_another_users_comment(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/comments/{$comment->id}", [
                'content' => 'Updated content',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to update this comment',
            ]);
    }

    /**
     * Test update comment fails without content.
     */
    public function test_update_comment_fails_without_content(): void
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/comments/{$comment->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /**
     * Test user can delete their own comment.
     */
    public function test_user_can_delete_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comment deleted successfully',
            ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * Test deleting comment decrements idea comment count.
     */
    public function test_deleting_comment_decrements_idea_comment_count(): void
    {
        $comment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'user_id' => $this->user->id,
        ]);

        $this->idea->increment('comments_count');
        $initialCount = $this->idea->fresh()->comments_count;

        $this->actingAs($this->user)
            ->deleteJson("/api/comments/{$comment->id}");

        $this->assertEquals($initialCount - 1, $this->idea->fresh()->comments_count);
    }

    /**
     * Test user cannot delete another user's comment.
     */
    public function test_user_cannot_delete_another_users_comment(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to delete this comment',
            ]);
    }

    /**
     * Test admin can delete any comment.
     */
    public function test_admin_can_delete_any_comment(): void
    {
        $comment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * Test user can like a comment.
     */
    public function test_user_can_like_comment(): void
    {
        $comment = Comment::factory()->create([
            'idea_id' => $this->idea->id,
            'likes_count' => 5,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/comments/{$comment->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comment liked',
                'data' => [
                    'likes_count' => 6,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'likes_count' => 6,
        ]);
    }

    /**
     * Test unauthenticated user cannot create comment.
     */
    public function test_unauthenticated_user_cannot_create_comment(): void
    {
        $response = $this->postJson('/api/comments', [
            'idea_id' => $this->idea->id,
            'content' => 'Test comment',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot view comments.
     */
    public function test_unauthenticated_user_cannot_view_comments(): void
    {
        $response = $this->getJson("/api/ideas/{$this->idea->id}/comments");

        $response->assertStatus(401);
    }
}
