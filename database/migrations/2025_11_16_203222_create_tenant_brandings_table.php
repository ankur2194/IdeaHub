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
        Schema::create('tenant_brandings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->onDelete('cascade');

            // Logo and Images
            $table->string('logo_url')->nullable();
            $table->string('logo_dark_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('login_background_url')->nullable();

            // Brand Colors
            $table->string('primary_color')->default('#3b82f6');
            $table->string('secondary_color')->default('#8b5cf6');
            $table->string('accent_color')->default('#10b981');
            $table->string('success_color')->default('#22c55e');
            $table->string('warning_color')->default('#f59e0b');
            $table->string('error_color')->default('#ef4444');

            // Text and Background
            $table->string('text_color')->default('#1f2937');
            $table->string('background_color')->default('#ffffff');
            $table->string('surface_color')->default('#f9fafb');

            // Typography
            $table->string('font_family')->default('Inter, system-ui, sans-serif');
            $table->string('heading_font_family')->nullable();

            // Customization
            $table->string('app_name')->nullable();
            $table->string('app_tagline')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_url')->nullable();

            // Custom CSS
            $table->text('custom_css')->nullable();

            // Social Links
            $table->json('social_links')->nullable();

            // Footer
            $table->text('footer_text')->nullable();
            $table->boolean('show_powered_by')->default(true);

            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_brandings');
    }
};
