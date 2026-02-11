<?php

namespace App\Http\Controllers;

use App\Models\TipoItem;
use App\Helpers\ExcelHelper;
use App\Helpers\LogoHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class TipoItemController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;

        $tipoItems = TipoItem::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return view('tipo-items.index', compact('tipoItems', 'search', 'perPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:tipo_items,nombre']);
        TipoItem::create($request->only('nombre'));
        return redirect()->route('tipo-items.index')->with('success', 'Tipo de item creado.');
    }

    public function update(Request $request, TipoItem $tipoItem): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:tipo_items,nombre,' . $tipoItem->id]);
        $tipoItem->update($request->only('nombre'));
        return redirect()->route('tipo-items.index')->with('success', 'Tipo de item actualizado.');
    }

    public function destroy(TipoItem $tipoItem): RedirectResponse
    {
        $tipoItem->delete();
        return redirect()->route('tipo-items.index')->with('success', 'Tipo de item eliminado.');
    }

    public function exportExcel()
    {
        $headers = ['#', 'Nombre'];
        $rows = [];
        foreach (TipoItem::orderBy('nombre')->get() as $i => $t) {
            $rows[] = [$i + 1, $t->nombre];
        }
        return ExcelHelper::downloadTable('tipo_items', 'Tipo de Items', $headers, $rows);
    }

    public function exportPdf()
    {
        $tipoItems = TipoItem::orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('tipo-items.pdf', array_merge(compact('tipoItems'), $logos));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('tipo_items_' . date('Y-m-d') . '.pdf');
    }
}
