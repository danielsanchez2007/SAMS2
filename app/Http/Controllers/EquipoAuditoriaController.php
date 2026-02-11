<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\TipoItem;
use App\Models\TipoEquipo;
use App\Models\EstadoRemision;
use App\Models\Proveedor;
use App\Models\Fabricante;
use App\Models\Empresa;
use App\Models\Sede;
use App\Models\Bodega;
use App\Models\UsoItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipoAuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        $empresaId = $request->get('empresa_id');
        $sedeId = $request->get('sede_id');
        $bodegaId = $request->get('bodega_id');

        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $equipos = Equipo::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'empresa', 'sede', 'bodega', 'usoItem'])
            ->where('tipo_registro', 'auditoria')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%")
                      ->orWhere('marca', 'like', "%{$search}%");
                });
            })
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->when($sedeId, function ($q) use ($sedeId) {
                $q->where('sede_id', $sedeId);
            })
            ->when($bodegaId, function ($q) use ($bodegaId) {
                $q->where('bodega_id', $bodegaId);
            })
            ->orderBy('codigo')
            ->paginate($perPage)
            ->withQueryString();

        foreach ($equipos as $equipo) {
            $equipo->imagen_url = \App\Http\Controllers\EquipoController::getImagenUrl($equipo);
        }

        $tipoItems = TipoItem::orderBy('nombre')->get();
        $tipoEquipos = TipoEquipo::orderBy('nombre')->get();
        $estadoRemisiones = EstadoRemision::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();
        $fabricantes = Fabricante::orderBy('nombre')->get();
        $empresas = Empresa::orderBy('nombre')->get();

        $sedes = $empresaId
            ? Sede::where('empresa_id', $empresaId)->orderBy('nombre')->get()
            : Sede::orderBy('nombre')->get();

        $bodegas = $sedeId
            ? Bodega::where('sede_id', $sedeId)->orderBy('nombre')->get()
            : Bodega::orderBy('nombre')->get();

        $usoItems = UsoItem::orderBy('nombre')->get();

        $tab = 'auditoria';

        // Verificar si el usuario puede desbloquear códigos
        $user = session('sams2_user');
        $puedeDesbloquearCodigo = \App\Models\PermisoCodigoBloqueado::puedeDesbloquear(
            $user['id'] ?? null, 
            $user['role_id'] ?? null
        );

        return view('equipos.index', compact(
            'equipos', 'search', 'perPage', 'tipoItems', 'tipoEquipos', 'estadoRemisiones',
            'proveedores', 'fabricantes', 'empresas', 'sedes', 'bodegas', 'usoItems', 'tab',
            'empresaId', 'sedeId', 'bodegaId', 'puedeDesbloquearCodigo'
        ));
    }
}

