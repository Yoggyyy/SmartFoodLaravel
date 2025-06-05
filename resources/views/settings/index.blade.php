<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configuración - SmartFood</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Settings CSS -->
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}">

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

                <!-- Perfil usuario -->
                <a href="/profile" class="flex items-center gap-3 px-4 py-2 mb-4 hover:bg-green-50 dark:hover:bg-gray-700 rounded-md transition cursor-pointer mx-2">
                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center">
                        <div class="w-6 h-6 bg-gray-400 dark:bg-gray-500 rounded-full flex items-center justify-center text-white text-xs" id="user-avatar">
                            <!-- Se llenará con JavaScript -->
                        </div>
                    </div>
                    <span class="text-gray-600 dark:text-gray-300 text-sm" id="user-name">Perfil</span>
                </a>

                <!-- Menú lateral -->
                <nav class="flex flex-col gap-1 px-2">
                    <a href="/chat" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition text-sm">
                        <span class="text-sm">💬</span> Chat
                    </a>
                    <a href="/settings" class="flex items-center gap-2 px-3 py-2 rounded-md bg-green-50 dark:bg-gray-700 text-green-600 dark:text-green-400 font-bold transition text-sm">
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
            <!-- Header -->
            <header class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0 transition-colors">
                <div class="flex items-center gap-2">
                    <!-- Logo eliminado por redundancia -->
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400 hidden sm:block">Configura tu experiencia en SmartFood</span>
            </header>

            <!-- Configuración -->
            <div class="flex-1 p-6 bg-gray-50 dark:bg-gray-900 overflow-y-auto transition-colors">
                <div class="max-w-5xl mx-auto">
                    <!-- Header principal -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center">
                                <span class="text-gray-600 dark:text-gray-300 text-xl">⚙️</span>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Configuración</h1>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 text-lg">Personaliza tu experiencia con SmartFood</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Columna principal -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Configuración General -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
                                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-600 dark:text-gray-300">🌐</span>
                                        </div>
                                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Configuración General</h2>
                                    </div>
                                </div>
                                <div class="p-6 space-y-6">
                                    <!-- Idioma -->
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Idioma</h3>
                                        </div>
                                        <select id="language-select" class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 min-w-32 transition-colors">
                                            <option value="es">Español</option>
                                            <option value="en">Inglés</option>
                                        </select>
                                    </div>

                                    <!-- Tema oscuro -->
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Apariencia</h3>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Tema oscuro</span>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" id="dark-mode-toggle" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Reduce el cansancio visual</p>
                                </div>
                            </div>

                            <!-- Notificaciones -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors hidden">
                                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-600 dark:text-gray-300">🔔</span>
                                        </div>
                                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Notificaciones</h2>
                                    </div>
                                </div>
                                <div class="p-6 space-y-4">
                                    <!-- Notificaciones por email -->
                                    <div class="flex items-center justify-between py-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                                <span class="text-gray-600 dark:text-gray-300">📧</span>
                                            </div>
                                            <div>
                                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Notificaciones por email</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Recibe actualizaciones importantes</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" id="email-notifications" class="w-5 h-5 text-green-600 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600" checked />
                                    </div>

                                    <!-- Promociones -->
                                    <div class="flex items-center justify-between py-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                                <span class="text-green-600 dark:text-green-400">🏷️</span>
                                            </div>
                                            <div>
                                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Promociones y ofertas</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Descubre descuentos exclusivos</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" id="promotional-emails" class="w-5 h-5 text-green-600 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600" />
                                    </div>

                                    <!-- Recordatorios -->
                                    <div class="flex items-center justify-between py-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                                <span class="text-gray-600 dark:text-gray-300">🛒</span>
                                            </div>
                                            <div>
                                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Recordatorios de compra</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">No olvides tus listas pendientes</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" id="shopping-reminders" class="w-5 h-5 text-green-600 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600" checked />
                                    </div>
                                </div>
                            </div>

                            <!-- Botón guardar -->
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-6 border-2 border-dashed border-green-200 dark:border-green-700 transition-colors">
                                <button id="save-settings-btn" class="w-full bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                    <span class="text-lg">💾</span>
                                    Guardar configuración
                                </button>
                                <div class="hidden mt-3 text-green-700 dark:text-green-400 text-center font-medium" id="settings-feedback">
                                    ✅ ¡Configuración guardada exitosamente!
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar derecha -->
                        <div class="space-y-6">
                            <!-- Acceso Rápido -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
                                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-600 dark:text-gray-300">⚡</span>
                                        </div>
                                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Acceso Rápido</h2>
                                    </div>
                                </div>
                                <div class="p-6 space-y-3">
                                    <a href="/profile" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition group">
                                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center group-hover:bg-gray-200 dark:group-hover:bg-gray-600 transition">
                                            <span class="text-gray-600 dark:text-gray-300">🔒</span>
                                        </div>
                                        <span class="text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">Privacidad y Seguridad</span>
                                        <span class="ml-auto text-gray-400 dark:text-gray-500">→</span>
                                    </a>

                                    <a href="mailto:soporte@smartfood.es" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition group">
                                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center group-hover:bg-gray-200 dark:group-hover:bg-gray-600 transition">
                                            <span class="text-gray-600 dark:text-gray-300">💬</span>
                                        </div>
                                        <span class="text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">Contactar Soporte</span>
                                        <span class="ml-auto text-gray-400 dark:text-gray-500">→</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Preguntas Frecuentes -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
                                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                            <span class="text-green-600 dark:text-green-400">❓</span>
                                        </div>
                                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Preguntas Frecuentes</h2>
                                    </div>
                                </div>
                                <div class="p-6 space-y-4">
                                    <details class="group">
                                        <summary class="flex items-center justify-between cursor-pointer text-gray-700 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition font-medium">
                                            ¿Qué es SmartFood?
                                            <span class="text-gray-400 dark:text-gray-500 group-open:rotate-180 transition-transform">▼</span>
                                        </summary>
                                        <p class="text-gray-600 dark:text-gray-400 mt-3 text-sm leading-relaxed pl-4 border-l-2 border-green-100 dark:border-green-800">
                                            SmartFood es una aplicación que te ayuda a crear y gestionar listas de la compra de forma inteligente y personalizada.
                                        </p>
                                    </details>

                                    <details class="group">
                                        <summary class="flex items-center justify-between cursor-pointer text-gray-700 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition font-medium">
                                            ¿Cómo se gestionan mis datos?
                                            <span class="text-gray-400 dark:text-gray-500 group-open:rotate-180 transition-transform">▼</span>
                                        </summary>
                                        <p class="text-gray-600 dark:text-gray-400 mt-3 text-sm leading-relaxed pl-4 border-l-2 border-green-100 dark:border-green-800">
                                            Tus datos se almacenan de forma segura y solo se utilizan para mejorar tu experiencia en la aplicación.
                                        </p>
                                    </details>

                                    <details class="group">
                                        <summary class="flex items-center justify-between cursor-pointer text-gray-700 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition font-medium">
                                            ¿Puedo cambiar el idioma?
                                            <span class="text-gray-400 dark:text-gray-500 group-open:rotate-180 transition-transform">▼</span>
                                        </summary>
                                        <p class="text-gray-600 dark:text-gray-400 mt-3 text-sm leading-relaxed pl-4 border-l-2 border-green-100 dark:border-green-800">
                                            Sí, puedes seleccionar el idioma desde esta sección de configuración.
                                        </p>
                                    </details>

                                    <details class="group">
                                        <summary class="flex items-center justify-between cursor-pointer text-gray-700 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition font-medium">
                                            ¿Cómo aprovechar SmartFood al máximo?
                                            <span class="text-gray-400 dark:text-gray-500 group-open:rotate-180 transition-transform">▼</span>
                                        </summary>
                                        <p class="text-gray-600 dark:text-gray-400 mt-3 text-sm leading-relaxed pl-4 border-l-2 border-green-100 dark:border-green-800">
                                            Explora todas las funciones, personaliza tus listas y actualiza tus preferencias alimentarias.
                                        </p>
                                    </details>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
</body>
</html>
