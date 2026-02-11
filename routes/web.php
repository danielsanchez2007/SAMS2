<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\EstadoRemisionController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoEquipoController;
use App\Http\Controllers\TipoItemController;
use App\Http\Controllers\EquipoAuditoriaController;
use App\Http\Controllers\UsoItemController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    if (session()->has('sams2_user')) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::get('/informacion', function () {
    return view('informacion');
})->name('informacion');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/password/forgot', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot');
Route::post('/password/forgot', [AuthController::class, 'sendResetCode'])->name('password.forgot.send');
Route::get('/password/reset', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset.submit');

// Ruta pública para imágenes de equipos (las etiquetas img necesitan carga sin auth)
Route::get('/equipos/{equipo}/imagen', [\App\Http\Controllers\EquipoController::class, 'imagen'])->name('equipos.imagen');

Route::middleware(['sams2.auth'])->group(function () {

    // Rutas accesibles para TODOS los usuarios autenticados (dashboard, ayuda, perfil)
    Route::get('/dashboard', function () {
        $user = session('sams2_user');
        $userId = $user['id'] ?? null;
        $esAdmin = false;
        $esMegaAdmin = ($user['role'] ?? null) === 'mega_admin';
        
        if (!$esMegaAdmin && isset($user['role_id'])) {
            $role = \App\Models\Role::find($user['role_id']);
            $esAdmin = $role && strtolower($role->nombre) === 'administrador';
        }
        
        // Estadísticas generales
        try {
            $stats = [
                'usuarios' => \App\Models\Usuario::count(),
                'usuarios_activos' => \App\Models\Usuario::where('activo', true)->count(),
                'equipos' => \App\Models\Equipo::count(),
                'asignaciones' => \App\Models\Asignacion::count(),
                'empresas' => \App\Models\Empresa::count(),
                'grupos' => \App\Models\Grupo::count(),
                'cargos' => \App\Models\Cargo::count(),
                'proveedores' => \App\Models\Proveedor::count(),
                'fabricantes' => \App\Models\Fabricante::count(),
            ];
        } catch (\Exception $e) {
            \Log::error('Error obteniendo estadísticas del dashboard: ' . $e->getMessage());
            $stats = [
                'usuarios' => 0,
                'usuarios_activos' => 0,
                'equipos' => 0,
                'asignaciones' => 0,
                'empresas' => 0,
                'grupos' => 0,
                'cargos' => 0,
                'proveedores' => 0,
                'fabricantes' => 0,
            ];
        }
        
        // Datos para gráficas
        try {
            $equiposPorTipo = \App\Models\Equipo::with('tipoEquipo')
                ->selectRaw('tipo_equipo_id, COUNT(*) as total')
                ->groupBy('tipo_equipo_id')
                ->get()
                ->map(function($item) {
                    return [
                        'label' => $item->tipoEquipo->nombre ?? 'Sin tipo',
                        'value' => $item->total
                    ];
                });
        } catch (\Exception $e) {
            \Log::error('Error en equiposPorTipo: ' . $e->getMessage());
            $equiposPorTipo = collect([]);
        }
        
        try {
            $usuariosPorRol = \App\Models\Usuario::with('role')
                ->selectRaw('role_id, COUNT(*) as total')
                ->groupBy('role_id')
                ->get()
                ->map(function($item) {
                    return [
                        'label' => $item->role->nombre ?? 'Sin rol',
                        'value' => $item->total
                    ];
                });
        } catch (\Exception $e) {
            \Log::error('Error en usuariosPorRol: ' . $e->getMessage());
            $usuariosPorRol = collect([]);
        }
        
        try {
            $asignacionesPorMes = \App\Models\Asignacion::selectRaw('MONTH(fecha_asignacion) as mes, COUNT(*) as total')
                ->whereYear('fecha_asignacion', date('Y'))
                ->whereNotNull('fecha_asignacion')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get()
                ->map(function($item) {
                    $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    return [
                        'label' => $meses[$item->mes] ?? 'Mes ' . $item->mes,
                        'value' => $item->total
                    ];
                });
        } catch (\Exception $e) {
            \Log::error('Error en asignacionesPorMes: ' . $e->getMessage());
            $asignacionesPorMes = collect([]);
        }
        
        // Si no hay datos, crear array vacío con todos los meses
        if ($asignacionesPorMes->isEmpty()) {
            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $asignacionesPorMes = collect($meses)->map(function($mes) {
                return ['label' => $mes, 'value' => 0];
            });
        }
        
        // Datos recientes para mini tablas
        try {
            $usuariosRecientes = \App\Models\Usuario::with('role', 'empresa')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error en usuariosRecientes: ' . $e->getMessage());
            $usuariosRecientes = collect([]);
        }
        
        try {
            $equiposRecientes = \App\Models\Equipo::with('tipoEquipo', 'empresa')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error en equiposRecientes: ' . $e->getMessage());
            $equiposRecientes = collect([]);
        }
        
        try {
            $asignacionesRecientes = \App\Models\Asignacion::with('usuario', 'equipo')
                ->orderBy('fecha_asignacion', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error en asignacionesRecientes: ' . $e->getMessage());
            $asignacionesRecientes = collect([]);
        }
        
        return view('dashboard', compact('stats', 'equiposPorTipo', 'usuariosPorRol', 'asignacionesPorMes', 'usuariosRecientes', 'equiposRecientes', 'asignacionesRecientes', 'esAdmin', 'esMegaAdmin'));
    })->name('dashboard');
    Route::get('/ayuda', fn () => view('pages.placeholder', ['title' => 'Ayuda', 'description' => 'Centro de ayuda. Contenido en desarrollo.']))->name('ayuda');
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

    // Personalización de sistema (solo megadmin)
    Route::middleware(['sams2.mega_admin'])->prefix('personalizacion')->name('personalizacion.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PersonalizacionController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\PersonalizacionController::class, 'store'])->name('store');
    });

    // Seguridad (solo administradores - mega_admin o rol Administrador)
    Route::middleware(['sams2.admin'])->group(function () {
    Route::get('/seguridad', [\App\Http\Controllers\SeguridadController::class, 'index'])->name('seguridad.index');
    Route::post('/seguridad/codigo', [\App\Http\Controllers\SeguridadController::class, 'updateCodigo'])->name('seguridad.update-codigo');
    Route::post('/seguridad/password', [\App\Http\Controllers\SeguridadController::class, 'updatePassword'])->name('seguridad.update-password');
    Route::post('/seguridad/bloqueo', [\App\Http\Controllers\SeguridadController::class, 'toggleBloqueo'])->name('seguridad.toggle-bloqueo');
    Route::post('/seguridad/permisos', [\App\Http\Controllers\SeguridadController::class, 'agregarPermiso'])->name('seguridad.agregar-permiso');
    Route::delete('/seguridad/permisos/{permiso}', [\App\Http\Controllers\SeguridadController::class, 'eliminarPermiso'])->name('seguridad.eliminar-permiso');
    Route::post('/seguridad/permisos-codigo', [\App\Http\Controllers\SeguridadController::class, 'agregarPermisoCodigo'])->name('seguridad.agregar-permiso-codigo');
    Route::post('/seguridad/bloquear-para-todos', [\App\Http\Controllers\SeguridadController::class, 'bloquearParaTodos'])->name('seguridad.bloquear-para-todos');
    Route::delete('/seguridad/permisos-codigo/{permisoCodigo}', [\App\Http\Controllers\SeguridadController::class, 'eliminarPermisoCodigo'])->name('seguridad.eliminar-permiso-codigo');
    });

    // Menú Usuarios (requiere permiso del módulo)
    Route::middleware(['sams2.module:usuarios'])->group(function () {
    Route::get('/usuarios', [\App\Http\Controllers\UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [\App\Http\Controllers\UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{usuario}', [\App\Http\Controllers\UsuarioController::class, 'update'])->name('usuarios.update');
    Route::post('/usuarios/{usuario}/reset-password', [\App\Http\Controllers\UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');
    Route::delete('/usuarios/{usuario}', [\App\Http\Controllers\UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    Route::get('/usuarios/export/excel', [\App\Http\Controllers\UsuarioController::class, 'exportExcel'])->name('usuarios.export.excel');
    Route::get('/usuarios/export/pdf', [\App\Http\Controllers\UsuarioController::class, 'exportPdf'])->name('usuarios.export.pdf');
    });
    
    Route::middleware(['sams2.module:roles'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('/roles/export/excel', [RoleController::class, 'exportExcel'])->name('roles.export.excel');
    Route::get('/roles/export/pdf', [RoleController::class, 'exportPdf'])->name('roles.export.pdf');
    });

    Route::middleware(['sams2.module:cargos'])->group(function () {
    Route::get('/cargos', [CargoController::class, 'index'])->name('cargos.index');
    Route::post('/cargos', [CargoController::class, 'store'])->name('cargos.store');
    Route::put('/cargos/{cargo}', [CargoController::class, 'update'])->name('cargos.update');
    Route::delete('/cargos/{cargo}', [CargoController::class, 'destroy'])->name('cargos.destroy');
    Route::get('/cargos/export/excel', [CargoController::class, 'exportExcel'])->name('cargos.export.excel');
    Route::get('/cargos/export/pdf', [CargoController::class, 'exportPdf'])->name('cargos.export.pdf');
    });

    Route::middleware(['sams2.module:grupos'])->group(function () {
    Route::get('/grupos', [GrupoController::class, 'index'])->name('grupos.index');
    Route::post('/grupos', [GrupoController::class, 'store'])->name('grupos.store');
    Route::put('/grupos/{grupo}', [GrupoController::class, 'update'])->name('grupos.update');
    Route::delete('/grupos/{grupo}', [GrupoController::class, 'destroy'])->name('grupos.destroy');
    Route::get('/grupos/export/excel', [GrupoController::class, 'exportExcel'])->name('grupos.export.excel');
    Route::get('/grupos/export/pdf', [GrupoController::class, 'exportPdf'])->name('grupos.export.pdf');
    });

    // Menú Usos - Equipos (requiere permiso equipos)
    Route::middleware(['sams2.module:equipos'])->group(function () {
    Route::get('/equipos', [\App\Http\Controllers\EquipoController::class, 'index'])->name('equipos.index');
    Route::get('/equipos/debaja', [\App\Http\Controllers\EquipoController::class, 'indexDebaja'])->name('equipos.debaja');
    Route::get('/equipos/material-didactico', [\App\Http\Controllers\EquipoController::class, 'indexMaterialDidactico'])->name('equipos.material-didactico');
    Route::get('/equipos/auditoria', [EquipoAuditoriaController::class, 'index'])->name('equipos.auditoria');
    Route::get('/equipos/{equipo}/almacen', [\App\Http\Controllers\EquipoController::class, 'almacen'])->name('equipos.almacen');
    Route::post('/equipos/{equipo}/imagen', [\App\Http\Controllers\EquipoController::class, 'reemplazarImagen'])->name('equipos.imagen.reemplazar');
    Route::post('/equipos', [\App\Http\Controllers\EquipoController::class, 'store'])->name('equipos.store');
    Route::put('/equipos/{equipo}', [\App\Http\Controllers\EquipoController::class, 'update'])->name('equipos.update');
    Route::post('/equipos/verificar-password-codigo', [\App\Http\Controllers\EquipoController::class, 'verificarPasswordCodigo'])->name('equipos.verificar-password-codigo');
    Route::post('/equipos/{equipo}/traspasar', [\App\Http\Controllers\EquipoController::class, 'traspasar'])->name('equipos.traspasar');
    Route::get('/equipos/codigos-disponibles', [\App\Http\Controllers\EquipoController::class, 'codigosDisponibles'])->name('equipos.codigos-disponibles');
    Route::get('/equipos/ultimo-codigo', [\App\Http\Controllers\EquipoController::class, 'getUltimoCodigo'])->name('equipos.ultimo-codigo');
    Route::get('/equipos/ultimo-traspasado', [\App\Http\Controllers\EquipoController::class, 'getUltimoEquipoTraspasado'])->name('equipos.ultimo-traspasado');
    Route::post('/equipos/crear-con-codigo', [\App\Http\Controllers\EquipoController::class, 'crearConCodigoDisponible'])->name('equipos.crear-con-codigo');
    Route::delete('/equipos/{equipo}', [\App\Http\Controllers\EquipoController::class, 'destroy'])->name('equipos.destroy');
    Route::get('/equipos/export/tipos-items', [\App\Http\Controllers\EquipoController::class, 'exportTiposItems'])->name('equipos.export.tipos-items');
    Route::get('/equipos/export/tipos-equipos', [\App\Http\Controllers\EquipoController::class, 'exportTiposEquipos'])->name('equipos.export.tipos-equipos');
    Route::get('/equipos/export/preview-data', [\App\Http\Controllers\EquipoController::class, 'exportPreviewData'])->name('equipos.export.preview-data');
    Route::get('/equipos/export/excel', [\App\Http\Controllers\EquipoController::class, 'exportExcel'])->name('equipos.export.excel');
    Route::get('/equipos/export/pdf', [\App\Http\Controllers\EquipoController::class, 'exportPdf'])->name('equipos.export.pdf');
    Route::get('/equipos/{equipo}/hoja-vida-datos', [\App\Http\Controllers\EquipoController::class, 'hojaVidaDatos'])->name('equipos.hoja-vida.datos');
    Route::get('/equipos/{equipo}/hoja-vida', [\App\Http\Controllers\EquipoController::class, 'hojaVidaPdf'])->name('equipos.hoja-vida.pdf');
    Route::post('/equipos/{equipo}/hoja-vida', [\App\Http\Controllers\EquipoController::class, 'hojaVidaPdf'])->name('equipos.hoja-vida.pdf.post');
    Route::post('/equipos/importar-pdf', [\App\Http\Controllers\EquipoController::class, 'importarPdf'])->name('equipos.importar-pdf');
    Route::get('/equipos/{equipo}/pdfs-almacen', [\App\Http\Controllers\EquipoController::class, 'getPdfsAlmacen'])->name('equipos.pdfs-almacen');
    Route::post('/equipos/asignar-pdf-tipo-equipo', [\App\Http\Controllers\EquipoController::class, 'asignarPdfTipoEquipo'])->name('equipos.asignar-pdf-tipo-equipo');
    Route::post('/equipos/reemplazar-pdf-tipo-equipo', [\App\Http\Controllers\EquipoController::class, 'reemplazarPdfTipoEquipo'])->name('equipos.reemplazar-pdf-tipo-equipo');
    Route::post('/equipos/desasignar-pdf-tipo-equipo', [\App\Http\Controllers\EquipoController::class, 'desasignarPdfTipoEquipo'])->name('equipos.desasignar-pdf-tipo-equipo');
    Route::post('/equipos/eliminar-pdf', [\App\Http\Controllers\EquipoController::class, 'eliminarPdf'])->name('equipos.eliminar-pdf');
    Route::match(['get', 'post'], '/equipos/{equipo}/exportar-pdf-plantilla', [\App\Http\Controllers\EquipoController::class, 'exportarPdfPlantilla'])->name('equipos.exportar-pdf-plantilla');
    Route::match(['get', 'post'], '/equipos/{equipo}/exportar-pdf-plantilla-html', [\App\Http\Controllers\EquipoController::class, 'exportarPdfPlantillaHtml'])->name('equipos.exportar-pdf-plantilla-html');
    Route::get('/almacen', [\App\Http\Controllers\AlmacenController::class, 'index'])->name('almacen.index');
    Route::post('/almacen/etiqueta', [\App\Http\Controllers\AlmacenController::class, 'storeEtiqueta'])->name('almacen.store.etiqueta');
    Route::post('/almacen/imagen', [\App\Http\Controllers\AlmacenController::class, 'storeImagen'])->name('almacen.store.imagen');
    Route::post('/almacen/archivo', [\App\Http\Controllers\AlmacenController::class, 'storeArchivo'])->name('almacen.store.archivo');
    Route::get('/almacen/imagen/{imagen}/descargar', [\App\Http\Controllers\AlmacenController::class, 'downloadImagen'])->name('almacen.download.imagen');
    Route::get('/almacen/archivo/{archivo}/descargar', [\App\Http\Controllers\AlmacenController::class, 'downloadArchivo'])->name('almacen.download.archivo');
    Route::delete('/almacen/imagen/{imagen}', [\App\Http\Controllers\AlmacenController::class, 'destroyImagen'])->name('almacen.destroy.imagen');
    Route::delete('/almacen/archivo/{archivo}', [\App\Http\Controllers\AlmacenController::class, 'destroyArchivo'])->name('almacen.destroy.archivo');
    });

    Route::middleware(['sams2.module:tipo_equipos'])->group(function () {
    Route::get('/tipo-equipos', [TipoEquipoController::class, 'index'])->name('tipo-equipos.index');
    Route::post('/tipo-equipos', [TipoEquipoController::class, 'store'])->name('tipo-equipos.store');
    Route::put('/tipo-equipos/{tipoEquipo}', [TipoEquipoController::class, 'update'])->name('tipo-equipos.update');
    Route::delete('/tipo-equipos/{tipoEquipo}', [TipoEquipoController::class, 'destroy'])->name('tipo-equipos.destroy');
    Route::post('/tipo-equipos/{tipoEquipo}/formato', [TipoEquipoController::class, 'uploadFormato'])->name('tipo-equipos.formato.upload');
    Route::delete('/tipo-equipos/{tipoEquipo}/formato', [TipoEquipoController::class, 'destroyFormato'])->name('tipo-equipos.formato.destroy');
    Route::get('/tipo-equipos/{tipoEquipo}/formato', [TipoEquipoController::class, 'showFormato'])->name('tipo-equipos.formato.show');
    Route::get('/tipo-equipos/export/preview', [TipoEquipoController::class, 'exportPreviewData'])->name('tipo-equipos.export.preview');
    Route::get('/tipo-equipos/export/excel', [TipoEquipoController::class, 'exportExcel'])->name('tipo-equipos.export.excel');
    Route::get('/tipo-equipos/export/pdf', [TipoEquipoController::class, 'exportPdf'])->name('tipo-equipos.export.pdf');
    });

    Route::middleware(['sams2.module:tipo_items'])->group(function () {
    Route::get('/tipo-items', [TipoItemController::class, 'index'])->name('tipo-items.index');
    Route::post('/tipo-items', [TipoItemController::class, 'store'])->name('tipo-items.store');
    Route::put('/tipo-items/{tipoItem}', [TipoItemController::class, 'update'])->name('tipo-items.update');
    Route::delete('/tipo-items/{tipoItem}', [TipoItemController::class, 'destroy'])->name('tipo-items.destroy');
    Route::get('/tipo-items/export/excel', [TipoItemController::class, 'exportExcel'])->name('tipo-items.export.excel');
    Route::get('/tipo-items/export/pdf', [TipoItemController::class, 'exportPdf'])->name('tipo-items.export.pdf');
    });

    Route::middleware(['sams2.module:uso_items'])->group(function () {
    Route::get('/uso-items', [UsoItemController::class, 'index'])->name('uso-items.index');
    Route::post('/uso-items/uso', [UsoItemController::class, 'storeUso'])->name('uso-items.store.uso');
    Route::put('/uso-items/uso/{usoItem}', [UsoItemController::class, 'updateUso'])->name('uso-items.update.uso');
    Route::delete('/uso-items/uso/{usoItem}', [UsoItemController::class, 'destroyUso'])->name('uso-items.destroy.uso');
    Route::get('/uso-items/export/excel', [UsoItemController::class, 'exportExcel'])->name('uso-items.export.excel');
    Route::get('/uso-items/export/pdf', [UsoItemController::class, 'exportPdf'])->name('uso-items.export.pdf');
    });


    Route::middleware(['sams2.module:estado_remision'])->group(function () {
    Route::get('/estado-remision', [EstadoRemisionController::class, 'index'])->name('estado-remision.index');
    Route::post('/estado-remision', [EstadoRemisionController::class, 'store'])->name('estado-remision.store');
    Route::put('/estado-remision/{estadoRemision}', [EstadoRemisionController::class, 'update'])->name('estado-remision.update');
    Route::delete('/estado-remision/{estadoRemision}', [EstadoRemisionController::class, 'destroy'])->name('estado-remision.destroy');
    Route::get('/estado-remision/export/excel', [EstadoRemisionController::class, 'exportExcel'])->name('estado-remision.export.excel');
    Route::get('/estado-remision/export/pdf', [EstadoRemisionController::class, 'exportPdf'])->name('estado-remision.export.pdf');
    });

    Route::middleware(['sams2.module:asignar'])->group(function () {
    Route::get('/asignar', [\App\Http\Controllers\AsignacionController::class, 'index'])->name('asignar.index');
    Route::post('/asignar', [\App\Http\Controllers\AsignacionController::class, 'store'])->name('asignar.store');
    Route::post('/asignar/multi', [\App\Http\Controllers\AsignacionController::class, 'storeMulti'])->name('asignar.store.multi');
    Route::put('/asignar/{asignacion}', [\App\Http\Controllers\AsignacionController::class, 'update'])->name('asignar.update');
    Route::delete('/asignar/{asignacion}', [\App\Http\Controllers\AsignacionController::class, 'destroy'])->name('asignar.destroy');
    });

    Route::middleware(['sams2.module:inspeccionar'])->group(function () {
    Route::get('/inspeccionar', [\App\Http\Controllers\InspeccionarController::class, 'index'])->name('inspeccionar.index');
    Route::get('/inspeccionar/tipo/{tipoEquipo}/equipos', [\App\Http\Controllers\InspeccionarController::class, 'equiposPorTipo'])->name('inspeccionar.tipo-equipos');
    Route::post('/inspeccionar/guardar', [\App\Http\Controllers\InspeccionarController::class, 'guardarInspeccion'])->name('inspeccionar.guardar');
    Route::delete('/inspeccionar/{inspeccion}', [\App\Http\Controllers\InspeccionarController::class, 'eliminar'])->name('inspeccionar.eliminar');
    Route::get('/inspecciones/tipo/{tipoEquipo}/formato-html', [\App\Http\Controllers\InspeccionarController::class, 'formatoHtml'])->name('inspeccionar.formato-html');
    });

    Route::middleware(['sams2.module:equipos'])->group(function () {
    Route::get('/equipos-baja', [\App\Http\Controllers\EquiposBajaController::class, 'index'])->name('equipos-baja.index');
    Route::post('/equipos-baja', [\App\Http\Controllers\EquiposBajaController::class, 'store'])->name('equipos-baja.store');
    Route::delete('/equipos-baja/registro/{equipoDebaja}', [\App\Http\Controllers\EquiposBajaController::class, 'destroyDebaja'])->name('equipos-baja.destroy');
    Route::get('/equipos-baja/{equipo}/formato-html', [\App\Http\Controllers\EquiposBajaController::class, 'formatoHtml'])->name('equipos-baja.formato-html');
    Route::get('/equipos-baja/{equipoBaja}/download', [\App\Http\Controllers\EquiposBajaController::class, 'download'])->name('equipos-baja.download');
    });

    // Menú Personalizaciones del Sistema
    Route::middleware(['sams2.module:redes_sociales'])->group(function () {
        Route::get('/configuracion', [\App\Http\Controllers\ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::post('/configuracion', [\App\Http\Controllers\ConfiguracionController::class, 'store'])->name('configuracion.store');
        // Rutas legacy para compatibilidad
        Route::get('/configuracion/redes-sociales', [\App\Http\Controllers\ConfiguracionController::class, 'redesSociales'])->name('configuracion.redes-sociales');
    });
    
    Route::middleware(['sams2.module:configuracion_login'])->group(function () {
        Route::get('/configuracion/login', [\App\Http\Controllers\ConfiguracionController::class, 'configuracionLogin'])->name('configuracion.login');
    });

    // Menú Configuración - Empresas (CRUD + pestañas Sede/Bodega)
    Route::middleware(['sams2.module:empresas'])->group(function () {
    Route::get('/empresas', [EmpresaController::class, 'index'])->name('empresas.index');
    Route::post('/empresas/empresa', [EmpresaController::class, 'storeEmpresa'])->name('empresas.store.empresa');
    Route::put('/empresas/empresa/{empresa}', [EmpresaController::class, 'updateEmpresa'])->name('empresas.update.empresa');
    Route::delete('/empresas/empresa/{empresa}', [EmpresaController::class, 'destroyEmpresa'])->name('empresas.destroy.empresa');
    Route::post('/empresas/sede', [EmpresaController::class, 'storeSede'])->name('empresas.store.sede');
    Route::put('/empresas/sede/{sede}', [EmpresaController::class, 'updateSede'])->name('empresas.update.sede');
    Route::delete('/empresas/sede/{sede}', [EmpresaController::class, 'destroySede'])->name('empresas.destroy.sede');
    Route::post('/empresas/bodega', [EmpresaController::class, 'storeBodega'])->name('empresas.store.bodega');
    Route::put('/empresas/bodega/{bodega}', [EmpresaController::class, 'updateBodega'])->name('empresas.update.bodega');
    Route::delete('/empresas/bodega/{bodega}', [EmpresaController::class, 'destroyBodega'])->name('empresas.destroy.bodega');
    Route::get('/empresas/export/excel', [EmpresaController::class, 'exportExcel'])->name('empresas.export.excel');
    Route::get('/empresas/export/pdf', [EmpresaController::class, 'exportPdf'])->name('empresas.export.pdf');
    });

    // Proveedores
    Route::middleware(['sams2.module:proveedores'])->group(function () {
    Route::get('/proveedores', [\App\Http\Controllers\ProveedorController::class, 'index'])->name('proveedores.index');
    Route::post('/proveedores', [\App\Http\Controllers\ProveedorController::class, 'store'])->name('proveedores.store');
    Route::put('/proveedores/{proveedor}', [\App\Http\Controllers\ProveedorController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}', [\App\Http\Controllers\ProveedorController::class , 'destroy'])->name('proveedores.destroy');
    Route::get('/proveedores/export/excel', [\App\Http\Controllers\ProveedorController::class, 'exportExcel'])->name('proveedores.export.excel');
    Route::get('/proveedores/export/pdf', [\App\Http\Controllers\ProveedorController::class, 'exportPdf'])->name('proveedores.export.pdf');
    });

    // Fabricantes
    Route::middleware(['sams2.module:fabricantes'])->group(function () {
    Route::get('/fabricantes', [\App\Http\Controllers\FabricanteController::class, 'index'])->name('fabricantes.index');
    Route::post('/fabricantes', [\App\Http\Controllers\FabricanteController::class, 'store'])->name('fabricantes.store');
    Route::put('/fabricantes/{fabricante}', [\App\Http\Controllers\FabricanteController::class, 'update'])->name('fabricantes.update');
    Route::delete('/fabricantes/{fabricante}', [\App\Http\Controllers\FabricanteController::class, 'destroy'])->name('fabricantes.destroy');
    Route::get('/fabricantes/export/excel', [\App\Http\Controllers\FabricanteController::class, 'exportExcel'])->name('fabricantes.export.excel');
    Route::get('/fabricantes/export/pdf', [\App\Http\Controllers\FabricanteController::class, 'exportPdf'])->name('fabricantes.export.pdf');
    });

    // Gemini / IA
    Route::post('/gemini/generate', [\App\Http\Controllers\GeminiController::class, 'generate'])->name('gemini.generate');
    Route::post('/gemini/analyze', [\App\Http\Controllers\GeminiController::class, 'analyze'])->name('gemini.analyze');
    Route::post('/gemini/summarize', [\App\Http\Controllers\GeminiController::class, 'summarize'])->name('gemini.summarize');
    Route::post('/gemini/translate', [\App\Http\Controllers\GeminiController::class, 'translate'])->name('gemini.translate');
    Route::post('/gemini/chat', [\App\Http\Controllers\GeminiController::class, 'chatWithContext'])->name('gemini.chat');
    Route::get('/gemini/system-info', [\App\Http\Controllers\GeminiController::class, 'getSystemInfo'])->name('gemini.system-info');
    Route::get('/gemini/history', [\App\Http\Controllers\GeminiController::class, 'getHistory'])->name('gemini.get-history');
    Route::post('/gemini/history', [\App\Http\Controllers\GeminiController::class, 'saveHistory'])->name('gemini.save-history');

    // API para cargar sedes y bodegas según filtros
    Route::get('/api/sedes', function (Request $request) {
        $empresaId = $request->get('empresa_id');
        $sedes = $empresaId 
            ? \App\Models\Sede::where('empresa_id', $empresaId)->orderBy('nombre')->get()
            : \App\Models\Sede::orderBy('nombre')->get();
        return response()->json(['sedes' => $sedes]);
    });
    
    Route::get('/api/bodegas', function (Request $request) {
        $sedeId = $request->get('sede_id');
        $bodegas = $sedeId 
            ? \App\Models\Bodega::where('sede_id', $sedeId)->orderBy('nombre')->get()
            : \App\Models\Bodega::orderBy('nombre')->get();
        return response()->json(['bodegas' => $bodegas]);
    });
});
