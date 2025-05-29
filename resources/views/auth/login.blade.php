@extends('layouts.app')

@section('title', 'Iniciar Sesión - SmartFood')
@section('page-title', 'SmartFood')
@section('page-subtitle', 'Bienvenido de nuevo a SmartFood')

@section('form-content')
<form id="login-form" class="flex flex-col gap-4 w-full">
    <!-- Mensajes de Laravel (verificación, etc.) -->
    @if(session('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Email -->
    <div class="flex flex-col">
        <label for="email" class="mb-1 text-sm">Email</label>
        <input
            type="email"
            name="email"
            id="email"
            class="input-field"
            required
        />
    </div>

    <!-- Contraseña -->
    <div class="flex flex-col">
        <label for="password" class="mb-1 text-sm">Contraseña</label>
        <div class="relative">
            <input
                type="password"
                name="password"
                id="password"
                class="input-field pr-10"
                required
            />
            <button
                type="button"
                id="toggle-password"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mensaje de error del login -->
    <p id="error-message" class="text-red-500 text-sm" style="display: none;"></p>

    <!-- Mensaje de verificación -->
    <div id="verification-message" class="text-orange-500 text-sm" style="display: none;">
        <p>Tu cuenta necesita verificación de email.</p>
        <p class="mt-2">
            <strong>Para desarrollo:</strong>
            <button type="button" id="verify-dev-btn" class="text-green-600 hover:text-green-700 underline">
                Verificar mi email automáticamente
            </button>
        </p>
    </div>

    <!-- Botón -->
    <button id="login-button" type="submit" class="btn-primary mt-4 flex items-center justify-center">
        <span id="login-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
        <span id="login-button-text">Iniciar Sesión</span>
    </button>

    <!-- Enlace a Registro -->
    <p class="text-center mt-4 text-sm">
        ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-green-600 hover:text-green-700 font-medium">Regístrate aquí</a>
    </p>


</form>
@endsection

@section('scripts')
<script src="{{ asset('js/common.js') }}"></script>
<script src="{{ asset('js/auth/login.js') }}"></script>
@endsection
