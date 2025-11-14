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
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'implemented'])->default('draft');
            $table->boolean('is_anonymous')->default(false);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->json('attachments')->nullable(); // Store file paths as JSON array
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('implemented_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); // For soft deleting ideas

            // Indexes for better query performance
            $table->index('status');
            $table->index('created_at');
            $table->index('likes_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ideas');
    }
};
