<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ideas table - common multi-tenant queries
        Schema::table('ideas', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'idx_ideas_tenant_status');
            $table->index(['tenant_id', 'category_id'], 'idx_ideas_tenant_category');
            $table->index(['tenant_id', 'user_id'], 'idx_ideas_tenant_user');
            $table->index(['tenant_id', 'created_at'], 'idx_ideas_tenant_created');
        });

        // Approvals table - filter by approver and status within tenant
        Schema::table('approvals', function (Blueprint $table) {
            $table->index(['tenant_id', 'approver_id'], 'idx_approvals_tenant_approver');
            $table->index(['tenant_id', 'status'], 'idx_approvals_tenant_status');
            $table->index(['tenant_id', 'idea_id'], 'idx_approvals_tenant_idea');
            $table->index(['tenant_id', 'level'], 'idx_approvals_tenant_level');
        });

        // Users table - SSO login and active users lookup
        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'email'], 'idx_users_tenant_email');
            $table->index(['tenant_id', 'is_active'], 'idx_users_tenant_active');
            $table->index(['tenant_id', 'role'], 'idx_users_tenant_role');
        });

        // Notifications table - user notifications within tenant
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_notifications_tenant_user');
            $table->index(['tenant_id', 'user_id', 'is_read'], 'idx_notifications_tenant_user_read');
            $table->index(['tenant_id', 'created_at'], 'idx_notifications_tenant_created');
        });

        // Comments table - idea comments within tenant
        Schema::table('comments', function (Blueprint $table) {
            $table->index(['tenant_id', 'idea_id'], 'idx_comments_tenant_idea');
            $table->index(['tenant_id', 'user_id'], 'idx_comments_tenant_user');
            $table->index(['tenant_id', 'parent_id'], 'idx_comments_tenant_parent');
        });

        // Categories table - active categories within tenant
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['tenant_id', 'is_active'], 'idx_categories_tenant_active');
        });

        // Tags table - active tags within tenant
        Schema::table('tags', function (Blueprint $table) {
            $table->index(['tenant_id', 'name'], 'idx_tags_tenant_name');
        });

        // Idea likes table - user likes within tenant
        Schema::table('idea_likes', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_idea_likes_tenant_user');
            $table->index(['tenant_id', 'idea_id'], 'idx_idea_likes_tenant_idea');
        });

        // Comment likes table - user likes within tenant
        Schema::table('comment_likes', function (Blueprint $table) {
            $table->index(['tenant_id', 'user_id'], 'idx_comment_likes_tenant_user');
            $table->index(['tenant_id', 'comment_id'], 'idx_comment_likes_tenant_comment');
        });

        // Approval workflows table - active workflows for tenant
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->index(['tenant_id', 'is_active'], 'idx_workflows_tenant_active');
            $table->index(['tenant_id', 'category_id'], 'idx_workflows_tenant_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropIndex('idx_ideas_tenant_status');
            $table->dropIndex('idx_ideas_tenant_category');
            $table->dropIndex('idx_ideas_tenant_user');
            $table->dropIndex('idx_ideas_tenant_created');
        });

        Schema::table('approvals', function (Blueprint $table) {
            $table->dropIndex('idx_approvals_tenant_approver');
            $table->dropIndex('idx_approvals_tenant_status');
            $table->dropIndex('idx_approvals_tenant_idea');
            $table->dropIndex('idx_approvals_tenant_level');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_tenant_email');
            $table->dropIndex('idx_users_tenant_active');
            $table->dropIndex('idx_users_tenant_role');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_tenant_user');
            $table->dropIndex('idx_notifications_tenant_user_read');
            $table->dropIndex('idx_notifications_tenant_created');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('idx_comments_tenant_idea');
            $table->dropIndex('idx_comments_tenant_user');
            $table->dropIndex('idx_comments_tenant_parent');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_tenant_active');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex('idx_tags_tenant_name');
        });

        Schema::table('idea_likes', function (Blueprint $table) {
            $table->dropIndex('idx_idea_likes_tenant_user');
            $table->dropIndex('idx_idea_likes_tenant_idea');
        });

        Schema::table('comment_likes', function (Blueprint $table) {
            $table->dropIndex('idx_comment_likes_tenant_user');
            $table->dropIndex('idx_comment_likes_tenant_comment');
        });

        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->dropIndex('idx_workflows_tenant_active');
            $table->dropIndex('idx_workflows_tenant_category');
        });
    }
};
