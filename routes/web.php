<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ChatController;
use App\Models\Allergen;

// Ruta principal - redirigir al login
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de vistas (Frontend)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    $allergens = Allergen::all();
    return view('auth.register', compact('allergens'));
})->name('register');

// Rutas de autenticación web
Route::post('/login', [AuthController::class, 'login'])->name('web.login');
Route::post('/register', [AuthController::class, 'register'])->name('web.register');
Route::post('/logout', [AuthController::class, 'logout'])->name('web.logout');

// Rutas públicas de autenticación (recuperación de contraseña)
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// FUNCIONALIDAD COMENTADA - FUTURA IMPLEMENTACIÓN
// Verificación de email (pendiente de implementar)
// Route::get('/verify-email/{id}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::get('/chat', function () {
    return view('chat.index');
})->middleware('auth:sanctum')->name('chat');

Route::get('/profile', function () {
    return view('profile.index');
})->middleware('auth:sanctum')->name('profile');

Route::get('/settings', function () {
    return view('settings.index');
})->middleware('auth:sanctum')->name('settings');

// Rutas para las listas de compra
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/listas', [ShoppingListController::class, 'index'])->name('shopping-lists.index');
    Route::post('/listas', [ShoppingListController::class, 'store'])->name('shopping-lists.store');
    Route::get('/listas/{id}', [ShoppingListController::class, 'show'])->name('shopping-lists.show');
    Route::delete('/listas/{id}', [ShoppingListController::class, 'destroy'])->name('shopping-lists.destroy');
    Route::get('/listas/grouped', [ShoppingListController::class, 'getGroupedLists'])->name('shopping-lists.grouped');
    Route::post('/listas/create-from-chat', [ShoppingListController::class, 'createFromChat'])->name('shopping-lists.create-from-chat');

    // Rutas para gestionar productos en las listas
    Route::post('/listas/{id}/productos', [ShoppingListController::class, 'addProduct'])->name('shopping-lists.add-product');
    Route::put('/listas/{listId}/productos/{productId}', [ShoppingListController::class, 'updateProduct'])->name('shopping-lists.update-product');
    Route::delete('/listas/{listId}/productos/{productId}', [ShoppingListController::class, 'removeProduct'])->name('shopping-lists.remove-product');

    // Rutas web para funcionalidades de completado (usando sesiones en lugar de API)
    Route::put('/listas/{listId}/productos/{productId}/completed', [ShoppingListController::class, 'updateProductCompleted'])->name('shopping-lists.update-product-completed');
    Route::put('/listas/{listId}/status', [ShoppingListController::class, 'updateListStatus'])->name('shopping-lists.update-status');
});

// Rutas web para datos de usuario (usando sesiones)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/me', [AuthController::class, 'me'])->name('web.user.me');
    Route::put('/user/update-profile', [AuthController::class, 'updateProfile'])->name('web.user.update-profile');
    Route::put('/user/change-password', [AuthController::class, 'changePassword'])->name('web.user.change-password');
    Route::get('/user/stats', [AuthController::class, 'userStats'])->name('web.user.stats');

    // Rutas del chat (usando sesiones)
    Route::post('/chat/send-message', [ChatController::class, 'sendMessage'])->name('web.chat.send-message');
    Route::get('/chat/usage-stats', [ChatController::class, 'getUsageStats'])->name('web.chat.usage-stats');
    Route::get('/chat/suggestions', [ChatController::class, 'getQuickSuggestions'])->name('web.chat.suggestions');

    // Rutas para gestionar conversaciones de chat por usuario
    Route::get('/chat/conversations', [ChatController::class, 'getConversations'])->name('web.chat.conversations');
    Route::post('/chat/conversations', [ChatController::class, 'createConversation'])->name('web.chat.create-conversation');
    Route::get('/chat/conversations/{id}', [ChatController::class, 'getConversation'])->name('web.chat.get-conversation');
    Route::put('/chat/conversations/{id}', [ChatController::class, 'updateConversation'])->name('web.chat.update-conversation');
    Route::delete('/chat/conversations/{id}', [ChatController::class, 'deleteConversation'])->name('web.chat.delete-conversation');
    Route::post('/chat/conversations/{id}/messages', [ChatController::class, 'addMessage'])->name('web.chat.add-message');

    // Rutas adicionales para verificación de email
    //A FUTURO
   //Route::post('/resend-verification', [AuthController::class, 'resendVerificationEmail'])->name('verification.send');
});

// TEMPORAL: Ruta para verificar email manualmente (solo para desarrollo)
Route::get('/dev/verify-email/{email}', function ($email) {
    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        $user->email_verified_at = now();
        $user->save();
        return redirect('/login')->with('message', '¡Email verificado! Ya puedes iniciar sesión.');
    }
    return redirect('/login')->with('error', 'Usuario no encontrado.');
})->name('dev.verify.email');
