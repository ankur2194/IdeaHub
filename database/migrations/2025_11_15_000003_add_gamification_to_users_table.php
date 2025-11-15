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
            $table->integer('level')->default(1)->after('points');
            $table->integer('experience')->default(0)->after('level'); // XP toward next level
            $table->string('title')->nullable()->after('experience'); // Rank title
            $table->integer('total_badges')->default(0)->after('title');
            $table->integer('ideas_submitted')->default(0)->after('total_badges');
            $table->integer('ideas_approved')->default(0)->after('ideas_submitted');
            $table->integer('comments_posted')->default(0)->after('ideas_approved');
            $table->integer('likes_given')->default(0)->after('comments_posted');
            $table->integer('likes_received')->default(0)->after('likes_given');

            $table->index('level');
            $table->index('experience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'level',
                'experience',
                'title',
                'total_badges',
                'ideas_submitted',
                'ideas_approved',
                'comments_posted',
                'likes_given',
                'likes_received',
            ]);
        });
    }
};
