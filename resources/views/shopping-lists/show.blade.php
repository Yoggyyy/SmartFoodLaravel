<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $shoppingList->name_list }} - SmartFood</title>
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

        /* Print styles */
        @media print {
            aside, .no-print {
                display: none !important;
            }

            main {
                margin: 0 !important;
            }

            section {
                margin: 0 !important;
                padding: 20px !important;
            }
        }

        /* Efectos para productos completados */
        .product-row.opacity-60 {
            opacity: 0.6;
        }

        .product-row.line-through .product-name {
            text-decoration: line-through;
        }

        /* Transiciones suaves */
        .product-row {
            transition: all 0.2s ease;
        }

        /* Hover effects */
        .product-row:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Search input focus */
        #search-products:focus {
            outline: none;
            ring: 2px;
            ring-color: #3b82f6;
            border-color: transparent;
        }

        /* Progress bar animation */
        #progress-bar {
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Custom checkbox styles */
        input[type="checkbox"]:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        /* Button hover effects */
        button:hover svg {
            transform: scale(1.1);
        }

        /* Category badges hover */
        .category-badge:hover {
            transform: scale(1.05);
        }

        /* Scroll interno personalizado */
        .scroll-container {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        .scroll-container::-webkit-scrollbar {
            width: 6px;
        }

        .scroll-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .scroll-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Asegurar que el header sticky funcione correctamente */
        .table-header-sticky {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f9fafb;
            box-shadow: 0 1px 0 0 #e5e7eb;
        }
    </style>

    <!-- Dark Mode JavaScript Tailwind Nativo (incluye configuración) -->
    <script src="{{ asset('js/dark-mode-tailwind.js') }}"></script>
</head>
<body class="font-onest bg-green-50 dark:bg-gray-900 h-screen transition-colors">
    <main class="flex min-h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 min-w-64 bg-green-100 dark:bg-gray-800 flex flex-col justify-between py-4 no-print transition-colors">
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
            <header class="flex items-center justify-between p-4 border-b bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 flex-shrink-0 no-print transition-colors">
                <div class="flex items-center gap-2">
                    <!-- Logo eliminado por redundancia -->
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400 hidden sm:block">Detalles de la lista</span>
                    <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        🖨️ Imprimir
                    </button>
                </div>
            </header>

            <!-- Contenido de la lista -->
            <div class="flex-1 p-4 bg-gray-50 dark:bg-gray-900 overflow-y-auto transition-colors">
                <div class="max-w-7xl mx-auto">
                    <!-- Botón volver -->
                    <div class="mb-4 no-print">
                        <a href="/listas" class="inline-flex items-center text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Volver a mis listas
                        </a>
                    </div>

                    <!-- Main Layout: Content + Sidebar -->
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <!-- Contenido principal (3/4) -->
                        <div class="lg:col-span-3 space-y-3">
                            <!-- Header con título -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $shoppingList->name_list }}</h1>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">Creada el {{ $shoppingList->created_at->format('d/m/Y - H:i') }}</p>
                                    </div>
                                    <!-- Badge de estado dinámico -->
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 transition-all duration-300" id="list-status-badge">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="status-icon">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span id="status-text">Activa</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Stats row -->
                                <div class="grid grid-cols-3 gap-4 mt-3">
                                    @if($shoppingList->budget)
                                    <div class="text-center">
                                        <div class="flex items-center justify-center gap-2 mb-1">
                                            <span class="text-green-600 dark:text-green-400">€</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Presupuesto</span>
                                        </div>
                                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($shoppingList->budget, 2) }}€</p>
                                    </div>
                                    @endif

                                    @if($shoppingList->supermarket)
                                    <div class="text-center">
                                        <div class="flex items-center justify-center gap-2 mb-1">
                                            <span class="text-gray-600 dark:text-gray-400">🏪</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Supermercado</span>
                                        </div>
                                        <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $shoppingList->supermarket->supermarket_name }}</p>
                                    </div>
                                    @endif

                                    <div class="text-center">
                                        <div class="flex items-center justify-center gap-2 mb-1">
                                            <span class="text-gray-600 dark:text-gray-400">📦</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Productos</span>
                                        </div>
                                        <p class="text-xl font-bold text-gray-600 dark:text-gray-400">{{ $shoppingList->products->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Progreso -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-3 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                            <svg class="w-3 h-3 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <h2 class="text-md font-semibold text-gray-900 dark:text-gray-100">Progreso</h2>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Completados</p>
                                        <p class="text-md font-bold text-gray-900 dark:text-gray-100" id="progress-text">0/{{ $shoppingList->products->count() }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-green-600 dark:bg-green-500 h-2 rounded-full transition-all duration-500" style="width: 0%" id="progress-bar"></div>
                                        </div>
                                    </div>
                                    <span class="text-md font-bold text-green-600 dark:text-green-400" id="progress-percentage">0%</span>
                                </div>
                            </div>

                            <!-- Lista de productos -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col transition-colors" style="height: 45vh;">
                                <!-- Header de la lista -->
                                <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-md font-semibold text-gray-900 dark:text-gray-100">Lista de la compra</h2>
                                        <div class="flex items-center gap-3">
                                            <button onclick="toggleSelectAll()" id="select-all-btn" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 font-medium transition-colors text-xs">
                                                Seleccionar todo
                                            </button>
                                            <button onclick="toggleAddProductForm()" class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white px-3 py-1.5 rounded-lg font-medium transition-colors no-print text-xs">
                                                + Añadir
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                @if($shoppingList->products->count() > 0)
                                <!-- Filtros -->
                                <div class="px-4 py-1.5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 flex-shrink-0">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <input type="text" placeholder="Buscar..." class="pl-8 pr-3 py-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent w-48 text-xs" id="search-products">
                                                <svg class="w-3 h-3 text-gray-400 dark:text-gray-500 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>

                                            <select class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent text-xs" id="filter-category">
                                                <option value="">Todas</option>
                                                @php
                                                    $categories = $shoppingList->products->pluck('category')->unique()->filter();
                                                @endphp
                                                @foreach($categories as $category)
                                                    <option value="{{ $category }}">{{ $category }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla con scroll interno -->
                                <div class="flex-1 overflow-y-auto scroll-container">
                                    <table class="w-full">
                                        <thead class="bg-gray-50 dark:bg-gray-700 table-header-sticky">
                                            <tr>
                                                <th class="text-left py-1.5 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-10"></th>
                                                <th class="text-left py-1.5 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PRODUCTO</th>
                                                <th class="text-left py-1.5 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CANTIDAD</th>
                                                <th class="text-left py-1.5 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PRECIO</th>
                                                <th class="text-left py-1.5 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800" id="products-container">
                                            @foreach($shoppingList->products as $product)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors product-row" data-product-id="{{ $product->id }}" data-category="{{ $product->category }}">
                                                <td class="py-1.5 px-3">
                                                    <input type="checkbox" class="h-3.5 w-3.5 text-green-600 rounded border-gray-300 dark:border-gray-600 focus:ring-green-500"
                                                           onchange="toggleProduct(this)"
                                                           {{ $product->pivot->completed ? 'checked' : '' }}>
                                                </td>
                                                <td class="py-1.5 px-3">
                                                    <div>
                                                        <h3 class="font-medium text-gray-900 dark:text-gray-100 product-name text-xs">{{ $product->name_product }}</h3>
                                                        @if($product->category)
                                                            @php
                                                                $colors = [
                                                                    'Frutas' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                                    'Verduras' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                                    'Carnes' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                                    'Pescados' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    'Lácteos' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    'Panadería' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    'Bebidas' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    'Limpieza' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    'Higiene' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    'Congelados' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    'Otros' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    'General' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                                ];
                                                                $colorClass = $colors[$product->category] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                                            @endphp
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                                                {{ $product->category }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-1.5 px-3">
                                                    <span class="text-gray-900 dark:text-gray-100 product-quantity text-xs">{{ $product->pivot->quantity ?: '1' }}</span>
                                                </td>
                                                <td class="py-1.5 px-3">
                                                    @if($product->price && $product->price > 0)
                                                        <span class="text-gray-900 dark:text-gray-100 font-medium product-price text-xs">{{ number_format($product->price, 2) }}€</span>
                                                    @else
                                                        <span class="text-gray-400 dark:text-gray-500 text-xs">-</span>
                                                    @endif
                                                </td>
                                                <td class="py-1.5 px-3 no-print">
                                                    <div class="flex items-center gap-1">
                                                        <button onclick="openEditProductModal({{ $product->id }}, '{{ $product->name_product }}', '{{ $product->pivot->quantity }}', '{{ $product->category }}', {{ $product->price }})"
                                                                class="p-0.5 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 rounded transition-colors">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                        <button onclick="deleteProduct({{ $product->id }})"
                                                                class="p-0.5 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 rounded transition-colors">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="flex-1 flex items-center justify-center">
                                    <div class="text-center py-8">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-md font-semibold text-gray-600 dark:text-gray-400 mb-2">Lista vacía</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mb-4 text-sm">Esta lista no tiene productos asociados.</p>
                                        <button onclick="toggleAddProductForm()" class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm">
                                            Añadir primer producto
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Sidebar derecho (1/4) -->
                        <div class="lg:col-span-1">
                            <!-- Resumen -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sticky top-4 transition-colors">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-6 h-6 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                        <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Resumen</h3>
                                </div>

                                <!-- Total estimado -->
                                <div class="mb-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total estimado</p>
                                    @php
                                        $totalEstimado = $shoppingList->products->sum(function($product) {
                                            return $product->price && $product->price > 0 ? $product->price : 0;
                                        });
                                    @endphp
                                    <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($totalEstimado, 2) }}€</p>
                                </div>

                                @if($shoppingList->budget)
                                <div class="space-y-2 mb-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Presupuesto</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ number_format($shoppingList->budget, 2) }}€</span>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Quedan</span>
                                        <span class="font-medium text-sm {{ $shoppingList->budget >= $totalEstimado ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($shoppingList->budget - $totalEstimado, 2) }}€
                                        </span>
                                    </div>
                                </div>
                                @endif

                                <!-- Categorías -->
                                @if($shoppingList->products->count() > 0)
                                <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-gray-100 mb-2">Categorías</h4>
                                    <div class="space-y-1">
                                        @foreach($categories as $category)
                                            @php
                                                $count = $shoppingList->products->where('category', $category)->count();
                                                $colors = [
                                                    'Frutas' => 'text-green-600 dark:text-green-400',
                                                    'Verduras' => 'text-green-600 dark:text-green-400',
                                                    'Carnes' => 'text-red-600 dark:text-red-400',
                                                    'Pescados' => 'text-gray-600 dark:text-gray-400',
                                                    'Lácteos' => 'text-gray-600 dark:text-gray-400',
                                                    'Panadería' => 'text-gray-600 dark:text-gray-400',
                                                    'Bebidas' => 'text-gray-600 dark:text-gray-400',
                                                    'Limpieza' => 'text-gray-600 dark:text-gray-400',
                                                    'Higiene' => 'text-gray-600 dark:text-gray-400',
                                                    'Congelados' => 'text-gray-600 dark:text-gray-400',
                                                    'Otros' => 'text-gray-600 dark:text-gray-400',
                                                    'General' => 'text-gray-600 dark:text-gray-400'
                                                ];
                                                $colorClass = $colors[$category] ?? 'text-gray-600 dark:text-gray-400';
                                            @endphp
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs {{ $colorClass }} font-medium">{{ $category }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $count }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal para añadir producto -->
    <div id="addProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Añadir Producto</h3>
                    <button onclick="closeAddProductModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        ✕
                    </button>
                </div>

                <form id="addProductForm" onsubmit="addProduct(event)">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del producto</label>
                            <input type="text" id="add_product_name" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="Ej: Manzanas">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad</label>
                            <input type="text" id="add_product_quantity"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="Ej: 1kg, 2 unidades">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                            <select id="add_product_category"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="General">General</option>
                                <option value="Frutas">Frutas</option>
                                <option value="Verduras">Verduras</option>
                                <option value="Carnes">Carnes</option>
                                <option value="Pescado">Pescado</option>
                                <option value="Lácteos">Lácteos</option>
                                <option value="Cereales">Cereales</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio (opcional)</label>
                            <input type="number" step="0.01" min="0" id="add_product_price"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeAddProductModal()"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white rounded-md transition-colors">
                            Añadir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar producto -->
    <div id="editProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Editar Producto</h3>
                    <button onclick="closeEditProductModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        ✕
                    </button>
                </div>

                <form id="editProductForm" onsubmit="updateProduct(event)">
                    <input type="hidden" id="edit_product_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del producto</label>
                            <input type="text" id="edit_product_name" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad</label>
                            <input type="text" id="edit_product_quantity"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                            <select id="edit_product_category"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="General">General</option>
                                <option value="Frutas">Frutas</option>
                                <option value="Verduras">Verduras</option>
                                <option value="Carnes">Carnes</option>
                                <option value="Pescado">Pescado</option>
                                <option value="Lácteos">Lácteos</option>
                                <option value="Cereales">Cereales</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio (opcional)</label>
                            <input type="number" step="0.01" min="0" id="edit_product_price"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeEditProductModal()"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white rounded-md transition-colors">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Incluir archivos JavaScript -->
    <script src="{{ asset('js/user-utils.js') }}"></script>
    <script src="{{ asset('js/shopping-list-manager.js') }}"></script>
    <script src="{{ asset('js/shopping-list-detail.js') }}"></script>

    <script>
        /**
         * Inicialización de la página de detalles de lista
         */
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar datos del usuario
            loadUserData();

            // Inicializar el gestor de listas con el ID actual (para modales)
            initializeShoppingListManager({{ $shoppingList->id }});

            // Inicializar funcionalidades específicas de la vista de detalles
            initializeShoppingListDetail({{ $shoppingList->id }});
        });
    </script>
</body>
</html>
