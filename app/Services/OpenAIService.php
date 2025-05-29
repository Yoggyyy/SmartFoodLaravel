<?php

namespace App\Services;

use OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenAIService
{
    private $client;
    private $model;
    private $maxTokens;
    private $temperature;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
        $this->model = config('services.openai.model', 'gpt-4o-mini');
        $this->maxTokens = config('services.openai.max_tokens', 1000);
        $this->temperature = config('services.openai.temperature', 0.7);
    }

    /**
     * Generar respuesta de chat para SmartFood
     */
    public function generateShoppingListResponse(string $userMessage, array $userContext = []): array
    {
        try {
            $systemPrompt = $this->buildSystemPrompt($userContext);
            $cacheKey = $this->generateCacheKey($userMessage, $userContext);

            // Intentar obtener respuesta del caché
            $cachedResponse = Cache::get($cacheKey);
            if ($cachedResponse) {
                Log::info('OpenAI: Respuesta obtenida del caché', ['cache_key' => $cacheKey]);
                return [
                    'success' => true,
                    'message' => $cachedResponse,
                    'cached' => true,
                    'tokens_used' => 0
                ];
            }

            // Llamada a OpenAI
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            $botMessage = $response->choices[0]->message->content;
            $tokensUsed = $response->usage->totalTokens;

            // Guardar en caché por 1 hora
            Cache::put($cacheKey, $botMessage, 3600);

            Log::info('OpenAI: Respuesta generada exitosamente', [
                'tokens_used' => $tokensUsed,
                'model' => $this->model
            ]);

            return [
                'success' => true,
                'message' => $botMessage,
                'cached' => false,
                'tokens_used' => $tokensUsed
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI: Error al generar respuesta', [
                'error' => $e->getMessage(),
                'user_message' => $userMessage
            ]);

            return [
                'success' => false,
                'message' => 'Lo siento, no puedo procesar tu solicitud en este momento. Por favor, inténtalo de nuevo.',
                'error' => $e->getMessage(),
                'tokens_used' => 0
            ];
        }
    }

    /**
     * Construir prompt del sistema personalizado
     */
    private function buildSystemPrompt(array $userContext): string
    {
        $basePrompt = "Eres SmartFood, un asistente inteligente especializado en crear listas de compra personalizadas.

CONTEXTO DEL USUARIO:";

        if (!empty($userContext['name'])) {
            $basePrompt .= "\n- Nombre: {$userContext['name']}";
        }

        if (!empty($userContext['allergens'])) {
            $allergensList = implode(', ', $userContext['allergens']);
            $basePrompt .= "\n- Alérgenos: {$allergensList}";
        }

        if (!empty($userContext['preferences'])) {
            $basePrompt .= "\n- Preferencias: {$userContext['preferences']}";
        }

        $basePrompt .= "\n\nINSTRUCCIONES:
1. Crea listas de compra detalladas y organizadas
2. Respeta SIEMPRE los alérgenos del usuario
3. Sugiere productos específicos con precios aproximados en euros
4. Organiza por categorías (Lácteos, Carnes, Verduras, etc.)
5. Incluye cantidades específicas
6. Proporciona alternativas cuando sea posible
7. Mantén un tono amigable y profesional
8. Si mencionan un presupuesto, ajústate a él
9. Si mencionan un supermercado específico, considera sus productos

FORMATO DE RESPUESTA:
- Usa emojis para hacer la lista más visual
- Estructura clara con categorías
- Incluye el total estimado al final
- Máximo 800 caracteres para mantener respuestas concisas

EJEMPLO:
🛒 **Lista de Compra Semanal**

🥛 **Lácteos**
- Leche desnatada (1L) - 1.20€
- Yogur natural (pack 4) - 2.50€

🥩 **Carnes**
- Pechuga de pollo (500g) - 4.80€

🥬 **Verduras**
- Lechuga iceberg - 1.50€
- Tomates (1kg) - 2.20€

💰 **Total estimado: 12.20€**";

        return $basePrompt;
    }

    /**
     * Generar clave de caché para respuestas similares
     */
    private function generateCacheKey(string $userMessage, array $userContext): string
    {
        $contextString = json_encode($userContext);
        return 'openai_response_' . md5($userMessage . $contextString);
    }

    /**
     * Obtener estadísticas de uso
     */
    public function getUsageStats(): array
    {
        return [
            'total_requests' => Cache::get('openai_total_requests', 0),
            'total_tokens' => Cache::get('openai_total_tokens', 0),
            'cached_responses' => Cache::get('openai_cached_responses', 0),
            'estimated_cost' => $this->calculateEstimatedCost()
        ];
    }

    /**
     * Calcular costo estimado
     */
    private function calculateEstimatedCost(): float
    {
        $totalTokens = Cache::get('openai_total_tokens', 0);

        // Precios GPT-4o-mini (por millón de tokens)
        $inputCost = 0.40; // $0.40 por millón
        $outputCost = 1.60; // $1.60 por millón

        // Estimación: 30% input, 70% output
        $inputTokens = $totalTokens * 0.3;
        $outputTokens = $totalTokens * 0.7;

        $cost = ($inputTokens / 1000000 * $inputCost) + ($outputTokens / 1000000 * $outputCost);

        return round($cost, 4);
    }

    /**
     * Incrementar contadores de estadísticas
     */
    public function incrementStats(int $tokensUsed, bool $cached = false): void
    {
        Cache::increment('openai_total_requests');

        if (!$cached) {
            Cache::increment('openai_total_tokens', $tokensUsed);
        } else {
            Cache::increment('openai_cached_responses');
        }
    }
}
