<?php

namespace App\Http\Controllers;

use App\Models\EquipoBaja;
use App\Models\Equipo;
use App\Models\EquipoDebaja;
use App\Models\EstadoRemision;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Throwable;

class EquiposBajaController extends Controller
{
    /**
     * Lista todos los equipos de baja.
     */
    public function index(Request $request): View
    {
        // Reconciliar registros marcados como baja en "equipos" para que siempre
        // aparezcan en la pestaña "Equipos de baja".
        $this->sincronizarEquiposMarcadosComoBaja();

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
            'acta_numero' => ['nullable', 'string', 'max:255'],
            'resumen_baja' => ['required', 'string'],
            'responsable_inventario_nombre' => ['nullable', 'string', 'max:255'],
            'responsable_inventario_cc' => ['nullable', 'string', 'max:255'],
            'gerente_administrativa_nombre' => ['nullable', 'string', 'max:255'],
            'gerente_administrativa_cc' => ['nullable', 'string', 'max:255'],
            'asistentes' => ['nullable', 'array'],
            'items_baja' => ['nullable', 'array'],
            'acta_html' => ['nullable', 'string'], // HTML del formato editable
        ]);

        // Generar número de acta si no se proporciona
        if (empty($data['acta_numero'])) {
            $ultimoNumero = EquipoBaja::whereYear('fecha_baja', date('Y', strtotime($data['fecha_baja'])))
                ->max('acta_numero');
            $data['acta_numero'] = ($ultimoNumero ? (int)$ultimoNumero + 1 : 1);
        }

        try {
            DB::beginTransaction();

            $equipoBaja = EquipoBaja::create($data);

            // Bloqueo por fila para evitar condiciones de carrera si se dan bajas simultáneas.
            $equipo = Equipo::query()->lockForUpdate()->find($data['equipo_id']);
            if ($equipo) {
                // Usar "debaja" como valor canónico del tipo de registro.
                $equipo->tipo_registro = 'debaja';

                // Opcional: actualizar el estado de remisión a uno que contenga "baja" si existe.
                $estadoBajaId = EstadoRemision::where('nombre', 'like', '%baja%')->value('id');
                if ($estadoBajaId) {
                    $equipo->estado_remision_id = $estadoBajaId;
                }

                $equipo->save();
                $this->upsertEquipoDebajaDesdeEquipo($equipo);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            \Log::error('Error al guardar baja de equipo', [
                'equipo_id' => $data['equipo_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo registrar la baja del equipo. Intenta de nuevo.',
            ], 500);
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
     * Crea o actualiza el registro espejo en equipos_debaja usando una estrategia
     * tolerante a datos históricos (por equipo_original_id y por código/empresa).
     */
    private function upsertEquipoDebajaDesdeEquipo(Equipo $equipo): EquipoDebaja
    {
        $datosDebaja = [
            'empresa_id' => $equipo->empresa_id,
            'equipo_original_id' => $equipo->id,
            'codigo_original' => $equipo->codigo,
            // Se conserva el mismo código al pasar por acta de baja.
            'codigo' => $equipo->codigo,
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

        $registro = EquipoDebaja::query()
            ->where('equipo_original_id', $equipo->id)
            ->orWhere(function ($q) use ($equipo) {
                $q->where('codigo', $equipo->codigo)
                    ->where('empresa_id', $equipo->empresa_id);
            })
            ->first();

        if ($registro) {
            $registro->fill($datosDebaja);
            $registro->save();
            return $registro;
        }

        return EquipoDebaja::create($datosDebaja);
    }

    /**
     * Reconciliación para corregir datos históricos:
     * todo equipo marcado como baja en la tabla "equipos" debe existir en "equipos_debaja".
     */
    private function sincronizarEquiposMarcadosComoBaja(): void
    {
        $equiposMarcados = Equipo::query()
            ->whereIn('tipo_registro', ['debaja', 'equipos_debaja'])
            ->get();

        foreach ($equiposMarcados as $equipo) {
            $this->upsertEquipoDebajaDesdeEquipo($equipo);
        }
    }

    /**
     * Devuelve una vista HTML editable del formato ACTA DE BAJA.
     */
    public function formatoHtml(Equipo $equipo): View
    {
        return view('equipos-baja.formato-html', [
            'equipo' => $equipo->load('sede', 'tipoEquipo'),
        ]);
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
