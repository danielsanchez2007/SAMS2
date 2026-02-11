<?php

namespace App\Http\Controllers;

use App\Models\UsoItem;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class UsoItemController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;

        $usoItems = UsoItem::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return view('uso-items.index', compact('usoItems', 'search', 'perPage'));
    }

    public function storeUso(Request $request): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:uso_items,nombre']);
        UsoItem::create($request->only('nombre'));
        return redirect()->route('uso-items.index')->with('success', 'Uso creado.');
    }

    public function updateUso(Request $request, UsoItem $usoItem): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:uso_items,nombre,' . $usoItem->id]);
        $usoItem->update($request->only('nombre'));
        return redirect()->route('uso-items.index')->with('success', 'Uso actualizado.');
    }

    public function destroyUso(UsoItem $usoItem): RedirectResponse
    {
        $usoItem->delete();
        return redirect()->route('uso-items.index')->with('success', 'Uso eliminado.');
    }

    public function exportExcel()
    {
        $headers = ['#', 'Nombre (uso de equipos)'];
        $rows = [];
        foreach (UsoItem::orderBy('nombre')->get() as $i => $u) {
            $rows[] = [$i + 1, $u->nombre];
        }
        return ExcelHelper::downloadTable('uso_items', 'Uso de Items', $headers, $rows);
    }

    public function exportPdf()
    {
        $usoItems = UsoItem::orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('uso-items.pdf', array_merge(compact('usoItems'), $logos));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('uso_items_' . date('Y-m-d') . '.pdf');
    }
}
