<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use App\Helpers\PermisoHelper;

class ConfiguracionController extends Controller
{
    /**
     * Muestra la página principal de personalizaciones con pestañas.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'redes-sociales');
        
        // Redes sociales
        $redesSociales = Cache::get('sistema_redes_sociales', []);
        if (!empty($redesSociales) && isset($redesSociales['facebook'])) {
            $redesSociales = [];
        }

        // Configuración de login
        $configLogin = Cache::get('sistema_config_login', [
            'titulo_principal' => 'Sistema de Gestión SAMS',
            'descripcion_principal' => 'La solución integral para la gestión de equipos, usuarios y recursos empresariales. Tecnología moderna, interfaz intuitiva y máxima seguridad.',
            'texto_boton_login' => 'Iniciar Sesión',
            'texto_boton_info' => 'Ver Información',
            'texto_boton_registro' => 'Registrarse',
            'texto_ayuda' => '¿Problemas para acceder? Contacta al administrador del sistema.',
        ]);

        // Pie de página
        $piePagina = Cache::get('sistema_pie_pagina', [
            'descripcion' => 'Estamos construyendo la nueva experiencia en el trabajo. Sistema de gestión avanzado con tecnología moderna y diseño profesional.',
            'enlace_concepto' => '#',
            'texto_concepto' => 'Concepto',
            'enlace_quienes_somos' => '#',
            'texto_quienes_somos' => '¿Quiénes somos?',
            'enlace_faq' => '#',
            'texto_faq' => 'FAQ',
            'texto_aviso_legal' => 'Aviso legal',
            'enlace_aviso_legal' => '#',
            'texto_terminos' => 'Términos y condiciones de uso',
            'enlace_terminos' => '#',
            'texto_privacidad' => 'Política de privacidad',
            'enlace_privacidad' => '#',
            'texto_cookies' => 'Gestionar cookies',
            'enlace_cookies' => '#',
            'texto_copyright' => 'Todos los derechos reservados',
        ]);

        // Encabezado
        $encabezado = Cache::get('sistema_encabezado', [
            'texto_nombre_empresa' => config('app.name'),
        ]);

        // Otros textos
        $otrosTextos = Cache::get('sistema_otros_textos', [
            'texto_bienvenida' => 'Bienvenido',
            'texto_dashboard' => 'Panel de Control',
        ]);

        // Obtener usuarios activos para el selector (solo si es admin)
        $usuarios = [];
        $user = session('sams2_user');
        $esAdmin = false;
        if (($user['role'] ?? null) === 'mega_admin') {
            $esAdmin = true;
        } elseif (isset($user['role_id'])) {
            $role = \App\Models\Role::find($user['role_id']);
            if ($role && strtolower($role->nombre) === 'administrador') {
                $esAdmin = true;
            }
        }
        
        // Variables para redes sociales
        $nombreUsuario = $user['name'] ?? 'Usuario';
        $imagenUsuario = null;
        $telefonoUsuario = null;
        $userIdActual = $user['id'] ?? '';
        
        // Obtener imagen y teléfono del usuario actual
        if (($user['id'] ?? null) !== 'mega_admin' && isset($user['id'])) {
            $usuarioDB = \App\Models\Usuario::find($user['id']);
            if ($usuarioDB) {
                $telefonoUsuario = $usuarioDB->telefono;
                if ($usuarioDB->imagen_usuario) {
                    $imagenPath = trim($usuarioDB->imagen_usuario);
                    if (str_starts_with($imagenPath, 'http://') || str_starts_with($imagenPath, 'https://')) {
                        $imagenUsuario = $imagenPath;
                    } elseif (str_starts_with($imagenPath, 'storage/')) {
                        $imagenUsuario = asset('storage/' . str_replace('storage/', '', $imagenPath));
                    } elseif (str_starts_with($imagenPath, 'public/img/')) {
                        $imagenUsuario = asset(str_replace('public/', '', $imagenPath));
                    } elseif (str_starts_with($imagenPath, 'img/')) {
                        $imagenUsuario = asset($imagenPath);
                    } else {
                        $imagenUsuario = asset($imagenPath);
                    }
                }
            }
        }
        
        // Verificar si puede agregar "Yo"
        $puedeAgregarYo = PermisoHelper::puede('redes_sociales', 'acceso') || PermisoHelper::puede('redes_sociales', 'agregar') || PermisoHelper::puede('redes_sociales', 'editar') || $esAdmin;
        
        if ($esAdmin) {
            $usuarios = \App\Models\Usuario::where('activo', true)
                ->orderBy('nombre')
                ->orderBy('apellidos')
                ->get(['id', 'nombre', 'apellidos', 'telefono', 'imagen_usuario'])
                ->map(function($u) {
                    $imagenUrl = null;
                    if ($u->imagen_usuario) {
                        $imagenPath = trim($u->imagen_usuario);
                        // Si ya es una URL completa (http/https), usarla directamente
                        if (str_starts_with($imagenPath, 'http://') || str_starts_with($imagenPath, 'https://')) {
                            $imagenUrl = $imagenPath;
                        }
                        // Si empieza con storage/, usar asset('storage/...')
                        elseif (str_starts_with($imagenPath, 'storage/')) {
                            $imagenUrl = asset('storage/' . str_replace('storage/', '', $imagenPath));
                        }
                        // Si empieza con public/img/, usar asset directamente
                        elseif (str_starts_with($imagenPath, 'public/img/')) {
                            $imagenUrl = asset(str_replace('public/', '', $imagenPath));
                        }
                        // Si empieza con img/, usar asset directamente
                        elseif (str_starts_with($imagenPath, 'img/')) {
                            $imagenUrl = asset($imagenPath);
                        }
                        // Para cualquier otra ruta, intentar con asset
                        else {
                            $imagenUrl = asset($imagenPath);
                        }
                    }
                    return [
                        'id' => $u->id,
                        'nombre' => $u->nombre . ' ' . $u->apellidos,
                        'telefono' => $u->telefono,
                        'imagen' => $imagenUrl, // URL completa para mostrar en el frontend
                        'imagen_ruta' => $u->imagen_usuario, // Ruta relativa original para guardar
                    ];
                });
        }

        return view('configuracion.index', compact(
            'tab', 'redesSociales', 'configLogin', 'piePagina', 'encabezado', 'otrosTextos', 'usuarios',
            'nombreUsuario', 'imagenUsuario', 'telefonoUsuario', 'userIdActual', 'esAdmin', 'puedeAgregarYo'
        ));
    }

    /**
     * Muestra la página de configuración de redes sociales (legacy).
     */
    public function redesSociales(): View
    {
        return $this->index(request()->merge(['tab' => 'redes-sociales']));
    }

    /**
     * Guarda la configuración de redes sociales.
     */
    public function storeRedesSociales(Request $request): RedirectResponse
    {
        // Guardar redes sociales dinámicas
        $redesSocialesArray = [];
        $redesInput = $request->input('redes_sociales', []);
        
        \Log::info('Datos recibidos en storeRedesSociales', [
            'redes_input' => $redesInput,
            'all_input' => $request->all()
        ]);
        
        foreach ($redesInput as $index => $red) {
            if (!empty($red['nombre'])) {
                $esWhatsApp = ($red['icono_svg'] ?? '') === 'whatsapp';
                
                // Para WhatsApp, siempre usar whatsapp://
                // Para otras redes, usar la URL proporcionada (puede estar vacía)
                $url = trim($red['url'] ?? '');
                
                // Si es WhatsApp, forzar whatsapp://
                if ($esWhatsApp) {
                    $url = 'whatsapp://';
                } else {
                    // Si no es WhatsApp y tiene whatsapp://, limpiarlo
                    if ($url === 'whatsapp://') {
                        $url = '';
                    }
                    // Guardar la URL tal como está (puede estar vacía o tener una URL válida)
                    // No hacer nada más, solo usar el valor tal cual
                }
                
                $redData = [
                    'nombre' => trim($red['nombre']),
                    'url' => $url,
                    'tipo_icono' => $red['tipo_icono'] ?? 'svg',
                    'icono_svg' => $red['icono_svg'] ?? '',
                ];
                
                \Log::info('Procesando red social', [
                    'index' => $index,
                    'nombre' => $redData['nombre'],
                    'url_original' => $red['url'] ?? 'null',
                    'url_final' => $redData['url'],
                    'esWhatsApp' => $esWhatsApp,
                    'icono_svg' => $redData['icono_svg']
                ]);
                
                // Si es WhatsApp, guardar números
                if ($esWhatsApp && !empty($red['numeros_whatsapp'])) {
                    $numerosJson = is_string($red['numeros_whatsapp']) ? $red['numeros_whatsapp'] : json_encode($red['numeros_whatsapp']);
                    $numeros = json_decode($numerosJson, true) ?: [];
                    $redData['numeros_whatsapp'] = array_filter($numeros, function($n) {
                        return !empty($n['numero']);
                    });
                    // Asegurar que todos los números tengan los campos necesarios
                    foreach ($redData['numeros_whatsapp'] as &$num) {
                        $num['nombre'] = $num['nombre'] ?? '';
                        $num['descripcion'] = $num['descripcion'] ?? '';
                        
                        // Normalizar la ruta de la imagen: convertir URLs completas a rutas relativas
                        $imagenPath = trim($num['imagen'] ?? '');
                        if (!empty($imagenPath)) {
                            // Si es una URL completa (http/https), mantenerla (para imágenes externas)
                            if (str_starts_with($imagenPath, 'http://') || str_starts_with($imagenPath, 'https://')) {
                                // Si es una URL de la misma aplicación, extraer la ruta relativa
                                $baseUrl = url('/');
                                if (str_starts_with($imagenPath, $baseUrl)) {
                                    $num['imagen'] = str_replace($baseUrl . '/', '', $imagenPath);
                                } else {
                                    // Es una URL externa, mantenerla
                                    $num['imagen'] = $imagenPath;
                                }
                            }
                            // Si contiene la URL base de la aplicación (sin http/https), extraer solo la ruta relativa
                            elseif (str_contains($imagenPath, url('/'))) {
                                $baseUrl = url('/');
                                $num['imagen'] = str_replace($baseUrl . '/', '', $imagenPath);
                            }
                            // Si ya es una ruta relativa (empieza con img/, storage/, etc.), mantenerla
                            else {
                                $num['imagen'] = $imagenPath;
                            }
                        } else {
                            $num['imagen'] = '';
                        }
                        
                        $num['es_yo'] = isset($num['es_yo']) ? (bool)$num['es_yo'] : false;
                    }
                    unset($num);
                }
                
                // Si hay un icono personalizado subido
                if ($request->hasFile("redes_sociales.{$index}.icono_imagen")) {
                    $file = $request->file("redes_sociales.{$index}.icono_imagen");
                    $filename = 'red_social_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $dir = public_path('img/redes-sociales');
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $file->move($dir, $filename);
                    $redData['icono_imagen'] = 'img/redes-sociales/' . $filename;
                    $redData['tipo_icono'] = 'imagen';
                } elseif (!empty($red['icono_imagen_existente'])) {
                    $redData['icono_imagen'] = $red['icono_imagen_existente'];
                    $redData['tipo_icono'] = 'imagen';
                }
                
                $redesSocialesArray[] = $redData;
            }
        }
        
        Cache::forever('sistema_redes_sociales', $redesSocialesArray);

        return redirect()
            ->route('configuracion.index', ['tab' => 'redes-sociales'])
            ->with('success', 'Redes sociales actualizadas correctamente.');
    }

    /**
     * Muestra la página de configuración de login (legacy).
     */
    public function configuracionLogin(): View
    {
        return $this->index(request()->merge(['tab' => 'login']));
    }

    /**
     * Guarda la configuración de login.
     */
    public function storeConfiguracionLogin(Request $request): RedirectResponse
    {
        // Guardar configuración de login
        $configLogin = [
            'titulo_principal' => $request->input('login_titulo_principal', 'Sistema de Gestión SAMS'),
            'descripcion_principal' => $request->input('login_descripcion_principal', 'La solución integral para la gestión de equipos, usuarios y recursos empresariales. Tecnología moderna, interfaz intuitiva y máxima seguridad.'),
            'texto_boton_login' => $request->input('login_texto_boton_login', 'Iniciar Sesión'),
            'texto_boton_info' => $request->input('login_texto_boton_info', 'Ver Información'),
            'texto_boton_registro' => $request->input('login_texto_boton_registro', 'Registrarse'),
            'texto_ayuda' => $request->input('login_texto_ayuda', '¿Problemas para acceder? Contacta al administrador del sistema.'),
        ];
        Cache::forever('sistema_config_login', $configLogin);

        return redirect()
            ->route('configuracion.index', ['tab' => 'login'])
            ->with('success', 'Configuración de login actualizada correctamente.');
    }

    /**
     * Guarda todas las configuraciones desde el formulario principal.
     */
    public function store(Request $request): RedirectResponse
    {
        $tab = $request->input('tab', 'redes-sociales');

        // Guardar según la pestaña activa
        switch ($tab) {
            case 'redes-sociales':
                return $this->storeRedesSociales($request);
            case 'login':
                return $this->storeConfiguracionLogin($request);
            case 'pie-pagina':
                return $this->storePiePagina($request);
            case 'encabezado':
                return $this->storeEncabezado($request);
            case 'otros-textos':
                return $this->storeOtrosTextos($request);
            default:
                return redirect()->route('configuracion.index')->with('error', 'Pestaña no válida.');
        }
    }

    /**
     * Guarda la configuración del pie de página.
     */
    public function storePiePagina(Request $request): RedirectResponse
    {
        $piePagina = [
            'descripcion' => trim($request->input('pie_descripcion', '')),
            'enlace_concepto' => trim($request->input('pie_enlace_concepto', '')),
            'texto_concepto' => trim($request->input('pie_texto_concepto', '')),
            'enlace_quienes_somos' => trim($request->input('pie_enlace_quienes_somos', '')),
            'texto_quienes_somos' => trim($request->input('pie_texto_quienes_somos', '')),
            'enlace_faq' => trim($request->input('pie_enlace_faq', '')),
            'texto_faq' => trim($request->input('pie_texto_faq', '')),
            'texto_aviso_legal' => trim($request->input('pie_texto_aviso_legal', '')),
            'enlace_aviso_legal' => trim($request->input('pie_enlace_aviso_legal', '')),
            'texto_terminos' => trim($request->input('pie_texto_terminos', '')),
            'enlace_terminos' => trim($request->input('pie_enlace_terminos', '')),
            'texto_privacidad' => trim($request->input('pie_texto_privacidad', '')),
            'enlace_privacidad' => trim($request->input('pie_enlace_privacidad', '')),
            'texto_cookies' => trim($request->input('pie_texto_cookies', '')),
            'enlace_cookies' => trim($request->input('pie_enlace_cookies', '')),
            'texto_copyright' => trim($request->input('pie_texto_copyright', '')),
        ];
        Cache::forever('sistema_pie_pagina', $piePagina);

        return redirect()
            ->route('configuracion.index', ['tab' => 'pie-pagina'])
            ->with('success', 'Pie de página actualizado correctamente.');
    }

    /**
     * Guarda la configuración del encabezado.
     */
    public function storeEncabezado(Request $request): RedirectResponse
    {
        $encabezado = [
            'texto_nombre_empresa' => $request->input('encabezado_texto_nombre_empresa', config('app.name')),
        ];
        Cache::forever('sistema_encabezado', $encabezado);

        return redirect()
            ->route('configuracion.index', ['tab' => 'encabezado'])
            ->with('success', 'Encabezado actualizado correctamente.');
    }

    /**
     * Guarda otros textos del sistema.
     */
    public function storeOtrosTextos(Request $request): RedirectResponse
    {
        $otrosTextos = [
            'texto_bienvenida' => $request->input('otros_texto_bienvenida', 'Bienvenido'),
            'texto_dashboard' => $request->input('otros_texto_dashboard', 'Panel de Control'),
        ];
        Cache::forever('sistema_otros_textos', $otrosTextos);

        return redirect()
            ->route('configuracion.index', ['tab' => 'otros-textos'])
            ->with('success', 'Otros textos actualizados correctamente.');
    }
}
