<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class GrupoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;

        $grupos = Grupo::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return view('grupos.index', compact('grupos', 'search', 'perPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:grupos,nombre']);
        Grupo::create($request->only('nombre'));
        return redirect()->route('grupos.index')->with('success', 'Grupo creado.');
    }

    public function update(Request $request, Grupo $grupo): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:grupos,nombre,' . $grupo->id]);
        $grupo->update($request->only('nombre'));
        return redirect()->route('grupos.index')->with('success', 'Grupo actualizado.');
    }

    public function destroy(Grupo $grupo): RedirectResponse
    {
        $grupo->delete();
        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado.');
    }

    public function exportExcel()
    {
        $headers = ['#', 'Nombre'];
        $rows = [];
        foreach (Grupo::orderBy('nombre')->get() as $i => $g) {
            $rows[] = [$i + 1, $g->nombre];
        }
        return ExcelHelper::downloadTable('grupos', 'Grupos', $headers, $rows);
    }

    public function exportPdf()
    {
        $grupos = Grupo::orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('grupos.pdf', array_merge(compact('grupos'), $logos));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('grupos_' . date('Y-m-d') . '.pdf');
    }
}
