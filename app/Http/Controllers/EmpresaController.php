<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use App\Models\Empresa;
use App\Models\Sede;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class EmpresaController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'empresa');
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $empresas = Empresa::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%")->orWhere('pais', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage, ['*'], 'page_empresa')
            ->withQueryString();

        $sedes = Sede::query()
            ->with('empresa')
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('pais', 'like', "%{$search}%")
                ->orWhere('municipio', 'like', "%{$search}%")
                ->orWhere('departamento', 'like', "%{$search}%")
                ->orWhereHas('empresa', fn ($eq) => $eq->where('nombre', 'like', "%{$search}%")))
            ->orderBy('nombre')
            ->paginate($perPage, ['*'], 'page_sede')
            ->withQueryString();

        $bodegas = Bodega::query()
            ->with('sede.empresa')
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%")
                ->orWhereHas('sede', fn ($sq) => $sq->where('nombre', 'like', "%{$search}%")))
            ->orderBy('nombre')
            ->paginate($perPage, ['*'], 'page_bodega')
            ->withQueryString();

        $allEmpresas = Empresa::orderBy('nombre')->get();
        $allSedes = Sede::with('empresa')->orderBy('nombre')->get();

        $paises = config('ubicacion.paises', []);
        $departamentosPorPais = config('ubicacion.departamentos', []);
        $municipiosPorDepartamento = config('ubicacion.municipios', []);

        return view('empresas.index', compact('empresas', 'sedes', 'bodegas', 'tab', 'search', 'perPage', 'allEmpresas', 'allSedes', 'paises', 'departamentosPorPais', 'municipiosPorDepartamento'));
    }

    public function storeEmpresa(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:empresas,nombre',
            'pais' => 'required|string|max:255',
            'pais_otro' => 'nullable|string|max:255',
        ]);
        $pais = $request->filled('pais_otro') ? $request->input('pais_otro') : $request->input('pais');
        Empresa::create(['nombre' => $request->input('nombre'), 'pais' => $pais]);
        return redirect()->route('empresas.index', ['tab' => 'empresa'])->with('success', 'Empresa creada.');
    }

    public function updateEmpresa(Request $request, Empresa $empresa): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:empresas,nombre,' . $empresa->id,
            'pais' => 'required|string|max:255',
            'pais_otro' => 'nullable|string|max:255',
        ]);
        $pais = $request->filled('pais_otro') ? $request->input('pais_otro') : $request->input('pais');
        $empresa->update(['nombre' => $request->input('nombre'), 'pais' => $pais]);
        return redirect()->route('empresas.index', ['tab' => 'empresa'])->with('success', 'Empresa actualizada.');
    }

    public function destroyEmpresa(Empresa $empresa): RedirectResponse
    {
        $empresa->delete();
        return redirect()->route('empresas.index', ['tab' => 'empresa'])->with('success', 'Empresa eliminada.');
    }

    public function storeSede(Request $request): RedirectResponse
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nombre' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existe = \App\Models\Sede::where('nombre', $value)
                        ->where('empresa_id', $request->empresa_id)
                        ->exists();
                    if ($existe) {
                        $fail('El nombre de la sede ya existe para esta empresa.');
                    }
                },
            ],
            'pais' => 'required|string|max:255',
            'pais_otro' => 'nullable|string|max:255',
            'municipio' => 'required|string|max:255',
            'municipio_otro' => 'nullable|string|max:255',
            'departamento' => 'required|string|max:255',
            'departamento_otro' => 'nullable|string|max:255',
        ]);
        $pais = $request->filled('pais_otro') ? $request->input('pais_otro') : $request->input('pais');
        $departamento = $request->filled('departamento_otro') ? $request->input('departamento_otro') : $request->input('departamento');
        $municipio = $request->filled('municipio_otro') ? $request->input('municipio_otro') : $request->input('municipio');
        Sede::create([
            'empresa_id' => $request->input('empresa_id'),
            'nombre' => $request->input('nombre'),
            'pais' => $pais,
            'departamento' => $departamento,
            'municipio' => $municipio,
        ]);
        return redirect()->route('empresas.index', ['tab' => 'sede'])->with('success', 'Sede creada.');
    }

    public function updateSede(Request $request, Sede $sede): RedirectResponse
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nombre' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $sede) {
                    $existe = \App\Models\Sede::where('nombre', $value)
                        ->where('empresa_id', $request->empresa_id)
                        ->where('id', '!=', $sede->id)
                        ->exists();
                    if ($existe) {
                        $fail('El nombre de la sede ya existe para esta empresa.');
                    }
                },
            ],
            'pais' => 'required|string|max:255',
            'pais_otro' => 'nullable|string|max:255',
            'municipio' => 'required|string|max:255',
            'municipio_otro' => 'nullable|string|max:255',
            'departamento' => 'required|string|max:255',
            'departamento_otro' => 'nullable|string|max:255',
        ]);
        $pais = $request->filled('pais_otro') ? $request->input('pais_otro') : $request->input('pais');
        $departamento = $request->filled('departamento_otro') ? $request->input('departamento_otro') : $request->input('departamento');
        $municipio = $request->filled('municipio_otro') ? $request->input('municipio_otro') : $request->input('municipio');
        $sede->update([
            'empresa_id' => $request->input('empresa_id'),
            'nombre' => $request->input('nombre'),
            'pais' => $pais,
            'departamento' => $departamento,
            'municipio' => $municipio,
        ]);
        return redirect()->route('empresas.index', ['tab' => 'sede'])->with('success', 'Sede actualizada.');
    }

    public function destroySede(Sede $sede): RedirectResponse
    {
        $sede->delete();
        return redirect()->route('empresas.index', ['tab' => 'sede'])->with('success', 'Sede eliminada.');
    }

    public function storeBodega(Request $request): RedirectResponse
    {
        $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'nombre' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existe = \App\Models\Bodega::where('nombre', $value)
                        ->where('sede_id', $request->sede_id)
                        ->exists();
                    if ($existe) {
                        $fail('El nombre de la bodega ya existe para esta sede.');
                    }
                },
            ],
        ]);
        Bodega::create($request->only('sede_id', 'nombre'));
        return redirect()->route('empresas.index', ['tab' => 'bodega'])->with('success', 'Bodega creada.');
    }

    public function updateBodega(Request $request, Bodega $bodega): RedirectResponse
    {
        $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'nombre' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $bodega) {
                    $existe = \App\Models\Bodega::where('nombre', $value)
                        ->where('sede_id', $request->sede_id)
                        ->where('id', '!=', $bodega->id)
                        ->exists();
                    if ($existe) {
                        $fail('El nombre de la bodega ya existe para esta sede.');
                    }
                },
            ],
        ]);
        $bodega->update($request->only('sede_id', 'nombre'));
        return redirect()->route('empresas.index', ['tab' => 'bodega'])->with('success', 'Bodega actualizada.');
    }

    public function destroyBodega(Bodega $bodega): RedirectResponse
    {
        $bodega->delete();
        return redirect()->route('empresas.index', ['tab' => 'bodega'])->with('success', 'Bodega eliminada.');
    }

    public function exportExcel(Request $request)
    {
        $tab = $request->get('tab', 'empresa');
        $headers = [];
        $rows = [];
        $titulo = '';

        if ($tab === 'empresa') {
            $titulo = 'Empresas';
            $headers = ['#', 'Nombre', 'País'];
            foreach (Empresa::orderBy('nombre')->get() as $i => $e) {
                $rows[] = [$i + 1, $e->nombre, $e->pais ?? ''];
            }
        } elseif ($tab === 'sede') {
            $titulo = 'Sedes';
            $headers = ['#', 'Nombre', 'País', 'Municipio', 'Departamento', 'Empresa'];
            foreach (Sede::with('empresa')->orderBy('nombre')->get() as $i => $s) {
                $rows[] = [$i + 1, $s->nombre, $s->pais ?? '', $s->municipio ?? '', $s->departamento ?? '', $s->empresa->nombre ?? ''];
            }
        } else {
            $titulo = 'Bodegas';
            $headers = ['#', 'Nombre', 'Sede', 'Ubicación'];
            foreach (Bodega::with('sede.empresa')->orderBy('nombre')->get() as $i => $b) {
                $ubicacion = $b->sede ? trim(implode(', ', array_filter([$b->sede->municipio, $b->sede->departamento, $b->sede->pais]))) : '';
                $rows[] = [$i + 1, $b->nombre, $b->sede->nombre ?? '', $ubicacion];
            }
        }

        return ExcelHelper::downloadTable('empresas_' . $tab, $titulo, $headers, $rows);
    }

    public function exportPdf(Request $request)
    {
        $tab = $request->get('tab', 'empresa');
        $empresas = Empresa::orderBy('nombre')->get();
        $sedes = Sede::with('empresa')->orderBy('nombre')->get();
        $bodegas = Bodega::with('sede.empresa')->orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();

        $pdf = Pdf::loadView('empresas.pdf', array_merge(compact('tab', 'empresas', 'sedes', 'bodegas'), $logos));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('empresas_' . $tab . '_' . date('Y-m-d') . '.pdf');
    }
}
