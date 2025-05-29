<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configuración - SmartFood</title>
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
    </style>
</head>
<body class="font-onest bg-green-50 min-h-screen">
    <main class="flex min-h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 min-w-64 bg-green-100 flex flex-col justify-between py-4">
            <div>
                <!-- Logo -->
                <div class="flex items-center gap-2 px-4 mb-6">
                    <img src="{{ asset('images/LogoSmartFood.webp') }}" alt="Logo SmartFood" class="h-8 w-auto" />
                </div>

                <!-- Perfil usuario -->
                <a href="/profile" class="flex items-center gap-3 px-4 py-2 mb-4 hover:bg-green-200 rounded-md transition cursor-pointer mx-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <div class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs" id="user-avatar">
                            <!-- Se llenará con JavaScript -->
                        </div>
                    </div>
                    <span class="text-gray-700 text-sm" id="user-name">Perfil</span>
                </a>

                <!-- Menú lateral -->
                <nav class="flex flex-col gap-1 px-2">
                    <a href="/chat" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-200 text-green-900 transition text-sm">
                        <span class="text-sm">💬</span> Chat
                    </a>
                    <a href="/settings" class="flex items-center gap-2 px-3 py-2 rounded-md bg-green-200 text-green-900 font-bold transition text-sm">
                        <span class="text-sm">⚙️</span> Configuración
                    </a>
                </nav>
            </div>

            <!-- Opciones inferiores -->
            <div class="flex flex-col gap-2 px-4">
                <a href="/settings" class="flex items-center gap-2 text-gray-700 hover:text-green-800 hover:bg-green-200 rounded-md px-2 py-2 transition text-sm">
                    <span class="text-sm">⚙️</span> Configuración
                </a>
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
                <span class="text-sm text-gray-600 hidden sm:block">Configura tu experiencia en SmartFood</span>
            </header>

            <!-- Configuración -->
            <div class="flex-1 flex flex-col items-center justify-start p-4 sm:p-8 bg-green-50 overflow-y-auto">
                <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 w-full max-w-md flex flex-col gap-6 sm:gap-8">
                    <h2 class="text-2xl font-bold text-center text-green-900 mb-4">Configuración</h2>

                    <!-- Selector de idioma -->
                    <div class="flex flex-col gap-2">
                        <label class="text-sm text-gray-600">Idioma</label>
                        <select id="language-select" class="bg-gray-100 rounded-md px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="es">Español</option>
                            <option value="en">Inglés</option>
                        </select>
                    </div>

                    <!-- Tema oscuro -->
                    <div class="flex items-center gap-4">
                        <label class="text-sm text-gray-600" for="dark-mode-toggle">Tema oscuro</label>
                        <input type="checkbox" id="dark-mode-toggle" class="accent-green-700 w-5 h-5" />
                        <span class="text-xs text-gray-500">Activa el modo oscuro para reducir el cansancio visual</span>
                    </div>

                    <!-- Notificaciones -->
                    <div class="flex flex-col gap-3">
                        <h3 class="text-lg font-semibold text-green-800">Notificaciones</h3>

                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-600" for="email-notifications">Notificaciones por email</label>
                            <input type="checkbox" id="email-notifications" class="accent-green-700 w-5 h-5" checked />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-600" for="promotional-emails">Promociones y ofertas</label>
                            <input type="checkbox" id="promotional-emails" class="accent-green-700 w-5 h-5" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-600" for="shopping-reminders">Recordatorios de compra</label>
                            <input type="checkbox" id="shopping-reminders" class="accent-green-700 w-5 h-5" checked />
                        </div>
                    </div>

                    <!-- Botón guardar configuración -->
                    <button
                        id="save-settings-btn"
                        class="mt-4 bg-green-200 hover:bg-green-300 text-green-900 font-bold rounded-md py-3 text-lg transition w-full"
                    >
                        Guardar configuración
                    </button>

                    <!-- Feedback visual -->
                    <div class="hidden mt-2 text-green-700 text-center font-semibold" id="settings-feedback">
                        ¡Configuración guardada exitosamente!
                    </div>

                    <!-- FAQ -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-green-800 mb-4">Preguntas frecuentes (FAQ)</h3>

                        <details class="mb-3 border-b border-gray-200 pb-2">
                            <summary class="cursor-pointer text-green-700 font-semibold hover:text-green-800 transition">¿Qué es SmartFood?</summary>
                            <p class="text-gray-700 mt-2 text-sm leading-relaxed">SmartFood es una aplicación que te ayuda a crear y gestionar listas de la compra de forma inteligente y personalizada, teniendo en cuenta tus preferencias alimentarias y presupuesto.</p>
                        </details>

                        <details class="mb-3 border-b border-gray-200 pb-2">
                            <summary class="cursor-pointer text-green-700 font-semibold hover:text-green-800 transition">¿Cómo se gestionan mis datos?</summary>
                            <p class="text-gray-700 mt-2 text-sm leading-relaxed">Tus datos se almacenan de forma segura y solo se utilizan para mejorar tu experiencia en la aplicación. Nunca compartimos tu información personal con terceros sin tu consentimiento.</p>
                        </details>

                        <details class="mb-3 border-b border-gray-200 pb-2">
                            <summary class="cursor-pointer text-green-700 font-semibold hover:text-green-800 transition">¿Puedo cambiar el idioma de la aplicación?</summary>
                            <p class="text-gray-700 mt-2 text-sm leading-relaxed">Sí, puedes seleccionar el idioma que prefieras desde esta sección de configuración. Actualmente soportamos español e inglés.</p>
                        </details>

                        <details class="mb-3 border-b border-gray-200 pb-2">
                            <summary class="cursor-pointer text-green-700 font-semibold hover:text-green-800 transition">¿Cómo puedo aprovechar SmartFood al máximo?</summary>
                            <p class="text-gray-700 mt-2 text-sm leading-relaxed">Explora todas las funciones, personaliza tus listas, actualiza tus preferencias alimentarias y alérgenos en tu perfil para obtener recomendaciones más precisas.</p>
                        </details>

                        <details class="mb-3 border-b border-gray-200 pb-2">
                            <summary class="cursor-pointer text-green-700 font-semibold hover:text-green-800 transition">¿Puedo usar SmartFood sin internet?</summary>
                            <p class="text-gray-700 mt-2 text-sm leading-relaxed">SmartFood requiere conexión a internet para generar listas inteligentes y sincronizar tus datos. Sin embargo, puedes consultar tus listas guardadas sin conexión.</p>
                        </details>

                        <details class="mb-3">
                            <summary class="cursor-pointer text-green-700 font-semibold hover:text-green-800 transition">¿Cómo puedo contactar con soporte?</summary>
                            <p class="text-gray-700 mt-2 text-sm leading-relaxed">Puedes contactarnos a través del email soporte@smartfood.es o desde la sección de ayuda en la aplicación. Respondemos en menos de 24 horas.</p>
                        </details>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>

    <!-- Estilos adicionales para tema oscuro -->
    <style>
        .dark-mode {
            background-color: #1a202c !important;
            color: #e2e8f0 !important;
        }

        .dark-mode aside {
            background-color: #2d3748 !important;
        }

        .dark-mode header {
            background-color: #4a5568 !important;
            color: #e2e8f0 !important;
        }

        .dark-mode .bg-white {
            background-color: #4a5568 !important;
        }

        .dark-mode .text-gray-700,
        .dark-mode .text-gray-600,
        .dark-mode .text-gray-800 {
            color: #e2e8f0 !important;
        }

        .dark-mode .bg-gray-100 {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
        }

        .dark-mode select option {
            background-color: #2d3748;
            color: #e2e8f0;
        }
    </style>
</body>
</html>
