<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShoppingListController;
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
Route::post('/login', [AuthController::class, 'webLogin'])->name('web.login');
Route::post('/register', [AuthController::class, 'webRegister'])->name('web.register');
Route::post('/logout', [AuthController::class, 'webLogout'])->name('web.logout');

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
    Route::get('/api/listas/grouped', [ShoppingListController::class, 'getGroupedLists'])->name('shopping-lists.grouped');
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
    Route::get('/user/me', [AuthController::class, 'webMe'])->name('web.user.me');
    Route::put('/user/update-profile', [AuthController::class, 'webUpdateProfile'])->name('web.user.update-profile');
    Route::put('/user/change-password', [AuthController::class, 'webChangePassword'])->name('web.user.change-password');
    Route::get('/user/stats', [AuthController::class, 'webUserStats'])->name('web.user.stats');

    // Rutas del chat (usando sesiones)
    Route::post('/chat/send-message', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('web.chat.send-message');
    Route::get('/chat/usage-stats', [\App\Http\Controllers\ChatController::class, 'getUsageStats'])->name('web.chat.usage-stats');
    Route::get('/chat/suggestions', [\App\Http\Controllers\ChatController::class, 'getQuickSuggestions'])->name('web.chat.suggestions');

    // Rutas para gestionar conversaciones de chat por usuario
    Route::get('/chat/conversations', [\App\Http\Controllers\ChatController::class, 'getConversations'])->name('web.chat.conversations');
    Route::post('/chat/conversations', [\App\Http\Controllers\ChatController::class, 'createConversation'])->name('web.chat.create-conversation');
    Route::get('/chat/conversations/{id}', [\App\Http\Controllers\ChatController::class, 'getConversation'])->name('web.chat.get-conversation');
    Route::put('/chat/conversations/{id}', [\App\Http\Controllers\ChatController::class, 'updateConversation'])->name('web.chat.update-conversation');
    Route::delete('/chat/conversations/{id}', [\App\Http\Controllers\ChatController::class, 'deleteConversation'])->name('web.chat.delete-conversation');
    Route::post('/chat/conversations/{id}/messages', [\App\Http\Controllers\ChatController::class, 'addMessage'])->name('web.chat.add-message');
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
