<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SavingsGoalController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ApiReportController;
use App\Http\Controllers\Api\AiTipsController;

// Public routes
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::put('/user', [ApiAuthController::class, 'updateProfile']);
    Route::put('/user/password', [ApiAuthController::class, 'updatePassword']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Transactions
    Route::apiResource('transactions', TransactionController::class)->names('api.transactions');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);

    // Budgets
    Route::apiResource('budgets', BudgetController::class)->names('api.budgets');

    // Savings Goals
    Route::apiResource('savings-goals', SavingsGoalController::class)->names('api.savings-goals');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Reports
    Route::get('/reports', [ApiReportController::class, 'index']);
    Route::get('/reports/export', [ApiReportController::class, 'export']);

    // AI Tips
    Route::get('/ai-tips', [AiTipsController::class, 'index']);
});
