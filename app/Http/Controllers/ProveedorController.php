<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Helpers\ExcelHelper;
use App\Helpers\LogoHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;
        $proveedores = Proveedor::when($search, fn($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();
        return view('proveedores.index', compact('proveedores', 'search', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:proveedores,nombre']);
        $proveedor = Proveedor::create($request->only('nombre'));
        
        if ($request->ajax()) {
            return response()->json(['id' => $proveedor->id, 'nombre' => $proveedor->nombre]);
        }
        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:proveedores,nombre,' . $proveedor->id]);
        $proveedor->update($request->only('nombre'));
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->delete();
        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado correctamente.');
    }

    public function exportExcel()
    {
        $headers = ['#', 'Nombre'];
        $rows = [];
        foreach (Proveedor::orderBy('nombre')->get() as $i => $p) {
            $rows[] = [$i + 1, $p->nombre];
        }
        return ExcelHelper::downloadTable('proveedores', 'Proveedores', $headers, $rows);
    }

    public function exportPdf()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('proveedores.pdf', array_merge(compact('proveedores'), $logos));
        return $pdf->download('proveedores_' . date('Y-m-d') . '.pdf');
    }
}
