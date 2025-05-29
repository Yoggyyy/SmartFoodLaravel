@extends('layouts.app')

@section('title', 'Registro - SmartFood')
@section('page-title', 'SmartFood')
@section('page-subtitle', 'La nueva forma de preparar tus listas de la compra')

@section('form-content')
<form id="register-form" class="flex flex-col gap-4 w-full">
    <!-- Fila Nombre y Apellidos -->
    <div class="flex gap-4">
        <div class="flex flex-col w-1/2">
            <label for="name" class="mb-1 text-sm">Nombre</label>
            <input
                type="text"
                name="name"
                id="name-input"
                class="input-field"
                required
            />
        </div>
        <div class="flex flex-col w-1/2">
            <label for="surname" class="mb-1 text-sm">Apellidos</label>
            <input
                type="text"
                name="surname"
                id="surname-input"
                class="input-field"
                required
            />
        </div>
    </div>

    <!-- Alérgenos con dropdown personalizado -->
    <div class="flex flex-col">
        <label for="allergens" class="mb-1 text-sm">Alérgenos</label>
        <div class="relative">
            <!-- Campo de display -->
            <div
                id="allergens-dropdown"
                class="input-field cursor-pointer flex items-center justify-between"
            >
                <span id="allergens-display" class="text-gray-400">Selecciona tus alérgenos</span>
                <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>

            <!-- Dropdown -->
            <div
                id="allergens-dropdown-menu"
                class="absolute top-full left-0 w-full bg-gray-600 border border-gray-500 rounded-md mt-1 max-h-60 overflow-y-auto z-10 hidden"
            >
                <!-- Campo de búsqueda -->
                <div class="p-2 border-b border-gray-500">
                    <input
                        type="text"
                        id="allergens-search"
                        placeholder="Buscar alérgenos..."
                        class="w-full px-3 py-2 bg-gray-700 text-white text-sm rounded border-none focus:outline-none focus:ring-1 focus:ring-green-500"
                    />
                </div>

                <!-- Lista de alérgenos -->
                <div id="allergens-list" class="p-2">
                    @foreach($allergens as $allergen)
                        <label class="flex items-center py-2 px-2 hover:bg-gray-700 rounded cursor-pointer">
                            <input
                                type="checkbox"
                                value="{{ $allergen->id }}"
                                data-name="{{ $allergen->name_allergen }}"
                                class="allergen-checkbox mr-3 w-4 h-4 text-green-500 bg-gray-700 border-gray-600 rounded focus:ring-green-500"
                            />
                            <span class="text-white text-sm">{{ $allergen->name_allergen }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-1">Puedes seleccionar múltiples alérgenos</p>
    </div>

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
                minlength="8"
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

    <!-- Confirmar contraseña -->
    <div class="flex flex-col">
        <label for="password_confirmation" class="mb-1 text-sm">Confirmar contraseña</label>
        <div class="relative">
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="input-field pr-10"
                required
                minlength="8"
            />
            <button
                type="button"
                id="toggle-confirm-password"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
                <svg id="eye-confirm-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mensaje de error -->
    <p id="error-message" class="text-red-500 text-sm" style="display: none;"></p>

    <!-- Botón -->
    <button id="register-button" type="submit" class="btn-primary mt-4 flex items-center justify-center">
        <span id="register-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
        <span id="register-button-text">Crear Cuenta</span>
    </button>

    <!-- Enlace a Login -->
    <p class="text-center mt-4 text-sm">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-green-600 hover:text-green-700 font-medium">Inicia Sesión</a>
    </p>
</form>
@endsection

@section('scripts')
<script src="{{ asset('js/common.js') }}"></script>
<script src="{{ asset('js/auth/register.js') }}"></script>
@endsection
