<?php

namespace App\Http\Controllers;

use App\Models\EquipoBaja;
use App\Models\Equipo;
use App\Models\EquipoDebaja;
use App\Models\EstadoRemision;
use App\Models\CodigoDisponible;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

class EquiposBajaController extends Controller
{
    /**
     * Lista todos los equipos de baja.
     */
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $equiposBaja = EquipoBaja::with(['equipo.tipoEquipo', 'equipo.sede'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('equipo', function ($query) use ($search) {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                })->orWhere('resumen_baja', 'like', "%{$search}%");
            })
            ->orderByDesc('fecha_baja')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $logoMain = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
        $logoSecondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));

        return view('equipos-baja.index', compact('equiposBaja', 'search', 'perPage', 'logoMain', 'logoSecondary'));
    }

    /**
     * Guarda un nuevo registro de baja de equipo.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'fecha_baja' => ['required', 'date'],
            'resumen_baja' => ['required', 'string'],
            'responsable_inventario_nombre' => ['nullable', 'string', 'max:255'],
            'responsable_inventario_cc' => ['nullable', 'string', 'max:255'],
            'gerente_administrativa_nombre' => ['nullable', 'string', 'max:255'],
            'gerente_administrativa_cc' => ['nullable', 'string', 'max:255'],
            // Campos adicionales del formulario
            'serial' => ['nullable', 'string'],
            'estado' => ['nullable', 'string'],
            'destino_final' => ['nullable', 'string'],
            'responsable_cargo' => ['nullable', 'string'],
            'gerente_cargo' => ['nullable', 'string'],
            'asistentes' => ['nullable', 'array'],
            'items_baja' => ['nullable', 'array'],
            'acta_html' => ['nullable', 'string'], // HTML del formato editable
        ]);

        // Generar número de acta automático y secuencial
        $ultimoNumero = EquipoBaja::max('acta_numero');
        $data['acta_numero'] = ($ultimoNumero ? (int)$ultimoNumero + 1 : 1);

        $equipoBaja = EquipoBaja::create($data);

        // Marcar el equipo original como "de baja" para que deje de aparecer en la lista normal
        // y crear/actualizar su registro en la tabla equipos_debaja (pestaña Equipos debaja).
        $equipo = Equipo::find($data['equipo_id']);
        if ($equipo) {
            // Cambiar el tipo de registro para que no salga en la pestaña principal
            $equipo->tipo_registro = 'equipos_debaja';

            // Opcional: actualizar el estado de remisión a uno que contenga "baja" si existe
            $estadoBajaId = EstadoRemision::where('nombre', 'like', '%baja%')->value('id');
            if ($estadoBajaId) {
                $equipo->estado_remision_id = $estadoBajaId;
            }

            $equipo->save();

            // Cambiar el código del equipo a formato DB2
            $codigoOriginal = $equipo->codigo;
            $equipo->codigo = 'DB2';
            $equipo->save();

            // Liberar el código original automáticamente para que esté disponible para reutilizar
            CodigoDisponible::create([
                'empresa_id' => $equipo->empresa_id,
                'codigo' => $codigoOriginal,
                'tipo' => 'normal',
                'utilizado' => false,
                'motivo' => 'Código liberado por baja de equipo: ' . $equipo->descripcion,
                'equipo_original_id' => $equipo->id,
            ]);

            // Crear o actualizar el registro en equipos_debaja para que aparezca en la pestaña "Equipos debaja"
            $datosDebaja = [
                'empresa_id' => $equipo->empresa_id,
                'codigo_original' => $codigoOriginal,
                'codigo' => 'DB2', // Cambiar a formato DB2
                'tipo_item_id' => $equipo->tipo_item_id,
                'tipo_equipo_id' => $equipo->tipo_equipo_id,
                'codigo_bloqueado' => $equipo->codigo_bloqueado,
                'estado_remision_id' => $equipo->estado_remision_id,
                'descripcion' => $equipo->descripcion,
                'marca' => $equipo->marca,
                'proveedor_id' => $equipo->proveedor_id,
                'fabricante_id' => $equipo->fabricante_id,
                'modelo' => $equipo->modelo,
                'sede_id' => $equipo->sede_id,
                'bodega_id' => $equipo->bodega_id,
                'vida_util' => $equipo->vida_util,
                'fecha_fabricacion' => $equipo->fecha_fabricacion,
                'fecha_uso' => $equipo->fecha_uso,
                'uso_item_id' => $equipo->uso_item_id,
                'es_kit' => $equipo->es_kit,
                'nombre_kit' => $equipo->nombre_kit,
                'componentes_kit' => $equipo->componentes_kit,
                'valor' => $equipo->valor,
                'numero_factura' => $equipo->numero_factura,
                'capacidades_resistencia' => $equipo->capacidades_resistencia,
                'lote' => $equipo->lote,
                'fecha_compra' => $equipo->fecha_compra,
                'tiene_manual_fabricante' => $equipo->tiene_manual_fabricante,
                'manual_fabricante' => $equipo->manual_fabricante,
                'tiene_certificacion' => $equipo->tiene_certificacion,
                'certificacion_fabricante' => $equipo->certificacion_fabricante,
                'tiene_imagen_general' => $equipo->tiene_imagen_general,
                'imagen_general' => $equipo->imagen_general,
                'tiene_imagen_etiqueta' => $equipo->tiene_imagen_etiqueta,
                'imagen_etiqueta' => $equipo->imagen_etiqueta,
            ];

            EquipoDebaja::updateOrCreate(
                ['equipo_original_id' => $equipo->id],
                $datosDebaja
            );
        }

        // Generar PDF del acta si hay HTML
        if (!empty($data['acta_html'])) {
            try {
                $pdf = Pdf::loadHTML($data['acta_html']);
                $pdfPath = 'actas_baja/' . $equipoBaja->id . '_acta_baja_' . time() . '.pdf';
                Storage::disk('public')->put($pdfPath, $pdf->output());
                $equipoBaja->update(['acta_pdf' => $pdfPath]);
            } catch (\Exception $e) {
                \Log::error('Error al generar PDF de baja: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Equipo dado de baja correctamente.',
            'equipo_baja' => $equipoBaja->load('equipo'),
        ]);
    }

    /**
     * Muestra el formato HTML de un equipo de baja específico (por ID de acta EquipoBaja).
     */     
    public function formatoHtml(EquipoBaja $equipoBaja): View
    {
        $equipo = $equipoBaja->equipo->load('sede', 'tipoEquipo');
        return view('equipos-baja.formato-html', [
            'equipo' => $equipo,
            'equipoBaja' => $equipoBaja,
            'proximaActa' => $equipoBaja->acta_numero,
        ]);
    }

    /**
     * Muestra el formato HTML del acta de baja por ID de equipo (para Inspeccionar / acta nueva).
     * Si ya existe un EquipoBaja para ese equipo lo usa; si no, muestra el formato vacío con próxima acta.
     */
    public function formatoHtmlPorEquipo(Equipo $equipo): View
    {
        $equipo->load('sede', 'tipoEquipo');
        $equipoBaja = EquipoBaja::where('equipo_id', $equipo->id)->first();
        $proximaActa = $equipoBaja
            ? $equipoBaja->acta_numero
            : (EquipoBaja::max('acta_numero') ? (int) EquipoBaja::max('acta_numero') + 1 : 1);
        if (! $equipoBaja) {
            $equipoBaja = new EquipoBaja([
                'equipo_id' => $equipo->id,
                'acta_numero' => $proximaActa,
            ]);
        }
        return view('equipos-baja.formato-html', [
            'equipo' => $equipo,
            'equipoBaja' => $equipoBaja,
            'proximaActa' => $proximaActa,
        ]);
    }

    /**
     * Obtiene el próximo número de acta para el modal.
     */
    public function getProximaActa(): JsonResponse
    {
        $ultimoNumero = EquipoBaja::max('acta_numero');
        $proximaActa = ($ultimoNumero ? (int)$ultimoNumero + 1 : 1);
        
        return response()->json([
            'proxima_acta' => $proximaActa
        ]);
    }

    /**
     * Busca un registro de baja por el ID del equipo.
     */
    public function buscarPorEquipo($equipoId): JsonResponse
    {
        $equipoBaja = EquipoBaja::where('equipo_id', $equipoId)->first();
        
        if ($equipoBaja) {
            return response()->json([
                'success' => true,
                'equipoBaja' => $equipoBaja
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No se encontró registro de baja para este equipo'
        ]);
    }

    /**
     * Exporta el listado de equipos de baja en formato PDF.
     */
    public function exportarPdf(Request $request): View
    {
        $search = $request->get('q', '');
        $empresaId = $request->get('empresa_id');
        $sedeId = $request->get('sede_id');
        $bodegaId = $request->get('bodega_id');
        
        $equiposBaja = EquipoBaja::with(['equipo.tipoEquipo', 'equipo.sede'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('equipo', function ($query) use ($search) {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                })->orWhere('resumen_baja', 'like', "%{$search}%");
            })
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->whereHas('equipo', function ($query) use ($empresaId) {
                    $query->where('empresa_id', $empresaId);
                });
            })
            ->when($sedeId, function ($q) use ($sedeId) {
                $q->whereHas('equipo', function ($query) use ($sedeId) {
                    $query->where('sede_id', $sedeId);
                });
            })
            ->when($bodegaId, function ($q) use ($bodegaId) {
                $q->whereHas('equipo', function ($query) use ($bodegaId) {
                    $query->where('bodega_id', $bodegaId);
                });
            })
            ->orderByDesc('fecha_baja')
            ->orderByDesc('created_at')
            ->get();

        $logoMain = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
        $logoSecondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));

        return view('equipos-baja.export-pdf', compact('equiposBaja', 'logoMain', 'logoSecondary'));
    }

    /**
     * Descarga el PDF del acta de baja.
     */
    public function download(EquipoBaja $equipoBaja)
    {
        if (!$equipoBaja->acta_pdf || !Storage::disk('public')->exists($equipoBaja->acta_pdf)) {
            abort(404, 'El archivo PDF no existe.');
        }

        return Storage::disk('public')->download($equipoBaja->acta_pdf, 'acta_baja_' . $equipoBaja->equipo->codigo . '.pdf');
    }
}
