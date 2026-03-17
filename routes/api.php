<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/users', [\App\Http\Controllers\Api\UserController::class, 'index']); // Lấy list toàn bộ users
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('projects', \App\Http\Controllers\Api\ProjectController::class);
    Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class);

    // Bổ sung API Bình luận
    Route::get('tasks/{task}/comments', [\App\Http\Controllers\CommentController::class, 'index']);
    Route::post('tasks/{task}/comments', [\App\Http\Controllers\CommentController::class, 'store']);
    Route::delete('comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy']);

    // API Thông báo (Notifications)
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
});
