<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perfil de usuario - SmartFood</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-onest {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* Responsive sidebar */
        @media (max-width: 768px) {
            aside {
                width: 200px !important;
                min-width: 200px !important;
            }
        }

        @media (max-width: 640px) {
            aside {
                width: 180px !important;
                min-width: 180px !important;
            }
        }

        /* Profile edit elements hidden by default */
        .profile-edit {
            display: none;
        }

        /* Hidden class for elements */
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body class="font-onest bg-green-50 h-screen">
    <main class="flex min-h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 min-w-64 bg-green-100 flex flex-col justify-between py-4">
            <div>
                <!-- Logo -->
                <div class="flex items-center gap-2 px-4 mb-6">
                    <img src="{{ asset('images/LogoSmartFood.webp') }}" alt="Logo SmartFood" class="h-8 w-auto" />
                </div>

                <!-- Perfil usuario (activo) -->
                <a href="/profile" class="flex items-center gap-3 px-4 py-2 mb-4 hover:bg-green-200 rounded-md transition cursor-pointer bg-green-200 mx-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <div class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs" id="user-avatar">
                            <!-- Se llenará con JavaScript -->
                        </div>
                    </div>
                    <span class="text-gray-700 text-sm">Perfil</span>
                </a>

                <!-- Menú lateral -->
                <nav class="flex flex-col gap-1 px-2">
                    <a href="/chat" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-200 text-green-900 transition text-sm">
                        <span class="text-sm">💬</span> Chat
                    </a>
                    <a href="/settings" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-200 text-green-900 transition text-sm">
                        <span class="text-sm">⚙️</span> Configuración
                    </a>
                </nav>
            </div>

            <!-- Opciones inferiores -->
            <div class="flex flex-col gap-2 px-4">
                <button onclick="logout()" class="flex items-center gap-2 text-gray-700 hover:text-green-800 transition text-sm">
                    <span class="text-sm">↩️</span> Cerrar sesión
                </button>
            </div>
        </aside>

        <!-- Contenido principal -->
        <section class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="flex items-center justify-between p-4 border-b bg-white flex-shrink-0">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/LogoSmartFood.webp') }}" alt="Logo SmartFood" class="h-8 w-auto" />
                </div>
                <span class="text-sm text-gray-600 hidden sm:block">Te facilitamos las listas de la compra</span>
            </header>

            <!-- Perfil -->
            <div class="flex-1 flex flex-col items-center justify-center p-4 sm:p-8 bg-green-50 overflow-y-auto">
                <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 w-full max-w-md flex flex-col gap-6">
                    <h2 class="text-2xl font-bold text-center text-green-900 mb-4">Perfil de usuario</h2>

                    <!-- Formulario de perfil -->
                    <form id="profile-form">
                        <!-- Foto de perfil -->
                        <div class="flex flex-col gap-2 items-center mb-4">
                            <label class="text-sm text-gray-600 w-full flex flex-col items-center cursor-pointer">
                                Foto de perfil
                                <div class="w-20 h-20 rounded-full bg-gray-400 flex items-center justify-center text-white text-2xl border-2 border-green-200 mb-2 mt-1" id="profile-avatar">
                                    <!-- Se llenará con JavaScript -->
                                </div>
                                <input type="file" id="avatar-input" class="hidden" accept="image/*" />
                            </label>
                        </div>

                        <!-- Nombre -->
                        <div class="flex flex-col gap-2 mb-4">
                            <label class="text-sm text-gray-600">Nombre</label>
                            <span id="display-name" class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 profile-display">
                                <!-- Se llenará con JavaScript -->
                            </span>
                            <input
                                type="text"
                                id="edit-name"
                                class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 profile-edit"
                            />
                        </div>

                        <!-- Apellidos -->
                        <div class="flex flex-col gap-2 mb-4">
                            <label class="text-sm text-gray-600">Apellidos</label>
                            <span id="display-surname" class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 profile-display">
                                <!-- Se llenará con JavaScript -->
                            </span>
                            <input
                                type="text"
                                id="edit-surname"
                                class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 profile-edit"
                            />
                        </div>

                        <!-- Email -->
                        <div class="flex flex-col gap-2 mb-4">
                            <label class="text-sm text-gray-600">Correo electrónico</label>
                            <span id="display-email" class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 profile-display">
                                <!-- Se llenará con JavaScript -->
                            </span>
                            <input
                                type="email"
                                id="edit-email"
                                class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 profile-edit"
                            />
                        </div>

                        <!-- Alérgenos -->
                        <div class="flex flex-col gap-2 mb-4">
                            <label class="text-sm text-gray-600">Alérgenos</label>
                            <span id="display-allergens" class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 profile-display">
                                <!-- Se llenará con JavaScript -->
                            </span>
                            <textarea
                                id="edit-allergens"
                                class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 resize-none profile-edit"
                                rows="2"
                                placeholder="Ej: Gluten, Lácteos, Frutos secos..."
                            ></textarea>
                        </div>

                        <!-- Preferencias -->
                        <div class="flex flex-col gap-2 mb-4">
                            <label class="text-sm text-gray-600">Preferencias alimentarias</label>
                            <span id="display-preferences" class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 profile-display">
                                <!-- Se llenará con JavaScript -->
                            </span>
                            <textarea
                                id="edit-preferences"
                                class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 resize-none profile-edit"
                                rows="3"
                                placeholder="Ej: Vegetariano, Comida mediterránea..."
                            ></textarea>
                        </div>

                        <!-- Botones de acción -->
                        <button
                            type="button"
                            id="edit-profile-btn"
                            class="mt-4 bg-green-200 hover:bg-green-300 text-green-900 font-bold rounded-md py-3 text-lg transition w-full"
                        >
                            Editar perfil
                        </button>

                        <div id="profile-action-buttons" class="flex gap-2 mt-4 hidden">
                            <button
                                type="button"
                                id="save-profile-btn"
                                class="flex-1 bg-blue-200 hover:bg-blue-300 text-blue-900 font-bold rounded-md py-2 transition"
                            >
                                Guardar
                            </button>
                            <button
                                type="button"
                                id="cancel-edit-btn"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 font-bold rounded-md py-2 transition"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>

                    <!-- Cambiar contraseña -->
                    <div class="mt-6">
                        <button
                            type="button"
                            id="change-password-toggle"
                            class="text-green-700 font-semibold mb-2 hover:text-green-900"
                        >
                            Cambiar contraseña
                        </button>

                        <div id="password-change-section" class="hidden">
                            <div class="flex flex-col gap-2 mt-2">
                                <label class="text-sm text-gray-600">Contraseña actual</label>
                                <input
                                    type="password"
                                    id="current-password"
                                    class="bg-gray-100 rounded-md px-4 py-2 text-gray-800"
                                    required
                                />

                                <label class="text-sm text-gray-600">Nueva contraseña</label>
                                <input
                                    type="password"
                                    id="new-password"
                                    class="bg-gray-100 rounded-md px-4 py-2 text-gray-800"
                                    minlength="8"
                                    required
                                />

                                <label class="text-sm text-gray-600">Confirmar nueva contraseña</label>
                                <input
                                    type="password"
                                    id="confirm-new-password"
                                    class="bg-gray-100 rounded-md px-4 py-2 text-gray-800"
                                    minlength="8"
                                    required
                                />

                                <button
                                    type="button"
                                    id="change-password-btn"
                                    class="mt-2 bg-green-200 hover:bg-green-300 text-green-900 font-bold rounded-md py-2 text-md transition"
                                >
                                    Actualizar contraseña
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/profile.js') }}"></script>
</body>
</html>
