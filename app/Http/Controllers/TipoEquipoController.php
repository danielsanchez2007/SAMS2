<?php

namespace App\Http\Controllers;

use App\Models\TipoEquipo;
use App\Models\Equipo;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TipoEquipoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;

        $tipoEquipos = TipoEquipo::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        // Obtener todos los tipos de equipo para el selector de exportación
        $todosLosTipoEquipos = TipoEquipo::orderBy('nombre')->get();

        $logoMain = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
        $logoSecondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));

        return view('tipo-equipos.index', compact('tipoEquipos', 'todosLosTipoEquipos', 'search', 'perPage', 'logoMain', 'logoSecondary'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:tipo_equipos,nombre']);
        TipoEquipo::create($request->only('nombre'));
        return redirect()->route('tipo-equipos.index')->with('success', 'Tipo de equipo creado.');
    }

    public function update(Request $request, TipoEquipo $tipoEquipo): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:tipo_equipos,nombre,' . $tipoEquipo->id]);
        $tipoEquipo->update($request->only('nombre'));
        return redirect()->route('tipo-equipos.index')->with('success', 'Tipo de equipo actualizado.');
    }

    public function destroy(TipoEquipo $tipoEquipo): RedirectResponse
    {
        $tipoEquipo->delete();
        return redirect()->route('tipo-equipos.index')->with('success', 'Tipo de equipo eliminado.');
    }

    public function exportPreviewData(Request $request): JsonResponse
    {
        $tipoEquiposIds = $request->query('tipo_equipos', $request->get('tipo_equipos', []));
        if (!is_array($tipoEquiposIds)) {
            $tipoEquiposIds = $tipoEquiposIds ? [$tipoEquiposIds] : [];
        }
        $tipoEquiposIds = array_values(array_map('intval', array_filter($tipoEquiposIds)));

        $query = Equipo::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'sede', 'bodega', 'usoItem'])
            ->where('tipo_registro', 'normal');
        
        if (!empty($tipoEquiposIds)) {
            $query->whereIn('tipo_equipo_id', $tipoEquiposIds);
        }
        
        $equipos = $query->orderBy('codigo')->get();

        $tiposSeleccionados = !empty($tipoEquiposIds)
            ? TipoEquipo::whereIn('id', $tipoEquiposIds)->pluck('nombre')->all()
            : [];

        $rows = $equipos->map(function ($e, $i) {
            return [
                'numero' => $i + 1,
                'codigo' => $e->codigo,
                'equipo' => $e->descripcion,
                'tipo' => $e->tipoEquipo?->nombre ?? $e->tipoItem?->nombre ?? '—',
                'serial_modelo' => $e->modelo ?? '—',
                'marca' => $e->marca ?? '—',
                'lote' => $e->lote ?? '—',
                'modelo' => $e->modelo ?? '—',
                'fecha_fabricacion' => $e->fecha_fabricacion?->format('d/m/Y') ?? '—',
                'fecha_compra' => $e->fecha_compra?->format('d/m/Y') ?? '—',
                'fabricante' => $e->fabricante?->nombre ?? '—',
                'proveedor' => $e->proveedor?->nombre ?? '—',
                'uso' => $e->usoItem?->nombre ?? '—',
                'capacidad_estructural' => $e->capacidades_resistencia ?? '—',
                'ubicacion' => $e->bodega?->nombre ?? $e->sede?->nombre ?? '—',
                'estado' => $e->estadoRemision?->nombre ?? '—',
            ];
        });

        return response()->json([
            'equipos' => $rows,
            'tipos_seleccionados' => $tiposSeleccionados,
            'total' => $equipos->count(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $tipoEquiposIds = $request->query('tipo_equipos', $request->get('tipo_equipos', []));
        if (!is_array($tipoEquiposIds)) {
            $tipoEquiposIds = $tipoEquiposIds ? [$tipoEquiposIds] : [];
        }
        $tipoEquiposIds = array_values(array_map('intval', array_filter($tipoEquiposIds)));

        $query = Equipo::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'sede', 'bodega', 'usoItem'])
            ->where('tipo_registro', 'normal')
            ->orderBy('codigo');
        
        if (!empty($tipoEquiposIds)) {
            $query->whereIn('tipo_equipo_id', $tipoEquiposIds);
        }
        
        $equipos = $query->get();

        $logos = LogoHelper::getBase64Logos();
        $html = view('equipos.export-trazabilidad', array_merge($logos, ['equipos' => $equipos]))->render();
        $filename = 'programa_trazabilidad_equipos_' . date('Y-m-d') . '.xls';
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $tipoEquiposIds = $request->query('tipo_equipos', $request->get('tipo_equipos', []));
        if (!is_array($tipoEquiposIds)) {
            $tipoEquiposIds = $tipoEquiposIds ? [$tipoEquiposIds] : [];
        }
        $tipoEquiposIds = array_values(array_map('intval', array_filter($tipoEquiposIds)));

        $query = Equipo::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'sede', 'bodega', 'proveedor', 'fabricante', 'usoItem'])
            ->where('tipo_registro', 'normal')
            ->orderBy('codigo');
        
        if (!empty($tipoEquiposIds)) {
            $query->whereIn('tipo_equipo_id', $tipoEquiposIds);
        }
        
        $equipos = $query->get();

        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('equipos.pdf-trazabilidad', array_merge(compact('equipos'), $logos));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('programa_trazabilidad_equipos_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Subir o reemplazar el formato (PDF) para este tipo de equipo.
     */
    public function uploadFormato(Request $request, TipoEquipo $tipoEquipo): RedirectResponse
    {
        $request->validate([
            'formato' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'formato.required' => 'Debes seleccionar un archivo PDF.',
            'formato.mimes' => 'El archivo debe ser un PDF.',
            'formato.max' => 'El PDF no debe superar 10 MB.',
        ]);

        $file = $request->file('formato');
        $dir = 'formatos_equipo/' . $tipoEquipo->id;
        if ($tipoEquipo->formato_archivo && Storage::disk('local')->exists($tipoEquipo->formato_archivo)) {
            Storage::disk('local')->delete($tipoEquipo->formato_archivo);
        }
        $ruta = $file->store($dir, 'local');
        $tipoEquipo->update(['formato_archivo' => $ruta]);

        return redirect()
            ->route('tipo-equipos.index')
            ->with('success', 'Formato aplicado correctamente para "' . $tipoEquipo->nombre . '".');
    }

    /**
     * Eliminar el formato asociado a este tipo de equipo.
     */
    public function destroyFormato(TipoEquipo $tipoEquipo): RedirectResponse
    {
        if ($tipoEquipo->formato_archivo && Storage::disk('local')->exists($tipoEquipo->formato_archivo)) {
            Storage::disk('local')->delete($tipoEquipo->formato_archivo);
        }
        $tipoEquipo->update(['formato_archivo' => null]);
        return redirect()
            ->route('tipo-equipos.index')
            ->with('success', 'Formato eliminado para "' . $tipoEquipo->nombre . '".');
    }

    /**
     * Servir el PDF del formato (para vista previa en iframe/panel derecho).
     */
    public function showFormato(TipoEquipo $tipoEquipo)
    {
        if (!$tipoEquipo->formato_archivo || !Storage::disk('local')->exists($tipoEquipo->formato_archivo)) {
            abort(404, 'No hay formato subido para este tipo de equipo.');
        }
        $path = Storage::disk('local')->path($tipoEquipo->formato_archivo);
        $nombre = 'formato-' . \Illuminate\Support\Str::slug($tipoEquipo->nombre) . '.pdf';
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nombre . '"',
        ]);
    }
}
