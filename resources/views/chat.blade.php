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
</head>
<body class="font-onest bg-green-50 h-screen">
    <main class="flex h-screen overflow-hidden">
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
                    <button id="new-list-btn" class="flex items-center gap-2 px-3 py-2 rounded-md bg-green-700 text-white font-semibold mb-1 text-sm hover:bg-green-800 transition">
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
                <span class="text-sm text-gray-600 hidden sm:block">Te facilitamos las listas de la compra</span>
            </header>

            <!-- Chat principal -->
            <div class="flex-1 p-4 sm:p-8 overflow-y-auto flex flex-col gap-6 justify-center bg-green-50" id="chat-container">
                <!-- Mensaje 1 del bot -->
                <div class="flex flex-col items-start gap-1">
                    <div class="flex items-start gap-3">
                        <img src="{{ asset('images/logoManzana.webp') }}" alt="Bot" class="h-8 w-8 rounded-full" />
                        <div class="bg-white rounded-lg p-4 shadow text-gray-800 max-w-xl" id="msg-bot-1">
                            ¡Hola! Soy SmartFood, tu asistente para crear listas de compra. ¿En qué puedo ayudarte hoy?
                        </div>
                    </div>
                    <button class="flex items-center gap-1 text-green-700 hover:text-green-900 text-xs ml-11" onclick="copyMessage('msg-bot-1')">
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a1 1 0 011 1v3M9 7h4' />
                        </svg>
                        Copiar
                    </button>
                </div>

                <!-- Mensaje 2 del bot -->
                <div class="flex flex-col items-start gap-1">
                    <div class="flex items-start gap-3">
                        <img src="{{ asset('images/logoManzana.webp') }}" alt="Bot" class="h-8 w-8 rounded-full" />
                        <div class="bg-white rounded-lg p-4 shadow text-gray-800 max-w-xl" id="msg-bot-2">
                            Estoy aquí para ayudarte con tus listas de compra. Puedes pedirme que cree una lista, que te sugiera productos o que te ayude a organizar tu compra según tu presupuesto y supermercado preferido.
                        </div>
                    </div>
                    <button class="flex items-center gap-1 text-green-700 hover:text-green-900 text-xs ml-11" onclick="copyMessage('msg-bot-2')">
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a1 1 0 011 1v3M9 7h4' />
                        </svg>
                        Copiar
                    </button>
                </div>
            </div>

            <!-- Input de mensaje -->
            <footer class="p-4 border-t bg-white">
                <form id="message-form" class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <input
                            type="text"
                            id="message-input"
                            placeholder="Escribe tu mensaje aquí..."
                            class="flex-1 rounded-md border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-300"
                            required
                        />
                        <button type="submit" class="bg-green-200 hover:bg-green-300 text-green-900 rounded-md px-4 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                    <span class="text-xs text-gray-500 text-center">
                        Explica el contenido de forma general, propón un presupuesto y supermercado de preferencia y ¡listo!
                    </span>
                </form>
            </footer>
        </section>
    </main>

    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/chat.js') }}"></script>
</body>
</html>
