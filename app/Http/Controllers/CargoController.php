<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class CargoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;

        $cargos = Cargo::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return view('cargos.index', compact('cargos', 'search', 'perPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:cargos,nombre']);
        Cargo::create($request->only('nombre'));
        return redirect()->route('cargos.index')->with('success', 'Cargo creado.');
    }

    public function update(Request $request, Cargo $cargo): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:cargos,nombre,' . $cargo->id]);
        $cargo->update($request->only('nombre'));
        return redirect()->route('cargos.index')->with('success', 'Cargo actualizado.');
    }

    public function destroy(Cargo $cargo): RedirectResponse
    {
        $cargo->delete();
        return redirect()->route('cargos.index')->with('success', 'Cargo eliminado.');
    }

    public function exportExcel()
    {
        $headers = ['#', 'Nombre'];
        $rows = [];
        foreach (Cargo::orderBy('nombre')->get() as $i => $c) {
            $rows[] = [$i + 1, $c->nombre];
        }
        return ExcelHelper::downloadTable('cargos', 'Cargos', $headers, $rows);
    }

    public function exportPdf()
    {
        $cargos = Cargo::orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('cargos.pdf', array_merge(compact('cargos'), $logos));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('cargos_' . date('Y-m-d') . '.pdf');
    }
}
