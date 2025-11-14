<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Resource routes will be added here
    // Route::apiResource('ideas', IdeaController::class);
    // Route::apiResource('comments', CommentController::class);
    // Route::apiResource('approvals', ApprovalController::class);
    // Route::apiResource('categories', CategoryController::class);
    // Route::apiResource('tags', TagController::class);
});
