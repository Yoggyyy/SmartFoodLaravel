<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
    return view('chat');
})->middleware('auth:sanctum')->name('chat');

Route::get('/profile', function () {
    return view('profile');
})->middleware('auth:sanctum')->name('profile');

Route::get('/settings', function () {
    return view('settings');
})->middleware('auth:sanctum')->name('settings');

// Rutas web para datos de usuario (usando sesiones)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/me', [AuthController::class, 'webMe'])->name('web.user.me');
    Route::put('/user/update-profile', [AuthController::class, 'webUpdateProfile'])->name('web.user.update-profile');
    Route::put('/user/change-password', [AuthController::class, 'webChangePassword'])->name('web.user.change-password');

    // Rutas del chat (usando sesiones)
    Route::post('/chat/send-message', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('web.chat.send-message');
    Route::get('/chat/usage-stats', [\App\Http\Controllers\ChatController::class, 'getUsageStats'])->name('web.chat.usage-stats');
    Route::get('/chat/suggestions', [\App\Http\Controllers\ChatController::class, 'getQuickSuggestions'])->name('web.chat.suggestions');
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
