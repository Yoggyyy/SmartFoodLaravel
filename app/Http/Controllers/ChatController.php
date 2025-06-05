<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Chat con IA para SmartFood
 *
 * Este controlador gestiona todas las interacciones del chat inteligente
 * que utiliza OpenAI para generar listas de compras personalizadas
 * basadas en las preferencias y alérgenos del usuario.
 *
 * Funcionalidades principales:
 * - Procesamiento de mensajes con IA (OpenAI GPT)
 * - Generación de respuestas contextualizadas por usuario
 * - Estadísticas de uso de tokens de OpenAI
 * - Sugerencias rápidas personalizadas
 * - Logging para monitoreo y debugging
 *
 * @package App\Http\Controllers
 * @author Tu Nombre
 * @version 1.0
 * @since 1.0.0
 */
class ChatController extends Controller
{
    /**
     * Servicio de OpenAI para generar respuestas de IA
     *
     * @var OpenAIService
     */
    private $openAIService;

    /**
     * Constructor del controlador
     *
     * Inyecta el servicio de OpenAI para su uso en los métodos del controlador.
     *
     * @param OpenAIService $openAIService Servicio configurado de OpenAI
     */
    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Enviar mensaje al chat y obtener respuesta de IA
     *
     * Este método procesa los mensajes del usuario, los envía a OpenAI
     * junto con el contexto personalizado (alérgenos, preferencias) y
     * devuelve una respuesta inteligente para generar listas de compras.
     *
     * @param Request $request Mensaje del usuario y datos de conversación
     * @return JsonResponse Respuesta de la IA con información de tokens
     *
     * @throws \Exception Si ocurre un error en la comunicación con OpenAI
     *
     * Body Parameters:
     * - message (required|string|max:1000): Mensaje del usuario para la IA
     * - conversation_id (optional|string|max:50): ID para mantener contexto de conversación
     *
     * Headers requeridos:
     * - Authorization: Bearer {token}
     *
     * @example
     * POST /api/chat/send-message
     * {
     *   "message": "Necesito una lista de compras para 4 personas sin gluten",
     *   "conversation_id": "conv_123"
     * }
     *
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "bot_message": "Aquí tienes una lista sin gluten para 4 personas...",
     *     "conversation_id": "conv_123",
     *     "tokens_used": 245,
     *     "cached": false,
     *     "timestamp": "2024-01-15T10:30:00Z"
     *   }
     * }
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            // Validación estricta de entrada para evitar spam y errores
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'conversation_id' => 'nullable|string|max:50'
            ], [
                'message.required' => 'El mensaje es obligatorio.',
                'message.max' => 'El mensaje no puede exceder 1000 caracteres.',
                'conversation_id.max' => 'ID de conversación inválido.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener datos del usuario autenticado
            $user = $request->user();
            $userMessage = $request->message;
            $conversationId = $request->conversation_id ?? 'default';

            // Preparar contexto personalizado del usuario para la IA
            $userContext = [
                'name' => $user->name,
                'allergens' => $user->allergens->pluck('name_allergen')->toArray(),
                'preferences' => $user->preferences
            ];

            // Generar respuesta inteligente usando OpenAI con contexto del usuario
            $aiResponse = $this->openAIService->generateShoppingListResponse($userMessage, $userContext);

            // Incrementar estadísticas de uso para control de costos
            $this->openAIService->incrementStats(
                $aiResponse['tokens_used'] ?? 0,
                $aiResponse['cached'] ?? false
            );

            // Logging detallado para monitoreo y debugging
            Log::info('Chat: Mensaje procesado exitosamente', [
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
                'message_length' => strlen($userMessage),
                'tokens_used' => $aiResponse['tokens_used'] ?? 0,
                'cached' => $aiResponse['cached'] ?? false,
                'timestamp' => now()->toISOString()
            ]);

            // Respuesta exitosa con todos los datos relevantes
            return response()->json([
                'success' => true,
                'data' => [
                    'bot_message' => $aiResponse['message'],
                    'conversation_id' => $conversationId,
                    'tokens_used' => $aiResponse['tokens_used'] ?? 0,
                    'cached' => $aiResponse['cached'] ?? false,
                    'timestamp' => now()->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            // Log detallado de errores para debugging
            Log::error('Chat: Error al procesar mensaje', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null,
                'message' => $request->message ?? null,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor, inténtalo de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de uso de OpenAI
     *
     * Devuelve información sobre el consumo de tokens, costos estimados
     * y estadísticas generales de uso de la API de OpenAI.
     *
     * @param Request $request Request autenticado
     * @return JsonResponse Estadísticas de uso de OpenAI
     *
     * @throws \Exception Si ocurre un error al obtener las estadísticas
     *
     * Headers requeridos:
     * - Authorization: Bearer {token}
     *
     * @example
     * GET /api/chat/usage-stats
     *
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "total_tokens": 15420,
     *     "total_requests": 89,
     *     "estimated_cost": 0.031,
     *     "cache_hit_rate": 23.5
     *   }
     * }
     */
    public function getUsageStats(Request $request): JsonResponse
    {
        try {
            // Obtener estadísticas del servicio de OpenAI
            $stats = $this->openAIService->getUsageStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chat: Error al obtener estadísticas de uso', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Generar sugerencias rápidas personalizadas para el usuario
     *
     * Crea una lista de sugerencias de mensajes pre-definidos que el usuario
     * puede usar rápidamente, personalizadas según sus alérgenos y perfil.
     *
     * @param Request $request Request autenticado
     * @return JsonResponse Array de sugerencias personalizadas
     *
     * @throws \Exception Si ocurre un error al generar las sugerencias
     *
     * Headers requeridos:
     * - Authorization: Bearer {token}
     *
     * @example
     * GET /api/chat/suggestions
     *
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "suggestions": [
     *       "Lista semanal para Juan",
     *       "Compra rápida para hoy",
     *       "Lista sin gluten, lácteos",
     *       "Productos básicos con 30€"
     *     ]
     *   }
     * }
     */
    public function getQuickSuggestions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Sugerencias base personalizadas con el nombre del usuario
            $suggestions = [
                "Lista semanal para {$user->name}",
                "Compra rápida para hoy",
                "Lista sin gluten para 4 personas",
                "Productos básicos con 30€",
                "Lista saludable para la semana"
            ];

            // Personalización adicional basada en alérgenos del usuario
            if ($user->allergens->count() > 0) {
                $allergenNames = $user->allergens->pluck('name_allergen')->toArray();
                $allergensList = implode(', ', $allergenNames);
                $suggestions[] = "Lista sin {$allergensList}";
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'suggestions' => $suggestions
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chat: Error al obtener sugerencias', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener sugerencias',
                'data' => ['suggestions' => []]
            ], 500);
        }
    }

    /**
     * Obtener todas las conversaciones del usuario autenticado
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getConversations(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $conversations = $user->conversations()
                ->with(['messages' => function($query) {
                    $query->orderBy('created_at', 'asc');
                }])
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $conversations
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chat: Error al obtener conversaciones', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conversaciones'
            ], 500);
        }
    }

    /**
     * Crear nueva conversación para el usuario
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createConversation(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            $conversation = $user->conversations()->create([
                'name' => $request->name ?? 'Nueva Lista',
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'data' => $conversation
            ], 201);

        } catch (\Exception $e) {
            Log::error('Chat: Error al crear conversación', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear conversación'
            ], 500);
        }
    }

    /**
     * Obtener conversación específica con sus mensajes
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getConversation(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            $conversation = $user->conversations()
                ->with(['messages' => function($query) {
                    $query->orderBy('created_at', 'asc');
                }])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $conversation
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chat: Error al obtener conversación', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'conversation_id' => $id,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada'
            ], 404);
        }
    }

    /**
     * Actualizar conversación (principalmente el nombre)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateConversation(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            $conversation = $user->conversations()->findOrFail($id);
            $conversation->update([
                'name' => $request->name
            ]);

            return response()->json([
                'success' => true,
                'data' => $conversation
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chat: Error al actualizar conversación', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'conversation_id' => $id,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar conversación'
            ], 500);
        }
    }

    /**
     * Eliminar conversación y todos sus mensajes
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function deleteConversation(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            $conversation = $user->conversations()->findOrFail($id);

            // Eliminar todos los mensajes asociados
            $conversation->messages()->delete();

            // Eliminar la conversación
            $conversation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversación eliminada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chat: Error al eliminar conversación', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'conversation_id' => $id,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar conversación'
            ], 500);
        }
    }

    /**
     * Agregar mensaje a una conversación específica
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function addMessage(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:10000',
                'type' => 'required|in:user,bot'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            $conversation = $user->conversations()->findOrFail($id);

            $message = $conversation->messages()->create([
                'user_id' => $user->id,
                'content' => $request->content,
                'type' => $request->type
            ]);

            // Actualizar timestamp de la conversación
            $conversation->touch();

            return response()->json([
                'success' => true,
                'data' => $message
            ], 201);

        } catch (\Exception $e) {
            Log::error('Chat: Error al agregar mensaje', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'conversation_id' => $id,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al agregar mensaje'
            ], 500);
        }
    }
}
