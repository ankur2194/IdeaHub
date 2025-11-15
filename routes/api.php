<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\IdeaController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public read-only routes (no auth required)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{tag}', [TagController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Ideas
    Route::apiResource('ideas', IdeaController::class);
    Route::post('/ideas/{idea}/submit', [IdeaController::class, 'submit']);
    Route::post('/ideas/{idea}/like', [IdeaController::class, 'like']);

    // Comments
    Route::get('/ideas/{idea}/comments', [CommentController::class, 'index']);
    Route::apiResource('comments', CommentController::class)->except(['index']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);

    // Approvals
    Route::apiResource('approvals', ApprovalController::class);
    Route::post('/approvals/{approval}/approve', [ApprovalController::class, 'approve']);
    Route::post('/approvals/{approval}/reject', [ApprovalController::class, 'reject']);
    Route::get('/approvals/pending/count', [ApprovalController::class, 'pending']);
    Route::get('/ideas/{idea}/workflow-status', [ApprovalController::class, 'workflowStatus']);

    // Categories (protected operations)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Tags (protected operations)
    Route::post('/tags', [TagController::class, 'store']);
    Route::put('/tags/{tag}', [TagController::class, 'update']);
    Route::delete('/tags/{tag}', [TagController::class, 'destroy']);

    // Analytics
    Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    Route::get('/analytics/ideas-trend', [AnalyticsController::class, 'ideasTrend']);
    Route::get('/analytics/category-distribution', [AnalyticsController::class, 'categoryDistribution']);
    Route::get('/analytics/status-breakdown', [AnalyticsController::class, 'statusBreakdown']);
    Route::get('/analytics/leaderboard', [AnalyticsController::class, 'leaderboard']);
    Route::get('/analytics/department-stats', [AnalyticsController::class, 'departmentStats']);
    Route::get('/analytics/recent-activity', [AnalyticsController::class, 'recentActivity']);
    Route::get('/analytics/user-stats', [AnalyticsController::class, 'userStats']);
});
