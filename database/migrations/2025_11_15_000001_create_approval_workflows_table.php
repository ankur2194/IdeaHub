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
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Standard Approval", "Department Specific"
            $table->string('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('min_budget')->nullable(); // Min budget requiring this workflow
            $table->integer('max_budget')->nullable(); // Max budget for this workflow
            $table->json('approval_levels'); // JSON array of approval level definitions
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('priority')->default(0); // Higher priority workflows are checked first
            $table->timestamps();

            // Indexes
            $table->index(['category_id', 'is_active']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};
