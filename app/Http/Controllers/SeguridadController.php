<?php

namespace App\Http\Controllers;

use App\Models\SeguridadCodigo;
use App\Models\SeguridadPermiso;
use App\Models\PermisoCodigoBloqueado;
use App\Models\Usuario;
use App\Models\Role;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class SeguridadController extends Controller
{
    /**
     * Muestra el apartado de seguridad con el candado.
     * Solo accesible para administradores (mega_admin o rol Administrador).
     */
    public function index(): View
    {
        $user = session('sams2_user');
        
        // Verificar que sea administrador
        $esAdmin = false;
        if (($user['role'] ?? null) === 'mega_admin') {
            $esAdmin = true;
        } elseif (isset($user['role_id'])) {
            $role = Role::find($user['role_id']);
            if ($role && strtolower($role->nombre) === 'administrador') {
                $esAdmin = true;
            }
        }
        
        if (!$esAdmin) {
            abort(403, 'Solo los administradores pueden acceder a esta sección.');
        }
        
        $seguridad = SeguridadCodigo::obtener();
        $puedeEditar = SeguridadCodigo::puedeEditar();
        
        // Obtener permisos para mostrar en la lista
        $permisos = SeguridadPermiso::with(['usuario', 'role'])
            ->where('puede_editar', true)
            ->get();

        // Contar personas con permisos (solo usuarios, no roles)
        $totalPersonasConPermiso = SeguridadPermiso::where('puede_editar', true)
            ->whereNotNull('usuario_id')
            ->count();

        // Obtener usuarios y roles para el selector (solo si es mega admin o administrador)
        $usuarios = [];
        $roles = [];
        $esAdmin = false;
        if (($user['role'] ?? null) === 'mega_admin') {
            $esAdmin = true;
        } elseif (isset($user['role_id'])) {
            $role = Role::find($user['role_id']);
            if ($role && strtolower($role->nombre) === 'administrador') {
                $esAdmin = true;
            }
        }
        
        if ($esAdmin) {
            $usuarios = Usuario::where('activo', true)->orderBy('nombre')->get();
            $roles = Role::orderBy('nombre')->get();
        }

        // Obtener todos los permisos de códigos bloqueados (tanto permitidos como bloqueados)
        $permisosCodigo = PermisoCodigoBloqueado::with(['usuario', 'role'])
            ->get();

        // Obtener lista de equipos para el selector
        $equipos = Equipo::with(['empresa'])
            ->orderBy('codigo')
            ->get()
            ->map(function($equipo) {
                return [
                    'id' => $equipo->id,
                    'codigo' => $equipo->codigo,
                    'nombre' => $equipo->nombre,
                    'empresa' => $equipo->empresa->nombre ?? '',
                    'display' => $equipo->codigo . ' - ' . $equipo->nombre . ' (' . ($equipo->empresa->nombre ?? 'Sin empresa') . ')'
                ];
            });

        return view('seguridad.index', compact('seguridad', 'puedeEditar', 'permisos', 'usuarios', 'roles', 'user', 'permisosCodigo', 'totalPersonasConPermiso', 'equipos'));
    }

    /**
     * Actualiza el código de seguridad (solo si tiene permiso o contraseña correcta).
     */
    public function updateCodigo(Request $request): RedirectResponse
    {
        $user = session('sams2_user');
        $puedeEditar = SeguridadCodigo::puedeEditar();
        
        // Si no tiene permiso, verificar contraseña
        if (!$puedeEditar) {
            $request->validate([
                'password_edicion' => 'required|string',
            ]);
            
            $seguridad = SeguridadCodigo::obtener();
            
            if (!$seguridad->password_edicion || !Hash::check($request->password_edicion, $seguridad->password_edicion)) {
                return redirect()->route('seguridad.index')
                    ->with('error', 'Contraseña incorrecta.');
            }
            
            // Contraseña correcta, generar nueva automáticamente
            $nuevaPassword = bin2hex(random_bytes(8)); // Generar contraseña aleatoria de 16 caracteres
            $seguridad->update([
                'password_edicion' => Hash::make($nuevaPassword),
            ]);
        }

        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'codigo' => 'required|string',
        ]);

        // Obtener el equipo
        $equipo = Equipo::findOrFail($request->equipo_id);
        
        // Verificar que el código no exista en otra empresa
        $existe = Equipo::where('codigo', $request->codigo)
            ->where('empresa_id', $equipo->empresa_id)
            ->where('id', '!=', $equipo->id)
            ->exists();
            
        if ($existe) {
            return redirect()->route('seguridad.index')
                ->with('error', 'El código ya existe para esta empresa.');
        }

        // Actualizar el código del equipo
        $equipo->update([
            'codigo' => $request->codigo,
        ]);

        return redirect()->route('seguridad.index')
            ->with('success', 'Código del equipo actualizado correctamente.');
    }

    /**
     * Establece o actualiza la contraseña de edición (solo mega admin).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = session('sams2_user');
        
        // Solo mega admin puede establecer la contraseña
        if (($user['role'] ?? null) !== 'mega_admin') {
            return redirect()->route('seguridad.index')
                ->with('error', 'Solo el super administrador puede establecer la contraseña.');
        }

        $request->validate([
            'password_edicion' => 'required|string|min:4',
        ]);

        $seguridad = SeguridadCodigo::obtener();
        $seguridad->update([
            'password_edicion' => Hash::make($request->password_edicion),
        ]);

        return redirect()->route('seguridad.index')
            ->with('success', 'Contraseña de edición actualizada correctamente.');
    }

    /**
     * Bloquea/desbloquea el código (solo si tiene permiso).
     */
    public function toggleBloqueo(Request $request): RedirectResponse
    {
        $puedeEditar = SeguridadCodigo::puedeEditar();
        
        if (!$puedeEditar) {
            return redirect()->route('seguridad.index')
                ->with('error', 'No tienes permiso para cambiar el estado del candado.');
        }

        $seguridad = SeguridadCodigo::obtener();
        $seguridad->update([
            'bloqueado' => $request->has('bloqueado'),
        ]);

        $mensaje = $seguridad->bloqueado ? 'Código bloqueado.' : 'Código desbloqueado.';
        
        return redirect()->route('seguridad.index')
            ->with('success', $mensaje);
    }

    /**
     * Agrega un permiso para que un usuario o rol pueda editar el código.
     */
    public function agregarPermiso(Request $request): RedirectResponse
    {
        $user = session('sams2_user');
        
        // Solo mega admin puede agregar permisos
        if (($user['role'] ?? null) !== 'mega_admin') {
            return redirect()->route('seguridad.index')
                ->with('error', 'Solo el super administrador puede gestionar permisos.');
        }

        $request->validate([
            'tipo' => 'required|in:usuario,role',
            'usuario_id' => 'required_if:tipo,usuario|exists:usuarios,id',
            'role_id' => 'required_if:tipo,role|exists:roles,id',
        ]);

        // Verificar que no exista ya
        $existe = SeguridadPermiso::where(function($q) use ($request) {
            if ($request->tipo === 'usuario') {
                $q->where('usuario_id', $request->usuario_id)
                  ->whereNull('role_id');
            } else {
                $q->where('role_id', $request->role_id)
                  ->whereNull('usuario_id');
            }
        })->first();

        if ($existe) {
            return redirect()->route('seguridad.index')
                ->with('error', 'Este permiso ya existe.');
        }

        // Crear el permiso de seguridad
        $permiso = SeguridadPermiso::create([
            'usuario_id' => $request->tipo === 'usuario' ? $request->usuario_id : null,
            'role_id' => $request->tipo === 'role' ? $request->role_id : null,
            'puede_editar' => true,
        ]);

        // Si es un usuario (no rol), automáticamente crear permiso para desbloquear códigos
        if ($request->tipo === 'usuario' && $request->usuario_id) {
            // Verificar que no exista ya el permiso de código
            $existeCodigo = PermisoCodigoBloqueado::where('usuario_id', $request->usuario_id)
                ->whereNull('role_id')
                ->first();

            if (!$existeCodigo) {
                PermisoCodigoBloqueado::create([
                    'usuario_id' => $request->usuario_id,
                    'role_id' => null,
                    'puede_desbloquear_codigo' => true,
                ]);
            } else {
                // Si existe pero está bloqueado, actualizarlo a permitido
                $existeCodigo->update(['puede_desbloquear_codigo' => true]);
            }
        }

        $mensaje = $request->tipo === 'usuario' 
            ? 'Permiso agregado correctamente. La persona ahora puede editar y desbloquear códigos.'
            : 'Permiso agregado al rol correctamente.';

        return redirect()->route('seguridad.index')
            ->with('success', $mensaje);
    }

    /**
     * Elimina un permiso.
     */
    public function eliminarPermiso(SeguridadPermiso $permiso): RedirectResponse
    {
        $user = session('sams2_user');
        
        // Solo mega admin puede eliminar permisos
        if (($user['role'] ?? null) !== 'mega_admin') {
            return redirect()->route('seguridad.index')
                ->with('error', 'Solo el super administrador puede eliminar permisos.');
        }

        // Si es un permiso de usuario, también eliminar el permiso de código bloqueado
        if ($permiso->usuario_id) {
            $permisoCodigo = PermisoCodigoBloqueado::where('usuario_id', $permiso->usuario_id)
                ->whereNull('role_id')
                ->first();
            
            if ($permisoCodigo) {
                $permisoCodigo->delete();
            }
        }

        $permiso->delete();

        return redirect()->route('seguridad.index')
            ->with('success', 'Permiso eliminado correctamente. La persona ya no podrá editar ni desbloquear códigos.');
    }

    /**
     * Agrega un permiso para que un usuario o rol pueda desbloquear códigos.
     */
    public function agregarPermisoCodigo(Request $request): RedirectResponse
    {
        $user = session('sams2_user');
        
        // Solo mega admin y administradores pueden agregar permisos
        if (($user['role'] ?? null) !== 'mega_admin') {
            $role = null;
            if (isset($user['role_id'])) {
                $role = Role::find($user['role_id']);
            }
            if (!$role || strtolower($role->nombre) !== 'administrador') {
                return redirect()->route('seguridad.index')
                    ->with('error', 'Solo los administradores pueden gestionar permisos de códigos.');
            }
        }

        $request->validate([
            'tipo_codigo' => 'required|in:usuario,role',
            'usuario_id_codigo' => 'required_if:tipo_codigo,usuario|exists:usuarios,id',
            'role_id_codigo' => 'required_if:tipo_codigo,role|exists:roles,id',
        ]);

        // Verificar que no exista ya
        $existe = PermisoCodigoBloqueado::where(function($q) use ($request) {
            if ($request->tipo_codigo === 'usuario') {
                $q->where('usuario_id', $request->usuario_id_codigo)
                  ->whereNull('role_id');
            } else {
                $q->where('role_id', $request->role_id_codigo)
                  ->whereNull('usuario_id');
            }
        })->first();

        if ($existe) {
            return redirect()->route('seguridad.index')
                ->with('error', 'Este permiso de código ya existe.');
        }

        PermisoCodigoBloqueado::create([
            'usuario_id' => $request->tipo_codigo === 'usuario' ? $request->usuario_id_codigo : null,
            'role_id' => $request->tipo_codigo === 'role' ? $request->role_id_codigo : null,
            'puede_desbloquear_codigo' => true,
        ]);

        $mensaje = $request->tipo_codigo === 'usuario' 
            ? 'Permiso de código agregado al usuario correctamente.'
            : 'Permiso de código agregado al rol correctamente.';

        return redirect()->route('seguridad.index')
            ->with('success', $mensaje);
    }

    /**
     * Elimina un permiso de código.
     */
    public function eliminarPermisoCodigo(PermisoCodigoBloqueado $permisoCodigo): RedirectResponse
    {
        $user = session('sams2_user');
        
        // Solo mega admin y administradores pueden eliminar permisos
        if (($user['role'] ?? null) !== 'mega_admin') {
            $role = null;
            if (isset($user['role_id'])) {
                $role = Role::find($user['role_id']);
            }
            if (!$role || strtolower($role->nombre) !== 'administrador') {
                return redirect()->route('seguridad.index')
                    ->with('error', 'Solo los administradores pueden eliminar permisos de códigos.');
            }
        }

        $permisoCodigo->delete();

        return redirect()->route('seguridad.index')
            ->with('success', 'Permiso de código eliminado correctamente.');
    }

    /**
     * Bloquea códigos para todos los usuarios excepto administradores.
     */
    public function bloquearParaTodos(Request $request): RedirectResponse
    {
        $user = session('sams2_user');
        
        // Solo mega admin y administradores pueden bloquear para todos
        if (($user['role'] ?? null) !== 'mega_admin') {
            $role = null;
            if (isset($user['role_id'])) {
                $role = Role::find($user['role_id']);
            }
            if (!$role || strtolower($role->nombre) !== 'administrador') {
                return redirect()->route('seguridad.index')
                    ->with('error', 'Solo los administradores pueden bloquear códigos para todos.');
            }
        }

        // Obtener el rol de administrador
        $rolAdministrador = Role::whereRaw('LOWER(nombre) = ?', ['administrador'])->first();
        
        // Obtener todos los usuarios activos que NO sean administradores
        $usuariosNoAdmin = Usuario::where('activo', true)
            ->where(function($q) use ($rolAdministrador) {
                if ($rolAdministrador) {
                    $q->where('role_id', '!=', $rolAdministrador->id)
                      ->orWhereNull('role_id');
                }
            })
            ->get();

        $agregados = 0;
        $yaExistentes = 0;

        foreach ($usuariosNoAdmin as $usuario) {
            // Verificar si ya tiene permiso
            $existe = PermisoCodigoBloqueado::where('usuario_id', $usuario->id)
                ->whereNull('role_id')
                ->first();

            if (!$existe) {
                PermisoCodigoBloqueado::create([
                    'usuario_id' => $usuario->id,
                    'role_id' => null,
                    'puede_desbloquear_codigo' => false, // false = bloqueado
                ]);
                $agregados++;
            } else {
                // Actualizar el permiso existente a bloqueado
                $existe->update(['puede_desbloquear_codigo' => false]);
                $yaExistentes++;
            }
        }

        $mensaje = "Se han bloqueado los códigos para {$agregados} usuarios. ";
        if ($yaExistentes > 0) {
            $mensaje .= "{$yaExistentes} usuarios ya tenían permisos configurados y fueron actualizados.";
        }

        return redirect()->route('seguridad.index')
            ->with('success', $mensaje);
    }
}
