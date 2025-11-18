<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\BrandingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\GamificationController;
use App\Http\Controllers\Api\IdeaController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\SsoController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\WidgetController;
use Illuminate\Support\Facades\Route;

// Public routes with rate limiting for security
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/sso/callback', [SsoController::class, 'callback']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Email verification (public but requires signed URL)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');

// Public read-only routes (no auth required)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{tag}', [TagController::class, 'show']);

// Branding (public - for displaying tenant branding)
Route::get('/branding', [BrandingController::class, 'index']);

// SSO (public - for SSO authentication flow)
Route::get('/sso/providers', [SsoController::class, 'index']);
Route::get('/sso/{provider}/initiate', [SsoController::class, 'initiate']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
        ->name('verification.send');

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

    // Badges
    Route::get('/badges', [BadgeController::class, 'index']);
    Route::get('/badges/{badge}', [BadgeController::class, 'show']);
    Route::get('/badges/user/{user}', [BadgeController::class, 'userBadges']);
    Route::get('/my/badges', [BadgeController::class, 'myBadges']);
    Route::get('/my/badge-progress', [BadgeController::class, 'progress']);

    // Gamification
    Route::get('/gamification/my-stats', [GamificationController::class, 'myStats']);
    Route::get('/gamification/user/{user}', [GamificationController::class, 'userStats']);
    Route::get('/gamification/leaderboard', [GamificationController::class, 'leaderboard']);
    Route::get('/gamification/level-rankings', [GamificationController::class, 'levelRankings']);
    Route::get('/gamification/recent-achievements', [GamificationController::class, 'recentAchievements']);
    Route::get('/gamification/xp-breakdown', [GamificationController::class, 'xpBreakdown']);

    // Export
    Route::get('/export/analytics/pdf', [ExportController::class, 'exportAnalyticsPDF']);
    Route::get('/export/analytics/csv', [ExportController::class, 'exportAnalyticsCSV']);
    Route::get('/export/ideas/csv', [ExportController::class, 'exportIdeasCSV']);
    Route::get('/export/users/csv', [ExportController::class, 'exportUsersCSV']);

    // Dashboards
    Route::apiResource('dashboards', DashboardController::class);
    Route::post('/dashboards/{dashboard}/set-default', [DashboardController::class, 'setDefault']);
    Route::post('/dashboards/{dashboard}/share', [DashboardController::class, 'share']);
    Route::get('/dashboards/{dashboard}/widgets/{widgetId}/data', [DashboardController::class, 'widgetData']);
    Route::get('/dashboards/shared/all', [DashboardController::class, 'shared']);

    // Widgets
    Route::apiResource('widgets', WidgetController::class);
    Route::get('/widgets/{widget}/preview', [WidgetController::class, 'preview']);
    Route::get('/widgets-metadata', [WidgetController::class, 'metadata']);

    // Branding (admin only for updates)
    Route::put('/branding', [BrandingController::class, 'update']);
    Route::post('/branding/upload-logo', [BrandingController::class, 'uploadLogo']);
    Route::delete('/branding/logo/{type}', [BrandingController::class, 'deleteLogo']);
    Route::post('/branding/reset', [BrandingController::class, 'reset']);

    // SSO (admin only for configuration)
    Route::get('/sso/providers/{provider}', [SsoController::class, 'show']);
    Route::post('/sso/configure', [SsoController::class, 'configure']);
    Route::delete('/sso/providers/{provider}', [SsoController::class, 'destroy']);
    Route::post('/sso/providers/{provider}/test', [SsoController::class, 'test']);

    // Integrations
    Route::apiResource('integrations', IntegrationController::class);
    Route::post('/integrations/{integration}/test', [IntegrationController::class, 'test']);
    Route::post('/integrations/{integration}/sync', [IntegrationController::class, 'sync']);
});
