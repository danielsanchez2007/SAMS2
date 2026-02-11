<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) $perPage = 10;
        $modulos = config('sams2_modulos', []);

        $roles = Role::query()
            ->when($search, fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return view('roles.index', compact('roles', 'search', 'modulos', 'perPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:roles,nombre']);
        $permisos = $this->normalizePermisos($request->input('permisos', []));
        Role::create([
            'nombre' => $request->nombre,
            'permisos' => $permisos,
        ]);
        return redirect()->route('roles.index')->with('success', 'Rol creado.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:roles,nombre,' . $role->id]);
        $permisos = $this->normalizePermisos($request->input('permisos', []));
        $role->update([
            'nombre' => $request->nombre,
            'permisos' => $permisos,
        ]);
        return redirect()->route('roles.index')->with('success', 'Rol actualizado.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado.');
    }

    public function exportExcel()
    {
        $modulos = config('sams2_modulos', []);
        $headers = ['#', 'Nombre', 'Módulos con acceso'];
        $rows = [];
        foreach (Role::orderBy('nombre')->get() as $i => $r) {
            $lista = $this->resumenPermisos($r->permisos ?? [], $modulos);
            $rows[] = [$i + 1, $r->nombre, $lista];
        }
        return ExcelHelper::downloadTable('roles', 'Roles', $headers, $rows);
    }

    public function exportPdf()
    {
        $roles = Role::orderBy('nombre')->get();
        $modulos = config('sams2_modulos', []);
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('roles.pdf', array_merge(compact('roles', 'modulos'), $logos));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('roles_' . date('Y-m-d') . '.pdf');
    }

    private function normalizePermisos(array $input): array
    {
        $modulos = array_keys(config('sams2_modulos', []));
        $out = [];
        foreach ($modulos as $key) {
            $p = $input[$key] ?? [];
            $out[$key] = [
                'acceso' => !empty($p['acceso']),
                'agregar' => !empty($p['agregar']),
                'editar' => !empty($p['editar']),
                'eliminar' => !empty($p['eliminar']),
            ];
        }
        return $out;
    }

    private function resumenPermisos(array $permisos, array $modulos): string
    {
        $partes = [];
        foreach ($permisos as $key => $p) {
            if (empty($p['acceso'])) {
                continue;
            }
            $nombre = $modulos[$key] ?? $key;
            $acciones = array_filter([
                !empty($p['agregar']) ? 'Agregar' : null,
                !empty($p['editar']) ? 'Editar' : null,
                !empty($p['eliminar']) ? 'Eliminar' : null,
            ]);
            $partes[] = $nombre . (count($acciones) ? ' (' . implode(', ', $acciones) . ')' : '');
        }
        return implode('; ', $partes) ?: '—';
    }
}
