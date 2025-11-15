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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "First Idea", "Innovator"
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->nullable(); // Icon class or path
            $table->string('type'); // achievement, milestone, special
            $table->string('category')->nullable(); // ideas, comments, likes, approvals
            $table->json('criteria'); // Conditions to earn this badge
            $table->integer('points_reward')->default(0); // Extra points for earning
            $table->string('rarity')->default('common'); // common, rare, epic, legendary
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'category']);
            $table->index('is_active');
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->integer('progress')->default(0); // For tracking progress toward badge
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
            $table->index('earned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
