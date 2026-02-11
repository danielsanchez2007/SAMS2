<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PerfilController extends Controller
{
    /**
     * Campos que el usuario puede editar en su perfil (sin rol, empresa, sede, grupo, cargo, username, password, activo).
     */
    private const PERFIL_FILLABLE = [
        'tipo_documento', 'cedula', 'nombre', 'apellidos', 'fecha_nacimiento',
        'direccion', 'telefono', 'correo_electronico',
        'tiene_correo_corporativo', 'correo_corporativo',
        'tiene_telefono_corporativo', 'telefono_corporativo',
        'departamento', 'departamento_otro', 'municipio', 'municipio_otro',
        'tratamiento', 'imagen_usuario', 'firma_imagen',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $userSession = session('sams2_user');
        if (!$userSession) {
            return redirect()->route('login');
        }

        // Mega Admin: no tiene perfil en BD
        if (($userSession['id'] ?? null) === 'mega_admin') {
            return view('perfil.index', ['usuario' => null, 'esMegaAdmin' => true]);
        }

        $usuario = Usuario::with('role')->find($userSession['id']);
        if (!$usuario) {
            session()->forget('sams2_user');
            return redirect()->route('login');
        }

        $ubicacion = config('ubicacion', []);
        return view('perfil.index', ['usuario' => $usuario, 'esMegaAdmin' => false, 'ubicacion' => $ubicacion]);
    }

    public function update(Request $request): RedirectResponse
    {
        $userSession = session('sams2_user');
        if (!$userSession || ($userSession['id'] ?? null) === 'mega_admin') {
            return redirect()->route('perfil')->with('error', 'No puedes actualizar este perfil.');
        }

        $usuario = Usuario::find($userSession['id']);
        if (!$usuario) {
            return redirect()->route('perfil')->with('error', 'Usuario no encontrado.');
        }

        $rules = [
            'tipo_documento' => 'required|string',
            'cedula' => 'required|string|unique:usuarios,cedula,' . $usuario->id,
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
        ];

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

        $request->validate($rules);

        $data = $request->only(self::PERFIL_FILLABLE);
        $data['tiene_correo_corporativo'] = $request->has('tiene_correo_corporativo');
        $data['tiene_telefono_corporativo'] = $request->has('tiene_telefono_corporativo');
        unset($data['imagen_usuario'], $data['firma_imagen']);

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

        // Actualizar sesión para nombre e imagen (para que el header muestre la foto al instante)
        $sessionUser = session('sams2_user');
        $sessionUser['name'] = $usuario->nombre . ' ' . $usuario->apellidos;
        $sessionUser['imagen'] = $usuario->imagen_usuario;
        session(['sams2_user' => $sessionUser]);

        return redirect()->route('perfil')->with('success', 'Perfil actualizado correctamente.');
    }
}
