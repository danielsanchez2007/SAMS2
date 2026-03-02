<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Controlador para interactuar con Gemini AI
 * 
 * Este controlador maneja las peticiones relacionadas con Gemini
 * y sirve como punto de entrada para las funcionalidades de IA.
 */
class GeminiController extends Controller
{
    /**
     * Instancia del servicio de Gemini
     */
    protected GeminiService $geminiService;

    /**
     * Constructor del controlador
     */
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Detecta mensajes de API key inválida en diferentes formatos.
     */
    private function hasInvalidApiKeyError(?string $text): bool
    {
        $value = mb_strtolower(trim((string) $text), 'UTF-8');
        if ($value === '') {
            return false;
        }

        return str_contains($value, 'api key not valid')
            || str_contains($value, 'invalid api key')
            || str_contains($value, 'pass a valid api key')
            || str_contains($value, 'api_key_invalid')
            || str_contains($value, 'gemini_api_key')
            || (str_contains($value, 'api key') && (
                str_contains($value, 'not valid')
                || str_contains($value, 'invalid')
                || str_contains($value, 'no es válida')
                || str_contains($value, 'no es valida')
            ));
    }

    /**
     * Muestra la página de prueba de Gemini (opcional)
     *
     * @return View
     */
    public function index(): View
    {
        $isConfigured = $this->geminiService->isConfigured();
        return view('gemini.index', compact('isConfigured'));
    }

    /**
     * Genera contenido usando Gemini
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        // Validar la petición
        $request->validate([
            'prompt' => 'required|string|max:5000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'maxOutputTokens' => 'nullable|integer|min:1|max:2048',
        ]);

        // Verificar si el servicio está configurado
        if (!$this->geminiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'El servicio de Gemini no está configurado. Por favor, contacta al administrador.',
            ], 503);
        }

        // Preparar opciones
        $options = [];
        if ($request->has('temperature')) {
            $options['temperature'] = (float) $request->temperature;
        }
        if ($request->has('maxOutputTokens')) {
            $options['maxOutputTokens'] = (int) $request->maxOutputTokens;
        }

        // Generar contenido
        $result = $this->geminiService->generateContent($request->prompt, $options);

        // Retornar respuesta
        $statusCode = $result['success'] ? 200 : 500;
        return response()->json($result, $statusCode);
    }

    /**
     * Analiza texto usando Gemini
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:10000',
            'instruction' => 'nullable|string|max:500',
        ]);

        if (!$this->geminiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'Servicio no disponible.',
            ], 503);
        }

        $result = $this->geminiService->analyzeText(
            $request->text,
            $request->instruction ?? ''
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Genera un resumen de texto
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function summarize(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:20000',
            'maxLength' => 'nullable|integer|min:50|max:500',
        ]);

        if (!$this->geminiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'Servicio no disponible.',
            ], 503);
        }

        $maxLength = $request->maxLength ?? 200;
        $result = $this->geminiService->summarize($request->text, $maxLength);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Traduce texto
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'targetLanguage' => 'required|string|max:50',
            'sourceLanguage' => 'nullable|string|max:50',
        ]);

        if (!$this->geminiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'Servicio no disponible.',
            ], 503);
        }

        $result = $this->geminiService->translate(
            $request->text,
            $request->targetLanguage,
            $request->sourceLanguage
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Verifica el estado del servicio
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        $isConfigured = $this->geminiService->isConfigured();
        
        return response()->json([
            'configured' => $isConfigured,
            'message' => $isConfigured 
                ? 'Gemini está configurado y listo para usar.' 
                : 'Gemini no está configurado correctamente.',
        ]);
    }

    /**
     * Chat con contexto del sistema
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chatWithContext(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'mode' => 'nullable|string|in:full,sams',
        ]);

        try {
            $requestedMode = $request->mode ?? 'sams';
            $history = $request->history ?? [];
            $message = $request->message;

            $result = $this->geminiService->chatWithContext(
                $message,
                $history,
                $requestedMode
            );

            // Asegurar que siempre se devuelva un array con success
            if (!isset($result['success'])) {
                $result['success'] = false;
                $result['error'] = $result['error'] ?? 'Error desconocido en el servicio.';
            }

            // Sanitizar errores de API key sin forzar cambio de modo.
            $invalidKeyDetected = $this->hasInvalidApiKeyError($result['error'] ?? null)
                || $this->hasInvalidApiKeyError($result['message'] ?? null);

            if ($invalidKeyDetected) {
                if (($result['success'] ?? false) && $requestedMode === 'full') {
                    $result['message'] = 'Modo Full activo con respaldo. No fue posible usar la API principal de Gemini en este momento.';
                    $result['effective_mode'] = 'full';
                } else {
                    $result['error'] = 'La API Key de Gemini no es válida o no tiene permisos. Verifica GEMINI_API_KEY para usar Gemini en Modo Full.';
                }
            }

            $statusCode = $result['success'] ? 200 : ($requestedMode === 'sams' ? 200 : 502);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            \Log::error('GeminiController: Excepción no capturada', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor.',
            ], 500);
        }
    }

    /**
     * Obtiene información completa del sistema
     *
     * @return JsonResponse
     */
    public function getSystemInfo(): JsonResponse
    {
        try {
            $info = $this->geminiService->getSystemInfo();
            return response()->json([
                'success' => true,
                'data' => $info,
            ]);
        } catch (\Exception $e) {
            \Log::error('GeminiController: Error obteniendo información del sistema', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener información del sistema: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene el historial de chat del usuario actual
     *
     * @return JsonResponse
     */
    public function getHistory(): JsonResponse
    {
        try {
            $user = session('sams2_user');
            $usuarioId = $user['id'] ?? null;
            
            if (!$usuarioId || $usuarioId === 'mega_admin') {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuario no identificado',
                ], 401);
            }

            $history = \App\Models\GeminiChatHistory::where('usuario_id', $usuarioId)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($history) {
                return response()->json([
                    'success' => true,
                    'history' => [
                        'messages' => $history->messages ?? [],
                        'mode' => $history->mode ?? 'sams',
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'history' => null,
            ]);
        } catch (\Exception $e) {
            \Log::error('GeminiController: Error obteniendo historial', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener historial: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Guarda el historial de chat del usuario actual
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveHistory(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'messages' => 'required|array',
                'mode' => 'nullable|string|in:full,sams',
            ]);

            $user = session('sams2_user');
            $usuarioId = $user['id'] ?? null;
            
            if (!$usuarioId || $usuarioId === 'mega_admin') {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuario no identificado',
                ], 401);
            }

            \App\Models\GeminiChatHistory::updateOrCreate(
                ['usuario_id' => $usuarioId],
                [
                    'messages' => $request->messages,
                    'mode' => $request->mode ?? 'sams',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Historial guardado correctamente',
            ]);
        } catch (\Exception $e) {
            \Log::error('GeminiController: Error guardando historial', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al guardar historial: ' . $e->getMessage(),
            ], 500);
        }
    }
}
