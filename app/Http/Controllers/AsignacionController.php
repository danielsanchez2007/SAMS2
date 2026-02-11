<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsignacionController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q', '');
        $usuarioId = $request->get('usuario_id', '');
        $equipoId = $request->get('equipo_id', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $query = Asignacion::with(['usuario', 'equipo.tipoItem', 'equipo.tipoEquipo', 'equipo.estadoRemision']);

        if ($usuarioId !== '') {
            $query->where('usuario_id', $usuarioId);
        }
        if ($equipoId !== '') {
            $query->where('equipo_id', $equipoId);
        }
        if ($q !== '') {
            $query->where(function ($qry) use ($q) {
                $qry->whereHas('usuario', function ($u) use ($q) {
                    $u->where('nombre', 'like', "%{$q}%")
                      ->orWhere('apellidos', 'like', "%{$q}%")
                      ->orWhere('cedula', 'like', "%{$q}%");
                })->orWhereHas('equipo', function ($e) use ($q) {
                    $e->where('codigo', 'like', "%{$q}%")
                      ->orWhere('descripcion', 'like', "%{$q}%");
                });
            });
        }

        $asignaciones = $query->orderByDesc('fecha_asignacion')->paginate($perPage)->withQueryString();

        // Añadir URL de imagen a cada equipo para la vista
        foreach ($asignaciones as $a) {
            $a->equipo->imagen_url = \App\Http\Controllers\EquipoController::getImagenUrl($a->equipo);
        }

        $usuarios = Usuario::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'apellidos', 'imagen_usuario']);
        $equiposYaAsignados = Asignacion::pluck('equipo_id')->toArray();
        $equiposDisponibles = Equipo::whereNotIn('id', $equiposYaAsignados)->orderBy('codigo')->get();
        foreach ($equiposDisponibles as $eq) {
            $eq->imagen_url = \App\Http\Controllers\EquipoController::getImagenUrl($eq);
        }
        $equiposAsignadosParaFiltro = Equipo::whereIn('id', $equiposYaAsignados)->orderBy('codigo')->get(['id', 'codigo']);
        $reasignarUsuarioId = $request->get('reasignar_usuario', '');

        return view('asignar.index', compact('asignaciones', 'usuarios', 'equiposDisponibles', 'equiposAsignadosParaFiltro', 'q', 'usuarioId', 'equipoId', 'perPage', 'reasignarUsuarioId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'usuario_id' => 'required|exists:usuarios,id',
        ], [
            'equipo_id.required' => 'Selecciona un ítem (equipo).',
            'usuario_id.required' => 'Selecciona el usuario a cargo.',
        ]);

        $existe = Asignacion::where('equipo_id', $request->equipo_id)->first();
        if ($existe) {
            return redirect()->route('asignar.index')->with('error', 'Ese ítem ya está asignado a otro usuario. Quita la asignación anterior primero.');
        }

        Asignacion::create([
            'equipo_id' => $request->equipo_id,
            'usuario_id' => $request->usuario_id,
        ]);

        return redirect()->route('asignar.index')->with('success', 'Asignación creada correctamente.');
    }

    public function storeMulti(Request $request): RedirectResponse
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'equipo_ids' => 'required|array',
            'equipo_ids.*' => 'exists:equipos,id',
        ], [
            'usuario_id.required' => 'Falta el usuario.',
            'equipo_ids.required' => 'Selecciona al menos un ítem.',
        ]);

        $creados = 0;
        foreach ($request->equipo_ids as $eid) {
            if (Asignacion::where('equipo_id', $eid)->exists()) {
                continue;
            }
            Asignacion::create([
                'equipo_id' => $eid,
                'usuario_id' => $request->usuario_id,
            ]);
            $creados++;
        }

        if ($creados === 0) {
            return redirect()->route('asignar.index')->with('error', 'Ningún ítem nuevo asignado (ya estaban asignados o no seleccionaste ninguno).');
        }

        return redirect()->route('asignar.index')->with('success', $creados === 1 ? '1 asignación creada.' : "{$creados} asignaciones creadas.");
    }

    public function update(Request $request, Asignacion $asignacion): RedirectResponse
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
        ], [
            'usuario_id.required' => 'Selecciona el usuario a cargo.',
        ]);

        $asignacion->update(['usuario_id' => $request->usuario_id]);

        return redirect()->route('asignar.index')->with('success', 'Asignación actualizada. Usuario a cargo modificado.');
    }

    public function destroy(Asignacion $asignacion): RedirectResponse
    {
        $asignacion->delete();
        return redirect()->route('asignar.index')->with('success', 'Asignación eliminada.');
    }
}
