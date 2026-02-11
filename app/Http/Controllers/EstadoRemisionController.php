<?php

namespace App\Http\Controllers;

use App\Models\EstadoRemision;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class EstadoRemisionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;

        $estadoRemisiones = EstadoRemision::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return view('estado-remision.index', compact('estadoRemisiones', 'search', 'perPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:estado_remisiones,nombre']);
        EstadoRemision::create($request->only('nombre'));
        return redirect()->route('estado-remision.index')->with('success', 'Estado de remisión creado.');
    }

    public function update(Request $request, EstadoRemision $estadoRemision): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:estado_remisiones,nombre,' . $estadoRemision->id]);
        $estadoRemision->update($request->only('nombre'));
        return redirect()->route('estado-remision.index')->with('success', 'Estado de remisión actualizado.');
    }

    public function destroy(EstadoRemision $estadoRemision): RedirectResponse
    {
        $estadoRemision->delete();
        return redirect()->route('estado-remision.index')->with('success', 'Estado de remisión eliminado.');
    }

    public function exportExcel()
    {
        $headers = ['#', 'Nombre'];
        $rows = [];
        foreach (EstadoRemision::orderBy('nombre')->get() as $i => $e) {
            $rows[] = [$i + 1, $e->nombre];
        }
        return ExcelHelper::downloadTable('estado_remision', 'Estado de Remisión', $headers, $rows);
    }

    public function exportPdf()
    {
        $estadoRemisiones = EstadoRemision::orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('estado-remision.pdf', array_merge(compact('estadoRemisiones'), $logos));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('estado_remision_' . date('Y-m-d') . '.pdf');
    }
}
