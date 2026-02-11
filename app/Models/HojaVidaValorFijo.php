<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class HojaVidaValorFijo extends Model
{
    protected $table = 'hoja_vida_valores_fijos';

    protected $fillable = ['hoja_vida_plantilla_id', 'clave', 'tipo', 'valor', 'config'];

    protected $casts = [
        'config' => 'array',
    ];

    public function hojaVidaPlantilla(): BelongsTo
    {
        return $this->belongsTo(HojaVidaPlantilla::class);
    }

    /**
     * Obtiene todos los valores fijos de una plantilla como array clave => valor (para reemplazo en PDF).
     * Respeta el tipo de cada campo: usuario, equipo, tabla, etc., para que todos queden bien rellenados.
     *
     * @param  int  $plantillaId
     * @param  \App\Models\Equipo|null  $equipo  Equipo del que se genera la hoja de vida
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user  Usuario que genera (para tipo "usuario")
     */
    public static function placeholdersParaPdf(int $plantillaId, $equipo = null, $user = null): array
    {
        $rows = self::where('hoja_vida_plantilla_id', $plantillaId)->get();
        $out = [];
        foreach ($rows as $r) {
            $tipo = $r->tipo ?? 'texto';
            $out[$r->clave] = self::valorParaTipo($tipo, $r->valor, $r->config ?? [], $equipo, $user);
        }
        return $out;
    }

    /**
     * Calcula el valor final de un campo según su tipo (para reemplazo en PDF).
     */
    public static function valorParaTipo(string $tipo, ?string $valor, array $config, $equipo, $user): string
    {
        switch ($tipo) {
            case 'usuario':
                if ($user && method_exists($user, 'name')) {
                    return (string) $user->name;
                }
                if ($user && isset($user->nombre)) {
                    return (string) $user->nombre;
                }
                return $valor ?? '—';
            case 'equipo':
                if ($equipo) {
                    return (string) ($equipo->codigo ?? $equipo->descripcion ?? $valor ?? '—');
                }
                return $valor ?? '—';
            case 'tabla':
                $filas = (int) ($config['filas'] ?? 5);
                $columnas = (int) ($config['columnas'] ?? 3);
                $filas = max(1, min(50, $filas));
                $columnas = max(1, min(20, $columnas));
                return self::generarHtmlTabla($filas, $columnas);
            case 'seleccion_multiple':
                return (string) ($valor ?? '—');
            case 'imagen':
                return self::imagenAutomaticaParaPdf($equipo, $config, $valor);
            case 'numero':
                return $valor !== null && $valor !== '' ? (string) $valor : '—';
            case 'fecha':
                return $valor !== null && $valor !== '' ? (string) $valor : '—';
            case 'texto':
            default:
                return (string) ($valor ?? '');
        }
    }

    /**
     * Genera el HTML de imagen para el PDF según la etiqueta elegida (automático por tipo de ítem/equipo).
     * - etiqueta_tipo = 'general' → imagen general del equipo
     * - etiqueta_tipo = 'etiqueta' → imagen etiqueta del equipo
     * - etiqueta_id = X → imagen del almacén con esa etiqueta para el equipo
     * Si no hay config, usa valor como URL (comportamiento legacy).
     */
    public static function imagenAutomaticaParaPdf($equipo, array $config, ?string $valor): string
    {
        $path = null;
        $mime = 'image/jpeg';

        if ($equipo) {
            $etiquetaTipo = $config['etiqueta_tipo'] ?? null;
            $etiquetaId = isset($config['etiqueta_id']) ? (int) $config['etiqueta_id'] : null;

            if ($etiquetaTipo === 'general' && $equipo->imagen_general) {
                $path = Storage::disk('public')->path($equipo->imagen_general);
            } elseif ($etiquetaTipo === 'etiqueta' && $equipo->imagen_etiqueta) {
                $path = Storage::disk('public')->path($equipo->imagen_etiqueta);
            } elseif ($etiquetaId) {
                $almacenImg = AlmacenImagen::where('equipo_id', $equipo->id)
                    ->where('etiqueta_id', $etiquetaId)
                    ->first();
                if ($almacenImg && $almacenImg->ruta) {
                    $path = Storage::disk('public')->path($almacenImg->ruta);
                }
            }
        }

        if ($path && File::isFile($path) && File::isReadable($path)) {
            $mime = mime_content_type($path) ?: 'image/jpeg';
            $b64 = base64_encode(File::get($path));
            $dataUrl = 'data:' . $mime . ';base64,' . $b64;
            return '<img src="' . $dataUrl . '" alt="" style="max-width:100%;height:auto;">';
        }

        if (!empty(trim((string) $valor))) {
            $url = trim($valor);
            if (str_starts_with($url, 'data:') || str_starts_with($url, 'http')) {
                return '<img src="' . e($url) . '" alt="" style="max-width:100%;height:auto;">';
            }
            return '<img src="' . e($url) . '" alt="" style="max-width:100%;height:auto;">';
        }

        return '';
    }

    /**
     * Genera HTML de una tabla vacía con N filas y M columnas (para reemplazo en plantilla).
     */
    public static function generarHtmlTabla(int $filas, int $columnas): string
    {
        $html = '<table border="1" cellpadding="4" cellspacing="0" style="width:100%;border-collapse:collapse;">';
        for ($i = 0; $i < $filas; $i++) {
            $html .= '<tr>';
            for ($j = 0; $j < $columnas; $j++) {
                $html .= '<td>&nbsp;</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    /**
     * @deprecated Usar placeholdersParaPdf() para que los campos queden bien rellenados por tipo.
     */
    public static function paraPlantilla(int $plantillaId): array
    {
        return self::where('hoja_vida_plantilla_id', $plantillaId)->pluck('valor', 'clave')->toArray();
    }

    /**
     * Lista todos los campos de una plantilla con tipo y config (para el formulario de edición).
     */
    public static function listadoParaPlantilla(int $plantillaId): array
    {
        return self::where('hoja_vida_plantilla_id', $plantillaId)
            ->get()
            ->map(fn ($r) => [
                'clave' => $r->clave,
                'valor' => $r->valor,
                'tipo' => $r->tipo ?? 'texto',
                'config' => $r->config ?? [],
            ])
            ->toArray();
    }

    /**
     * Guarda o actualiza un valor fijo de una plantilla (con tipo y config opcionales).
     */
    public static function guardarParaPlantilla(int $plantillaId, string $clave, ?string $valor, string $tipo = 'texto', ?array $config = null): void
    {
        self::updateOrCreate(
            ['hoja_vida_plantilla_id' => $plantillaId, 'clave' => $clave],
            ['valor' => $valor, 'tipo' => $tipo, 'config' => $config]
        );
    }
}
