<?php

namespace App\Services;

use App\Models\Proveedor;
use App\Models\Equipo;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Fabricante;
use App\Models\Role;
use App\Models\Grupo;
use App\Models\Cargo;
use App\Models\Sede;
use App\Models\Bodega;
use App\Models\TipoEquipo;
use App\Models\TipoItem;
use App\Models\UsoItem;
use App\Models\EstadoRemision;
use App\Models\Asignacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para interactuar con Google Gemini AI
 * 
 * Este servicio centraliza todas las peticiones a la API de Gemini
 * y maneja errores de forma uniforme.
 */
class GeminiService
{
    /**
     * URL base de la API de Gemini
     */
    protected string $apiUrl;

    /**
     * API Key de Gemini
     */
    protected string $apiKey;

    /**
     * Modelo de Gemini a utilizar
     */
    protected string $model;

    /**
     * Tiempo máximo de espera para las peticiones (segundos)
     */
    protected int $timeout;

    /**
     * Indica si el servicio está habilitado
     */
    protected bool $enabled;

    /**
     * Constructor del servicio
     */
    public function __construct()
    {
        $this->apiUrl = config('services.gemini.api_url');
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model');
        $this->timeout = config('services.gemini.timeout', 30);
        $this->enabled = config('services.gemini.enabled', true);
    }

    /**
     * Verifica si el servicio está correctamente configurado
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiUrl) && $this->enabled;
    }

    /**
     * Genera contenido usando Gemini
     *
     * @param string $prompt El prompt a enviar a Gemini
     * @param array $options Opciones adicionales (temperature, maxOutputTokens, etc.)
     * @return array Respuesta de Gemini o array con error
     */
    public function generateContent(string $prompt, array $options = []): array
    {
        // Verificar si está configurado
        if (!$this->isConfigured()) {
            Log::warning('Gemini: Servicio no configurado o deshabilitado');
            return [
                'success' => false,
                'error' => 'El servicio de Gemini no está configurado correctamente.',
                'message' => null,
            ];
        }

        try {
            // La API Key debe ir como query parameter en la URL, NO en el body
            $endpoint = "{$this->apiUrl}/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

            // Preparar el payload (sin incluir la key en el body)
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $options['temperature'] ?? 0.7,
                    'maxOutputTokens' => $options['maxOutputTokens'] ?? 1000,
                    'topP' => $options['topP'] ?? 0.95,
                    'topK' => $options['topK'] ?? 40,
                ]
            ];

            // Realizar la petición
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            // Verificar si la petición fue exitosa
            if ($response->successful()) {
                $data = $response->json();
                
                // Extraer el texto de la respuesta
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    Log::info('Gemini: Respuesta generada exitosamente');
                    return [
                        'success' => true,
                        'error' => null,
                        'message' => $text,
                        'raw' => $data,
                    ];
                }
                
                Log::warning('Gemini: Respuesta sin contenido', ['data' => $data]);
                return [
                    'success' => false,
                    'error' => 'No se recibió contenido de Gemini.',
                    'message' => null,
                ];
            }

            // Manejar errores de la API
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Error desconocido';
            
            Log::error('Gemini: Error en la API', [
                'status' => $response->status(),
                'error' => $errorMessage,
            ]);

            return [
                'success' => false,
                'error' => "Error de Gemini: {$errorMessage}",
                'message' => null,
            ];

        } catch (Exception $e) {
            Log::error('Gemini: Excepción al generar contenido', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Ocurrió un error al comunicarse con Gemini. Por favor, intenta nuevamente.',
                'message' => null,
            ];
        }
    }

    /**
     * Genera un chat o conversación con Gemini
     *
     * @param array $messages Array de mensajes [['role' => 'user', 'text' => '...'], ...]
     * @param array $options Opciones adicionales
     * @return array Respuesta de Gemini o array con error
     */
    public function chat(array $messages, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'El servicio de Gemini no está configurado correctamente.',
                'message' => null,
            ];
        }

        try {
            $endpoint = "{$this->apiUrl}/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

            // Convertir mensajes al formato de Gemini
            $contents = array_map(function ($msg) {
                return [
                    'role' => $msg['role'] ?? 'user',
                    'parts' => [
                        ['text' => $msg['text'] ?? $msg['content'] ?? '']
                    ]
                ];
            }, $messages);

            $payload = [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => $options['temperature'] ?? 0.7,
                    'maxOutputTokens' => $options['maxOutputTokens'] ?? 1000,
                ]
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    return [
                        'success' => true,
                        'error' => null,
                        'message' => $text,
                        'raw' => $data,
                    ];
                }
            }

            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Error desconocido';
            
            return [
                'success' => false,
                'error' => "Error de Gemini: {$errorMessage}",
                'message' => null,
            ];

        } catch (Exception $e) {
            Log::error('Gemini: Error en chat', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Error al comunicarse con Gemini.',
                'message' => null,
            ];
        }
    }

    /**
     * Analiza texto y devuelve información estructurada
     *
     * @param string $text Texto a analizar
     * @param string $instruction Instrucción específica para el análisis
     * @return array Respuesta procesada
     */
    public function analyzeText(string $text, string $instruction = ''): array
    {
        $prompt = $instruction 
            ? "{$instruction}\n\nTexto:\n{$text}" 
            : "Analiza el siguiente texto:\n\n{$text}";

        return $this->generateContent($prompt);
    }

    /**
     * Obtiene un resumen de un texto largo
     *
     * @param string $text Texto a resumir
     * @param int $maxLength Longitud máxima del resumen
     * @return array Respuesta con el resumen
     */
    public function summarize(string $text, int $maxLength = 200): array
    {
        $prompt = "Resume el siguiente texto en máximo {$maxLength} palabras:\n\n{$text}";
        return $this->generateContent($prompt, ['maxOutputTokens' => $maxLength * 2]);
    }

    /**
     * Traduce texto de un idioma a otro
     *
     * @param string $text Texto a traducir
     * @param string $targetLanguage Idioma destino (ej: 'español', 'inglés')
     * @param string|null $sourceLanguage Idioma origen (opcional)
     * @return array Respuesta con la traducción
     */
    public function translate(string $text, string $targetLanguage, ?string $sourceLanguage = null): array
    {
        $sourceInfo = $sourceLanguage ? " desde {$sourceLanguage}" : "";
        $prompt = "Traduce el siguiente texto{$sourceInfo} a {$targetLanguage}:\n\n{$text}";
        return $this->generateContent($prompt);
    }

    /**
     * Obtiene estadísticas globales del sistema para incluir en el contexto.
     */
    private function getSystemStats(): string
    {
        try {
            $proveedores = Proveedor::count();
            $equipos = Equipo::count();
            $usuarios = Usuario::count();
            $empresas = Empresa::count();
            $fabricantes = Fabricante::count();
            $grupos = Grupo::count();
            $cargos = Cargo::count();
            $sedes = Sede::count();
            $bodegas = Bodega::count();
            $tipoEquipos = TipoEquipo::count();
            $tipoItems = TipoItem::count();
            $usoItems = UsoItem::count();
            $estadoRemisiones = EstadoRemision::count();
            $asignaciones = Asignacion::count();
            $roles = Role::count();

            return "ESTADÍSTICAS ACTUALES DEL SISTEMA (datos en tiempo real):
- Usuarios: {$usuarios}
- Roles: {$roles}
- Grupos: {$grupos}
- Cargos: {$cargos}
- Equipos: {$equipos}
- Asignaciones: {$asignaciones}
- Empresas: {$empresas}
- Sedes: {$sedes}
- Bodegas: {$bodegas}
- Proveedores: {$proveedores}
- Fabricantes: {$fabricantes}
- Tipo Equipos: {$tipoEquipos}
- Tipo Items: {$tipoItems}
- Uso Items: {$usoItems}
- Estado Remisiones: {$estadoRemisiones}
- Estado Remisión: {$estadoRemisiones}

Usa estos datos para responder preguntas sobre cantidades, listas, duplicados, etc.";
        } catch (\Exception $e) {
            Log::warning('Gemini: Error obteniendo stats', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /** Módulos SAMS2 para describir permisos de roles */
    private const MODULOS_NOMBRES = [
        'usuarios' => 'Usuarios',
        'roles' => 'Roles',
        'cargos' => 'Cargos',
        'grupos' => 'Grupos',
        'equipos' => 'Equipos',
        'tipo_equipos' => 'Tipo Equipos',
        'tipo_items' => 'Tipo Items',
        'uso_items' => 'Uso Items',
        'estado_remision' => 'Estado Remisión',
        'asignar' => 'Asignar',
        'inspeccionar' => 'Inspeccionar',
        'empresas' => 'Empresas',
        'proveedores' => 'Proveedores',
        'fabricantes' => 'Fabricantes',
    ];

    /**
     * Query equipos que coincidan con un término en descripción o código.
     */
    private function equiposPorTermino(string $termino): \Illuminate\Database\Eloquent\Collection
    {
        $t = strtoupper($termino);
        $tAcento = $termino === 'arnes' ? 'ARNÉS' : null;

        return Equipo::where(function ($q) use ($t, $tAcento) {
            $q->where('descripcion', 'like', "%{$t}%")
              ->orWhere('codigo', 'like', "%{$t}%");
            if ($tAcento) {
                $q->orWhere('descripcion', 'like', "%{$tAcento}%")
                  ->orWhere('nombre_kit', 'like', "%{$tAcento}%");
            }
            $q->orWhere('nombre_kit', 'like', "%{$t}%");
        })->orderBy('codigo')->get(['id', 'codigo', 'descripcion']);
    }

    /** Plural -> singular para búsqueda de equipos */
    private const TERMINO_EQUIPO_SINGULAR = [
        'arneses' => 'arnes', 'arnes' => 'arnes', 'arnés' => 'arnes',
        'escaleras' => 'escalera', 'escalera' => 'escalera',
        'extintores' => 'extintor', 'extintor' => 'extintor',
        'botiquines' => 'botiquin', 'botiquin' => 'botiquin',
        'camillas' => 'camilla', 'camilla' => 'camilla',
        'monitores' => 'monitor', 'monitor' => 'monitor',
    ];

    /**
     * Extrae un término de búsqueda del mensaje para equipos (ej. "códigos de arneses" -> arnes).
     */
    private function extraerTerminoEquipo(string $text, array $conversationHistory): ?string
    {
        foreach (array_keys(self::TERMINO_EQUIPO_SINGULAR) as $p) {
            if (str_contains($text, $p)) {
                return self::TERMINO_EQUIPO_SINGULAR[$p];
            }
        }
        foreach (array_reverse($conversationHistory) as $msg) {
            $role = $msg['role'] ?? '';
            if ($role === 'assistant' || $role === 'model') {
                $lastText = mb_strtolower($msg['text'] ?? $msg['content'] ?? '', 'UTF-8');
                if (str_contains($lastText, 'equipos relacionados con arneses') || str_contains($lastText, 'arneses')) {
                    return 'arnes';
                }
                foreach (array_keys(self::TERMINO_EQUIPO_SINGULAR) as $p) {
                    if (str_contains($lastText, $p)) {
                        return self::TERMINO_EQUIPO_SINGULAR[$p];
                    }
                }
                break;
            }
        }
        return null;
    }

    /**
     * Describe los permisos de un rol para responder "qué acceso tiene el rol X".
     */
    private function describirPermisosRol(Role $role): string
    {
        $permisos = $role->permisos ?? [];
        if (empty($permisos)) {
            return "El rol \"{$role->nombre}\" tiene acceso total (Mega Admin).";
        }
        $lineas = ["El rol \"{$role->nombre}\" tiene acceso a:"];
        foreach (self::MODULOS_NOMBRES as $mod => $nombre) {
            $acc = $permisos[$mod]['acceso'] ?? false;
            if ($acc) {
                $lineas[] = "• {$nombre}";
            }
        }
        if (count($lineas) === 1) {
            return "El rol \"{$role->nombre}\" no tiene acceso a ningún módulo configurado.";
        }
        return implode("\n", $lineas);
    }

    /**
     * Respuestas rápidas para interacción básica en modo SAMS
     * (saludos, ayuda y mensajes introductorios).
     */
    private function answerBasicSamsQuestion(string $normalizedMessage): ?string
    {
        $text = trim($normalizedMessage);

        if ($text === '') {
            return null;
        }

        if (preg_match('/^(hola|hi|hello|buenas|buenos dias|buenas tardes|buenas noches|hey|holi|que tal|qué tal)[\s!¡¿?.,]*$/u', $text)) {
            return "¡Hola! Soy el asistente de SAMS2.\nPuedo ayudarte con usuarios, equipos, roles, inventario y reportes.\n\nEjemplos:\n• ¿Cuántos equipos hay?\n• ¿Hay usuarios duplicados?\n• Buscar usuario con cédula 123456789";
        }

        if (str_contains($text, 'ayuda')
            || str_contains($text, 'que puedes hacer')
            || str_contains($text, 'qué puedes hacer')
            || str_contains($text, 'comandos')
            || str_contains($text, 'como funciona')
            || str_contains($text, 'cómo funciona')
            || str_contains($text, 'como uso')
            || str_contains($text, 'cómo uso')) {
            return $this->buildSamsLocalFallbackMessage();
        }

        return null;
    }

    /**
     * Mensaje de respaldo para mantener útil el chat SAMS
     * cuando Gemini externo no responde.
     */
    private function buildSamsLocalFallbackMessage(?string $serviceError = null): string
    {
        $intro = $serviceError
            ? "No pude completar esta consulta con Gemini en este momento, pero sigo disponible con datos de SAMS2."
            : "Estoy disponible en modo SAMS con datos reales del sistema.";

        return "{$intro}\n\nPrueba con:\n• ¿Cuántos usuarios/equipos/proveedores hay?\n• ¿Qué acceso tiene el rol Administrador?\n• Buscar usuario con cédula 123456789\n• Revisar usuarios con nombres duplicados o parecidos";
    }

    /**
     * Detecta si un texto corresponde a error de API key inválida.
     */
    private function isInvalidApiKeyErrorMessage(?string $message): bool
    {
        $normalized = mb_strtolower(trim((string) $message), 'UTF-8');
        if ($normalized === '') {
            return false;
        }

        return str_contains($normalized, 'api key not valid')
            || str_contains($normalized, 'invalid api key')
            || str_contains($normalized, 'pass a valid api key')
            || str_contains($normalized, 'api_key_invalid')
            || str_contains($normalized, 'gemini_api_key')
            || (str_contains($normalized, 'api key') && (
                str_contains($normalized, 'not valid')
                || str_contains($normalized, 'invalid')
                || str_contains($normalized, 'no es válida')
                || str_contains($normalized, 'no es valida')
            ));
    }

    /**
     * Construye una respuesta segura en modo SAMS cuando Full no está disponible.
     */
    private function buildSamsModeFallbackResponse(
        string $originalMode,
        string $fallbackReason,
        string $userMessage,
        array $conversationHistory = []
    ): array {
        $directAnswer = null;

        try {
            $directAnswer = $this->answerSamsCountQuestion($userMessage, $conversationHistory);
        } catch (\Exception $e) {
            Log::warning('Gemini: Error construyendo fallback SAMS', ['error' => $e->getMessage()]);
        }

        $baseMessage = $directAnswer ?? $this->buildSamsLocalFallbackMessage($fallbackReason);

        if ($originalMode === 'full') {
            $baseMessage = "⚠️ Modo Full no disponible por configuración/servicio de Gemini. Se activó Modo SAMS automáticamente.\n\n{$baseMessage}";
        }

        return [
            'success' => true,
            'error' => null,
            'message' => $baseMessage,
            'effective_mode' => 'sams',
            'fallback_reason' => $fallbackReason,
        ];
    }

    /**
     * Intenta responder directamente preguntas típicas en modo SAMS (conteos, listas, usuarios, roles, códigos),
     * consultando la base de datos sin llamar a la API de Gemini.
     *
     * @param string $userMessage Mensaje actual del usuario
     * @param array $conversationHistory Historial reciente [['role'=>'user'|'assistant','text'=>'...'], ...]
     */
    private function answerSamsCountQuestion(string $userMessage, array $conversationHistory = []): ?string
    {
        $text = mb_strtolower($userMessage, 'UTF-8');

        $basic = $this->answerBasicSamsQuestion($text);
        if ($basic !== null) {
            return $basic;
        }

        // —— Búsqueda combinada: cédula Y nombre (ej: "con cedula 1079176426 y alguna que se llam laura") ——
        if (preg_match('/\b(?:con\s+)?(?:c[ée]dula|cedula|id)\s+([0-9]+)\s+(?:y|e)\s+(?:alg[uo]na?\s+)?(?:que\s+se\s+llam[ae]|llamad[oa]|con\s+nombre)\s+([a-záéíóúñ\s]+)/ui', $text, $m)
            || preg_match('/\b(?:c[ée]dula|cedula|id)\s+([0-9]+).*?(?:y|e).*?(?:llamad[oa]|llam[ae]|nombre)\s+([a-záéíóúñ\s]+)/ui', $text, $m)) {
            $cedula = trim($m[1]);
            $nombre = trim($m[2]);
            
            // Buscar por cédula
            $usuarioCedula = Usuario::where('cedula', $cedula)->first();
            if ($usuarioCedula) {
                $nom = trim(($usuarioCedula->nombre ?? '') . ' ' . ($usuarioCedula->apellidos ?? ''));
                $estado = $usuarioCedula->activo ? 'activo' : 'inactivo';
                $rol = $usuarioCedula->role ? $usuarioCedula->role->nombre : 'Sin rol';
                return "Sí, existe un usuario con cédula {$cedula}:\n• Nombre: {$nom}\n• Username: {$usuarioCedula->username}\n• Estado: {$estado}\n• Rol: {$rol}";
            }
            
            // Buscar por nombre
            if (strlen($nombre) >= 2) {
                $usuarios = Usuario::where('nombre', 'like', "%{$nombre}%")
                    ->orWhere('apellidos', 'like', "%{$nombre}%")
                    ->orWhereRaw("CONCAT(nombre, ' ', apellidos) LIKE ?", ["%{$nombre}%"])
                    ->limit(10)
                    ->get(['id', 'cedula', 'nombre', 'apellidos', 'username', 'activo', 'role_id'])
                    ->load('role:id,nombre');
                if (!$usuarios->isEmpty()) {
                    $lineas = [];
                    foreach ($usuarios as $u) {
                        $nom = trim(($u->nombre ?? '') . ' ' . ($u->apellidos ?? ''));
                        $estado = $u->activo ? 'activo' : 'inactivo';
                        $rol = $u->role ? $u->role->nombre : 'Sin rol';
                        $lineas[] = "• {$nom} (cédula: {$u->cedula}, usuario: {$u->username}, rol: {$rol}, {$estado})";
                    }
                    return "Usuarios que se llaman {$nombre}:\n" . implode("\n", $lineas);
                }
            }
            
            return "No se encontró ningún usuario con cédula {$cedula} ni con el nombre {$nombre}.";
        }

        // —— Códigos o lista de equipos (por término o contexto) ——
        $pideCodigos = str_contains($text, 'código') || str_contains($text, 'codigo') || str_contains($text, 'códigos') || str_contains($text, 'codigos')
            || str_contains($text, 'lista de') || str_contains($text, 'cuáles son') || str_contains($text, 'cuales son');
        if ($pideCodigos) {
            $termino = $this->extraerTerminoEquipo($text, $conversationHistory);
            if ($termino) {
                $equipos = $this->equiposPorTermino($termino);
                if ($equipos->isEmpty()) {
                    return "No hay equipos que coincidan con \"{$termino}\" en SAMS2.";
                }
                $codigos = $equipos->pluck('codigo')->implode(', ');
                $n = $equipos->count();
                return "Los códigos de esos equipos son: {$codigos} ({$n} en total).";
            }
        }

        // —— Rol: qué acceso tiene, permisos del rol X ——
        $nombreRol = null;
        if (preg_match('/\b(?:qué|que)\s+acceso\s+(?:tiene\s+)?(?:el\s+)?rol\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombreRol = trim($m[1]);
        } elseif (preg_match('/\bpermisos\s+(?:del\s+)?rol\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombreRol = trim($m[1]);
        } elseif (preg_match('/\b(?:qué|que)\s+puede\s+hacer\s+(?:el\s+)?rol\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombreRol = trim($m[1]);
        } elseif (preg_match('/\brol\s+([a-záéíóúñ]+)(?:\s+qué|\s+acceso|\s+permisos|\?|\.|$)/ui', $text, $m)) {
            $nombreRol = trim($m[1]);
        }
        if ($nombreRol !== null && $nombreRol !== '') {
            $role = Role::where('nombre', 'like', '%' . $nombreRol . '%')->first();
            if ($role) {
                return $this->describirPermisosRol($role);
            }
            return "No hay ningún rol con ese nombre en SAMS2.";
        }

        // —— Usuarios con mismo nombre o nombres duplicados/parecidos ——
        if (preg_match('/\b(?:cuántos|cuantos)\s+usuarios\s+(?:tienen\s+)?(?:el\s+)?(?:mismo\s+)?nombre\b/ui', $text)
            || str_contains($text, 'usuarios duplicados') || str_contains($text, 'usuarios con el mismo nombre')
            || str_contains($text, 'nombres duplicados') || str_contains($text, 'nombres repetidos')
            || str_contains($text, 'nombres parecidos') || str_contains($text, 'nombres similares')
            || str_contains($text, 'nombres parseados') || str_contains($text, 'cuántos tienen nombre') || str_contains($text, 'cuantos tienen nombre')
            || str_contains($text, 'revisar usuarios') || str_contains($text, 'revisar cuántos usuarios')) {
            // Duplicados exactos (nombre + apellidos iguales)
            $usuariosExactos = Usuario::select('nombre', 'apellidos', 'username')
                ->get()
                ->groupBy(fn ($u) => trim(($u->nombre ?? '') . ' ' . ($u->apellidos ?? '')))
                ->filter(fn ($g, $key) => trim((string) $key) !== '' && $g->count() > 1);
            // Usernames duplicados
            $usuariosUsername = Usuario::select('username', 'nombre', 'apellidos')
                ->get()
                ->groupBy('username')
                ->filter(fn ($g) => $g->count() > 1);
            // Nombres parecidos (normalizados: lowercase, sin acentos básicos, trim)
            $todos = Usuario::select('nombre', 'apellidos', 'username')->get();
            $normalizado = fn ($s) => preg_replace('/\s+/', ' ', strtolower(trim($s ?? '')));
            $gruposParecidos = $todos->groupBy(fn ($u) => $normalizado(($u->nombre ?? '') . ' ' . ($u->apellidos ?? '')))
                ->filter(fn ($g, $key) => $key !== '' && $g->count() > 1);

            $partes = [];
            if (!$usuariosExactos->isEmpty()) {
                $lineas = [];
                foreach ($usuariosExactos as $nom => $grp) {
                    $n = $grp->count();
                    $usernames = $grp->pluck('username')->implode(', ');
                    $lineas[] = "• \"{$nom}\" ({$n} usuarios): {$usernames}";
                }
                $total = $usuariosExactos->sum(fn ($g) => $g->count());
                $partes[] = "NOMBRES DUPLICADOS (mismo nombre completo):\n" . implode("\n", $lineas) . "\nTotal: {$total} usuarios.";
            }
            if (!$usuariosUsername->isEmpty()) {
                $lineas = [];
                foreach ($usuariosUsername as $un => $grp) {
                    $n = $grp->count();
                    $nombres = $grp->map(fn ($u) => trim(($u->nombre ?? '') . ' ' . ($u->apellidos ?? '')))->implode(', ');
                    $lineas[] = "• Username \"{$un}\": {$n} usuarios ({$nombres})";
                }
                $partes[] = "USERNAMES DUPLICADOS:\n" . implode("\n", $lineas);
            }
            $keysParecidos = $gruposParecidos->keys()->diff($usuariosExactos->keys()->map($normalizado));
            if (!$gruposParecidos->isEmpty() && $keysParecidos->isNotEmpty()) {
                $lineas = [];
                foreach ($keysParecidos as $keyNorm) {
                    $grp = $gruposParecidos->get($keyNorm);
                    if ($grp && $grp->count() > 1) {
                        $n = $grp->count();
                        $primer = trim(($grp->first()->nombre ?? '') . ' ' . ($grp->first()->apellidos ?? ''));
                        $lineas[] = "• \"{$primer}\" (variantes mayúsculas/minúsculas): {$n} usuarios";
                    }
                }
                if (!empty($lineas)) {
                    $partes[] = "NOMBRES PARECIDOS/SIMILARES (misma escritura, diferente capitalización):\n" . implode("\n", $lineas);
                }
            }
            if (empty($partes)) {
                return "No hay usuarios con nombres duplicados, parecidos ni usernames repetidos en SAMS2. Cada usuario tiene nombre y username únicos.";
            }
            return "Revisión de usuarios en SAMS2:\n\n" . implode("\n\n", $partes);
        }

        // —— Usernames duplicados específicamente ——
        if (str_contains($text, 'usernames duplicados') || str_contains($text, 'username duplicado')
            || str_contains($text, 'usuario mismo username') || str_contains($text, 'username repetido')) {
            $usuariosUsername = Usuario::select('username', 'nombre', 'apellidos')
                ->get()
                ->groupBy('username')
                ->filter(fn ($g) => $g->count() > 1);
            if ($usuariosUsername->isEmpty()) {
                return "No hay usernames duplicados en SAMS2. Cada usuario tiene un username único.";
            }
            $lineas = [];
            foreach ($usuariosUsername as $un => $grp) {
                $n = $grp->count();
                $nombres = $grp->map(fn ($u) => trim($u->nombre . ' ' . $u->apellidos))->implode(', ');
                $lineas[] = "• Username \"{$un}\": {$n} usuarios ({$nombres})";
            }
            return "Usernames duplicados en SAMS2:\n" . implode("\n", $lineas);
        }

        // —— Usuario por cédula: "usuario con cédula X", "cedula X", "ID X" ——
        if (preg_match('/\b(?:usuario|usario|alguien|persona)\s+(?:con\s+)?(?:c[ée]dula|cedula|id|identificaci[oó]n)\s+([0-9]+)/ui', $text, $m)
            || preg_match('/\b(?:c[ée]dula|cedula|id)\s+([0-9]+)/ui', $text, $m)
            || preg_match('/\b([0-9]{7,15})\b/ui', $text, $m)) {
            $cedula = trim($m[1]);
            if (strlen($cedula) >= 7) {
                $usuario = Usuario::where('cedula', $cedula)->first();
                if ($usuario) {
                    $nom = trim(($usuario->nombre ?? '') . ' ' . ($usuario->apellidos ?? ''));
                    $estado = $usuario->activo ? 'activo' : 'inactivo';
                    $rol = $usuario->role ? $usuario->role->nombre : 'Sin rol';
                    $username = $usuario->username ?? 'N/A';
                    return "Sí, existe un usuario con cédula {$cedula}:\n• Nombre: {$nom}\n• Username: {$username}\n• Estado: {$estado}\n• Rol: {$rol}";
                } else {
                    return "No hay ningún usuario con la cédula {$cedula} en SAMS2.";
                }
            }
        }

        // —— Usuario por nombre: "hay usuario llamado daniel", "ahi algun usario llamdo X", "alguno llamado X" ——
        $patronesUsuario = [
            '/\b(?:hay|ahi|existe|tienes?)\s+(?:alg[uú]n?\s+)?(?:usuario|usario)\s+(?:llamad[oa]?|llamdo)\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui',
            '/\b(?:usuario|usario|alguien|persona)\s+(?:llamad[oa]?|llamdo|con\s+nombre|que\s+se\s+llame)\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui',
            '/\b(?:hay|ahi)\s+alguien\s+(?:llamad[oa]?|llamdo)\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui',
            '/\bbuscar\s+usuario\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui',
            '/\balgun[oa]?\s+(?:llamad[oa]?|llamdo)\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui',
            '/\b(?:hay|ahi)\s+(?:alg[uú]n?\s+)?(?:usuario|usario|alguien)\s+(?:llamad[oa]?|llamdo)\s+([a-záéíóúñ\s]+?)(?:\?|\.|$)/ui',
        ];
        foreach ($patronesUsuario as $pat) {
            if (preg_match($pat, $text, $m)) {
                $nombre = trim(preg_replace('/\?|\.$/', '', $m[1]));
                if (strlen($nombre) >= 2) {
                    $usuarios = Usuario::where('nombre', 'like', "%{$nombre}%")
                        ->orWhere('apellidos', 'like', "%{$nombre}%")
                        ->orWhereRaw("CONCAT(nombre, ' ', apellidos) LIKE ?", ["%{$nombre}%"])
                        ->limit(15)
                        ->get(['id', 'cedula', 'nombre', 'apellidos', 'username', 'activo', 'role_id'])
                        ->load('role:id,nombre');
                    if ($usuarios->isEmpty()) {
                        return "No hay ningún usuario con ese nombre o apellido en SAMS2.";
                    }
                    $lineas = [];
                    foreach ($usuarios as $u) {
                        $nom = trim(($u->nombre ?? '') . ' ' . ($u->apellidos ?? ''));
                        $estado = $u->activo ? 'activo' : 'inactivo';
                        $rol = $u->role ? $u->role->nombre : 'Sin rol';
                        $cedula = $u->cedula ?? 'N/A';
                        $lineas[] = "• {$nom} (cédula: {$cedula}, usuario: {$u->username}, rol: {$rol}, {$estado})";
                    }
                    return "En SAMS2 " . (count($lineas) === 1 ? 'hay 1 usuario' : 'hay ' . count($lineas) . ' usuarios') . " que coinciden:\n" . implode("\n", $lineas);
                }
                break;
            }
        }

        // —— Bodega por nombre: "hay bodega X", "bodega llamada X", "bodega con nombre X" ——
        if (preg_match('/\bbodega[s]?\s+(?:llamad[oa]?|con\s+nombre|que\s+se\s+llame)\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi|existe)\s+(?:alg[uú]n[ao]?\s+)?bodega\s+(?:llamad[oa]?|con\s+nombre)?\s*([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi)\s+bodega[s]?\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombre = trim(preg_replace('/\?|\.$/', '', $m[1]));
            if (strlen($nombre) >= 2) {
                $bodegas = Bodega::where('nombre', 'like', "%{$nombre}%")
                    ->with('sede:id,nombre,empresa_id')
                    ->limit(15)
                    ->get(['id', 'nombre', 'sede_id']);
                if ($bodegas->isEmpty()) {
                    return "No hay ninguna bodega con ese nombre en SAMS2.";
                }
                $lineas = [];
                foreach ($bodegas as $b) {
                    $sede = $b->sede ? $b->sede->nombre : 'N/A';
                    $lineas[] = "• {$b->nombre} (sede: {$sede})";
                }
                return "En SAMS2 " . (count($lineas) === 1 ? 'hay 1 bodega' : 'hay ' . count($lineas) . ' bodegas') . " que coinciden:\n" . implode("\n", $lineas);
            }
        }

        // —— Fabricante por nombre: "hay fabricante X", "fabricante llamado X" ——
        if (preg_match('/\bfabricante[s]?\s+(?:llamad[oa]?|con\s+nombre)\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi|existe)\s+(?:alg[uú]n\s+)?fabricante\s+(?:llamad[oa]?|con\s+nombre)?\s*([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi)\s+fabricante[s]?\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombre = trim(preg_replace('/\?|\.$/', '', $m[1]));
            if (strlen($nombre) >= 2) {
                $fabricantes = Fabricante::where('nombre', 'like', "%{$nombre}%")->limit(15)->get(['nombre']);
                if ($fabricantes->isEmpty()) {
                    return "No hay ningún fabricante con ese nombre en SAMS2.";
                }
                $lineas = $fabricantes->map(fn ($f) => "• {$f->nombre}")->all();
                return "En SAMS2 " . (count($lineas) === 1 ? 'hay 1 fabricante' : 'hay ' . count($lineas) . ' fabricantes') . " que coinciden:\n" . implode("\n", $lineas);
            }
        }

        // —— Proveedor por nombre: "hay proveedor X", "proveedor llamado X" ——
        if (preg_match('/\bproveedor(?:es)?\s+(?:llamad[oa]?|con\s+nombre)\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi|existe)\s+(?:alg[uú]n\s+)?proveedor\s+(?:llamad[oa]?|con\s+nombre)?\s*([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi)\s+proveedor(?:es)?\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombre = trim(preg_replace('/\?|\.$/', '', $m[1]));
            if (strlen($nombre) >= 2) {
                $proveedores = Proveedor::where('nombre', 'like', "%{$nombre}%")->limit(15)->get(['nombre']);
                if ($proveedores->isEmpty()) {
                    return "No hay ningún proveedor con ese nombre en SAMS2.";
                }
                $lineas = $proveedores->map(fn ($p) => "• {$p->nombre}")->all();
                return "En SAMS2 " . (count($lineas) === 1 ? 'hay 1 proveedor' : 'hay ' . count($lineas) . ' proveedores') . " que coinciden:\n" . implode("\n", $lineas);
            }
        }

        // —— Empresa por nombre: "hay empresa X", "empresa llamada X" ——
        if (preg_match('/\bempresa[s]?\s+(?:llamad[oa]?|con\s+nombre)\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi|existe)\s+(?:alg[uú]n[ao]?\s+)?empresa\s+(?:llamad[oa]?|con\s+nombre)?\s*([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi)\s+empresa[s]?\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombre = trim(preg_replace('/\?|\.$/', '', $m[1]));
            if (strlen($nombre) >= 2) {
                $empresas = Empresa::where('nombre', 'like', "%{$nombre}%")->limit(15)->get(['nombre', 'pais']);
                if ($empresas->isEmpty()) {
                    return "No hay ninguna empresa con ese nombre en SAMS2.";
                }
                $lineas = [];
                foreach ($empresas as $e) {
                    $pais = $e->pais ?? 'N/A';
                    $lineas[] = "• {$e->nombre} ({$pais})";
                }
                return "En SAMS2 " . (count($lineas) === 1 ? 'hay 1 empresa' : 'hay ' . count($lineas) . ' empresas') . " que coinciden:\n" . implode("\n", $lineas);
            }
        }

        // —— Sede por nombre: "hay sede X", "sede llamada X" ——
        if (preg_match('/\bsede[s]?\s+(?:llamad[oa]?|con\s+nombre)\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi|existe)\s+(?:alg[uú]n[ao]?\s+)?sede\s+(?:llamad[oa]?|con\s+nombre)?\s*([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi)\s+sede[s]?\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombre = trim(preg_replace('/\?|\.$/', '', $m[1]));
            if (strlen($nombre) >= 2) {
                $sedes = Sede::where('nombre', 'like', "%{$nombre}%")
                    ->with('empresa:id,nombre')
                    ->limit(15)
                    ->get(['id', 'nombre', 'empresa_id']);
                if ($sedes->isEmpty()) {
                    return "No hay ninguna sede con ese nombre en SAMS2.";
                }
                $lineas = [];
                foreach ($sedes as $s) {
                    $emp = $s->empresa ? $s->empresa->nombre : 'N/A';
                    $lineas[] = "• {$s->nombre} (empresa: {$emp})";
                }
                return "En SAMS2 " . (count($lineas) === 1 ? 'hay 1 sede' : 'hay ' . count($lineas) . ' sedes') . " que coinciden:\n" . implode("\n", $lineas);
            }
        }

        // —— Grupo por nombre: "hay grupo X", "grupo llamado X" ——
        if (preg_match('/\bgrupo[s]?\s+(?:llamad[oa]?|con\s+nombre)\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi|existe)\s+(?:alg[uú]n[ao]?\s+)?grupo\s+(?:llamad[oa]?|con\s+nombre)?\s*([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi)\s+grupo[s]?\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombre = trim(preg_replace('/\?|\.$/', '', $m[1]));
            if (strlen($nombre) >= 2) {
                $grupos = Grupo::where('nombre', 'like', "%{$nombre}%")->limit(15)->get(['nombre']);
                if ($grupos->isEmpty()) {
                    return "No hay ningún grupo con ese nombre en SAMS2.";
                }
                $lineas = $grupos->map(fn ($g) => "• {$g->nombre}")->all();
                return "En SAMS2 " . (count($lineas) === 1 ? 'hay 1 grupo' : 'hay ' . count($lineas) . ' grupos') . " que coinciden:\n" . implode("\n", $lineas);
            }
        }

        // —— Cargo por nombre: "hay cargo X", "cargo llamado X" ——
        if (preg_match('/\bcargo[s]?\s+(?:llamad[oa]?|con\s+nombre)\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi|existe)\s+(?:alg[uú]n[ao]?\s+)?cargo\s+(?:llamad[oa]?|con\s+nombre)?\s*([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)
            || preg_match('/\b(?:hay|ahi)\s+cargo[s]?\s+([a-záéíóúñ\s0-9]+?)(?:\?|\.|$)/ui', $text, $m)) {
            $nombre = trim(preg_replace('/\?|\.$/', '', $m[1]));
            if (strlen($nombre) >= 2) {
                $cargos = Cargo::where('nombre', 'like', "%{$nombre}%")->limit(15)->get(['nombre']);
                if ($cargos->isEmpty()) {
                    return "No hay ningún cargo con ese nombre en SAMS2.";
                }
                $lineas = $cargos->map(fn ($c) => "• {$c->nombre}")->all();
                return "En SAMS2 " . (count($lineas) === 1 ? 'hay 1 cargo' : 'hay ' . count($lineas) . ' cargos') . " que coinciden:\n" . implode("\n", $lineas);
            }
        }

        // —— Lista de usuarios (quién está en el sistema) ——
        if (str_contains($text, 'lista de usuarios') || str_contains($text, 'quién está') || str_contains($text, 'quien esta')
            || str_contains($text, 'usuarios registrados') || str_contains($text, 'todos los usuarios')) {
            $usuarios = Usuario::orderBy('nombre')->limit(30)->get(['nombre', 'apellidos', 'username']);
            $total = Usuario::count();
            $lineas = [];
            foreach ($usuarios as $u) {
                $lineas[] = "• " . trim($u->nombre . ' ' . $u->apellidos) . " ({$u->username})";
            }
            $res = "Hay {$total} usuarios en SAMS2. " . ($total <= 30 ? '' : "Mostrando los primeros 30:\n") . implode("\n", $lineas);
            return $total > 30 ? $res : "Usuarios en SAMS2 ({$total}):\n" . implode("\n", $lineas);
        }

        // —— Lista de roles / qué roles hay ——
        if (str_contains($text, 'lista de roles') || str_contains($text, 'qué roles hay') || str_contains($text, 'que roles hay')
            || str_contains($text, 'cuántos roles') || str_contains($text, 'cuantos roles') || str_contains($text, 'roles existen')) {
            $roles = Role::orderBy('nombre')->get(['nombre']);
            $total = $roles->count();
            if ($total === 0) {
                return "No hay roles configurados en SAMS2.";
            }
            $nombres = $roles->pluck('nombre')->implode(', ');
            return "En SAMS2 hay {$total} roles: {$nombres}.";
        }

        // —— Usuarios con rol X ——
        if (preg_match('/\b(?:cuántos|cuantos)\s+usuarios\s+(?:tienen\s+)?(?:el\s+)?rol\s+(.+?)(?:\?|$)/ui', $text, $m)
            || preg_match('/\busuarios\s+con\s+(?:el\s+)?rol\s+(.+?)(?:\?|$)/ui', $text, $m)) {
            $nombreRol = trim(preg_replace('/\?|\.$/', '', $m[1]));
            $role = Role::where('nombre', 'like', '%' . $nombreRol . '%')->first();
            if (!$role) {
                return "No existe un rol con ese nombre en SAMS2.";
            }
            $n = Usuario::where('role_id', $role->id)->count();
            return "Hay {$n} usuarios con el rol \"{$role->nombre}\" en SAMS2.";
        }

        // —— Conteos: proveedores / vendedores ——
        if (str_contains($text, 'cuántos proveedores') || str_contains($text, 'cuantos proveedores')
            || str_contains($text, 'cuántos vendedores') || str_contains($text, 'cuantos vendedores')) {
            $total = Proveedor::count();
            return "Actualmente hay {$total} proveedores registrados en SAMS2.";
        }

        // —— Conteos: equipos ——
        if (str_contains($text, 'cuántos equipos') || str_contains($text, 'cuantos equipos')) {
            $total = Equipo::count();
            return "Actualmente hay {$total} equipos registrados en SAMS2.";
        }

        // —— Conteos: usuarios ——
        if (str_contains($text, 'cuántos usuarios') || str_contains($text, 'cuantos usuarios')) {
            $total = Usuario::count();
            return "Actualmente hay {$total} usuarios registrados en SAMS2.";
        }

        // —— Conteos: empresas ——
        if (str_contains($text, 'cuántas empresas') || str_contains($text, 'cuantas empresas')) {
            $total = Empresa::count();
            return "Actualmente hay {$total} empresas registradas en SAMS2.";
        }

        // —— Conteos: fabricantes ——
        if (str_contains($text, 'cuántos fabricantes') || str_contains($text, 'cuantos fabricantes')) {
            $total = Fabricante::count();
            return "Actualmente hay {$total} fabricantes registrados en SAMS2.";
        }

        // —— Conteos: grupos, cargos, sedes, bodegas, asignaciones ——
        if (str_contains($text, 'cuántos grupos') || str_contains($text, 'cuantos grupos')) {
            return "Actualmente hay " . Grupo::count() . " grupos en SAMS2.";
        }
        if (str_contains($text, 'cuántos cargos') || str_contains($text, 'cuantos cargos')) {
            return "Actualmente hay " . Cargo::count() . " cargos en SAMS2.";
        }
        if (str_contains($text, 'cuántas sedes') || str_contains($text, 'cuantas sedes') || str_contains($text, 'cuántos sedes')) {
            return "Actualmente hay " . Sede::count() . " sedes en SAMS2.";
        }
        if (str_contains($text, 'cuántas bodegas') || str_contains($text, 'cuantas bodegas') || str_contains($text, 'cuántos bodegas')) {
            return "Actualmente hay " . Bodega::count() . " bodegas en SAMS2.";
        }
        if (str_contains($text, 'cuántas asignaciones') || str_contains($text, 'cuantas asignaciones')) {
            return "Actualmente hay " . Asignacion::count() . " asignaciones en SAMS2.";
        }
        if (str_contains($text, 'cuántos tipo') || str_contains($text, 'cuantos tipo')) {
            if (str_contains($text, 'equipo')) {
                return "Actualmente hay " . TipoEquipo::count() . " tipos de equipo en SAMS2.";
            }
            if (str_contains($text, 'item')) {
                return "Actualmente hay " . TipoItem::count() . " tipos de ítem en SAMS2.";
            }
        }
        if (str_contains($text, 'uso items') || str_contains($text, 'usos de items')) {
            return "Actualmente hay " . UsoItem::count() . " usos de ítem en SAMS2.";
        }
        if (str_contains($text, 'estado items') || str_contains($text, 'estados de items')) {
            return "Actualmente hay " . EstadoRemision::count() . " estados de remisión en SAMS2.";
        }
        if (str_contains($text, 'estado remisión') || str_contains($text, 'estados de remisión')) {
            return "Actualmente hay " . EstadoRemision::count() . " estados de remisión en SAMS2.";
        }

        // —— Conteos por tipo de equipo (arneses, escaleras, etc.) ——
        $terminosConteo = ['arnes' => 'arneses', 'escalera' => 'escaleras', 'extintor' => 'extintores', 'botiquin' => 'botiquines', 'camilla' => 'camillas'];
        foreach ($terminosConteo as $t => $plural) {
            if (str_contains($text, $t) || str_contains($text, $plural)) {
                $equipos = $this->equiposPorTermino($t);
                $total = $equipos->count();
                if ($total > 0) {
                    return "Actualmente hay {$total} equipos relacionados con {$plural} registrados en SAMS2.";
                }
                return "Actualmente no hay equipos relacionados con {$plural} registrados en SAMS2.";
            }
        }

        return null;
    }

    /**
     * Chat con contexto del sistema SAMS2
     *
     * @param string $userMessage Mensaje del usuario
     * @param array $conversationHistory Historial de conversación (opcional)
     * @param string $mode 'full' = conocimiento general, 'sams' = solo SAMS2 con datos
     * @return array Respuesta de Gemini
     */
    public function chatWithContext(string $userMessage, array $conversationHistory = [], string $mode = 'sams'): array
    {
        // Primero verificar si es un comando de acción
        try {
            $actionResult = $this->processActionCommand($userMessage);
            if ($actionResult !== null) {
                return $actionResult;
            }
        } catch (\Exception $e) {
            Log::warning('Gemini: Error procesando comando de acción', ['error' => $e->getMessage()]);
        }

        // En modo SAMS, primero intentamos contestar directamente desde la BD
        if ($mode === 'sams') {
            try {
                $direct = $this->answerSamsCountQuestion($userMessage, $conversationHistory);
                if ($direct !== null) {
                    return [
                        'success' => true,
                        'error'   => null,
                        'message' => $direct,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Gemini: Error respondiendo pregunta directa SAMS', ['error' => $e->getMessage()]);
            }
        }

        if (!$this->isConfigured()) {
            return $this->buildSamsModeFallbackResponse(
                $mode,
                'gemini_not_configured',
                $userMessage,
                $conversationHistory
            );
        }

        if ($mode === 'full') {
            $systemContext = "Eres un asistente inteligente con conocimiento general. Puedes responder preguntas de cualquier tema.
INSTRUCCIONES:
- Responde SIEMPRE en español
- Sé BREVE y CONCISO (máximo 2-4 párrafos cortos)
- Responde de forma directa y resumida
";
        } else {
            // Modo SAMS: solo sistema + datos reales
            $stats = $this->getSystemStats();
            $modulos = "
MÓDULOS Y FUNCIONALIDADES SAMS2 (conocimiento detallado):

• Usuarios: gestión de usuarios (crear, editar, desactivar, exportar Excel/PDF). Campos: tipo documento, cédula, nombre, apellidos, fecha nacimiento, dirección, teléfono, correo, departamento, municipio, empresa, sede, grupo, cargo, rol, username, contraseña, imagen, firma. Puede haber usuarios con el mismo nombre completo (duplicados) o usernames parecidos.

• Roles: permisos por módulo (acceso, agregar, editar, eliminar). Mega administrador tiene acceso total. Módulos: usuarios, roles, cargos, grupos, equipos, tipo_equipos, tipo_items, uso_items, estado_remision, asignar, inspeccionar, empresas, proveedores, fabricantes.

• Grupos: agrupación de usuarios (ej: área operativa, mantenimiento). CRUD completo y exportar.

• Cargos: puesto laboral (ej: técnico, supervisor). CRUD completo y exportar.

• Empresas: organizaciones que usan el sistema. Cada empresa tiene nombre, país, sedes y bodegas asociadas. CRUD empresas, sedes, bodegas.

• Sedes: ubicaciones físicas de cada empresa. Una sede puede tener varias bodegas.

• Bodegas: almacenes/almacenes por sede. Los equipos están en bodegas o asignados a usuarios.

• Equipos: ítems físicos (arneses, extintores, escaleras, botiquines, camillas, monitores, etc.) con código único, descripción, tipo equipo, tipo ítem, estado, proveedor, fabricante, sede, bodega. Pueden estar en bodega (almacén) o asignados a usuarios. Hoja de vida PDF por equipo.

• Almacén: módulo para equipos en bodega. Etiquetado, subir imágenes y archivos por equipo, descargar documentación.

• Asignar: vincular equipos a usuarios (un usuario puede tener varios equipos, un equipo solo un usuario). Crear, editar, reasignar asignaciones.

• Inspeccionar: revisiones periódicas de equipos (módulo en desarrollo).

• Tipo Equipos: clasificación de equipos (ej: arnés, extintor). Cada tipo puede tener un formato PDF para hoja de vida. Aplicar/reemplazar formato, rellenar campos.

• Tipo Items: categoría del ítem. Catálogo maestro.

• Uso Items: uso asignado (ej: industrial, residencial). Catálogo maestro.

• Estado Items: estado del equipo (ej: operativo, en mantenimiento, en remisión). Catálogo maestro.

• Estado Remisión: estados en proceso de remisión. Catálogo maestro.

• Proveedores: empresas proveedoras de equipos. CRUD y exportar.

• Fabricantes: marcas/fabricantes de equipos. CRUD y exportar.

• Formatos: plantillas PDF por tipo de equipo para hojas de vida. Se suben en Tipo Equipos. Permite rellenar campos no automáticos.

• Personalización: temas de color (paletas por 2, 3, 4, 5, 6 colores, opción personalizado), logos principal y secundario del sistema.

REVISIÓN DE USUARIOS: Puedes preguntar cuántos usuarios tienen el mismo nombre, nombres duplicados, nombres parecidos, usernames similares, buscar usuarios por nombre, lista de usuarios, usuarios con rol X.
";
            $systemContext = "Eres un asistente experto en SAMS2, sistema de inventario de equipos de seguridad, altura y más (arneses, extintores, escaleras, botiquines, camillas, etc.). No es para equipamiento hospitalario ni activos médicos.

{$stats}
{$modulos}

ACCIONES QUE PUEDES REALIZAR:
- Crear usuario: Di \"crear usuario con username X y contraseña Y\" o \"agregar usuario X con password Y\"
- Eliminar usuario: Di \"eliminar usuario X\" o \"borrar usuario con username X\" o \"eliminar usuario llamado [nombre]\"
- Consultar información: Puedes preguntar sobre usuarios, equipos, inventario, estadísticas, etc.

INSTRUCCIONES:
- Responde SIEMPRE en español
- Sé BREVE y CONCISO (respuestas cortas, directas, máx 3-4 frases)
- Si preguntan cantidades, usuarios duplicados, nombres parecidos, listas, usa los datos de ESTADÍSTICAS arriba
- Puedes revisar cuántos usuarios tienen el mismo nombre, nombres duplicados, nombres parecidos
- Cuando el usuario pida crear o eliminar un usuario, usa los comandos exactos mencionados arriba
- Solo habla de SAMS2; si preguntan otro tema, indica que solo ayudas con SAMS2
";
        }

        // Incluir historial previo en el prompt
        if (!empty($conversationHistory)) {
            $systemContext .= "\n\nCONVERSACIÓN PREVIA:\n";
            foreach ($conversationHistory as $msg) {
                $rol = ($msg['role'] ?? '') === 'model' || ($msg['role'] ?? '') === 'assistant' ? 'Asistente' : 'Usuario';
                $texto = $msg['text'] ?? $msg['content'] ?? '';
                $systemContext .= "{$rol}: {$texto}\n";
            }
            $systemContext .= "\n";
        }
        $systemContext .= "Usuario pregunta: {$userMessage}";

        // Un solo mensaje user (evita error "use un rol válido: user, model")
        $contents = [
            [
                'role' => 'user',
                'parts' => [['text' => $systemContext]]
            ]
        ];

        try {
            $endpoint = "{$this->apiUrl}/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

            $payload = [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1500,
                    'topP' => 0.9,
                ]
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = null;
                try {
                    $data = $response->json();
                } catch (\Exception $jsonError) {
                    Log::error('Gemini: Error parseando JSON de respuesta exitosa', [
                        'error' => $jsonError->getMessage(),
                        'body' => substr($response->body(), 0, 500)
                    ]);
                    return $this->buildSamsModeFallbackResponse(
                        $mode,
                        'invalid_json_response',
                        $userMessage,
                        $conversationHistory
                    );
                }
                
                // Verificar si hay candidatos
                if (!isset($data['candidates']) || empty($data['candidates'])) {
                    Log::warning('Gemini: Respuesta sin candidatos', ['data' => $data]);
                    return $this->buildSamsModeFallbackResponse(
                        $mode,
                        'empty_candidates',
                        $userMessage,
                        $conversationHistory
                    );
                }
                
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    if ($mode === 'full' && $this->isInvalidApiKeyErrorMessage($text)) {
                        Log::warning('Gemini: Texto de respuesta contiene error de API key inválida', [
                            'text' => $text,
                        ]);
                        return $this->buildSamsModeFallbackResponse(
                            $mode,
                            'invalid_api_key_text',
                            $userMessage,
                            $conversationHistory
                        );
                    }

                    Log::info('Gemini: Respuesta de chat generada exitosamente');
                    return [
                        'success' => true,
                        'error' => null,
                        'message' => $text,
                        'raw' => $data,
                    ];
                }
                
                // Si no hay texto pero hay candidatos, puede haber un bloqueo de seguridad
                $safetyRatings = $data['candidates'][0]['safetyRatings'] ?? [];
                if (!empty($safetyRatings)) {
                    $blocked = false;
                    foreach ($safetyRatings as $rating) {
                        if (($rating['category'] ?? '') !== 'HARM_CATEGORY_HATE_SPEECH' && 
                            ($rating['probability'] ?? '') === 'HIGH') {
                            $blocked = true;
                            break;
                        }
                    }
                    if ($blocked) {
                        return $this->buildSamsModeFallbackResponse(
                            $mode,
                            'safety_blocked',
                            $userMessage,
                            $conversationHistory
                        );
                    }
                }

                return $this->buildSamsModeFallbackResponse(
                    $mode,
                    'empty_text_response',
                    $userMessage,
                    $conversationHistory
                );
            }

            // Manejar errores HTTP
            $statusCode = $response->status();
            
            // Intentar obtener el JSON de error, pero manejar si no es válido
            $errorData = null;
            try {
                $errorData = $response->json();
            } catch (\Exception $jsonError) {
                Log::warning('Gemini: No se pudo parsear JSON de error', [
                    'status' => $statusCode,
                    'body' => $response->body()
                ]);
            }
            
            $errorMessage = null;
            
            $apiErrorRaw = (string) ($errorData['error']['message'] ?? '');
            $apiErrorLower = mb_strtolower($apiErrorRaw, 'UTF-8');
            $isInvalidApiKeyError = $this->isInvalidApiKeyErrorMessage($apiErrorRaw);

            if ($statusCode === 401 || $statusCode === 403 || $isInvalidApiKeyError) {
                $errorMessage = 'La API Key de Gemini no es válida o no tiene permisos. Actualiza GEMINI_API_KEY en .env o usa el Modo SAMS.';
            } elseif ($statusCode === 429) {
                $errorMessage = 'Se excedió el límite de solicitudes a Gemini. Por favor, espera un momento e intenta nuevamente.';
            } elseif ($statusCode === 400) {
                $errorMessage = $errorData['error']['message'] ?? 'Solicitud inválida a Gemini. Verifica tu pregunta.';
            } elseif ($statusCode >= 500) {
                $errorMessage = 'Error en el servidor de Gemini. Por favor, intenta más tarde.';
            } else {
                if ($errorData && isset($errorData['error']['message'])) {
                    $errorMessage = $errorData['error']['message'];
                } else {
                    $errorMessage = "Error de comunicación con Gemini (código HTTP: {$statusCode})";
                }
            }
            
            Log::error('Gemini: Error en chat', [
                'status' => $statusCode,
                'error' => $errorMessage,
                'response' => $errorData,
                'body' => $response->body()
            ]);

            return $this->buildSamsModeFallbackResponse(
                $mode,
                $errorMessage ?: 'http_error',
                $userMessage,
                $conversationHistory
            );

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini: Error de conexión', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->buildSamsModeFallbackResponse(
                $mode,
                'connection_error',
                $userMessage,
                $conversationHistory
            );
        } catch (\Exception $e) {
            Log::error('Gemini: Error en chatWithContext', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->buildSamsModeFallbackResponse(
                $mode,
                'unexpected_error',
                $userMessage,
                $conversationHistory
            );
        }
    }

    /**
     * Procesa comandos de acción desde Gemini
     * Detecta comandos como "crear usuario X con contraseña Y" o "eliminar usuario X"
     *
     * @param string $userMessage Mensaje del usuario que puede contener comandos
     * @return array|null Resultado de la acción o null si no es un comando
     */
    public function processActionCommand(string $userMessage): ?array
    {
        $text = mb_strtolower(trim($userMessage), 'UTF-8');

        // Comando: crear usuario
        if (preg_match('/\b(?:crear|agregar|añadir)\s+usuario\s+(?:con\s+)?(?:username|usuario)?\s*[:\s]+([a-z0-9_]+)\s+(?:y\s+)?(?:contraseña|password|clave)\s*[:\s]+([^\s]+)/ui', $text, $m)) {
            $username = trim($m[1]);
            $password = trim($m[2]);
            return $this->crearUsuarioSimple($username, $password);
        }

        // Comando: eliminar usuario
        if (preg_match('/\b(?:eliminar|borrar|quitar|remover)\s+usuario\s+(?:con\s+)?(?:username|usuario)?\s*[:\s]+([a-z0-9_]+)/ui', $text, $m)) {
            $username = trim($m[1]);
            return $this->eliminarUsuarioPorUsername($username);
        }

        // Comando: eliminar usuario por nombre
        if (preg_match('/\b(?:eliminar|borrar|quitar|remover)\s+usuario\s+(?:llamado|llamad[oa]?)\s+([a-záéíóúñ\s]+)/ui', $text, $m)) {
            $nombre = trim($m[1]);
            return $this->eliminarUsuarioPorNombre($nombre);
        }

        return null;
    }

    /**
     * Crea un usuario simple con solo username y password
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    private function crearUsuarioSimple(string $username, string $password): array
    {
        try {
            // Verificar si el username ya existe
            if (Usuario::where('username', $username)->exists()) {
                return [
                    'success' => false,
                    'error' => "El usuario '{$username}' ya existe en el sistema.",
                    'message' => null,
                ];
            }

            // Obtener valores por defecto
            $empresa = Empresa::first();
            $sede = $empresa ? Sede::where('empresa_id', $empresa->id)->first() : null;
            $grupo = Grupo::first();
            $cargo = Cargo::first();
            $role = Role::first();

            if (!$empresa || !$sede || !$grupo || !$cargo || !$role) {
                return [
                    'success' => false,
                    'error' => 'No hay datos suficientes en el sistema para crear un usuario. Se necesitan empresa, sede, grupo, cargo y rol.',
                    'message' => null,
                ];
            }

            // Crear usuario con datos mínimos
            $usuario = Usuario::create([
                'tipo_documento' => 'CC',
                'cedula' => 'TEMP_' . time() . '_' . rand(1000, 9999),
                'nombre' => $username,
                'apellidos' => 'Usuario',
                'fecha_nacimiento' => now()->subYears(25)->format('Y-m-d'),
                'direccion' => 'Por definir',
                'telefono' => '0000000000',
                'correo_electronico' => $username . '@sams2.local',
                'departamento' => 'Cundinamarca',
                'municipio' => 'Bogotá',
                'empresa_id' => $empresa->id,
                'sede_id' => $sede->id,
                'grupo_id' => $grupo->id,
                'cargo_id' => $cargo->id,
                'role_id' => $role->id,
                'username' => $username,
                'password' => \Hash::make($password),
                'activo' => true,
            ]);

            Log::info('Gemini: Usuario creado', ['username' => $username, 'id' => $usuario->id]);

            return [
                'success' => true,
                'error' => null,
                'message' => "Usuario '{$username}' creado exitosamente con la contraseña proporcionada.",
            ];
        } catch (\Exception $e) {
            Log::error('Gemini: Error al crear usuario', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Error al crear el usuario: ' . $e->getMessage(),
                'message' => null,
            ];
        }
    }

    /**
     * Elimina un usuario por username
     *
     * @param string $username
     * @return array
     */
    private function eliminarUsuarioPorUsername(string $username): array
    {
        try {
            $usuario = Usuario::where('username', $username)->first();

            if (!$usuario) {
                return [
                    'success' => false,
                    'error' => "No se encontró ningún usuario con el username '{$username}'.",
                    'message' => null,
                ];
            }

            $nombreCompleto = trim($usuario->nombre . ' ' . $usuario->apellidos);
            $usuario->delete();

            Log::info('Gemini: Usuario eliminado', ['username' => $username]);

            return [
                'success' => true,
                'error' => null,
                'message' => "Usuario '{$username}' ({$nombreCompleto}) eliminado exitosamente del sistema.",
            ];
        } catch (\Exception $e) {
            Log::error('Gemini: Error al eliminar usuario', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Error al eliminar el usuario: ' . $e->getMessage(),
                'message' => null,
            ];
        }
    }

    /**
     * Elimina un usuario por nombre
     *
     * @param string $nombre
     * @return array
     */
    private function eliminarUsuarioPorNombre(string $nombre): array
    {
        try {
            $usuarios = Usuario::where('nombre', 'like', "%{$nombre}%")
                ->orWhere('apellidos', 'like', "%{$nombre}%")
                ->orWhereRaw("CONCAT(nombre, ' ', apellidos) LIKE ?", ["%{$nombre}%"])
                ->get();

            if ($usuarios->isEmpty()) {
                return [
                    'success' => false,
                    'error' => "No se encontró ningún usuario con el nombre '{$nombre}'.",
                    'message' => null,
                ];
            }

            if ($usuarios->count() > 1) {
                $lista = $usuarios->map(fn($u) => $u->username . ' (' . trim($u->nombre . ' ' . $u->apellidos) . ')')->implode(', ');
                return [
                    'success' => false,
                    'error' => "Hay múltiples usuarios con ese nombre. Especifica el username. Usuarios encontrados: {$lista}",
                    'message' => null,
                ];
            }

            $usuario = $usuarios->first();
            $nombreCompleto = trim($usuario->nombre . ' ' . $usuario->apellidos);
            $username = $usuario->username;
            $usuario->delete();

            Log::info('Gemini: Usuario eliminado por nombre', ['username' => $username, 'nombre' => $nombreCompleto]);

            return [
                'success' => true,
                'error' => null,
                'message' => "Usuario '{$username}' ({$nombreCompleto}) eliminado exitosamente del sistema.",
            ];
        } catch (\Exception $e) {
            Log::error('Gemini: Error al eliminar usuario por nombre', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Error al eliminar el usuario: ' . $e->getMessage(),
                'message' => null,
            ];
        }
    }

    /**
     * Obtiene información completa del sistema para Gemini
     *
     * @return array
     */
    public function getSystemInfo(): array
    {
        try {
            $usuarios = Usuario::select('id', 'nombre', 'apellidos', 'username', 'activo', 'role_id')
                ->with('role:id,nombre')
                ->get()
                ->map(function($u) {
                    return [
                        'id' => $u->id,
                        'nombre_completo' => trim($u->nombre . ' ' . $u->apellidos),
                        'username' => $u->username,
                        'activo' => $u->activo,
                        'rol' => $u->role ? $u->role->nombre : 'Sin rol',
                    ];
                });

            $equipos = Equipo::select('id', 'codigo', 'descripcion', 'tipo_equipo_id')
                ->with('tipoEquipo:id,nombre')
                ->get()
                ->map(function($e) {
                    return [
                        'id' => $e->id,
                        'codigo' => $e->codigo,
                        'descripcion' => $e->descripcion,
                        'tipo' => $e->tipoEquipo ? $e->tipoEquipo->nombre : 'Sin tipo',
                    ];
                });

            return [
                'usuarios' => $usuarios,
                'total_usuarios' => Usuario::count(),
                'usuarios_activos' => Usuario::where('activo', true)->count(),
                'equipos' => $equipos,
                'total_equipos' => Equipo::count(),
                'empresas' => Empresa::count(),
                'sedes' => Sede::count(),
                'bodegas' => Bodega::count(),
                'proveedores' => Proveedor::count(),
                'fabricantes' => Fabricante::count(),
                'roles' => Role::count(),
                'grupos' => Grupo::count(),
                'cargos' => Cargo::count(),
            ];
        } catch (\Exception $e) {
            Log::error('Gemini: Error obteniendo información del sistema', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
