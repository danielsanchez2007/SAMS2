<?php

namespace App\Http\Controllers;

use App\Models\Fabricante;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class FabricanteController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;
        $fabricantes = Fabricante::when($search, fn($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();
        return view('fabricantes.index', compact('fabricantes', 'search', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:fabricantes,nombre']);
        $fabricante = Fabricante::create($request->only('nombre'));
        
        if ($request->ajax()) {
            return response()->json(['id' => $fabricante->id, 'nombre' => $fabricante->nombre]);
        }
        return redirect()->route('fabricantes.index')->with('success', 'Fabricante creado correctamente.');
    }

    public function update(Request $request, Fabricante $fabricante): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:fabricantes,nombre,' . $fabricante->id]);
        $fabricante->update($request->only('nombre'));
        return redirect()->route('fabricantes.index')->with('success', 'Fabricante actualizado correctamente.');
    }

    public function destroy(Fabricante $fabricante): RedirectResponse
    {
        $fabricante->delete();
        return redirect()->route('fabricantes.index')->with('success', 'Fabricante eliminado correctamente.');
    }

    public function exportExcel()
    {
        $headers = ['#', 'Nombre'];
        $rows = [];
        foreach (Fabricante::orderBy('nombre')->get() as $i => $f) {
            $rows[] = [$i + 1, $f->nombre];
        }
        return ExcelHelper::downloadTable('fabricantes', 'Fabricantes', $headers, $rows);
    }

    public function exportPdf()
    {
        $fabricantes = Fabricante::orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('fabricantes.pdf', array_merge(compact('fabricantes'), $logos));
        return $pdf->download('fabricantes_' . date('Y-m-d') . '.pdf');
    }
}
