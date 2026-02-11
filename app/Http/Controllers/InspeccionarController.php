<?php

namespace App\Http\Controllers;

use App\Models\TipoEquipo;
use App\Models\Equipo;
use App\Models\InspeccionEquipo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class InspeccionarController extends Controller
{
    /**
     * Pantalla principal del módulo Inspeccionar.
     * Muestra los tipos de equipo y permite aplicar/reemplazar
     * el PDF de inspección (formato) para cada tipo.
     */
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $tipoEquipos = TipoEquipo::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        $inspecciones = InspeccionEquipo::with(['equipo.tipoEquipo', 'equipo'])
            // No mostrar inspecciones de equipos que ya están marcados como de baja / fuera de uso
            ->whereHas('equipo', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('tipo_registro')
                       ->orWhere('tipo_registro', 'normal');
                });
            })
            ->orderByDesc('fecha_inspeccion')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Preparar datos planos para usarlos en Alpine (cards de inspecciones)
        $todasInspecciones = $inspecciones->map(function (InspeccionEquipo $insp) {
            return [
                'id' => $insp->id,
                'equipo_id' => $insp->equipo_id,
                'tipo_equipo_id' => $insp->tipo_equipo_id,
                'fecha_inspeccion' => optional($insp->fecha_inspeccion)->format('Y-m-d'),
                'validez_inspeccion' => optional($insp->validez_inspeccion)->format('Y-m-d'),
                'equipo_codigo' => $insp->equipo->codigo ?? '',
                'tipo_equipo' => $insp->tipoEquipo->nombre ?? '',
            ];
        })->values();

        $logoMain = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
        $logoSecondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));

        return view('inspeccionar.index', compact(
            'tipoEquipos',
            'search',
            'perPage',
            'logoMain',
            'logoSecondary',
            'inspecciones',
            'todasInspecciones'
        ));
    }

    /**
     * Devuelve los equipos de un tipo de equipo (para el selector en el modal).
     */
    public function equiposPorTipo(TipoEquipo $tipoEquipo): JsonResponse
    {
        $equipos = Equipo::where('tipo_equipo_id', $tipoEquipo->id)
            // Solo mostrar equipos activos / normales, no los que ya están marcados como de baja
            ->where(function ($q) {
                $q->whereNull('tipo_registro')
                  ->orWhere('tipo_registro', 'normal');
            })
            ->withCount('inspecciones')
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'descripcion', 'sede_id', 'estado_remision_id'])
            ->map(function (Equipo $equipo) {
                return [
                    'id' => $equipo->id,
                    'codigo' => $equipo->codigo,
                    'descripcion' => $equipo->descripcion,
                    'sede' => $equipo->sede?->nombre,
                    'estado' => $equipo->estadoRemision?->nombre,
                    'inspecciones_count' => $equipo->inspecciones_count ?? 0,
                ];
            });

        return response()->json([
            'success' => true,
            'equipos' => $equipos,
        ]);
    }

    /**
     * Guarda el registro de una inspección realizada a un equipo.
     */
    public function guardarInspeccion(Request $request): JsonResponse
    {
        // Si el equipo está "dado de baja", la validez de inspección no aplica.
        $equipo = Equipo::with('estadoRemision')->findOrFail($request->input('equipo_id'));
        $estadoNombre = Str::lower((string) optional($equipo->estadoRemision)->nombre);
        $equipoDadoDeBaja = $estadoNombre !== '' && Str::contains($estadoNombre, 'baja');

        $data = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'tipo_equipo_id' => ['required', 'exists:tipo_equipos,id'],
            'fecha_inspeccion' => ['required', 'date'],
            'validez_inspeccion' => [$equipoDadoDeBaja ? 'nullable' : 'required', 'date'],
        ]);

        $id = $request->input('id');
        if ($id) {
            $inspeccion = InspeccionEquipo::findOrFail($id);
            $inspeccion->update($data);
        } else {
            $inspeccion = InspeccionEquipo::create($data);
        }
        $inspeccion->load(['equipo.tipoEquipo']);

        return response()->json([
            'success' => true,
            'message' => 'Inspección guardada correctamente.',
            'inspeccion' => [
                'id' => $inspeccion->id,
                'fecha_inspeccion' => optional($inspeccion->fecha_inspeccion)->format('Y-m-d'),
                'validez_inspeccion' => optional($inspeccion->validez_inspeccion)->format('Y-m-d'),
                'equipo_codigo' => $inspeccion->equipo->codigo ?? '',
                'tipo_equipo' => $inspeccion->tipoEquipo->nombre ?? '',
            ],
        ]);
    }

    /**
     * Elimina una inspección SOLO si es del mismo día en que se realizó.
     */
    public function eliminar(InspeccionEquipo $inspeccion): JsonResponse
    {
        $today = Carbon::today()->format('Y-m-d');
        $fechaInspeccion = optional($inspeccion->fecha_inspeccion)->format('Y-m-d');

        if ($fechaInspeccion !== $today) {
            return response()->json([
                'success' => false,
                'message' => 'Solo puedes eliminar la inspección el mismo día en que se realizó.',
            ], 422);
        }

        $inspeccion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inspección eliminada correctamente.',
        ]);
    }

    /**
     * Devuelve una vista HTML editable del formato de inspección
     * (similar a la vista previa de la hoja de vida).
     */
    public function formatoHtml(TipoEquipo $tipoEquipo): View
    {
        return view('inspecciones.formato-html', [
            'tipoEquipo' => $tipoEquipo,
        ]);
    }
}

