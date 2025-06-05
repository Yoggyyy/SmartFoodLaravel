<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SmartFood - Auth')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/layouts.css') }}">

    <!-- Dark Mode JavaScript Tailwind Nativo -->
    <script src="{{ asset('js/dark-mode-tailwind.js') }}"></script>

    <!-- Estilos específicos por página -->
    @yield('styles')
</head>

<body class="font-onest">
    <main class="grid grid-cols-[1.2fr_1fr] grid-rows-1 bg-sf-bg h-screen px-16 py-12">
        <!-- Parte izquierda del contenido -->
        <div class="relative flex flex-col h-full rounded-3xl overflow-hidden">
            <img class="inset-0 w-full h-full object-cover" src="{{ asset('images/background.webp') }}"
                alt="Background SmartFood" />
            <div class="absolute z-1 w-full h-full flex flex-col gap-8 justify-end items-center pb-20 px-10">
                <img class="w-[332px] h-[70.65px]" src="{{ asset('images/LogoSmartFood.webp') }}"
                    alt="Logo SmartFood" />
                <!-- Contenido debajo Logo -->
                <div class="flex flex-col items-center gap-1 text-center">
                    <h2 class="text-lg font-semibold text-white">
                        La nueva forma de realizar tus listas de la compra
                    </h2>
                    <p class="text-pretty max-w-100 text-white">
                        @yield('description', 'Explica el contenido de forma general, propón un presupuesto y supermercado de preferencia y ¡listo!')
                    </p>
                    <h3 class="text-md font-medium text-white">Fácil, rápido y sencillo</h3>
                </div>
            </div>
        </div>

        <!-- Parte derecha del contenido -->
        <section class="flex flex-col items-center justify-center px-12 py-16">
            <!-- Form -->
            <div class="flex flex-col items-center gap-6 w-full max-w-lg">
                <!-- Arriba -->
                <div class="flex flex-col gap-6 w-full">
                    <div class="text-center space-y-2">
                        <h1 class="text-3xl font-bold text-gray-900">@yield('page-title', 'SmartFood')</h1>
                        <h2 class="text-base text-gray-600">@yield('page-subtitle')</h2>
                    </div>

                    <!-- Botones sociales -->
                    <div class="flex gap-3">
                        <button type="button" data-social-login="Google"
                            class="bg-gray-900 text-white font-medium py-3.5 px-6 rounded-lg border-2 border-gray-600 hover:bg-gray-700 hover:border-gray-400 transition-all duration-200 text-base flex-1 flex items-center justify-center gap-3">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google"
                                class="w-5 h-5">
                            Google
                        </button>
                        <button type="button" data-social-login="GitHub"
                            class="bg-gray-900 text-white font-medium py-3.5 px-6 rounded-lg border-2 border-gray-600 hover:bg-gray-700 hover:border-gray-400 transition-all duration-200 text-base flex-1 flex items-center justify-center gap-3">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            GitHub
                        </button>
                    </div>

                    <!-- Centro - Separador -->
                    <div class="flex items-center w-full my-2">
                        <div class="h-px bg-gray-400 flex-1"></div>
                        <div class="mx-4 w-10 h-5 bg-gray-100 rounded-lg"></div>
                        <div class="h-px bg-gray-400 flex-1"></div>
                    </div>

                    <!-- Abajo - Formulario -->
                    @yield('form-content')
                </div>
            </div>
        </section>
    </main>

    <!-- Scripts -->
    <script>
        // Configurar CSRF token para todas las peticiones AJAX
        window.axios = window.axios || {};
        if (window.axios.defaults) {
            window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')
                .getAttribute('content');
        }
    </script>

    @yield('scripts')
</body>

</html>
