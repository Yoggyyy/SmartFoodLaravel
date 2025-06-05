<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SmartFood - Chat</title>
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

    <!-- Dark Mode JavaScript Tailwind Nativo (incluye configuración) -->
    <script src="{{ asset('js/dark-mode-tailwind.js') }}"></script>
</head>
<body class="font-onest bg-gray-50 dark:bg-gray-900 h-screen transition-colors">
    <main class="flex h-screen overflow-hidden">
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
                    <button id="new-list-btn" class="flex items-center gap-2 px-3 py-2 rounded-md bg-green-600 dark:bg-green-700 text-white font-semibold mb-1 text-sm hover:bg-green-700 dark:hover:bg-green-800 transition">
                        <span class="text-lg">+</span> Nueva lista
                    </button>

                    <!-- Lista de conversaciones -->
                    <div id="conversations-list">
                        <!-- Las conversaciones se agregarán dinámicamente aquí -->
                    </div>
                </nav>
            </div>

            <!-- Opciones inferiores -->
            <div class="flex flex-col gap-2 px-4">
                <a href="/listas" class="flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-gray-700 rounded-md px-2 py-2 transition text-sm">
                    <span class="text-sm">📋</span> Mis Listas
                </a>
                <a href="/settings" class="flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-gray-700 rounded-md px-2 py-2 transition text-sm">
                    <span class="text-sm">⚙️</span> Configuración
                </a>
                <button onclick="logout()" class="flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition text-sm">
                    <span class="text-sm">↩️</span> Cerrar sesión
                </button>
            </div>
        </aside>

        <!-- Contenido principal -->
        <section class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="flex items-center justify-between p-4 border-b bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 flex-shrink-0 transition-colors">
                <div class="flex items-center gap-2">
                    <!-- Logo eliminado por redundancia -->
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400 hidden sm:block">Te facilitamos las listas de la compra</span>
            </header>

            <!-- Chat principal -->
            <div class="flex-1 p-4 sm:p-8 overflow-y-auto flex flex-col gap-6 justify-center bg-gray-50 dark:bg-gray-900 transition-colors" id="chat-container">
                <!-- Los mensajes se cargarán dinámicamente aquí -->
            </div>

            <!-- Input de mensaje -->
            <footer class="p-4 border-t bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 transition-colors">
                <form id="message-form" class="flex flex-col gap-2">
                    <div class="flex gap-2 items-end">
                        <textarea
                            id="message-input"
                            placeholder="Escribe tu mensaje aquí..."
                            class="flex-1 rounded-md border border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600 dark:focus:ring-green-500 resize-none min-h-[2.5rem] max-h-32 leading-6 transition-colors"
                            required
                            rows="1"
                        ></textarea>
                        <button type="submit" class="bg-green-100 dark:bg-green-900 hover:bg-green-600 dark:hover:bg-green-600 hover:text-white text-gray-900 dark:text-green-400 rounded-md px-4 py-2 flex items-center justify-center transition-colors h-10 w-10 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 text-center">
                        Explica el contenido de forma general, propón un presupuesto y supermercado de preferencia y ¡listo!
                    </span>
                </form>
            </footer>
        </section>
    </main>

    <script src="{{ asset('js/user-utils.js') }}"></script>
    <script src="{{ asset('js/chat.js') }}"></script>
</body>
</html>
