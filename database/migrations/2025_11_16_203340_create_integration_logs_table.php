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
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('action'); // what was synced (e.g., 'idea_created', 'comment_posted')
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->json('payload')->nullable(); // request/response data
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['integration_id', 'created_at']);
            $table->index(['tenant_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
