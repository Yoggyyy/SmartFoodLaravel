<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mis Listas - SmartFood</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/shopping-list.css') }}">

    <!-- Dark Mode JavaScript Tailwind Nativo (incluye configuración) -->
    <script src="{{ asset('js/dark-mode-tailwind.js') }}"></script>
</head>
<body class="font-onest bg-green-50 dark:bg-gray-900 h-screen transition-colors">
    <main class="flex min-h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 min-w-64 bg-green-100 dark:bg-gray-800 flex flex-col justify-between py-4 transition-colors">
            <div>
                <!-- Logo -->
                <div class="flex items-center gap-2 px-4 mb-6">
                    <img src="{{ asset('images/LogoSmartFood.webp') }}" alt="Logo SmartFood" class="h-8 w-auto" />
                </div>

                <!-- Perfil usuario -->
                <a href="/profile" class="flex items-center gap-3 px-4 py-2 mb-4 hover:bg-green-200 dark:hover:bg-gray-700 rounded-md transition cursor-pointer mx-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                        <div class="w-6 h-6 bg-gray-400 dark:bg-gray-500 rounded-full flex items-center justify-center text-white text-xs" id="user-avatar">
                            <!-- Se llenará con JavaScript -->
                        </div>
                    </div>
                    <span class="text-gray-700 dark:text-gray-300 text-sm">Perfil</span>
                </a>

                <!-- Menú lateral -->
                <nav class="flex flex-col gap-1 px-2">
                    <a href="/chat" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-200 dark:hover:bg-gray-700 text-green-900 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition text-sm">
                        <span class="text-sm">💬</span> Chat
                    </a>
                    <a href="/settings" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-green-200 dark:hover:bg-gray-700 text-green-900 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition text-sm">
                        <span class="text-sm">⚙️</span> Configuración
                    </a>
                    <a href="/listas" class="flex items-center gap-2 px-3 py-2 rounded-md bg-green-200 dark:bg-gray-700 text-green-900 dark:text-green-400 transition text-sm">
                        <span class="text-sm">📋</span> Mis Listas
                    </a>
                </nav>
            </div>

            <!-- Opciones inferiores -->
            <div class="flex flex-col gap-2 px-4">
                <button onclick="logout()" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-green-800 dark:hover:text-green-400 transition text-sm">
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

            <!-- Contenido de las listas -->
            <div class="flex-1 p-4 sm:p-6 bg-green-50 dark:bg-gray-900 overflow-y-auto transition-colors">
                <div class="max-w-6xl mx-auto">
                    <!-- Header con título y estadísticas -->
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-green-900 dark:text-gray-100 mb-2">Mis Listas de la Compra</h1>
                        @if($groupedLists->count() > 0)
                            @php
                                $totalLists = $groupedLists->sum(function($lists) { return $lists->count(); });
                                $totalProducts = $groupedLists->sum(function($lists) {
                                    return $lists->sum(function($list) { return $list->products->count(); });
                                });
                            @endphp
                            <p class="text-green-700 dark:text-gray-300 text-lg">Gestiona tus compras de manera eficiente</p>
                            <div class="flex items-center gap-6 mt-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600 dark:text-green-400">🛒</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $totalLists }} listas</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600 dark:text-green-400">📦</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $totalProducts }} productos</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($groupedLists->count() > 0)
                        <!-- Grid de listas organizadas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                            @foreach($groupedLists as $conversationId => $lists)
                                    @foreach($lists as $list)
                                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden min-h-[360px]">
                                        <!-- Header de la tarjeta -->
                                        <div class="p-8 border-b border-gray-100 dark:border-gray-700">
                                            <!-- Título y fecha -->
                                            <div class="flex items-start justify-between mb-3">
                                                <h3 class="font-bold text-gray-900 dark:text-gray-100 text-xl truncate flex-1 mr-3">
                                                    {{ $list->name_list }}
                                                </h3>
                                                <div class="text-right flex-shrink-0">
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        📅 {{ $list->created_at->format('d/m/Y') }}
                                                    </div>
                                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                                        {{ $list->created_at->format('H:i') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Estado y conversación -->
                                            <div class="flex items-center justify-between">
                                                @if($list->conversation)
                                                    <div class="flex items-center gap-1 text-sm">
                                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                                        <span class="text-gray-500 dark:text-gray-400 truncate">{{ $list->conversation->name }}</span>
                                                    </div>
                                                @else
                                                    <div class="flex items-center gap-1 text-sm">
                                                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                                        <span class="text-gray-400 dark:text-gray-500">Sin conversación</span>
                                                    </div>
                                                @endif

                                                @php
                                                    // Verificar si la lista está completada
                                                    $totalProducts = $list->products->count();
                                                    $completedProducts = $list->products->where('pivot.completed', true)->count();
                                                    $isCompleted = $totalProducts > 0 && $completedProducts === $totalProducts;
                                                @endphp

                                                @if($isCompleted)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                        Completada
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                        Activa
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Contenido de la tarjeta -->
                                        <div class="p-8 space-y-6">
                                            <!-- Información principal -->
                                            <div class="grid grid-cols-2 gap-4">
                                                @if($list->budget)
                                                    <div class="text-center">
                                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($list->budget, 2) }}€</div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">Presupuesto</div>
                                                    </div>
                                                @endif

                                                <div class="text-center">
                                                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $list->products->count() }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">productos</div>
                                                </div>
                                            </div>

                                            @if($list->supermarket)
                                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <div class="flex items-center justify-center gap-2">
                                                        <span class="text-gray-600 dark:text-gray-400">🏪</span>
                                                        <span class="text-base font-medium text-gray-700 dark:text-gray-300">{{ $list->supermarket->supermarket_name }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Footer con acciones -->
                                        <div class="p-8 bg-gray-50 dark:bg-gray-700 border-t border-gray-100 dark:border-gray-600">
                                            <div class="flex gap-3">
                                                <button onclick="viewList({{ $list->id }})"
                                                        class="flex-1 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white px-4 py-3 rounded-md text-sm font-medium transition-colors">
                                                    Ver detalles
                                                </button>
                                                <button onclick="deleteList({{ $list->id }})"
                                                        class="px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-md text-sm transition-colors">
                                                    🗑️
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                    @endforeach
                                </div>
                    @else
                        <!-- Estado vacío -->
                        <div class="text-center py-16">
                            <div class="text-8xl mb-6">📋</div>
                            <h2 class="text-3xl font-bold text-gray-600 dark:text-gray-400 mb-4">No tienes listas de compra</h2>
                            <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg">Ve al chat para generar tu primera lista</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <!-- Modal para confirmar eliminación -->
    <div id="deleteModal" class="modal fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closeDeleteModal()"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Confirmar eliminación</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">¿Estás seguro de que quieres eliminar esta lista? Esta acción no se puede deshacer.</p>

                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-md font-medium transition">
                        Cancelar
                    </button>
                    <button type="button" onclick="confirmDelete()" class="flex-1 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white px-4 py-2 rounded-md font-medium transition">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript externo -->
    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/user-utils.js') }}"></script>
    <script src="{{ asset('js/shopping-list-index.js') }}"></script>
</body>
</html>
