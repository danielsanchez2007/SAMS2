<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Sede;
use App\Models\Grupo;
use App\Models\Cargo;
use App\Models\Role;
use App\Helpers\LogoHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ExcelHelper;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $usuarios = Usuario::with(['empresa', 'sede', 'grupo', 'cargo', 'role'])
            ->when($search, function($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellidos', 'like', "%{$search}%")
                      ->orWhere('cedula', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        $empresas = Empresa::orderBy('nombre')->get();
        $sedes = Sede::orderBy('nombre')->get();
        $grupos = Grupo::orderBy('nombre')->get();
        $cargos = Cargo::orderBy('nombre')->get();
        $roles = Role::orderBy('nombre')->get();
        $ubicacion = config('ubicacion', []);
        
        // Verificar si el usuario actual puede cambiar roles
        $user = session('sams2_user');
        $puedeCambiarRol = false;
        if (($user['role'] ?? null) === 'mega_admin') {
            $puedeCambiarRol = true;
        } elseif (isset($user['role_id'])) {
            $role = Role::find($user['role_id']);
            if ($role && strtolower($role->nombre) === 'administrador') {
                $puedeCambiarRol = true;
            }
        }

        return view('usuarios.index', compact('usuarios', 'search', 'perPage', 'empresas', 'sedes', 'grupos', 'cargos', 'roles', 'ubicacion', 'puedeCambiarRol'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'tipo_documento' => 'required|string',
            'cedula' => 'required|string|unique:usuarios,cedula',
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'required|string|max:500',
            'telefono' => 'required|string|max:20',
            'correo_electronico' => 'required|email|max:255|unique:usuarios,correo_electronico',
            'departamento' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'empresa_id' => 'required|exists:empresas,id',
            'sede_id' => 'required|exists:sedes,id',
            'grupo_id' => 'required|exists:grupos,id',
            'cargo_id' => 'required|exists:cargos,id',
            'role_id' => 'required|exists:roles,id',
            'username' => 'required|string|unique:usuarios,username',
            'password' => 'required|string|min:6|confirmed',
            'imagen_usuario' => 'required|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:10240',
            'firma_imagen' => 'required|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:10240',
        ];

        $validated = $request->validate($rules);
        
        $data = $request->except(['password_confirmation', 'imagen_usuario', 'firma_imagen']);
        $data['password'] = Hash::make($request->password);
        $data['activo'] = $request->has('activo');
        $data['tiene_correo_corporativo'] = $request->has('tiene_correo_corporativo');
        $data['tiene_telefono_corporativo'] = $request->has('tiene_telefono_corporativo');
        
        // Guardar imagen de usuario
        if ($request->hasFile('imagen_usuario')) {
            $file = $request->file('imagen_usuario');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/logos/perfiles/imagenes'), $filename);
            $data['imagen_usuario'] = 'img/logos/perfiles/imagenes/' . $filename;
        }
        
        // Guardar firma imagen
        if ($request->hasFile('firma_imagen')) {
            $file = $request->file('firma_imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/logos/perfiles/firmas'), $filename);
            $data['firma_imagen'] = 'img/logos/perfiles/firmas/' . $filename;
        }
        
        Usuario::create($data);
        
        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $user = session('sams2_user');
        $puedeCambiarRol = false;
        
        // Verificar si el usuario actual puede cambiar roles
        if (($user['role'] ?? null) === 'mega_admin') {
            $puedeCambiarRol = true;
        } elseif (isset($user['role_id'])) {
            $role = Role::find($user['role_id']);
            if ($role && strtolower($role->nombre) === 'administrador') {
                $puedeCambiarRol = true;
            }
        }
        
        $rules = [
            'tipo_documento' => 'required|string',
            'cedula' => 'required|string|unique:usuarios,cedula,' . $usuario->id,
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'required|string|max:500',
            'telefono' => 'required|string|max:20',
            'correo_electronico' => 'required|email|max:255|unique:usuarios,correo_electronico,' . $usuario->id,
            'departamento' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'empresa_id' => 'required|exists:empresas,id',
            'sede_id' => 'required|exists:sedes,id',
            'grupo_id' => 'required|exists:grupos,id',
            'cargo_id' => 'required|exists:cargos,id',
            'username' => 'required|string|unique:usuarios,username,' . $usuario->id,
        ];
        
        // Solo validar role_id si el usuario tiene permiso para cambiarlo
        if ($puedeCambiarRol) {
            $rules['role_id'] = 'required|exists:roles,id';
        }

        // Si el usuario no tiene foto o firma, hacerlas obligatorias
        // Aumentado a 10MB (10240 KB) para permitir imágenes pesadas
        if (empty($usuario->imagen_usuario)) {
            $rules['imagen_usuario'] = 'required|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:10240';
        } else {
            $rules['imagen_usuario'] = 'nullable|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:10240';
        }

        if (empty($usuario->firma_imagen)) {
            $rules['firma_imagen'] = 'required|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:10240';
        } else {
            $rules['firma_imagen'] = 'nullable|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:10240';
        }

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        $validated = $request->validate($rules);
        
        $data = $request->except(['password', 'password_confirmation', 'imagen_usuario', 'firma_imagen']);
        
        // Solo permitir cambiar el rol si el usuario tiene permiso
        if (!$puedeCambiarRol) {
            unset($data['role_id']);
        }
        
        $data['activo'] = $request->has('activo');
        $data['tiene_correo_corporativo'] = $request->has('tiene_correo_corporativo');
        $data['tiene_telefono_corporativo'] = $request->has('tiene_telefono_corporativo');
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        // Actualizar imagen de usuario
        if ($request->hasFile('imagen_usuario')) {
            // Eliminar imagen anterior si existe
            if ($usuario->imagen_usuario && file_exists(public_path($usuario->imagen_usuario))) {
                unlink(public_path($usuario->imagen_usuario));
            }
            // Guardar en public/img/logos/perfiles/imagenes
            $file = $request->file('imagen_usuario');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/logos/perfiles/imagenes'), $filename);
            $data['imagen_usuario'] = 'img/logos/perfiles/imagenes/' . $filename;
        }
        
        // Actualizar firma imagen
        if ($request->hasFile('firma_imagen')) {
            // Eliminar firma anterior si existe
            if ($usuario->firma_imagen && file_exists(public_path($usuario->firma_imagen))) {
                unlink(public_path($usuario->firma_imagen));
            }
            // Guardar en public/img/logos/perfiles/firmas
            $file = $request->file('firma_imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/logos/perfiles/firmas'), $filename);
            $data['firma_imagen'] = 'img/logos/perfiles/firmas/' . $filename;
        }
        
        $usuario->update($data);
        
        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Restablece la contraseña de un usuario (solo para administradores).
     */
    public function resetPassword(Request $request, Usuario $usuario): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Contraseña restablecida correctamente para ' . $usuario->nombre . ' ' . $usuario->apellidos . '.');
    }

    public function exportExcel()
    {
        $headers = ['#', 'Cédula', 'Nombre', 'Apellidos', 'Username', 'Empresa', 'Cargo', 'Estado'];
        $rows = [];
        foreach (Usuario::with(['empresa', 'cargo'])->orderBy('nombre')->get() as $i => $u) {
            $rows[] = [
                $i + 1,
                $u->cedula,
                $u->nombre,
                $u->apellidos,
                $u->username,
                $u->empresa?->nombre ?? '—',
                $u->cargo?->nombre ?? '—',
                $u->activo ? 'Activo' : 'Inactivo',
            ];
        }
        return ExcelHelper::downloadTable('usuarios', 'Usuarios', $headers, $rows);
    }

    public function exportPdf()
    {
        $usuarios = Usuario::with(['empresa', 'cargo'])->orderBy('nombre')->get();
        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('usuarios.pdf', array_merge(compact('usuarios'), $logos));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('usuarios_' . date('Y-m-d') . '.pdf');
    }
}
