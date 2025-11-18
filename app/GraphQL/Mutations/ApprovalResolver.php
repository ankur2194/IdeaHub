<?php

namespace App\GraphQL\Mutations;

use App\Models\Approval;
use App\Models\Idea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalResolver
{
    /**
     * Approve an idea.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function approve($_, array $args)
    {
        $user = Auth::user();
        $idea = Idea::findOrFail($args['id']);

        // Check if user has permission to approve
        if (! $user->isAdmin() && ! $user->isDepartmentHead() && ! $user->isTeamLead()) {
            throw new \Exception('Unauthorized to approve ideas.');
        }

        if ($idea->status !== 'pending') {
            throw new \Exception('Only pending ideas can be approved.');
        }

        DB::beginTransaction();

        try {
            // Create approval record
            $approval = Approval::create([
                'idea_id' => $idea->id,
                'approver_id' => $user->id,
                'status' => 'approved',
                'notes' => $args['notes'] ?? null,
                'level' => 1, // Simple approval system for now
                'approved_at' => now(),
            ]);

            // Update idea status
            $idea->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Update idea author stats
            $idea->user->increment('ideas_approved');

            DB::commit();

            return $approval;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject an idea.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function reject($_, array $args)
    {
        $user = Auth::user();
        $idea = Idea::findOrFail($args['id']);

        // Check if user has permission to reject
        if (! $user->isAdmin() && ! $user->isDepartmentHead() && ! $user->isTeamLead()) {
            throw new \Exception('Unauthorized to reject ideas.');
        }

        if ($idea->status !== 'pending') {
            throw new \Exception('Only pending ideas can be rejected.');
        }

        DB::beginTransaction();

        try {
            // Create approval record
            $approval = Approval::create([
                'idea_id' => $idea->id,
                'approver_id' => $user->id,
                'status' => 'rejected',
                'notes' => $args['notes'],
                'level' => 1,
                'rejected_at' => now(),
            ]);

            // Update idea status
            $idea->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            DB::commit();

            return $approval;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
