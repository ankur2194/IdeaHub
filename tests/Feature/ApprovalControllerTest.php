<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected User $departmentHead;

    protected User $approver;

    protected Idea $idea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->departmentHead = User::factory()->create(['role' => 'department_head']);
        $this->approver = User::factory()->create(['role' => 'team_lead']);
        $this->idea = Idea::factory()->create();
    }

    /**
     * Test listing approvals for authenticated user.
     */
    public function test_user_can_list_their_approvals(): void
    {
        Approval::factory()->count(3)->create(['approver_id' => $this->approver->id]);
        Approval::factory()->count(2)->create(['approver_id' => $this->admin->id]);

        $response = $this->actingAs($this->approver)
            ->getJson('/api/approvals');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'idea_id', 'approver_id', 'status', 'level'],
                    ],
                ],
            ]);

        $approvals = $response->json('data.data');
        $this->assertCount(3, $approvals);
    }

    /**
     * Test filtering approvals by status.
     */
    public function test_can_filter_approvals_by_status(): void
    {
        Approval::factory()->create([
            'approver_id' => $this->approver->id,
            'status' => 'pending',
        ]);
        Approval::factory()->create([
            'approver_id' => $this->approver->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->approver)
            ->getJson('/api/approvals?status=pending');

        $response->assertStatus(200);

        $approvals = $response->json('data.data');
        $this->assertCount(1, $approvals);
        $this->assertEquals('pending', $approvals[0]['status']);
    }

    /**
     * Test admin can create approval request.
     */
    public function test_admin_can_create_approval_request(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/approvals', [
                'idea_id' => $this->idea->id,
                'approver_id' => $this->approver->id,
                'level' => 1,
                'notes' => 'Please review this idea',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Approval request created successfully',
                'data' => [
                    'idea_id' => $this->idea->id,
                    'approver_id' => $this->approver->id,
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('approvals', [
            'idea_id' => $this->idea->id,
            'approver_id' => $this->approver->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test department head can create approval request.
     */
    public function test_department_head_can_create_approval_request(): void
    {
        $response = $this->actingAs($this->departmentHead)
            ->postJson('/api/approvals', [
                'idea_id' => $this->idea->id,
                'approver_id' => $this->approver->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('approvals', [
            'idea_id' => $this->idea->id,
            'approver_id' => $this->approver->id,
        ]);
    }

    /**
     * Test regular user cannot create approval request.
     */
    public function test_regular_user_cannot_create_approval_request(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/approvals', [
                'idea_id' => $this->idea->id,
                'approver_id' => $this->approver->id,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to create approval requests',
            ]);
    }

    /**
     * Test create approval fails without required fields.
     */
    public function test_create_approval_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/approvals', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['idea_id', 'approver_id']);
    }

    /**
     * Test create approval fails with invalid idea_id.
     */
    public function test_create_approval_fails_with_invalid_idea_id(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/approvals', [
                'idea_id' => 99999,
                'approver_id' => $this->approver->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['idea_id']);
    }

    /**
     * Test viewing an approval.
     */
    public function test_user_can_view_approval(): void
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->approver->id,
        ]);

        $response = $this->actingAs($this->approver)
            ->getJson("/api/approvals/{$approval->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $approval->id,
                ],
            ]);
    }

    /**
     * Test approver can approve their pending approval.
     */
    public function test_approver_can_approve_pending_approval(): void
    {
        $approval = Approval::factory()->create([
            'idea_id' => $this->idea->id,
            'approver_id' => $this->approver->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->approver)
            ->postJson("/api/approvals/{$approval->id}/approve", [
                'notes' => 'Looks good, approved!',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Idea approved successfully',
            ]);

        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('ideas', [
            'id' => $this->idea->id,
            'status' => 'approved',
        ]);

        $this->assertNotNull($approval->fresh()->approved_at);
        $this->assertNotNull($this->idea->fresh()->approved_at);
    }

    /**
     * Test approver cannot approve another user's approval.
     */
    public function test_approver_cannot_approve_another_users_approval(): void
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->approver)
            ->postJson("/api/approvals/{$approval->id}/approve");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to approve this request',
            ]);
    }

    /**
     * Test cannot approve already processed approval.
     */
    public function test_cannot_approve_already_processed_approval(): void
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->approver->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->approver)
            ->postJson("/api/approvals/{$approval->id}/approve");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'This approval has already been processed',
            ]);
    }

    /**
     * Test approver can reject pending approval.
     */
    public function test_approver_can_reject_pending_approval(): void
    {
        $approval = Approval::factory()->create([
            'idea_id' => $this->idea->id,
            'approver_id' => $this->approver->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->approver)
            ->postJson("/api/approvals/{$approval->id}/reject", [
                'notes' => 'Needs more detail',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Idea rejected',
            ]);

        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('ideas', [
            'id' => $this->idea->id,
            'status' => 'rejected',
        ]);

        $this->assertNotNull($approval->fresh()->rejected_at);
        $this->assertNotNull($this->idea->fresh()->rejected_at);
    }

    /**
     * Test rejection requires notes.
     */
    public function test_rejection_requires_notes(): void
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->approver->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->approver)
            ->postJson("/api/approvals/{$approval->id}/reject", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    /**
     * Test approver cannot reject another user's approval.
     */
    public function test_approver_cannot_reject_another_users_approval(): void
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->approver)
            ->postJson("/api/approvals/{$approval->id}/reject", [
                'notes' => 'Rejected',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to reject this request',
            ]);
    }

    /**
     * Test cannot reject already processed approval.
     */
    public function test_cannot_reject_already_processed_approval(): void
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->approver->id,
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($this->approver)
            ->postJson("/api/approvals/{$approval->id}/reject", [
                'notes' => 'Rejected again',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'This approval has already been processed',
            ]);
    }

    /**
     * Test getting pending approvals count.
     */
    public function test_can_get_pending_approvals_count(): void
    {
        Approval::factory()->count(3)->create([
            'approver_id' => $this->approver->id,
            'status' => 'pending',
        ]);
        Approval::factory()->count(2)->create([
            'approver_id' => $this->approver->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->approver)
            ->getJson('/api/approvals/pending/count');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 3,
                ],
            ]);
    }

    /**
     * Test admin can delete approval.
     */
    public function test_admin_can_delete_approval(): void
    {
        $approval = Approval::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/approvals/{$approval->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Approval deleted successfully',
            ]);

        $this->assertDatabaseMissing('approvals', [
            'id' => $approval->id,
        ]);
    }

    /**
     * Test non-admin cannot delete approval.
     */
    public function test_non_admin_cannot_delete_approval(): void
    {
        $approval = Approval::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/approvals/{$approval->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to delete approvals',
            ]);
    }

    /**
     * Test unauthenticated user cannot access approvals.
     */
    public function test_unauthenticated_user_cannot_access_approvals(): void
    {
        $response = $this->getJson('/api/approvals');
        $response->assertStatus(401);
    }
}
