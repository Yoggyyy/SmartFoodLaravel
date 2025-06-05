<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perfil de usuario - SmartFood</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Profile CSS -->
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <!-- Dark Mode JavaScript Tailwind Nativo (incluye configuración) -->
    <script src="{{ asset('js/dark-mode-tailwind.js') }}"></script>
</head>
<body class="font-onest bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors">
    <main class="flex min-h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 min-w-64 bg-green-100 dark:bg-gray-800 flex flex-col justify-between py-4 transition-colors">
            <div>
                <!-- Logo -->
                <div class="flex items-center gap-2 px-4 mb-6">
                    <img src="{{ asset('images/LogoSmartFood.webp') }}" alt="Logo SmartFood" class="h-8 w-auto" />
                </div>

                <!-- Perfil usuario (activo) -->
                <a href="/profile" class="flex items-center gap-3 px-4 py-2 mb-4 hover:bg-green-50 dark:hover:bg-gray-700 rounded-md transition cursor-pointer bg-green-50 dark:bg-gray-700 mx-2">
                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center">
                        <div class="w-6 h-6 bg-gray-400 dark:bg-gray-500 rounded-full flex items-center justify-center text-white text-xs" id="user-avatar-sidebar">
                            <!-- Se llenará con JavaScript -->
                        </div>
                    </div>
                    <span class="text-gray-600 dark:text-gray-300 text-sm">Perfil</span>
                </a>

                <!-- Menú lateral -->
                <nav class="flex flex-col gap-1 px-2">
                    <a href="/chat" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition text-sm">
                        <span class="text-sm">💬</span> Chat
                    </a>
                    <a href="/settings" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition text-sm">
                        <span class="text-sm">⚙️</span> Configuración
                    </a>
                    <a href="/listas" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition text-sm">
                        <span class="text-sm">📋</span> Mis Listas
                    </a>
                </nav>
            </div>

            <!-- Opciones inferiores -->
            <div class="flex flex-col gap-2 px-4">
                <button onclick="logout()" class="flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition text-sm">
                    <span class="text-sm">↩️</span> Cerrar sesión
                </button>
            </div>
        </aside>

        <!-- Contenido principal -->
        <section class="flex-1 flex flex-col min-w-0">
            <!-- Header principal -->
            <header class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-6 py-4 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Perfil de usuario</h1>
                        <p class="text-gray-600 dark:text-gray-400">Gestiona tu información personal</p>
                    </div>
                    <div id="header-buttons">
                        <!-- Botón Editar perfil (modo vista) -->
                        <button id="edit-profile-btn" class="view-mode-content bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition font-medium">
                            <span>✏️</span>
                            Editar perfil
                        </button>

                        <!-- Botón Cancelar (modo edición) -->
                        <button id="cancel-edit-btn" class="edit-mode-content bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition font-medium">
                            <span>✕</span>
                            Cancelar
                        </button>
                    </div>
                </div>
            </header>

            <!-- Contenido del perfil -->
            <div class="flex-1 p-8 bg-gray-50 dark:bg-gray-900 overflow-y-auto transition-colors">
                <div class="max-w-8xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Columna principal (2/3) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Información Personal -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="p-8 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-600 dark:text-gray-300 text-lg">👤</span>
                                    </div>
                                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Información Personal</h2>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="flex items-start gap-8">
                                    <!-- Foto de perfil -->
                                    <div class="flex-shrink-0">
                                        <div class="w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center" id="profile-avatar-container">
                                            <div class="w-24 h-24 rounded-full bg-gray-400 dark:bg-gray-500 flex items-center justify-center text-white text-3xl" id="profile-avatar">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Información -->
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Nombre -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Nombre</label>
                                            <div class="view-mode-content">
                                                <p class="text-gray-900 dark:text-gray-100 font-medium text-lg" id="display-name">Jordi</p>
                                            </div>
                                            <div class="edit-mode-content">
                                                <input type="text" id="edit-name" class="profile-input text-lg" value="Jordi">
                                            </div>
                                        </div>

                                        <!-- Apellidos -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Apellidos</label>
                                            <div class="view-mode-content">
                                                <p class="text-gray-900 dark:text-gray-100 font-medium text-lg" id="display-surname">Santos</p>
                                            </div>
                                            <div class="edit-mode-content">
                                                <input type="text" id="edit-surname" class="profile-input text-lg" value="Santos">
                                            </div>
                                        </div>

                                        <!-- Email -->
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Correo electrónico</label>
                                            <div class="view-mode-content">
                                                <p class="text-gray-900 dark:text-gray-100 font-medium text-lg" id="display-email">jordi.s1511@gmail.com</p>
                                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">El correo electrónico no se puede modificar por seguridad</p>
                                            </div>
                                            <div class="edit-mode-content">
                                                <div class="profile-input text-lg bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed flex items-center">
                                                    <span id="readonly-email">jordi.s1511@gmail.com</span>
                                                </div>
                                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">El correo electrónico no se puede modificar por seguridad</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alérgenos -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="p-8 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                        <span class="text-red-600 dark:text-red-400 text-lg">⚠️</span>
                                    </div>
                                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Alérgenos</h2>
                                </div>
                            </div>

                            <div class="p-8">
                                <!-- Tags de alérgenos -->
                                <div id="allergens-display" class="mb-6">
                                    <div class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-full text-sm m-1">
                                        Frutos secos
                                        <span class="edit-mode-content cursor-pointer bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full w-5 h-5 flex items-center justify-center text-xs transition-colors" onclick="removeAllergen(this)">✕</span>
                                    </div>
                                    <div class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-full text-sm m-1">
                                        Lactosa
                                        <span class="edit-mode-content cursor-pointer bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full w-5 h-5 flex items-center justify-center text-xs transition-colors" onclick="removeAllergen(this)">✕</span>
                                    </div>
                                </div>

                                <!-- Input para nuevo alérgeno (solo en modo edición) -->
                                <div class="edit-mode-content">
                                    <div class="flex gap-3">
                                        <input
                                            type="text"
                                            id="new-allergen-input"
                                            placeholder="Escribe un nuevo alérgeno..."
                                            class="flex-1 profile-input text-lg"
                                            onkeypress="handleAllergenKeyPress(event)"
                                        >
                                        <button
                                            onclick="addAllergen()"
                                            class="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white px-6 py-3 rounded-lg flex items-center gap-2 transition font-medium whitespace-nowrap text-lg"
                                        >
                                            <span>+</span>
                                            Añadir
                                        </button>
                                    </div>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-3">Presiona Enter o haz clic en "Añadir" para agregar un nuevo alérgeno</p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción en modo edición -->
                        <div class="edit-mode-content bg-green-50 dark:bg-green-900/20 rounded-xl p-8 border-2 border-dashed border-green-100 dark:border-green-700 transition-colors">
                            <div class="flex gap-6 justify-center">
                                <button id="save-profile-btn" class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white px-10 py-4 rounded-lg flex items-center gap-3 transition font-semibold text-lg">
                                    <span>💾</span>
                                    Guardar cambios
                                </button>
                                <button id="cancel-changes-btn" class="bg-gray-100 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 hover:text-white dark:hover:text-white text-gray-600 dark:text-gray-300 px-10 py-4 rounded-lg flex items-center gap-3 transition font-semibold text-lg">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar derecha (1/3) -->
                    <div class="space-y-8">
                        <!-- Información de Cuenta -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="p-8 border-b border-gray-100 dark:border-gray-700">
                                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Información de Cuenta</h2>
                            </div>

                            <div class="p-8 space-y-6">
                                <div>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-1">Miembro desde</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100 text-lg" id="member-since">Cargando...</p>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-1">Listas creadas</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100 text-lg" id="lists-count">Cargando...</p>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-1">Última actividad</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100 text-lg" id="last-activity">Cargando...</p>
                                </div>

                                <hr class="my-6">

                                <!-- Cambiar contraseña -->
                                <div>
                                    <button
                                        id="change-password-toggle"
                                        class="flex items-center gap-3 text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition font-medium w-full text-left text-lg"
                                    >
                                        <span>🔒</span>
                                        Cambiar contraseña
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sección cambiar contraseña (expandible) -->
                        <div id="password-change-section" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 hidden">
                            <div class="p-8 border-b border-gray-100 dark:border-gray-700">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Cambiar Contraseña</h3>
                            </div>

                            <div class="p-8 space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Contraseña actual</label>
                                    <input type="password" id="current-password" class="profile-input text-lg" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Nueva contraseña</label>
                                    <input type="password" id="new-password" class="profile-input text-lg" minlength="8" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Confirmar nueva contraseña</label>
                                    <input type="password" id="confirm-new-password" class="profile-input text-lg" minlength="8" required>
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <button
                                        id="save-password-btn"
                                        class="flex-1 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white px-6 py-3 rounded-lg font-medium transition text-lg"
                                    >
                                        Actualizar
                                    </button>
                                    <button
                                        id="cancel-password-btn"
                                        class="flex-1 bg-gray-100 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 hover:text-white dark:hover:text-white text-gray-600 dark:text-gray-300 px-6 py-3 rounded-lg font-medium transition text-lg"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/user-utils.js') }}"></script>
    <script src="{{ asset('js/profile.js') }}"></script>
</body>
</html>
