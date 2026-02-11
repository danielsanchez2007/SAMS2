<?php

namespace App\Http\Controllers;

use App\Mail\CodigoRecuperacionMail;
use App\Models\PasswordResetCode;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportException;

class AuthController extends Controller
{
    /**
     * Credenciales del Mega Admin (integrado en código).
     */
    private const MEGA_ADMIN_USERNAME = 'megadmin';
    private const MEGA_ADMIN_PASSWORD = '1079176426';
    private const MEGA_ADMIN_ROLE = 'mega_admin';

    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function showLoginForm()
    {
        if (session()->has('sams2_user')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión.
     * Mega admin: usuario megadmin, contraseña 1079176426 (acceso a todo).
     */
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ], [
            'usuario.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = $request->input('usuario');
        $password = $request->input('password');

        // Mega Admin: credenciales integradas en código
        if ($usuario === self::MEGA_ADMIN_USERNAME && $password === self::MEGA_ADMIN_PASSWORD) {
            session([
                'sams2_user' => [
                    'id' => 'mega_admin',
                    'role' => self::MEGA_ADMIN_ROLE,
                    'role_id' => null,
                    'name' => 'Mega Admin',
                    'identifier' => $usuario,
                    'permisos' => null, // null = acceso total
                ],
            ]);
            return redirect()->intended(route('dashboard'))->with('success', 'Sesión iniciada correctamente.');
        }

        // Validar usuarios de la base de datos
        $usuarioDB = Usuario::with('role')->where('username', $usuario)->first();
        
        if ($usuarioDB && $usuarioDB->activo && Hash::check($password, $usuarioDB->password)) {
            session([
                'sams2_user' => [
                    'id' => $usuarioDB->id,
                    'role' => 'usuario',
                    'role_id' => $usuarioDB->role_id,
                    'name' => $usuarioDB->nombre . ' ' . $usuarioDB->apellidos,
                    'identifier' => $usuarioDB->username,
                    'permisos' => $usuarioDB->role?->permisos ?? [],
                    'imagen' => $usuarioDB->imagen_usuario,
                ],
            ]);
            return redirect()->intended(route('dashboard'))->with('success', 'Sesión iniciada correctamente.');
        }

        throw ValidationException::withMessages([
            'usuario' => ['Las credenciales no son correctas o el usuario está inactivo.'],
        ]);
    }

    /**
     * Cierra la sesión.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('sams2_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Sesión cerrada.');
    }

    /**
     * Muestra el formulario "Olvidé mi contraseña" (solo correo).
     */
    public function showForgotPasswordForm()
    {
        if (session()->has('sams2_user')) {
            return redirect()->route('dashboard');
        }
        return view('auth.forgot-password');
    }

    /**
     * Envía el código de recuperación al correo si existe en el sistema.
     * Por seguridad siempre muestra el mismo mensaje (no revelar si el correo existe).
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no es válido.',
        ]);

        $email = $request->input('email');

        $usuario = Usuario::where('correo_electronico', $email)
            ->orWhere('correo_corporativo', $email)
            ->where('activo', true)
            ->first();

        $devCode = null;
        if ($usuario) {
            // Invalidar códigos anteriores para este correo
            PasswordResetCode::where('email', $email)->delete();

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            PasswordResetCode::create([
                'email' => $email,
                'code' => $code,
                'expires_at' => now()->addMinutes(60),
            ]);

            try {
                Mail::to($email)->send(new CodigoRecuperacionMail($code, config('app.name')));
            } catch (TransportException $e) {
                // Gmail rechazó usuario/contraseña (ej. no es contraseña de aplicación): mostramos el código para no bloquear al usuario
                $devCode = $code;
            }

            // Si el correo no se envía (driver log/array), mostrar el código en la siguiente pantalla para poder probar
            $driver = config('mail.default');
            if (in_array($driver, ['log', 'array'], true)) {
                $devCode = $code;
            }
        }

        $redirect = redirect()->route('password.reset')->with('email_sent', $email)->with('success', 'Si el correo está registrado en el sistema, recibirás un código para restablecer tu contraseña. Revisa tu bandeja de entrada (y carpeta spam).');
        if ($devCode !== null) {
            $redirect->with('dev_code', $devCode)->with('mail_error', 'No se pudo enviar el correo. Comprueba en .env que MAIL_PASSWORD sea una contraseña de aplicación de Gmail (no la contraseña normal).');
        }
        return $redirect;
    }

    /**
     * Muestra el formulario para ingresar código y nueva contraseña.
     */
    public function showResetPasswordForm(Request $request)
    {
        if (session()->has('sams2_user')) {
            return redirect()->route('dashboard');
        }
        $email = session('email_sent') ?? $request->query('email', old('email'));
        $devCode = session('dev_code');
        if ($devCode !== null) {
            session()->forget('dev_code'); // Mostrar solo una vez
        }
        return view('auth.reset-password', ['email' => $email, 'dev_code' => $devCode]);
    }

    /**
     * Restablece la contraseña con el código recibido por correo.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'El correo es obligatorio.',
            'code.required' => 'El código es obligatorio.',
            'code.size' => 'El código debe tener 6 dígitos.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $record = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$record) {
            return redirect()->back()->withInput($request->only('email'))->withErrors(['code' => 'El código no es válido o ha expirado.']);
        }

        if ($record->isExpired()) {
            $record->delete();
            return redirect()->back()->withInput($request->only('email'))->withErrors(['code' => 'El código ha expirado. Solicita uno nuevo.']);
        }

        $usuario = Usuario::where('correo_electronico', $request->email)
            ->orWhere('correo_corporativo', $request->email)
            ->first();

        if (!$usuario) {
            return redirect()->back()->withErrors(['email' => 'No se encontró el usuario.']);
        }

        $usuario->update(['password' => Hash::make($request->password)]);
        $record->delete();

        return redirect()->route('login')->with('success', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
    }

    /**
     * Muestra el formulario de registro público.
     */
    public function showRegisterForm()
    {
        if (session()->has('sams2_user')) {
            return redirect()->route('dashboard');
        }
        
        // Obtener datos necesarios para el formulario
        $ubicacion = config('sams2_ubicacion', []);
        $empresas = \App\Models\Empresa::orderBy('nombre')->get();
        $sedes = \App\Models\Sede::orderBy('nombre')->get();
        $grupos = \App\Models\Grupo::orderBy('nombre')->get();
        $cargos = \App\Models\Cargo::orderBy('nombre')->get();
        
        return view('auth.register', compact('ubicacion', 'empresas', 'sedes', 'grupos', 'cargos'));
    }

    /**
     * Procesa el registro de un nuevo usuario.
     * Asigna automáticamente el rol "cliente".
     */
    public function register(Request $request)
    {
        // Buscar el rol "cliente"
        $rolCliente = \App\Models\Role::where('nombre', 'Cliente')
            ->orWhere('nombre', 'cliente')
            ->orWhere('nombre', 'Cliente')
            ->first();
        
        if (!$rolCliente) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'El rol "Cliente" no existe en el sistema. Contacta al administrador.']);
        }

        $rules = [
            'tipo_documento' => 'required|string',
            'cedula' => 'required|string|unique:usuarios,cedula',
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'correo_electronico' => 'required|email|max:255|unique:usuarios,correo_electronico',
            'departamento' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:255',
            'empresa_id' => 'nullable|exists:empresas,id',
            'sede_id' => 'nullable|exists:sedes,id',
            'grupo_id' => 'nullable|exists:grupos,id',
            'cargo_id' => 'nullable|exists:cargos,id',
            'username' => 'required|string|unique:usuarios,username',
            'password' => 'required|string|min:6|confirmed',
            'imagen_usuario' => 'required|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:10240',
            'firma_imagen' => 'required|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg|max:10240',
        ];

        $validated = $request->validate($rules);
        
        $data = $request->except(['password_confirmation', 'imagen_usuario', 'firma_imagen']);
        $data['password'] = Hash::make($request->password);
        $data['activo'] = true; // Los usuarios registrados quedan activos
        $data['role_id'] = $rolCliente->id; // Asignar rol "Cliente"
        $data['tiene_correo_corporativo'] = $request->has('tiene_correo_corporativo');
        $data['tiene_telefono_corporativo'] = $request->has('tiene_telefono_corporativo');
        
        // Crear directorios si no existen
        $dirImagenes = public_path('img/logos/perfiles/imagenes');
        $dirFirmas = public_path('img/logos/perfiles/firmas');
        if (!file_exists($dirImagenes)) {
            mkdir($dirImagenes, 0755, true);
        }
        if (!file_exists($dirFirmas)) {
            mkdir($dirFirmas, 0755, true);
        }
        
        // Guardar imagen de usuario
        if ($request->hasFile('imagen_usuario')) {
            $file = $request->file('imagen_usuario');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dirImagenes, $filename);
            $data['imagen_usuario'] = 'img/logos/perfiles/imagenes/' . $filename;
        }
        
        // Guardar firma imagen
        if ($request->hasFile('firma_imagen')) {
            $file = $request->file('firma_imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dirFirmas, $filename);
            $data['firma_imagen'] = 'img/logos/perfiles/firmas/' . $filename;
        }
        
        Usuario::create($data);
        
        return redirect()->route('login')
            ->with('success', '¡Registro exitoso! Ya puedes iniciar sesión con tus credenciales.');
    }
}
