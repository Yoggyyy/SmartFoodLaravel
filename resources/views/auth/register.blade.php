@extends('layouts.app')

@section('title', 'Registro - SmartFood')
@section('page-title', 'SmartFood')
@section('page-subtitle', 'La nueva forma de preparar tus listas de la compra')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('form-content')
<!-- Formulario usando solo Tailwind con paleta simplificada -->
<form id="register-form" method="POST" action="{{ route('web.register') }}" class="flex flex-col gap-4 w-full">
    @csrf

    <!-- Mensajes de Laravel (éxito, errores, etc.) -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-600 text-green-700 px-4 py-3 rounded-md text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-600 text-red-600 px-4 py-3 rounded-md text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Errores de validación generales -->
    @if($errors->has('general'))
        <div class="bg-red-100 border border-red-600 text-red-600 px-4 py-3 rounded-md text-sm">
            {{ $errors->first('general') }}
        </div>
    @endif

    <!-- Fila Nombre y Apellidos -->
    <div class="flex gap-4">
        <div class="flex flex-col w-1/2">
            <label for="name" class="text-gray-900 text-sm font-medium mb-1">Nombre</label>
            <input
                type="text"
                name="name"
                id="name-input"
                value="{{ old('name') }}"
                class="bg-gray-600 border-2 rounded-md px-3 py-2 text-white text-sm w-full placeholder-gray-400 focus:outline-none focus:ring-2 {{ $errors->has('name') ? 'border-red-600 focus:border-red-600 focus:ring-red-600/20' : 'border-transparent focus:border-green-600 focus:ring-green-600/20' }}"
                required
                aria-describedby="name-error"
                placeholder="Tu nombre"
            />
            @error('name')
                <p id="name-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex flex-col w-1/2">
            <label for="surname" class="text-gray-900 text-sm font-medium mb-1">Apellidos</label>
            <input
                type="text"
                name="surname"
                id="surname-input"
                value="{{ old('surname') }}"
                class="bg-gray-600 border-2 rounded-md px-3 py-2 text-white text-sm w-full placeholder-gray-400 focus:outline-none focus:ring-2 {{ $errors->has('surname') ? 'border-red-600 focus:border-red-600 focus:ring-red-600/20' : 'border-transparent focus:border-green-600 focus:ring-green-600/20' }}"
                required
                aria-describedby="surname-error"
                placeholder="Tus apellidos"
            />
            @error('surname')
                <p id="surname-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Alérgenos con dropdown simplificado -->
    <div class="flex flex-col">
        <label for="allergens" class="text-gray-900 text-sm font-medium mb-1">Alérgenos</label>
        <div class="relative">
            <!-- Campo de display -->
            <div
                id="allergens-dropdown"
                class="bg-gray-600 border-2 rounded-md px-3 py-2 text-white text-sm w-full cursor-pointer flex items-center justify-between hover:border-green-600 {{ $errors->has('allergens') ? 'border-red-600' : 'border-transparent' }}"
                aria-describedby="allergens-error"
            >
                <span id="allergens-display" class="text-gray-400">Selecciona tus alérgenos</span>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>

            <!-- Dropdown -->
            <div id="allergens-dropdown-menu" class="hidden absolute top-full left-0 right-0 bg-gray-600 border-2 border-gray-400 rounded-md mt-1 max-h-48 overflow-y-auto z-50 shadow-lg">
                <!-- Campo de búsqueda -->
                <div class="p-2 border-b border-gray-500">
                    <input
                        type="text"
                        id="allergens-search"
                        placeholder="Buscar alérgenos..."
                        class="bg-gray-900 border-none rounded px-3 py-2 text-white text-sm w-full placeholder-gray-400 focus:outline-none focus:bg-gray-600"
                    />
                </div>

                <!-- Lista de alérgenos -->
                <div id="allergens-list" class="p-2">
                    @foreach($allergens as $allergen)
                        <label class="flex items-center gap-3 p-2 cursor-pointer hover:bg-gray-900 border-b border-gray-500 last:border-b-0">
                            <input
                                type="checkbox"
                                name="allergens[]"
                                value="{{ $allergen->id }}"
                                data-name="{{ $allergen->name_allergen }}"
                                class="w-4 h-4 accent-green-600"
                                {{ in_array($allergen->id, old('allergens', [])) ? 'checked' : '' }}
                            />
                            <span class="text-white text-sm">{{ $allergen->name_allergen }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        @error('allergens')
            <p id="allergens-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <p class="text-gray-400 text-xs mt-1">Puedes seleccionar múltiples alérgenos</p>
    </div>

    <!-- Email -->
    <div class="flex flex-col">
        <label for="email" class="text-gray-900 text-sm font-medium mb-1">Email</label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email') }}"
            class="bg-gray-600 border-2 rounded-md px-3 py-2 text-white text-sm w-full placeholder-gray-400 focus:outline-none focus:ring-2 {{ $errors->has('email') ? 'border-red-600 focus:border-red-600 focus:ring-red-600/20' : 'border-transparent focus:border-green-600 focus:ring-green-600/20' }}"
            required
            aria-describedby="email-error"
            placeholder="tu@email.com"
        />
        @error('email')
            <p id="email-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Contraseña -->
    <div class="flex flex-col">
        <label for="password" class="text-gray-900 text-sm font-medium mb-1">Contraseña</label>
        <div class="relative">
            <input
                type="password"
                name="password"
                id="password"
                class="bg-gray-600 border-2 rounded-md px-3 py-2 pr-10 text-white text-sm w-full placeholder-gray-400 focus:outline-none focus:ring-2 {{ $errors->has('password') ? 'border-red-600 focus:border-red-600 focus:ring-red-600/20' : 'border-transparent focus:border-green-600 focus:ring-green-600/20' }}"
                required
                minlength="8"
                aria-describedby="password-error"
                placeholder="Mínimo 8 caracteres"
            />
            <button
                type="button"
                id="toggle-password"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 hover:text-gray-600"
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

    <!-- Confirmar contraseña -->
    <div class="flex flex-col">
        <label for="password_confirmation" class="text-gray-900 text-sm font-medium mb-1">Confirmar contraseña</label>
        <div class="relative">
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="bg-gray-600 border-2 rounded-md px-3 py-2 pr-10 text-white text-sm w-full placeholder-gray-400 focus:outline-none focus:ring-2 {{ $errors->has('password_confirmation') ? 'border-red-600 focus:border-red-600 focus:ring-red-600/20' : 'border-transparent focus:border-green-600 focus:ring-green-600/20' }}"
                required
                minlength="8"
                aria-describedby="password_confirmation-error"
                placeholder="Repite tu contraseña"
            />
            <button
                type="button"
                id="toggle-confirm-password"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 hover:text-gray-600"
                aria-label="Mostrar/ocultar confirmación de contraseña"
            >
                <svg id="eye-confirm-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
        @error('password_confirmation')
            <p id="password_confirmation-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Mensaje de error dinámico (JavaScript) -->
    <p id="error-message" class="text-red-600 text-sm hidden" role="alert"></p>

    <!-- Botón -->
    <button id="register-button" type="submit" class="bg-green-100 hover:bg-green-600 hover:text-white text-gray-900 font-semibold py-3 px-6 rounded-md border-none w-full cursor-pointer transition-all duration-200 text-sm flex items-center justify-center gap-2 mt-4 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
        <span id="register-spinner" class="hidden w-4 h-4 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
        <span id="register-button-text">Crear Cuenta</span>
    </button>

    <!-- Enlace a Login -->
    <p class="text-center mt-4 text-sm">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-green-600 hover:text-green-700 hover:underline font-medium transition-colors duration-200">Inicia Sesión</a>
    </p>

    <!-- Nota sobre funcionalidad híbrida -->
    <noscript>
        <div class="bg-blue-100 border border-blue-600 text-blue-600 px-4 py-3 rounded-md text-sm mt-4">
            <p><strong>JavaScript deshabilitado:</strong> El formulario funciona sin JavaScript, pero la experiencia será más básica.</p>
        </div>
    </noscript>
</form>
@endsection

@section('scripts')
<script src="{{ asset('js/user-utils.js') }}"></script>
<script src="{{ asset('js/auth/register.js') }}"></script>
@endsection
