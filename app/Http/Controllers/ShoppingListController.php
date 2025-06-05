<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\Conversation;
use App\Models\Supermarket;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

/**
 * Controlador para gestión de listas de compra
 * Implementa API REST con respuestas JSON estandarizadas
 */
class ShoppingListController extends Controller
{
    /**
     * Respuesta de éxito estandarizada
     */
    private function successResponse($data = null, $message = 'Operación exitosa', $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Respuesta de error estandarizada
     */
    private function errorResponse($message = 'Error en la operación', $errors = null, $statusCode = 500): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Mostrar todas las listas de compra del usuario autenticado
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Obtener las listas de compra del usuario ordenadas por conversación
            $shoppingLists = ShoppingList::with(['conversation', 'supermarket', 'products'])
                ->where('user_id', $user->id)
                ->orderBy('conversation_id')
                ->orderBy('created_at', 'desc')
                ->get();

            // Agrupar por conversación
            $groupedLists = $shoppingLists->groupBy('conversation_id');

            // Obtener supermercados para los modales
            $supermarkets = Supermarket::orderBy('supermarket_name')->get();

            return view('shopping-lists.index', compact('groupedLists', 'supermarkets'));

        } catch (\Exception $e) {
            return view('shopping-lists.index', [
                'groupedLists' => collect(),
                'supermarkets' => collect()
            ]);
        }
    }

    /**
     * Mostrar una lista de compra específica
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            $shoppingList = ShoppingList::with(['conversation', 'supermarket', 'products'])
                ->where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            // Obtener supermercados para los modales
            $supermarkets = Supermarket::orderBy('supermarket_name')->get();

            // Obtener categorías únicas de los productos de la lista
            $categories = $shoppingList->products->pluck('category')->unique()->filter()->values();

            return view('shopping-lists.show', compact('shoppingList', 'supermarkets', 'categories'));

        } catch (\Exception $e) {
            return redirect()->route('shopping-lists.index')->with('error', 'Lista no encontrada');
        }
    }

    /**
     * Crear una nueva lista de compra desde el modal
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'budget' => 'nullable|numeric|min:0',
                'supermarket_id' => 'nullable|exists:supermarkets,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'Error de validación',
                    $validator->errors(),
                    422
                );
            }

            // Crear una conversación por defecto para esta lista
            $conversation = Conversation::create([
                'name' => $request->name,
                'user_id' => $user->id,
                'is_active' => true
            ]);

            // Crear la lista de compra
            $shoppingList = ShoppingList::create([
                'name_list' => $request->name,
                'budget' => $request->budget ?? 0,
                'user_id' => $user->id,
                'supermarket_id' => $request->supermarket_id,
                'conversation_id' => $conversation->id,
            ]);

            Log::info('Lista creada desde modal', [
                'list_id' => $shoppingList->id,
                'user_id' => $user->id,
                'conversation_id' => $conversation->id
            ]);

            return $this->successResponse(
                $shoppingList->load(['conversation', 'supermarket', 'products']),
                'Lista creada correctamente'
            );

        } catch (\Exception $e) {
            Log::error('Error al crear lista desde modal', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'request_data' => $request->all()
            ]);

            return $this->errorResponse(
                'Error al crear la lista',
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Eliminar una lista de compra
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            $shoppingList = ShoppingList::where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            $listName = $shoppingList->name_list; // Guardar para el log
            $shoppingList->delete();

            Log::info('Lista eliminada correctamente', [
                'list_id' => $id,
                'list_name' => $listName,
                'user_id' => $user->id
            ]);

            return $this->successResponse(
                null,
                'Lista eliminada correctamente'
            );

        } catch (\Exception $e) {
            Log::error('Error al eliminar lista', [
                'error' => $e->getMessage(),
                'list_id' => $id,
                'user_id' => $request->user()->id ?? null
            ]);

            return $this->errorResponse(
                'Error al eliminar la lista',
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtener listas de compra agrupadas por conversación (API Web)
     */
    public function getGroupedLists(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $shoppingLists = ShoppingList::with(['conversation', 'supermarket', 'products'])
                ->where('user_id', $user->id)
                ->orderBy('conversation_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $groupedLists = $shoppingLists->groupBy('conversation_id')->map(function ($lists) {
                return [
                    'conversation' => $lists->first()->conversation,
                    'lists' => $lists
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $groupedLists->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las listas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una lista de compra desde el chat
     */
    public function createFromChat(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Log de datos recibidos para debugging
            Log::info('Datos recibidos para crear lista desde chat', [
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'name_list' => 'required|string|max:255',
                'conversation_id' => 'nullable|string|max:50', // Cambiado a nullable
                'conversation_title' => 'required|string|max:255',
                'budget' => 'nullable|numeric|min:0',
                'supermarket_name' => 'nullable|string|max:255',
                'products' => 'nullable|array',
                'products.*.name' => 'required|string|max:255',
                'products.*.quantity' => 'nullable|string|max:50',
                'products.*.category' => 'nullable|string|max:100',
                'products.*.price' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                Log::error('Error de validación al crear lista desde chat', [
                    'user_id' => $user->id,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar conversación existente por ID o crear nueva
            $conversation = null;

            // Si conversation_id es numérico y no nulo, buscar por ID
            if ($request->conversation_id && is_numeric($request->conversation_id)) {
                $conversation = Conversation::where('id', $request->conversation_id)
                    ->where('user_id', $user->id)
                    ->first();

                Log::info('Buscando conversación existente', [
                    'conversation_id' => $request->conversation_id,
                    'found' => $conversation ? 'sí' : 'no'
                ]);
            }

            // Si no se encontró o es un ID local/nulo, crear nueva conversación
            if (!$conversation) {
                $conversation = Conversation::create([
                    'name' => $request->conversation_title,
                    'user_id' => $user->id,
                    'is_active' => true
                ]);

                Log::info('Nueva conversación creada para lista', [
                    'conversation_id' => $conversation->id,
                    'name' => $conversation->name,
                    'user_id' => $user->id
                ]);
            } else {
                // Si encontramos una conversación existente, actualizar su nombre si es diferente
                if ($conversation->name !== $request->conversation_title &&
                    ($conversation->name === 'Nueva Lista' || strpos($conversation->name, 'Lista de') === 0)) {

                    $oldName = $conversation->name;
                    $conversation->update(['name' => $request->conversation_title]);

                    Log::info('Nombre de conversación actualizado', [
                        'conversation_id' => $conversation->id,
                        'old_name' => $oldName,
                        'new_name' => $request->conversation_title,
                        'user_id' => $user->id
                    ]);
                }
            }

            // Crear o encontrar supermercado si se especifica
            $supermarket = null;
            if ($request->supermarket_name) {
                $supermarket = Supermarket::firstOrCreate([
                    'supermarket_name' => $request->supermarket_name
                ]);

                Log::info('Supermercado procesado', [
                    'supermarket_name' => $request->supermarket_name,
                    'supermarket_id' => $supermarket->id
                ]);
            }

            // Crear la lista de compra
            $shoppingList = ShoppingList::create([
                'name_list' => $request->name_list,
                'budget' => $request->budget ?? 0,
                'user_id' => $user->id,
                'supermarket_id' => $supermarket ? $supermarket->id : null,
                'conversation_id' => $conversation->id,
            ]);

            Log::info('Lista de compra creada desde chat', [
                'list_id' => $shoppingList->id,
                'conversation_id' => $conversation->id,
                'products_count' => count($request->products ?? [])
            ]);

            // Asegurar que existe un supermercado por defecto para productos
            $defaultSupermarket = Supermarket::firstOrCreate([
                'supermarket_name' => 'General'
            ]);

            // Usar el supermercado especificado o el por defecto para productos
            $productSupermarket = $supermarket ?: $defaultSupermarket;

            // Agregar productos si se proporcionan
            if ($request->products && is_array($request->products)) {
                foreach ($request->products as $index => $productData) {
                    try {
                        // Crear o encontrar producto
                        $product = Product::firstOrCreate([
                            'name_product' => $productData['name']
                        ], [
                            'category' => $productData['category'] ?? 'General',
                            'price' => $productData['price'] ?? 0,
                            'supermarket_id' => $productSupermarket->id
                        ]);

                        // Asociar producto con la lista
                        $shoppingList->products()->attach($product->id, [
                            'quantity' => $productData['quantity'] ?? '1',
                            'content' => $productData['category'] ?? '',
                        ]);

                        Log::info('Producto agregado a lista', [
                            'product_id' => $product->id,
                            'product_name' => $product->name_product,
                            'list_id' => $shoppingList->id
                        ]);

                    } catch (\Exception $productError) {
                        Log::error('Error al agregar producto individual', [
                            'product_index' => $index,
                            'product_data' => $productData,
                            'error' => $productError->getMessage()
                        ]);
                        // Continuar con el siguiente producto
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Lista de compra creada exitosamente',
                'data' => $shoppingList->load(['conversation', 'supermarket', 'products'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear lista desde chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la lista de compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Añadir un producto a la lista de compra
     */
    public function addProduct(Request $request, $id)
    {
        $user = $request->user();
        $shoppingList = ShoppingList::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
        ]);

        // Asegurar que existe un supermercado por defecto para productos
        $defaultSupermarket = Supermarket::firstOrCreate([
            'supermarket_name' => 'General'
        ]);

        // Usar el supermercado de la lista o el por defecto
        $productSupermarket = $shoppingList->supermarket ?: $defaultSupermarket;

        // Crear o encontrar producto
        $product = Product::firstOrCreate([
            'name_product' => $request->name
        ], [
            'category' => $request->category ?: 'General',
            'price' => $request->price ?: 0,
            'supermarket_id' => $productSupermarket->id,
        ]);

        // Verificar si el producto ya está en la lista
        if ($shoppingList->products()->where('product_id', $product->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El producto ya está en la lista'
            ], 400);
        }

        // Agregar producto a la lista
        $shoppingList->products()->attach($product->id, [
            'quantity' => $request->quantity ?: '1',
            'content' => $request->content ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto añadido correctamente',
            'product' => [
                'id' => $product->id,
                'name' => $product->name_product,
                'quantity' => $request->quantity ?: '1',
                'category' => $product->category,
                'price' => $product->price,
            ]
        ]);
    }

    /**
     * Actualizar un producto en la lista de compra
     */
    public function updateProduct(Request $request, $listId, $productId)
    {
        $user = $request->user();
        $shoppingList = ShoppingList::where('user_id', $user->id)->findOrFail($listId);

        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
        ]);

        // Verificar que el producto está en la lista
        $pivotRecord = $shoppingList->products()->where('product_id', $productId)->first();
        if (!$pivotRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado en la lista'
            ], 404);
        }

        // Actualizar el producto base
        $product = Product::findOrFail($productId);
        $product->update([
            'name_product' => $request->name,
            'category' => $request->category ?: $product->category,
            'price' => $request->price ?? $product->price,
        ]);

        // Actualizar la relación pivot (cantidad)
        $shoppingList->products()->updateExistingPivot($productId, [
            'quantity' => $request->quantity ?: '1',
            'content' => $request->content ?? $pivotRecord->pivot->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente',
            'product' => [
                'id' => $product->id,
                'name' => $product->name_product,
                'quantity' => $request->quantity ?: '1',
                'category' => $product->category,
                'price' => $product->price,
            ]
        ]);
    }

    /**
     * Eliminar un producto de la lista de compra
     */
    public function removeProduct(Request $request, $listId, $productId)
    {
        $user = $request->user();
        $shoppingList = ShoppingList::where('user_id', $user->id)->findOrFail($listId);

        // Verificar que el producto está en la lista
        if (!$shoppingList->products()->where('product_id', $productId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado en la lista'
            ], 404);
        }

        // Eliminar producto de la lista
        $shoppingList->products()->detach($productId);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado de la lista'
        ]);
    }

    /**
     * Actualizar estado completado de un producto en la lista
     */
    public function updateProductCompleted(Request $request, $listId, $productId)
    {
        try {
            $user = $request->user();
            $shoppingList = ShoppingList::where('user_id', $user->id)->findOrFail($listId);

            $request->validate([
                'completed' => 'required|boolean',
            ]);

            // Verificar que el producto está en la lista
            if (!$shoppingList->products()->where('product_id', $productId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado en la lista'
                ], 404);
            }

            // Actualizar estado completado en la tabla pivot
            $shoppingList->products()->updateExistingPivot($productId, [
                'completed' => $request->completed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado del producto actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado del producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado de la lista de compra
     */
    public function updateListStatus(Request $request, $listId)
    {
        try {
            $user = $request->user();
            $shoppingList = ShoppingList::where('user_id', $user->id)->findOrFail($listId);

            $request->validate([
                'status' => 'required|string|in:active,completed',
            ]);

            // Para este caso, el estado se maneja automáticamente basado en los productos completados
            // Esta función está disponible para futuras extensiones
            return response()->json([
                'success' => true,
                'message' => 'Estado registrado',
                'status' => $request->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado de la lista',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
