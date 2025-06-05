@extends('layouts.app')

@section('title', 'Iniciar Sesión - SmartFood')
@section('page-title', 'SmartFood')
@section('page-subtitle', 'Bienvenido de nuevo a SmartFood')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('form-content')
<!-- Formulario usando solo Tailwind con paleta simplificada -->
<form id="login-form" method="POST" action="{{ route('web.login') }}" class="flex flex-col gap-5 w-full">
    @csrf

    <!-- Mensajes de Laravel (verificación, éxito, etc.) -->
    @if(session('message'))
        <div class="bg-green-50 border border-green-600 text-green-700 px-4 py-3 rounded-lg text-base">
            {{ session('message') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-50 border border-green-600 text-green-700 px-4 py-3 rounded-lg text-base">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-600 text-red-600 px-4 py-3 rounded-lg text-base">
            {{ session('error') }}
        </div>
    @endif

    <!-- Errores de validación generales -->
    @if($errors->has('general'))
        <div class="bg-red-100 border border-red-600 text-red-600 px-4 py-3 rounded-lg text-base">
            {{ $errors->first('general') }}
        </div>
    @endif

    <!-- Email -->
    <div class="flex flex-col space-y-2">
        <label for="email" class="text-gray-900 text-base font-medium">Email</label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email') }}"
            class="bg-gray-600 border-2 rounded-lg px-4 py-3.5 text-white text-base w-full placeholder-gray-400 focus:outline-none focus:ring-2 {{ $errors->has('email') ? 'border-red-600 focus:border-red-600 focus:ring-red-600/20' : 'border-transparent focus:border-green-600 focus:ring-green-600/20' }}"
            required
            aria-describedby="email-error"
            placeholder="Ingresa tu email"
        />
        @error('email')
            <p id="email-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Contraseña -->
    <div class="flex flex-col space-y-2">
        <label for="password" class="text-gray-900 text-base font-medium">Contraseña</label>
        <div class="relative">
            <input
                type="password"
                name="password"
                id="password"
                class="bg-gray-600 border-2 rounded-lg px-4 py-3.5 pr-12 text-white text-base w-full placeholder-gray-400 focus:outline-none focus:ring-2 {{ $errors->has('password') ? 'border-red-600 focus:border-red-600 focus:ring-red-600/20' : 'border-transparent focus:border-green-600 focus:ring-green-600/20' }}"
                required
                aria-describedby="password-error"
                placeholder="Ingresa tu contraseña"
            />
            <button
                type="button"
                id="toggle-password"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                aria-label="Mostrar/ocultar contraseña"
            >
                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
        @error('password')
            <p id="password-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Recordar sesión -->
    <div class="flex items-center">
        <input
            type="checkbox"
            name="remember"
            id="remember"
            value="1"
            class="w-5 h-5 accent-green-600 mr-3"
            {{ old('remember') ? 'checked' : '' }}
        />
        <label for="remember" class="text-gray-900 text-base">Recordar mi sesión</label>
    </div>

    <!-- Mensaje de error dinámico (JavaScript) -->
    <p id="error-message" class="text-red-600 text-base hidden" role="alert"></p>

    <!-- Mensaje de verificación dinámico (JavaScript) -->
    <div id="verification-message" class="text-blue-600 text-base hidden" role="alert">
        <p>Tu cuenta necesita verificación de email.</p>
        <p class="mt-2">
            <strong>Para desarrollo:</strong>
            <button type="button" id="verify-dev-btn" class="text-green-600 hover:text-green-700 hover:underline font-medium transition-colors duration-200">
                Verificar mi email automáticamente
            </button>
        </p>
    </div>

    <!-- Botón -->
    <button id="login-button" type="submit" class="bg-green-100 hover:bg-green-600 hover:text-white text-gray-900 font-semibold py-4 px-8 rounded-lg border-none w-full cursor-pointer transition-all duration-200 text-base flex items-center justify-center gap-3 mt-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
        <span id="login-spinner" class="hidden w-5 h-5 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
        <span id="login-button-text">Iniciar Sesión</span>
    </button>

    <!-- Enlace a Registro -->
    <p class="text-center mt-4 text-base">
        ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-green-600 hover:text-green-700 hover:underline font-medium transition-colors duration-200">Regístrate aquí</a>
    </p>

    <!-- Nota sobre funcionalidad híbrida -->
    <noscript>
        <div class="bg-blue-100 border border-blue-600 text-blue-600 px-4 py-3 rounded-lg text-base mt-4">
            <p><strong>JavaScript deshabilitado:</strong> El formulario funciona sin JavaScript, pero la experiencia será más básica.</p>
        </div>
    </noscript>
</form>
@endsection

@section('scripts')
<script src="{{ asset('js/user-utils.js') }}"></script>
<script src="{{ asset('js/auth/login.js') }}"></script>
@endsection
