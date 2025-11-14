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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'team_lead', 'department_head', 'admin'])->default('user')->after('email');
            $table->string('avatar')->nullable()->after('role');
            $table->string('department')->nullable()->after('avatar');
            $table->string('job_title')->nullable()->after('department');
            $table->integer('points')->default(0)->after('job_title'); // For gamification
            $table->boolean('is_active')->default(true)->after('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar', 'department', 'job_title', 'points', 'is_active']);
        });
    }
};
