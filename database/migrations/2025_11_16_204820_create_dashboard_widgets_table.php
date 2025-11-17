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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // bar, line, pie, area, stats_card, list, table
            $table->string('category'); // ideas, users, analytics, approvals
            $table->json('config')->nullable(); // Widget configuration (query, filters, display options)
            $table->boolean('is_system')->default(false); // System-wide or custom
            $table->text('description')->nullable();
            $table->timestamps();

            // Index for faster queries
            $table->index(['tenant_id', 'category']);
            $table->index('is_system');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
