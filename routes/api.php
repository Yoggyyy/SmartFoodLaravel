<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ShoppingListController;


// Rutas públicas de autenticación (API)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/verify-email/{id}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
});

// Rutas protegidas que requieren autenticación (API)
Route::middleware('auth:sanctum')->group(function () {
    // Rutas de autenticación para usuarios autenticados
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerificationEmail']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/update-profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });

    // Rutas del chat con gpt
    Route::prefix('chat')->group(function () {
        Route::post('/send-message', [ChatController::class, 'sendMessage']);
        Route::get('/usage-stats', [ChatController::class, 'getUsageStats']);
        Route::get('/suggestions', [ChatController::class, 'getQuickSuggestions']);
    });

    // Rutas de listas de compra (API)
    Route::prefix('listas')->group(function () {
        Route::put('/{listId}/productos/{productId}', [ShoppingListController::class, 'updateProduct']);
        Route::delete('/{listId}/productos/{productId}', [ShoppingListController::class, 'removeProduct']);
        Route::put('/{listId}/productos/{productId}/completed', [ShoppingListController::class, 'updateProductCompleted']);
        Route::put('/{listId}/status', [ShoppingListController::class, 'updateListStatus']);
    });
});
