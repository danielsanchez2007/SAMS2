<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Equipo;
use App\Models\EquipoDebaja;
use App\Models\MaterialDidactico;
use App\Models\CodigoDisponible;
use App\Models\TipoItem;
use App\Models\TipoEquipo;
use App\Models\EstadoRemision;
use App\Models\Proveedor;
use App\Models\Fabricante;
use App\Models\Empresa;
use App\Models\Sede;
use App\Models\Bodega;
use App\Models\UsoItem;
use App\Models\EtiquetaAlmacen;
use App\Models\AlmacenImagen;
use App\Models\AlmacenArchivo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use App\Models\SeguridadCodigo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\LogoHelper;
use App\Helpers\ExcelHelper;
use App\Models\HojaVidaPlantilla;
use App\Models\HojaVidaValorFijo;
use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf;
use Smalot\PdfParser\Parser;
use App\Services\ItemSqlParser;

class EquipoController extends Controller
{
    /** Mapeo codigo -> id item antiguo (resources/css/img/Items/{id}) */
    protected static ?array $codigoToItemId = null;
    
    /**
     * Carga el mapeo codigo → id_item (carpeta img/Items) para mostrar imágenes.
     * Incluye config + mapa generado desde SQL para que todos los equipos con código en la base tengan imagen.
     */
    protected static function getCodigoToItemId(): array
    {
        if (self::$codigoToItemId === null) {
            $configPath = config_path('equipo_image_map.php');
            $sqlMapPath = storage_path('app/equipo_codigo_to_id_sql.php');
            if (!is_file($sqlMapPath)) {
                $sqlFile = base_path('sams_19_01_2026.sql');
                if (is_file($sqlFile)) {
                    try {
                        $sqlMap = ItemSqlParser::codigoToIdItem($sqlFile);
                        File::put($sqlMapPath, '<?php return ' . var_export($sqlMap, true) . ';');
                    } catch (\Throwable $e) {
                        $sqlMap = [];
                    }
                }
            }
            $config = is_file($configPath) ? require $configPath : [];
            $sqlMap = is_file($sqlMapPath) ? require $sqlMapPath : [];
            if (!is_array($config)) {
                $config = [];
            }
            if (!is_array($sqlMap)) {
                $sqlMap = [];
            }
            self::$codigoToItemId = $sqlMap + $config;
        }
        return self::$codigoToItemId;
    }

    /**
     * Ordena archivos de imagen para preferir "imagen general" del equipo (no etiqueta, serial, documento, logo).
     * No usa logos (SAI, icono, etc.) como foto del equipo; devuelve null si solo hay ese tipo.
     */
    private static function elegirMejorImagenGeneral(array $archivos): ?\SplFileInfo
    {
        $validos = [];
        foreach ($archivos as $archivo) {
            $ext = strtolower($archivo->getExtension());
            $filename = $archivo->getFilename();
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                || str_starts_with($filename, '.')
                || stripos($filename, 'thumb') !== false
                || strpos($filename, '.Metadatos') !== false) {
                continue;
            }
            $validos[] = $archivo;
        }
        if (empty($validos)) {
            return null;
        }
        $logoPalabras = ['logo', 'icon', 'icono', 'sai', 'sams', 'marca', 'purafil', 'label'];
        $etiquetaPalabras = ['etiqueta', 'tag', 'serial', 'normatividad', 'certific', 'doc', 'e.', 'imagen etiqueta'];
        $generalPalabras = ['general', 'equipo', 'producto', 'imagen', 'foto', 'g.', 'principal'];
        usort($validos, function ($a, $b) use ($logoPalabras, $etiquetaPalabras, $generalPalabras) {
            $na = strtolower($a->getFilename());
            $nb = strtolower($b->getFilename());
            $scoreA = 0;
            $scoreB = 0;
            foreach ($logoPalabras as $p) {
                if (strpos($na, $p) !== false) $scoreA -= 10;
                if (strpos($nb, $p) !== false) $scoreB -= 10;
            }
            foreach ($etiquetaPalabras as $p) {
                if (strpos($na, $p) !== false) $scoreA -= 2;
                if (strpos($nb, $p) !== false) $scoreB -= 2;
            }
            foreach ($generalPalabras as $p) {
                if (strpos($na, $p) !== false) $scoreA += 2;
                if (strpos($nb, $p) !== false) $scoreB += 2;
            }
            if ($scoreA !== $scoreB) {
                return $scoreB <=> $scoreA;
            }
            return $a->getSize() <=> $b->getSize();
        });
        $elegido = $validos[0];
        $nombreElegido = strtolower($elegido->getFilename());
        foreach ($logoPalabras as $p) {
            if (strpos($nombreElegido, $p) !== false) {
                return null;
            }
        }
        return $elegido;
    }

    /**
     * Devuelve la ruta del archivo de imagen "general" más adecuado en una carpeta Items/{id}, o null.
     * Evita devolver etiquetas, seriales o documentos; prefiere imagen del equipo.
     */
    private static function primeraImagenEnCarpetaItems(int $folderId): ?string
    {
        try {
            $publicPath = public_path("img/Items/{$folderId}");
            if (File::isDirectory($publicPath)) {
                $archivos = File::files($publicPath);
                $mejor = self::elegirMejorImagenGeneral($archivos);
                if ($mejor) {
                    return $mejor->getPathname();
                }
            }
            $itemsPath = base_path("resources/css/img/Items/{$folderId}");
            if (File::isDirectory($itemsPath)) {
                $archivos = File::files($itemsPath);
                $mejor = self::elegirMejorImagenGeneral($archivos);
                if ($mejor) {
                    return $mejor->getPathname();
                }
            }
            return null;
        } catch (\Exception $e) {
            \Log::error("Error buscando imagen en Items/{$folderId}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtiene la URL pública de la imagen "general" de un equipo.
     * Fuentes: storage, id_item_legacy, mapa codigo→id (SQL + config), carpeta por código.
     */
    public static function getImagenUrl(Equipo $equipo): ?string
    {
        if ($equipo->imagen_general) {
            return str_starts_with($equipo->imagen_general, 'storage/') 
                ? asset('storage/' . str_replace('storage/', '', $equipo->imagen_general))
                : asset($equipo->imagen_general);
        }
        $oldId = $equipo->id_item_legacy ?? null;
        if ($oldId) {
            $path = self::primeraImagenEnCarpetaItems((int) $oldId);
            if ($path) {
                $folderId = basename(dirname($path));
                return asset("img/Items/{$folderId}/" . basename($path));
            }
        }
        $mapping = self::getCodigoToItemId();
        $codigo = $equipo->codigo;
        $codigoTrim = trim((string) $codigo);
        $mappedId = $mapping[$codigo] ?? $mapping[$codigoTrim] ?? null;
        if ($mappedId) {
            $path = self::primeraImagenEnCarpetaItems((int) $mappedId);
            if ($path) {
                $folderId = basename(dirname($path));
                return asset("img/Items/{$folderId}/" . basename($path));
            }
        }
        if ($codigoTrim !== '') {
            $path = self::primeraImagenEnCarpetaPorCodigo($codigoTrim);
            if ($path) {
                $folder = basename(dirname($path));
                return asset("img/Items/{$folder}/" . basename($path));
            }
        }
        return null;
    }

    /**
     * Obtiene la URL pública de la imagen "general" de un equipo de baja.
     */
    public static function getImagenUrlDebaja(EquipoDebaja $equipo): ?string
    {
        if ($equipo->imagen_general) {
            if (str_starts_with($equipo->imagen_general, 'storage/')) {
                return asset('storage/' . str_replace('storage/', '', $equipo->imagen_general));
            } elseif (str_starts_with($equipo->imagen_general, 'img/')) {
                return asset($equipo->imagen_general);
            } else {
                return asset('storage/' . $equipo->imagen_general);
            }
        }
        
        // Si no hay imagen_general, intentar obtenerla por código
        $imagenPath = self::primeraImagenEnCarpetaPorCodigo($equipo->codigo);
        if ($imagenPath) {
            $relativePath = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $imagenPath);
            return asset(str_replace('\\', '/', $relativePath));
        }
        
        return null;
    }

    /**
     * Obtiene la URL pública de la imagen "general" de un material didáctico.
     */
    public static function getImagenUrlMaterialDidactico(MaterialDidactico $equipo): ?string
    {
        if ($equipo->imagen_general) {
            if (str_starts_with($equipo->imagen_general, 'storage/')) {
                return asset('storage/' . str_replace('storage/', '', $equipo->imagen_general));
            } elseif (str_starts_with($equipo->imagen_general, 'img/')) {
                return asset($equipo->imagen_general);
            } else {
                return asset('storage/' . $equipo->imagen_general);
            }
        }
        
        // Si no hay imagen_general, intentar obtenerla por código
        $imagenPath = self::primeraImagenEnCarpetaPorCodigo($equipo->codigo);
        if ($imagenPath) {
            $relativePath = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $imagenPath);
            return asset(str_replace('\\', '/', $relativePath));
        }
        
        return null;
    }

    /**
     * Busca imagen en carpeta img/Items/{codigo} (por si las carpetas están nombradas por código).
     */
    private static function primeraImagenEnCarpetaPorCodigo(string $codigo): ?string
    {
        $safe = preg_replace('/[^a-zA-Z0-9\-_]/', '', $codigo);
        if ($safe === '') {
            return null;
        }
        foreach ([public_path("img/Items/{$safe}"), base_path("resources/css/img/Items/{$safe}")] as $dir) {
            if (File::isDirectory($dir)) {
                $archivos = File::files($dir);
                $mejor = self::elegirMejorImagenGeneral($archivos);
                if ($mejor) {
                    return $mejor->getPathname();
                }
            }
        }
        return null;
    }

    /**
     * Ruta absoluta del archivo de imagen del equipo (para incrustar en PDF).
     */
    public static function getImagenPath(Equipo $equipo): ?string
    {
        if ($equipo->imagen_general) {
            $path = Storage::disk('public')->path($equipo->imagen_general);
            return File::exists($path) ? $path : null;
        }
        $oldId = $equipo->id_item_legacy ?? null;
        if ($oldId) {
            $path = self::primeraImagenEnCarpetaItems((int) $oldId);
            if ($path) return $path;
        }
        $mapping = self::getCodigoToItemId();
        $codigoTrim = trim((string) $equipo->codigo);
        $mappedId = $mapping[$equipo->codigo] ?? $mapping[$codigoTrim] ?? null;
        if ($mappedId) {
            $path = self::primeraImagenEnCarpetaItems($mappedId);
            if ($path) return $path;
        }
        if ($codigoTrim !== '') {
            $path = self::primeraImagenEnCarpetaPorCodigo($codigoTrim);
            if ($path) return $path;
        }
        return null;
    }

    public function imagen(Equipo $equipo): Response
    {
        $headers = [
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ];

        try {
            // 1. Imagen en storage (subida por usuario)
            if ($equipo->imagen_general) {
                $path = Storage::disk('public')->path($equipo->imagen_general);
                if (File::exists($path)) {
                    return response()->file($path, $headers);
                }
            }

            // 2. Imagen legacy: id_item_legacy en BD (carpeta Items/{id})
            $oldId = $equipo->id_item_legacy ?? null;
            if ($oldId) {
                $path = self::primeraImagenEnCarpetaItems((int) $oldId);
                if ($path) {
                    return response()->file($path, $headers);
                }
            }

            // 3. Imagen legacy: mapeo codigo -> id (SQL + config)
            $mapping = self::getCodigoToItemId();
            $codigoTrim = trim((string) $equipo->codigo);
            $oldId = $mapping[$equipo->codigo] ?? $mapping[$codigoTrim] ?? null;
            if ($oldId) {
                $path = self::primeraImagenEnCarpetaItems($oldId);
                if ($path) {
                    return response()->file($path, $headers);
                }
            }

            // 4. Carpeta por código (img/Items/EW-00234)
            if ($codigoTrim !== '') {
                $path = self::primeraImagenEnCarpetaPorCodigo($codigoTrim);
                if ($path) {
                    return response()->file($path, $headers);
                }
            }

            // 5. Placeholder por defecto (no usar Items/{equipo->id} para evitar imágenes de otro ítem) (primero en public, luego en resources)
            $placeholder = public_path('img/Icono.png');
            if (File::exists($placeholder)) {
                return response()->file($placeholder, ['Cache-Control' => 'public, max-age=86400']);
            }
            $placeholder = base_path('resources/css/img/Icono.png');
            if (File::exists($placeholder)) {
                return response()->file($placeholder, ['Cache-Control' => 'public, max-age=86400']);
            }

            // 6. SVG placeholder si todo lo demás falla
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24" fill="#475569"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="#94a3b8"/><path d="M21 15l-5-5L5 21h16v-6z" fill="#94a3b8"/></svg>';
            return response($svg, 200, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Exception $e) {
            \Log::error("Error cargando imagen del equipo {$equipo->id}: " . $e->getMessage());
            // Retornar placeholder SVG
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24" fill="#475569"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="#94a3b8"/><path d="M21 15l-5-5L5 21h16v-6z" fill="#94a3b8"/></svg>';
            return response($svg, 200, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    /**
     * Reemplaza la imagen general de un equipo (subida desde la tabla).
     */
    public function reemplazarImagen(Request $request, Equipo $equipo): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'imagen' => 'required|image|max:2048',
        ]);

        try {
            if ($equipo->imagen_general) {
                Storage::disk('public')->delete($equipo->imagen_general);
            }
            $path = $request->file('imagen')->store('equipos/imagenes', 'public');
            $equipo->update([
                'imagen_general' => $path,
                'tiene_imagen_general' => true,
            ]);

            $url = asset('storage/' . $path);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'url' => $url, 'message' => 'Imagen actualizada correctamente.']);
            }
            return redirect()->route('equipos.index')->with('success', 'Imagen actualizada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error reemplazando imagen equipo ' . $equipo->id . ': ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Error al guardar la imagen.'], 500);
            }
            return redirect()->back()->with('error', 'Error al guardar la imagen.');
        }
    }

    /**
     * Devuelve JSON con imágenes y archivos del almacén asociados a este equipo.
     */
    public function almacen(Equipo $equipo): \Illuminate\Http\JsonResponse
    {
        $equipo->load(['almacenImagenes.etiqueta', 'almacenArchivos.etiqueta']);
        
        // Imágenes del almacén
        $imagenesAlmacen = $equipo->almacenImagenes->map(function ($img) {
            return [
                'id' => $img->id,
                'etiqueta' => $img->etiqueta?->nombre,
                'ruta' => asset('storage/' . $img->ruta),
                'nombre_original' => $img->nombre_original,
                'download_url' => route('almacen.download.imagen', $img),
                'tipo' => 'almacen',
            ];
        });
        
        // Imágenes del equipo (imagen_general e imagen_etiqueta)
        $imagenesEquipo = [];
        if ($equipo->imagen_general) {
            // Generar URL correcta según el formato de la ruta
            $rutaImagenGeneral = null;
            if (str_starts_with($equipo->imagen_general, 'storage/')) {
                $rutaImagenGeneral = asset('storage/' . str_replace('storage/', '', $equipo->imagen_general));
            } elseif (str_starts_with($equipo->imagen_general, 'img/')) {
                $rutaImagenGeneral = asset($equipo->imagen_general);
            } else {
                // Intentar con storage
                $rutaImagenGeneral = asset('storage/' . $equipo->imagen_general);
            }
            
            if ($rutaImagenGeneral) {
                $imagenesEquipo[] = [
                    'id' => 'equipo_general_' . $equipo->id,
                    'etiqueta' => 'Imagen General',
                    'ruta' => $rutaImagenGeneral,
                    'nombre_original' => 'Imagen General',
                    'download_url' => $rutaImagenGeneral,
                    'tipo' => 'equipo',
                ];
            }
        }
        if ($equipo->imagen_etiqueta) {
            // Generar URL correcta según el formato de la ruta
            $rutaImagenEtiqueta = null;
            if (str_starts_with($equipo->imagen_etiqueta, 'storage/')) {
                $rutaImagenEtiqueta = asset('storage/' . str_replace('storage/', '', $equipo->imagen_etiqueta));
            } elseif (str_starts_with($equipo->imagen_etiqueta, 'img/')) {
                $rutaImagenEtiqueta = asset($equipo->imagen_etiqueta);
            } else {
                // Intentar con storage
                $rutaImagenEtiqueta = asset('storage/' . $equipo->imagen_etiqueta);
            }
            
            if ($rutaImagenEtiqueta) {
                $imagenesEquipo[] = [
                    'id' => 'equipo_etiqueta_' . $equipo->id,
                    'etiqueta' => 'Imagen de Etiqueta',
                    'ruta' => $rutaImagenEtiqueta,
                    'nombre_original' => 'Imagen de Etiqueta',
                    'download_url' => $rutaImagenEtiqueta,
                    'tipo' => 'equipo',
                ];
            }
        }
        
        // Combinar todas las imágenes
        $imagenes = array_merge($imagenesEquipo, $imagenesAlmacen->toArray());
        
        $archivos = $equipo->almacenArchivos->map(function ($a) {
            return [
                'id' => $a->id,
                'nombre' => $a->nombre,
                'etiqueta' => $a->etiqueta?->nombre,
                'download_url' => route('almacen.download.archivo', $a),
            ];
        });
        return response()->json([
            'equipo' => ['id' => $equipo->id, 'codigo' => $equipo->codigo, 'descripcion' => $equipo->descripcion],
            'imagenes' => $imagenes,
            'archivos' => $archivos,
        ]);
    }

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
            ->where('tipo_registro', 'normal')
            ->where('tipo_registro', 'normal')
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

        // Precalcular URLs de imágenes para carga directa (más rápido)
        foreach ($equipos as $equipo) {
            $equipo->imagen_url = self::getImagenUrl($equipo);
        }

        $tipoItems = TipoItem::orderBy('nombre')->get();
        $tipoEquipos = TipoEquipo::orderBy('nombre')->get();
        $estadoRemisiones = EstadoRemision::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();
        $fabricantes = Fabricante::orderBy('nombre')->get();
        $empresas = Empresa::orderBy('nombre')->get();
        
        // Filtrar sedes según empresa seleccionada
        $sedes = $empresaId 
            ? Sede::where('empresa_id', $empresaId)->orderBy('nombre')->get()
            : Sede::orderBy('nombre')->get();
        
        // Filtrar bodegas según sede seleccionada
        $bodegas = $sedeId 
            ? Bodega::where('sede_id', $sedeId)->orderBy('nombre')->get()
            : Bodega::orderBy('nombre')->get();
        
        $usoItems = UsoItem::orderBy('nombre')->get();

        $tab = 'equipos'; // Pestaña activa
        
        // Verificar si el usuario puede desbloquear códigos
        $user = session('sams2_user');
        $puedeDesbloquearCodigo = \App\Models\PermisoCodigoBloqueado::puedeDesbloquear(
            $user['id'] ?? null, 
            $user['role_id'] ?? null
        );

        // Obtener el último equipo registrado (para mostrar en el modal)
        $ultimoEquipoRegistrado = Equipo::where('tipo_registro', 'normal')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $ultimoEquipoData = null;
        if ($ultimoEquipoRegistrado) {
            $imagenUrl = self::getImagenUrl($ultimoEquipoRegistrado);
            $ultimoEquipoData = [
                'codigo' => $ultimoEquipoRegistrado->codigo,
                'nombre' => $ultimoEquipoRegistrado->nombre ?? 'Sin nombre',
                'descripcion' => $ultimoEquipoRegistrado->descripcion ?? 'Sin descripción',
                'imagen_url' => $imagenUrl,
            ];
        }

        return view('equipos.index', compact(
            'equipos', 'search', 'perPage', 'tipoItems', 'tipoEquipos', 'estadoRemisiones',
            'proveedores', 'fabricantes', 'empresas', 'sedes', 'bodegas', 'usoItems', 'tab',
            'empresaId', 'sedeId', 'bodegaId', 'puedeDesbloquearCodigo', 'ultimoEquipoData'
        ));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $empresaId = $request->input('empresa_id');
        
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'codigo' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($empresaId) {
                    $existe = Equipo::where('codigo', $value)
                        ->where('empresa_id', $empresaId)
                        ->exists();
                    if ($existe) {
                        $fail('El código ya existe para esta empresa.');
                    }
                },
            ],
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'tipo_registro' => 'required|string|in:normal,debaja,material,auditoria',
            // Validación de archivos más permisiva para no bloquear el guardado
            'manual_fabricante_file' => 'nullable|file|max:5120',
            'certificacion_file' => 'nullable|file|max:5120',
            'imagen_general_file' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'imagen_etiqueta_file' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);
        
        $data = $request->except(['manual_fabricante_file', 'certificacion_file', 'imagen_general_file', 'imagen_etiqueta_file']);
        
        // Para equipos normales, asegurar que el código tenga el prefijo "IN"
        if ($request->input('tipo_registro') === 'normal' && isset($data['codigo'])) {
            $codigo = trim($data['codigo']);
            // Si el código no empieza con "IN", agregarlo
            if (!str_starts_with(strtoupper($codigo), 'IN')) {
                // Si solo tiene números, agregar "IN" al inicio
                if (preg_match('/^\d+$/', $codigo)) {
                    $data['codigo'] = 'IN' . $codigo;
                } else {
                    // Si tiene otro formato, extraer números y agregar "IN"
                    if (preg_match('/(\d+)$/', $codigo, $matches)) {
                        $data['codigo'] = 'IN' . $matches[1];
                    }
                }
            } else {
                // Asegurar que esté en mayúsculas
                $data['codigo'] = strtoupper($codigo);
            }
        }
        
        // Verificar permisos para desbloquear código
        $user = session('sams2_user');
        $puedeDesbloquear = \App\Models\PermisoCodigoBloqueado::puedeDesbloquear(
            $user['id'] ?? null, 
            $user['role_id'] ?? null
        );
        
        // Si el código está bloqueado y el usuario no tiene permiso, mantenerlo bloqueado
        if ($request->has('codigo_bloqueado') && $request->input('codigo_bloqueado') == '0' && !$puedeDesbloquear) {
            // Si no tiene permiso, mantenerlo bloqueado
            $data['codigo_bloqueado'] = true;
        } else {
            $data['codigo_bloqueado'] = $request->has('codigo_bloqueado') && $request->input('codigo_bloqueado') == '1';
        }
        $data['es_kit'] = $request->has('es_kit');
        $data['tiene_manual_fabricante'] = $request->has('tiene_manual_fabricante');
        $data['tiene_certificacion'] = $request->has('tiene_certificacion');
        $data['tiene_imagen_general'] = $request->has('tiene_imagen_general');
        $data['tiene_imagen_etiqueta'] = $request->has('tiene_imagen_etiqueta');
        
        // Archivos - Manejar con try-catch para evitar errores que bloqueen el guardado
        $erroresArchivos = [];
        
        if ($request->hasFile('manual_fabricante_file')) {
            try {
            $file = $request->file('manual_fabricante_file');
                if ($file->isValid()) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $directorio = public_path('img/equipos/manuales');
                    if (!File::exists($directorio)) {
                        File::makeDirectory($directorio, 0755, true);
                    }
                    $file->move($directorio, $filename);
            $data['manual_fabricante'] = 'img/equipos/manuales/' . $filename;
        }
            } catch (\Exception $e) {
                $erroresArchivos[] = 'Error al subir manual de fabricante: ' . $e->getMessage();
                \Log::error('Error subiendo manual_fabricante_file: ' . $e->getMessage());
            }
        }
        
        if ($request->hasFile('certificacion_file')) {
            try {
            $file = $request->file('certificacion_file');
                if ($file->isValid()) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $directorio = public_path('img/equipos/certificaciones');
                    if (!File::exists($directorio)) {
                        File::makeDirectory($directorio, 0755, true);
                    }
                    $file->move($directorio, $filename);
            $data['certificacion_fabricante'] = 'img/equipos/certificaciones/' . $filename;
        }
            } catch (\Exception $e) {
                $erroresArchivos[] = 'Error al subir certificación: ' . $e->getMessage();
                \Log::error('Error subiendo certificacion_file: ' . $e->getMessage());
            }
        }
        
        if ($request->hasFile('imagen_general_file')) {
            try {
            $file = $request->file('imagen_general_file');
                // Verificar que el archivo existe y es válido
                if ($file && $file->isValid() && $file->getError() === UPLOAD_ERR_OK) {
                    $extension = $file->getClientOriginalExtension();
                    // Validar extensión
                    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array(strtolower($extension), $extensionesPermitidas)) {
                        throw new \Exception('Extensión de archivo no permitida: ' . $extension);
                    }
                    
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $directorio = public_path('img/equipos/imagenes');
                    if (!File::exists($directorio)) {
                        File::makeDirectory($directorio, 0755, true);
                    }
                    
                    // Intentar mover el archivo
                    if ($file->move($directorio, $filename)) {
            $data['imagen_general'] = 'img/equipos/imagenes/' . $filename;
                    } else {
                        throw new \Exception('No se pudo mover el archivo al directorio de destino');
                    }
                } else {
                    $errorCode = $file ? $file->getError() : 'Archivo no válido';
                    throw new \Exception('Archivo no válido (código de error: ' . $errorCode . ')');
                }
            } catch (\Exception $e) {
                $nombreArchivo = $request->file('imagen_general_file') ? $request->file('imagen_general_file')->getClientOriginalName() : 'archivo desconocido';
                $erroresArchivos[] = 'Error al subir imagen general (' . $nombreArchivo . '): ' . $e->getMessage();
                \Log::error('Error subiendo imagen_general_file: ' . $e->getMessage() . ' | Archivo: ' . $nombreArchivo);
                // No bloquear el guardado, continuar sin la imagen
            }
        }
        
        if ($request->hasFile('imagen_etiqueta_file')) {
            try {
            $file = $request->file('imagen_etiqueta_file');
                // Verificar que el archivo existe y es válido
                if ($file && $file->isValid() && $file->getError() === UPLOAD_ERR_OK) {
                    $extension = $file->getClientOriginalExtension();
                    // Validar extensión
                    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array(strtolower($extension), $extensionesPermitidas)) {
                        throw new \Exception('Extensión de archivo no permitida: ' . $extension);
                    }
                    
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $directorio = public_path('img/equipos/etiquetas');
                    if (!File::exists($directorio)) {
                        File::makeDirectory($directorio, 0755, true);
                    }
                    
                    // Intentar mover el archivo
                    if ($file->move($directorio, $filename)) {
            $data['imagen_etiqueta'] = 'img/equipos/etiquetas/' . $filename;
                    } else {
                        throw new \Exception('No se pudo mover el archivo al directorio de destino');
                    }
                } else {
                    $errorCode = $file ? $file->getError() : 'Archivo no válido';
                    throw new \Exception('Archivo no válido (código de error: ' . $errorCode . ')');
                }
            } catch (\Exception $e) {
                $nombreArchivo = $request->file('imagen_etiqueta_file') ? $request->file('imagen_etiqueta_file')->getClientOriginalName() : 'archivo desconocido';
                $erroresArchivos[] = 'Error al subir imagen de etiqueta (' . $nombreArchivo . '): ' . $e->getMessage();
                \Log::error('Error subiendo imagen_etiqueta_file: ' . $e->getMessage() . ' | Archivo: ' . $nombreArchivo);
                // No bloquear el guardado, continuar sin la imagen
            }
        }
        
        // Detectar códigos saltados antes de crear el equipo y agregarlos automáticamente
        $codigoIngresado = $request->input('codigo');
        $codigosSaltados = $this->detectarCodigosSaltados($codigoIngresado, $empresaId);
        
        // Agregar automáticamente los códigos saltados a códigos disponibles
        if (!empty($codigosSaltados)) {
            foreach ($codigosSaltados as $codigoSaltado) {
                CodigoDisponible::firstOrCreate(
                    ['codigo' => $codigoSaltado],
                    [
                        'origen_tipo' => 'equipos',
                        'equipo_original_id' => null,
                        'descripcion_original' => 'Código saltado automáticamente',
                        'utilizado' => false,
                    ]
                );
            }
        }
        
        // Crear el equipo primero, incluso si hay errores con archivos
        try {
        $equipo = Equipo::create($data);
        
            // Crear etiquetas automáticamente y guardar imágenes en almacén (si hay archivos válidos)
            try {
        $this->guardarImagenesEnAlmacen($equipo, $request);
            } catch (\Exception $e) {
                \Log::error('Error guardando imágenes en almacén: ' . $e->getMessage());
                // No bloquear el guardado si falla el almacén
            }
        } catch (\Exception $e) {
            \Log::error('Error creando equipo: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el equipo: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Error al crear el equipo.'])->withInput();
        }

        // Si el código viene de códigos disponibles, eliminarlo completamente (ya fue usado)
        if ($request->has('codigo')) {
            $codigoUsado = $request->input('codigo');
            // Eliminar el código disponible sin importar su estado (utilizado o no)
            $eliminados = CodigoDisponible::where('codigo', $codigoUsado)->delete();
            \Log::info("Código disponible eliminado: {$codigoUsado}, registros eliminados: {$eliminados}");
        }

        // Limpiar código prellenado de la sesión
        session()->forget('codigo_prellenado');
        
        // Si es petición AJAX, devolver JSON
        if ($request->wantsJson() || $request->ajax()) {
            $mensaje = 'Equipo creado correctamente.';
            if (!empty($erroresArchivos)) {
                $mensaje .= '\n\nAdvertencia: Algunos archivos no se pudieron subir:\n' . implode('\n', $erroresArchivos);
            }
            
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'errores_archivos' => $erroresArchivos,
                'equipo_id' => $equipo->id,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
        
        $mensaje = 'Equipo creado correctamente.';
        if (!empty($erroresArchivos)) {
            $mensaje .= ' Advertencia: Algunos archivos no se pudieron subir.';
        }
        
        return redirect()->route('equipos.index')->with('success', $mensaje)->with('errores_archivos', $erroresArchivos);
    }

    public function update(Request $request, Equipo $equipo): RedirectResponse|JsonResponse
    {
        $empresaId = $request->input('empresa_id', $equipo->empresa_id);
        
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'codigo' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($empresaId, $equipo) {
                    $existe = Equipo::where('codigo', $value)
                        ->where('empresa_id', $empresaId)
                        ->where('id', '!=', $equipo->id)
                        ->exists();
                    if ($existe) {
                        $fail('El código ya existe para esta empresa.');
                    }
                },
            ],
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'tipo_registro' => 'required|string|in:normal,debaja,material,auditoria',
            'manual_fabricante_file' => 'nullable|file|max:5120',
            'certificacion_file' => 'nullable|file|max:5120',
            'imagen_general_file' => 'nullable|image|max:2048',
            'imagen_etiqueta_file' => 'nullable|image|max:2048',
        ]);
        
        $data = $request->except(['manual_fabricante_file', 'certificacion_file', 'imagen_general_file', 'imagen_etiqueta_file', 'password_edicion_codigo']);
        
        // Para equipos normales, asegurar que el código tenga el prefijo "IN"
        if ($request->input('tipo_registro') === 'normal' && isset($data['codigo'])) {
            $codigo = trim($data['codigo']);
            // Si el código no empieza con "IN", agregarlo
            if (!str_starts_with(strtoupper($codigo), 'IN')) {
                // Si solo tiene números, agregar "IN" al inicio
                if (preg_match('/^\d+$/', $codigo)) {
                    $data['codigo'] = 'IN' . $codigo;
                } else {
                    // Si tiene otro formato, extraer números y agregar "IN"
                    if (preg_match('/(\d+)$/', $codigo, $matches)) {
                        $data['codigo'] = 'IN' . $matches[1];
                    }
                }
            } else {
                // Asegurar que esté en mayúsculas
                $data['codigo'] = strtoupper($codigo);
            }
        }
        
        // Verificar permisos para desbloquear código
        $user = session('sams2_user');
        $puedeDesbloquear = \App\Models\PermisoCodigoBloqueado::puedeDesbloquear(
            $user['id'] ?? null, 
            $user['role_id'] ?? null
        );
        
        // Si el código cambió y el usuario no tiene permiso, verificar contraseña
        $codigoCambio = $request->input('codigo') !== $equipo->codigo;
        $usarPassword = false;
        if ($codigoCambio && !$puedeDesbloquear && $equipo->codigo_bloqueado) {
            if (!$request->has('password_edicion_codigo') || empty($request->input('password_edicion_codigo'))) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['errors' => ['codigo' => ['Debes ingresar la contraseña para cambiar el código.']]], 422);
                }
                return redirect()->back()
                    ->withErrors(['codigo' => 'Debes ingresar la contraseña para cambiar el código.'])
                    ->withInput();
            }
            
            $seguridad = SeguridadCodigo::obtener();
            if (!$seguridad->password_edicion || !Hash::check($request->input('password_edicion_codigo'), $seguridad->password_edicion)) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['errors' => ['codigo' => ['Contraseña incorrecta.']]], 422);
                }
                return redirect()->back()
                    ->withErrors(['codigo' => 'Contraseña incorrecta.'])
                    ->withInput();
            }
            
            // Contraseña correcta, generar nueva automáticamente
            $nuevaPassword = bin2hex(random_bytes(8));
            $seguridad->update([
                'password_edicion' => Hash::make($nuevaPassword),
            ]);
            $usarPassword = true;
            // Bloquear el código después de usar la contraseña
            $data['codigo_bloqueado'] = true;
        }
        
        // Si el código está bloqueado y el usuario no tiene permiso, mantenerlo bloqueado
        if (!$usarPassword) {
            if ($request->has('codigo_bloqueado') && $request->input('codigo_bloqueado') == '0' && !$puedeDesbloquear) {
                // Si el código ya estaba bloqueado, mantenerlo bloqueado
                if ($equipo->codigo_bloqueado) {
                    $data['codigo_bloqueado'] = true;
                } else {
                    // Si no estaba bloqueado, permitir el cambio solo si tiene permiso
                    $data['codigo_bloqueado'] = false;
                }
            } else {
                $data['codigo_bloqueado'] = $request->has('codigo_bloqueado') && $request->input('codigo_bloqueado') == '1';
            }
        }
        $data['es_kit'] = $request->has('es_kit');
        $data['tiene_manual_fabricante'] = $request->has('tiene_manual_fabricante');
        $data['tiene_certificacion'] = $request->has('tiene_certificacion');
        $data['tiene_imagen_general'] = $request->has('tiene_imagen_general');
        $data['tiene_imagen_etiqueta'] = $request->has('tiene_imagen_etiqueta');
        
        // Archivos
        if ($request->hasFile('manual_fabricante_file')) {
            if ($equipo->manual_fabricante && file_exists(public_path($equipo->manual_fabricante))) {
                unlink(public_path($equipo->manual_fabricante));
            } elseif ($equipo->manual_fabricante && str_starts_with($equipo->manual_fabricante, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $equipo->manual_fabricante));
            }
            $file = $request->file('manual_fabricante_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/equipos/manuales'), $filename);
            $data['manual_fabricante'] = 'img/equipos/manuales/' . $filename;
        }
        if ($request->hasFile('certificacion_file')) {
            if ($equipo->certificacion_fabricante && file_exists(public_path($equipo->certificacion_fabricante))) {
                unlink(public_path($equipo->certificacion_fabricante));
            } elseif ($equipo->certificacion_fabricante && str_starts_with($equipo->certificacion_fabricante, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $equipo->certificacion_fabricante));
            }
            $file = $request->file('certificacion_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/equipos/certificaciones'), $filename);
            $data['certificacion_fabricante'] = 'img/equipos/certificaciones/' . $filename;
        }
        if ($request->hasFile('imagen_general_file')) {
            if ($equipo->imagen_general && file_exists(public_path($equipo->imagen_general))) {
                unlink(public_path($equipo->imagen_general));
            } elseif ($equipo->imagen_general && str_starts_with($equipo->imagen_general, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $equipo->imagen_general));
            }
            $file = $request->file('imagen_general_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/equipos/imagenes'), $filename);
            $data['imagen_general'] = 'img/equipos/imagenes/' . $filename;
        }
        if ($request->hasFile('imagen_etiqueta_file')) {
            if ($equipo->imagen_etiqueta && file_exists(public_path($equipo->imagen_etiqueta))) {
                unlink(public_path($equipo->imagen_etiqueta));
            } elseif ($equipo->imagen_etiqueta && str_starts_with($equipo->imagen_etiqueta, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $equipo->imagen_etiqueta));
            }
            $file = $request->file('imagen_etiqueta_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/equipos/etiquetas'), $filename);
            $data['imagen_etiqueta'] = 'img/equipos/etiquetas/' . $filename;
        }
        
        $equipo->update($data);
        
        // Crear etiquetas automáticamente y guardar imágenes en almacén
        $this->guardarImagenesEnAlmacen($equipo, $request);
        
        // Si es petición AJAX, devolver JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Equipo actualizado correctamente.',
                'usarPassword' => $usarPassword
            ]);
        }
        
        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        // Permitir eliminar tanto equipos normales como material didáctico
        $equipo = Equipo::find($id);
        $esMaterialDidactico = false;

        if (!$equipo) {
            $equipo = MaterialDidactico::find($id);
            $esMaterialDidactico = (bool) $equipo;
        }

        if (!$equipo) {
            return redirect()->back()->with('error', 'El registro de equipo/material no existe.');
        }

        // Guardar el código antes de eliminar para agregarlo a códigos disponibles
        $codigoEquipo = $equipo->codigo;
        $descripcionEquipo = $equipo->descripcion;
        
        // Eliminar archivos
        if ($equipo->manual_fabricante) Storage::disk('public')->delete($equipo->manual_fabricante);
        if ($equipo->certificacion_fabricante) Storage::disk('public')->delete($equipo->certificacion_fabricante);
        if ($equipo->imagen_general) Storage::disk('public')->delete($equipo->imagen_general);
        if ($equipo->imagen_etiqueta) Storage::disk('public')->delete($equipo->imagen_etiqueta);
        
        // Eliminar el registro (equipo normal o material didáctico)
        $equipo->delete();
        
        // Agregar el código a códigos disponibles
        CodigoDisponible::firstOrCreate(
            ['codigo' => $codigoEquipo],
            [
                'origen_tipo' => 'equipos',
                'equipo_original_id' => null,
                'descripcion_original' => $descripcionEquipo,
                'utilizado' => false,
            ]
        );
        
        $mensaje = $esMaterialDidactico
            ? 'Material didáctico eliminado correctamente. El código ' . $codigoEquipo . ' está ahora disponible.'
            : 'Equipo eliminado correctamente. El código ' . $codigoEquipo . ' está ahora disponible.';

        $ruta = $esMaterialDidactico ? 'equipos.material-didactico' : 'equipos.index';

        return redirect()->route($ruta)->with('success', $mensaje);
    }

    /**
     * Devuelve JSON con equipos filtrados por tipo_item_id (Tipo de ítem = columna TIPO de la tabla).
     */
    public function exportTiposItems(Request $request): \Illuminate\Http\JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        if (!$empresaId) {
            return response()->json(['tipo_items' => []]);
        }

        // Obtener tipos de ítem que tienen equipos en esa empresa
        $tipoItemIds = Equipo::where('empresa_id', $empresaId)
            ->where('tipo_registro', 'normal')
            ->whereNotNull('tipo_item_id')
            ->distinct()
            ->pluck('tipo_item_id');

        $tipoItems = TipoItem::whereIn('id', $tipoItemIds)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json(['tipo_items' => $tipoItems]);
    }

    public function exportTiposEquipos(Request $request): \Illuminate\Http\JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        $tipoItemId = $request->get('tipo_item_id');
        
        if (!$empresaId || !$tipoItemId) {
            return response()->json(['tipo_equipos' => []]);
        }

        // Obtener tipos de equipo que tienen equipos con esa empresa y tipo de ítem
        $tipoEquipoIds = Equipo::where('empresa_id', $empresaId)
            ->where('tipo_item_id', $tipoItemId)
            ->where('tipo_registro', 'normal')
            ->whereNotNull('tipo_equipo_id')
            ->distinct()
            ->pluck('tipo_equipo_id');

        $tipoEquipos = TipoEquipo::whereIn('id', $tipoEquipoIds)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json(['tipo_equipos' => $tipoEquipos]);
    }

    public function exportPreviewData(Request $request): \Illuminate\Http\JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        $tipoItemId = $request->get('tipo_item_id');
        $tipoEquiposIds = $request->query('tipo_equipos', $request->get('tipo_equipos', []));
        
        if (!is_array($tipoEquiposIds)) {
            $tipoEquiposIds = $tipoEquiposIds ? [$tipoEquiposIds] : [];
        }
        $tipoEquiposIds = array_values(array_map('intval', array_filter($tipoEquiposIds)));

        $query = Equipo::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'sede', 'bodega', 'usoItem'])
            ->where('tipo_registro', 'normal');
        
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }
        
        if ($tipoItemId) {
            $query->where('tipo_item_id', $tipoItemId);
        }
        
        if (!empty($tipoEquiposIds)) {
            $query->whereIn('tipo_equipo_id', $tipoEquiposIds);
        }
        
        $equipos = $query->orderBy('codigo')->get();

        $tiposSeleccionados = !empty($tipoEquiposIds)
            ? TipoEquipo::whereIn('id', $tipoEquiposIds)->pluck('nombre')->all()
            : [];

        $rows = $equipos->map(function ($e, $i) {
            return [
                'numero' => $i + 1,
                'codigo' => $e->codigo,
                'equipo' => $e->descripcion,
                'tipo' => $e->tipoEquipo?->nombre ?? $e->tipoItem?->nombre ?? '—',
                'serial_modelo' => $e->modelo ?? '—',
                'marca' => $e->marca ?? '—',
                'lote' => $e->lote ?? '—',
                'modelo' => $e->modelo ?? '—',
                'fecha_fabricacion' => $e->fecha_fabricacion?->format('d/m/Y') ?? '—',
                'fecha_compra' => $e->fecha_compra?->format('d/m/Y') ?? '—',
                'fabricante' => $e->fabricante?->nombre ?? '—',
                'proveedor' => $e->proveedor?->nombre ?? '—',
                'uso' => $e->usoItem?->nombre ?? '—',
                'capacidad_estructural' => $e->capacidades_resistencia ?? '—',
                'ubicacion' => $e->bodega?->nombre ?? $e->sede?->nombre ?? '—',
                'estado' => $e->estadoRemision?->nombre ?? '—',
            ];
        });

        return response()->json([
            'equipos' => $rows,
            'tipos_seleccionados' => $tiposSeleccionados,
            'total' => $equipos->count(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $empresaId = $request->get('empresa_id');
        $tipoItemId = $request->get('tipo_item_id');
        $tipoEquiposIds = $request->query('tipo_equipos', $request->get('tipo_equipos', []));
        
        if (!is_array($tipoEquiposIds)) {
            $tipoEquiposIds = $tipoEquiposIds ? [$tipoEquiposIds] : [];
        }
        $tipoEquiposIds = array_values(array_map('intval', array_filter($tipoEquiposIds)));

        $query = Equipo::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'sede', 'bodega', 'usoItem'])
            ->where('tipo_registro', 'normal')
            ->orderBy('codigo');
        
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }
        
        if ($tipoItemId) {
            $query->where('tipo_item_id', $tipoItemId);
        }
        
        if (!empty($tipoEquiposIds)) {
            $query->whereIn('tipo_equipo_id', $tipoEquiposIds);
        }
        
        $equipos = $query->get();

        $logos = LogoHelper::getBase64Logos();
        $html = view('equipos.export-trazabilidad', array_merge($logos, ['equipos' => $equipos]))->render();
        $filename = 'programa_trazabilidad_equipos_' . date('Y-m-d') . '.xls';
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $empresaId = $request->get('empresa_id');
        $tipoItemId = $request->get('tipo_item_id');
        $tipoEquiposIds = $request->query('tipo_equipos', $request->get('tipo_equipos', []));
        
        if (!is_array($tipoEquiposIds)) {
            $tipoEquiposIds = $tipoEquiposIds ? [$tipoEquiposIds] : [];
        }
        $tipoEquiposIds = array_values(array_map('intval', array_filter($tipoEquiposIds)));

        $query = Equipo::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'sede', 'bodega', 'proveedor', 'fabricante', 'usoItem'])
            ->where('tipo_registro', 'normal')
            ->orderBy('codigo');
        
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }
        
        if ($tipoItemId) {
            $query->where('tipo_item_id', $tipoItemId);
        }
        
        if (!empty($tipoEquiposIds)) {
            $query->whereIn('tipo_equipo_id', $tipoEquiposIds);
        }
        
        $equipos = $query->get();

        $logos = LogoHelper::getBase64Logos();
        $pdf = Pdf::loadView('equipos.pdf-trazabilidad', array_merge(compact('equipos'), $logos));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('programa_trazabilidad_equipos_' . date('Y-m-d') . '.pdf');
    }

    /** Etiquetas de placeholders para la hoja de vida (modal de llenado rápido). */
    public static function placeholdersEquipo(): array
    {
        return [
            'codigo' => 'Código',
            'descripcion' => 'Descripción',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'tipo_item' => 'Tipo de ítem',
            'estado_item' => 'Estado',
            'fabricante' => 'Fabricante',
            'proveedor' => 'Proveedor',
            'sede' => 'Sede',
            'bodega' => 'Bodega',
            'fecha_fabricacion' => 'Fecha fabricación',
            'fecha_compra' => 'Fecha compra',
            'fecha_uso' => 'Fecha uso',
            'valor' => 'Valor',
            'numero_factura' => 'Nº factura',
            'capacidades_resistencia' => 'Capacidades/resistencia',
            'lote' => 'Lote',
            'vida_util' => 'Vida útil',
            'uso_item' => 'Uso',
            'nombre_kit' => 'Nombre kit',
            'componentes_kit' => 'Componentes kit',
            'imagen_equipo' => 'Imagen del equipo',
        ];
    }

    /**
     * Construye el array de placeholders para la hoja de vida (equipo + valores fijos).
     */
    public static function buildPlaceholdersHojaVida(Equipo $equipo): array
    {
        $equipo->load(['tipoItem', 'estadoRemision', 'proveedor', 'fabricante', 'sede', 'bodega', 'usoItem']);

        $placeholders = [
            'codigo' => $equipo->codigo ?? '',
            'descripcion' => $equipo->descripcion ?? '',
            'marca' => $equipo->marca ?? '',
            'modelo' => $equipo->modelo ?? '',
            'tipo_item' => $equipo->tipoItem?->nombre ?? '—',
            'estado_remision' => $equipo->estadoRemision?->nombre ?? '—',
            'fabricante' => $equipo->fabricante?->nombre ?? '—',
            'proveedor' => $equipo->proveedor?->nombre ?? '—',
            'sede' => $equipo->sede?->nombre ?? '—',
            'bodega' => $equipo->bodega?->nombre ?? '—',
            'fecha_fabricacion' => $equipo->fecha_fabricacion?->format('d/m/Y') ?? '—',
            'fecha_compra' => $equipo->fecha_compra?->format('d/m/Y') ?? '—',
            'fecha_uso' => $equipo->fecha_uso?->format('d/m/Y') ?? '—',
            'valor' => $equipo->valor !== null ? number_format((float) $equipo->valor, 2, ',', '.') : '—',
            'numero_factura' => $equipo->numero_factura ?? '—',
            'capacidades_resistencia' => $equipo->capacidades_resistencia ?? '—',
            'lote' => $equipo->lote ?? '—',
            'vida_util' => $equipo->vida_util ? (string) $equipo->vida_util : '—',
            'uso_item' => $equipo->usoItem?->nombre ?? '—',
            'nombre_kit' => $equipo->nombre_kit ?? '—',
            'componentes_kit' => $equipo->componentes_kit ?? '—',
        ];

        $imgPath = self::getImagenPath($equipo);
        if ($imgPath && is_readable($imgPath)) {
            $mime = mime_content_type($imgPath) ?: 'image/jpeg';
            $placeholders['imagen_equipo'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($imgPath));
        } else {
            $placeholders['imagen_equipo'] = '';
        }

        $tipoEquipoId = $equipo->tipo_equipo_id ? (int) $equipo->tipo_equipo_id : null;
        $plantilla = HojaVidaPlantilla::porTipoEquipo($tipoEquipoId);
        $valoresFijos = $plantilla
            ? HojaVidaValorFijo::placeholdersParaPdf($plantilla->id, $equipo, auth()->user())
            : [];
        foreach ($valoresFijos as $clave => $valor) {
            $placeholders[$clave] = $valor ?? '';
        }

        return $placeholders;
    }

    /**
     * Devuelve JSON con placeholders, etiquetas y claves vacías (para modal de llenado rápido).
     */
    public function hojaVidaDatos(Equipo $equipo): Response
    {
        $placeholders = self::buildPlaceholdersHojaVida($equipo);
        $labels = self::placeholdersEquipo();
        $emptyKeys = [];
        $vacios = ['—', 'N/A', 'N/D', ''];
        $excluirLlenado = ['imagen_equipo'];
        foreach ($placeholders as $key => $value) {
            if (in_array($key, $excluirLlenado, true)) {
                continue;
            }
            $v = is_string($value) ? trim($value) : (string) $value;
            if ($v === '' || in_array($v, $vacios, true)) {
                $emptyKeys[] = $key;
            }
        }
        foreach (array_keys($placeholders) as $key) {
            if (!isset($labels[$key])) {
                $labels[$key] = str_replace('_', ' ', ucfirst($key));
            }
        }
        return response()->json([
            'placeholders' => $placeholders,
            'labels' => $labels,
            'empty_keys' => $emptyKeys,
            'equipo_codigo' => $equipo->codigo,
        ]);
    }

    /**
     * Genera y descarga la hoja de vida en PDF de un equipo.
     * Acepta overrides (GET/POST) para llenado rápido de campos vacíos.
     */
    public function hojaVidaPdf(Request $request, Equipo $equipo)
    {
        $placeholders = self::buildPlaceholdersHojaVida($equipo);

        $overrides = $request->input('overrides', []);
        if (empty($overrides) && $request->get('overrides')) {
            $decoded = json_decode($request->get('overrides'), true);
            $overrides = is_array($decoded) ? $decoded : [];
        }
        foreach ($overrides as $key => $value) {
            if (is_string($key) && trim($key) !== '') {
                $placeholders[$key] = $value;
            }
        }

        $tipoEquipoId = $equipo->tipo_equipo_id ? (int) $equipo->tipo_equipo_id : null;
        $plantilla = HojaVidaPlantilla::porTipoEquipo($tipoEquipoId);

        if ($plantilla && !empty(trim($plantilla->contenido_html ?? ''))) {
            $html = $plantilla->contenido_html;
            foreach ($placeholders as $key => $value) {
                $html = str_replace('{{' . $key . '}}', (string) $value, $html);
                $html = str_replace('{{ ' . $key . ' }}', (string) $value, $html);
            }
            $html = self::injectPdfFullWidthStyles($html);
            $pdf = Pdf::loadHTML($html);
        } else {
            $pdf = Pdf::loadView('equipos.hoja-vida-pdf', [
                'equipo' => $equipo,
                'placeholders' => $placeholders,
            ]);
        }

        $pdf->setPaper('a4', 'portrait');
        $nombreArchivo = 'hoja-vida-' . preg_replace('/[^a-zA-Z0-9\-]/', '-', $equipo->codigo) . '-' . date('Y-m-d') . '.pdf';
        return $pdf->download($nombreArchivo);
    }

    /**
     * Importa un PDF de hoja de vida para un equipo.
     */
    public function importarPdf(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'archivo' => 'required|file|mimes:pdf|max:20480',
            'equipo_id' => 'required|exists:equipos,id',
        ]);

        try {
            $equipo = Equipo::findOrFail($request->equipo_id);
            
            // Crear o obtener etiqueta "Hoja de Vida"
            $etiqueta = EtiquetaAlmacen::firstOrCreate(
                ['nombre' => 'Hoja de Vida', 'tipo' => 'archivo'],
                ['nombre' => 'Hoja de Vida', 'tipo' => 'archivo']
            );

            $file = $request->file('archivo');
            
            // Validar que el archivo existe y es válido
            if (!$file->isValid()) {
                throw new \Exception('El archivo no es válido.');
            }
            
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            
            // Crear directorio si no existe
            $directorio = public_path('img/almacen/archivos');
            if (!File::exists($directorio)) {
                File::makeDirectory($directorio, 0755, true);
            }
            
            $rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $filename;
            
            // Obtener el contenido del archivo y guardarlo directamente
            // Esto evita problemas con archivos temporales que pueden ser eliminados
            $contenido = file_get_contents($file->getRealPath());
            if ($contenido === false) {
                throw new \Exception('No se pudo leer el contenido del archivo.');
            }
            
            if (file_put_contents($rutaCompleta, $contenido) === false) {
                throw new \Exception('No se pudo guardar el archivo en el directorio de destino.');
            }
            
            $path = 'img/almacen/archivos/' . $filename;
            
            AlmacenArchivo::create([
                'nombre' => $request->nombre,
                'ruta' => $path,
                'etiqueta_id' => $etiqueta->id,
                'equipo_id' => $equipo->id,
                'mime_type' => $file->getMimeType(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PDF importado correctamente.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al importar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los PDFs del almacén para un equipo específico.
     */
    public function getPdfsAlmacen(Equipo $equipo): JsonResponse
    {
        try {
            $etiquetaHojaVida = EtiquetaAlmacen::where('nombre', 'Hoja de Vida')
                ->where('tipo', 'archivo')
                ->first();
            
            if (!$etiquetaHojaVida) {
                return response()->json([
                    'success' => true,
                    'pdfs' => []
                ]);
            }
            
            // Obtener TODOS los PDFs con esta etiqueta (no solo los del equipo actual)
            $pdfs = AlmacenArchivo::where('etiqueta_id', $etiquetaHojaVida->id)
                ->with('tipoEquipo')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($pdf) {
                    $rutaUrl = str_starts_with($pdf->ruta, 'storage/')
                        ? asset('storage/' . str_replace('storage/', '', $pdf->ruta))
                        : asset($pdf->ruta);
                    
                    return [
                        'id' => $pdf->id,
                        'nombre' => $pdf->nombre,
                        'ruta' => $pdf->ruta,
                        'ruta_url' => $rutaUrl,
                        'tipo_equipo' => $pdf->tipoEquipo ? $pdf->tipoEquipo->nombre : null,
                        'tipo_equipo_id' => $pdf->tipo_equipo_id,
                        'created_at' => $pdf->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'pdfs' => $pdfs
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener PDFs del almacén: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los PDFs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Asigna un PDF a un tipo de equipo.
     * Solo puede haber un PDF por tipo de equipo, pero un PDF puede estar asignado a varios tipos.
     */
    public function asignarPdfTipoEquipo(Request $request): JsonResponse
    {
        $request->validate([
            'pdf_id' => 'required|exists:almacen_archivos,id',
            'tipo_equipo_id' => 'required|exists:tipo_equipos,id',
        ]);

        try {
            // Verificar que no haya otro PDF asignado a este tipo de equipo
            $pdfExistente = AlmacenArchivo::where('tipo_equipo_id', $request->tipo_equipo_id)
                ->where('id', '!=', $request->pdf_id)
                ->first();
            
            if ($pdfExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un PDF asignado a este tipo de equipo. Solo se permite un PDF por tipo de equipo.',
                ], 400);
            }

            $pdf = AlmacenArchivo::findOrFail($request->pdf_id);
            $pdf->tipo_equipo_id = $request->tipo_equipo_id;
            $pdf->save();

            // Obtener todos los equipos con este tipo de equipo
            $equipos = Equipo::where('tipo_equipo_id', $request->tipo_equipo_id)
                ->orderBy('codigo')
                ->get()
                ->map(function ($equipo) {
                    return [
                        'id' => $equipo->id,
                        'codigo' => $equipo->codigo,
                        'descripcion' => $equipo->descripcion,
                        'imagen_url' => self::getImagenUrl($equipo),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'PDF asignado correctamente al tipo de equipo.',
                'equipos' => $equipos,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al asignar PDF a tipo de equipo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reemplaza un PDF asignado a un tipo de equipo por otro PDF.
     */
    public function reemplazarPdfTipoEquipo(Request $request): JsonResponse
    {
        $request->validate([
            'pdf_actual_id' => 'required|exists:almacen_archivos,id',
            'pdf_nuevo_id' => 'required|exists:almacen_archivos,id',
            'tipo_equipo_id' => 'required|exists:tipo_equipos,id',
        ]);

        try {
            $pdfActual = AlmacenArchivo::findOrFail($request->pdf_actual_id);
            
            // Verificar que el PDF actual esté asignado al tipo de equipo indicado
            if ($pdfActual->tipo_equipo_id != $request->tipo_equipo_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El PDF actual no está asignado a este tipo de equipo.',
                ], 400);
            }

            $pdfNuevo = AlmacenArchivo::findOrFail($request->pdf_nuevo_id);
            
            // Desasignar el PDF actual
            $pdfActual->tipo_equipo_id = null;
            $pdfActual->save();
            
            // Verificar que no haya otro PDF asignado a este tipo de equipo
            $pdfExistente = AlmacenArchivo::where('tipo_equipo_id', $request->tipo_equipo_id)
                ->where('id', '!=', $request->pdf_nuevo_id)
                ->first();
            
            if ($pdfExistente) {
                // Si hay otro PDF asignado, desasignarlo primero
                $pdfExistente->tipo_equipo_id = null;
                $pdfExistente->save();
            }
            
            // Asignar el nuevo PDF
            $pdfNuevo->tipo_equipo_id = $request->tipo_equipo_id;
            $pdfNuevo->save();

            // Obtener todos los equipos con este tipo de equipo
            $equipos = Equipo::where('tipo_equipo_id', $request->tipo_equipo_id)
                ->orderBy('codigo')
                ->get()
                ->map(function ($equipo) {
                    return [
                        'id' => $equipo->id,
                        'codigo' => $equipo->codigo,
                        'descripcion' => $equipo->descripcion,
                        'imagen_url' => self::getImagenUrl($equipo),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'PDF reemplazado correctamente.',
                'equipos' => $equipos,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al reemplazar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reemplazar el PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Desasigna un PDF de un tipo de equipo.
     */
    public function desasignarPdfTipoEquipo(Request $request): JsonResponse
    {
        $request->validate([
            'pdf_id' => 'required|exists:almacen_archivos,id',
        ]);

        try {
            $pdf = AlmacenArchivo::findOrFail($request->pdf_id);
            $pdf->tipo_equipo_id = null;
            $pdf->save();

            return response()->json([
                'success' => true,
                'message' => 'PDF desasignado correctamente.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al desasignar PDF de tipo de equipo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al desasignar el PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina un PDF del almacén.
     */
    public function eliminarPdf(Request $request): JsonResponse
    {
        $request->validate([
            'pdf_id' => 'required|exists:almacen_archivos,id',
        ]);

        try {
            $pdf = AlmacenArchivo::findOrFail($request->pdf_id);
            
            // Eliminar el archivo físico si existe
            if ($pdf->ruta) {
                $rutaCompleta = public_path($pdf->ruta);
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
            }
            
            // Eliminar el registro de la base de datos
            $pdf->delete();

            return response()->json([
                'success' => true,
                'message' => 'PDF eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exporta la hoja de vida del equipo usando un diseño HTML que replica el formato GGO3.
     * Se llena automáticamente con la información del equipo y deja en blanco lo que no exista.
     */
    public function exportarPdfPlantilla(Request $request, Equipo $equipo)
    {
        try {
            // Lista completa de todos los campos posibles que el PDF puede tener
            // Esto permite que el sistema se adapte automáticamente a cambios en el formato
            $todosLosCamposPosibles = [
                'codigo', 'descripcion', 'nombre', 'marca', 'modelo', 'fabricante', 'proveedor',
                'fecha_fabricacion', 'fecha_compra', 'fecha_uso', 'lote', 'capacidades_resistencia',
                'ubicacion', 'uso', 'puntos_anclaje', 'talla', 'material', 'color',
                'otras_especificaciones', 'cumple_normas', 'descripcion_inspecciones',
                'fecha_inspeccion', 'validez_inspeccion', 'tiene_ficha_tecnica', 'tiene_certificacion',
                'serial', 'numero_serial', 'numero_serie', 'inspeccion', 'normas', 'certificacion',
            ];

            // Datos principales del equipo (valores por defecto)
            $datosEquipo = [
                'codigo' => $equipo->codigo ?? '',
                'descripcion' => $equipo->descripcion ?? '',
                'nombre' => $equipo->descripcion ?? '',
                'marca' => $equipo->marca ?? '',
                'modelo' => $equipo->modelo ?? '',
                'fabricante' => $equipo->fabricante?->nombre ?? '',
                'proveedor' => $equipo->proveedor?->nombre ?? '',
                'fecha_fabricacion' => $equipo->fecha_fabricacion ? $equipo->fecha_fabricacion->format('d/m/Y') : '',
                'fecha_compra' => $equipo->fecha_compra ? $equipo->fecha_compra->format('d/m/Y') : '',
                'fecha_uso' => $equipo->fecha_uso ? $equipo->fecha_uso->format('d/m/Y') : '',
                'lote' => $equipo->lote ?? '',
                'capacidades_resistencia' => $equipo->capacidades_resistencia ?? '',
                'ubicacion' => ($equipo->sede?->nombre ?? '') . ($equipo->bodega ? ' - ' . $equipo->bodega->nombre : ''),
                'uso' => $equipo->usoItem?->nombre ?? '',
                'puntos_anclaje' => '',
                'talla' => '',
                'material' => '',
                'color' => '',
                'otras_especificaciones' => '',
                'cumple_normas' => '',
                'descripcion_inspecciones' => '',
                'fecha_inspeccion' => '',
                'validez_inspeccion' => '',
                'tiene_ficha_tecnica' => $equipo->tiene_manual_fabricante ?? false,
                'tiene_certificacion' => $equipo->tiene_certificacion ?? false,
            ];

            // Sobrescribir con datos manuales enviados desde el modal (si existen)
            // El sistema acepta cualquier campo que se envíe, adaptándose automáticamente
            foreach ($request->all() as $key => $value) {
                if (in_array($key, $todosLosCamposPosibles) || str_starts_with($key, 'campo_')) {
                    if ($request->filled($key)) {
                        $datosEquipo[$key] = $request->input($key);
                    }
                }
            }

            // Logo específico para el PDF
            $logoPath = Storage::disk('public')->path('logos/rRotfplm6FUnXJfJqQNZ7TVcajhRR3iKryZHzUKx.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath) ?: 'image/png';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pdf = Pdf::loadView('equipos.pdf-hoja-vida-equipo', [
                'logoPreventionBase64' => $logoBase64,
                'equipo'      => $equipo,
                'datosEquipo' => $datosEquipo,
            ]);

            $pdf->setPaper('a4', 'portrait');
            $nombreArchivo = 'hoja-vida-equipo-' . preg_replace('/[^a-zA-Z0-9\-]/', '-', $equipo->codigo ?? 'sin-codigo') . '-' . date('Y-m-d') . '.pdf';

            return $pdf->download($nombreArchivo);
        } catch (\Exception $e) {
            \Log::error('Error al exportar PDF plantilla: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            $mensaje = 'Error al exportar el PDF: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $mensaje,
                ], 500);
            }
            return redirect()->back()->with('error', $mensaje);
        }
    }

    /**
     * Devuelve el HTML del PDF para edición (sin generar PDF)
     */
    public function exportarPdfPlantillaHtml(Request $request, Equipo $equipo)
    {
        try {
            // Lista completa de todos los campos posibles que el PDF puede tener
            $todosLosCamposPosibles = [
                'codigo', 'descripcion', 'nombre', 'marca', 'modelo', 'fabricante', 'proveedor',
                'fecha_fabricacion', 'fecha_compra', 'fecha_uso', 'lote', 'capacidades_resistencia',
                'ubicacion', 'uso', 'puntos_anclaje', 'talla', 'material', 'color',
                'otras_especificaciones', 'cumple_normas', 'descripcion_inspecciones',
                'fecha_inspeccion', 'validez_inspeccion', 'tiene_ficha_tecnica', 'tiene_certificacion',
                'serial', 'numero_serial', 'numero_serie', 'inspeccion', 'normas', 'certificacion',
            ];

            // Datos principales del equipo
            $datosEquipo = [
                'codigo' => $equipo->codigo ?? '',
                'descripcion' => $equipo->descripcion ?? '',
                'nombre' => $equipo->descripcion ?? '',
                'marca' => $equipo->marca ?? '',
                'modelo' => $equipo->modelo ?? '',
                'fabricante' => $equipo->fabricante?->nombre ?? '',
                'proveedor' => $equipo->proveedor?->nombre ?? '',
                'fecha_fabricacion' => $equipo->fecha_fabricacion ? $equipo->fecha_fabricacion->format('d/m/Y') : '',
                'fecha_compra' => $equipo->fecha_compra ? $equipo->fecha_compra->format('d/m/Y') : '',
                'fecha_uso' => $equipo->fecha_uso ? $equipo->fecha_uso->format('d/m/Y') : '',
                'lote' => $equipo->lote ?? '',
                'capacidades_resistencia' => $equipo->capacidades_resistencia ?? '',
                'ubicacion' => ($equipo->sede?->nombre ?? '') . ($equipo->bodega ? ' - ' . $equipo->bodega->nombre : ''),
                'uso' => $equipo->usoItem?->nombre ?? '',
                'puntos_anclaje' => '',
                'talla' => '',
                'material' => '',
                'color' => '',
                'otras_especificaciones' => '',
                'cumple_normas' => '',
                'descripcion_inspecciones' => '',
                'fecha_inspeccion' => '',
                'validez_inspeccion' => '',
                'tiene_ficha_tecnica' => $equipo->tiene_manual_fabricante ?? false,
                'tiene_certificacion' => $equipo->tiene_certificacion ?? false,
            ];

            // Sobrescribir con datos manuales enviados desde el modal
            foreach ($request->all() as $key => $value) {
                if (in_array($key, $todosLosCamposPosibles) || str_starts_with($key, 'campo_')) {
                    if ($request->filled($key)) {
                        $datosEquipo[$key] = $request->input($key);
                    }
                }
            }

            // Logo específico para el PDF
            $logoPath = Storage::disk('public')->path('logos/rRotfplm6FUnXJfJqQNZ7TVcajhRR3iKryZHzUKx.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath) ?: 'image/png';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }

            // Devolver el HTML renderizado (para edición)
            return view('equipos.pdf-hoja-vida-equipo-editable', [
                'logoPreventionBase64' => $logoBase64,
                'equipo'      => $equipo,
                'datosEquipo' => $datosEquipo,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al generar HTML del PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el HTML: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Inyecta estilos en el HTML de la plantilla para que el PDF use todo el ancho de la hoja
     * y no quede con márgenes grandes o aspecto de "dos hojas".
     */
    protected static function injectPdfFullWidthStyles(string $html): string
    {
        $styles = '
        <style>
            @page { margin: 12mm; size: A4 portrait; }
            html, body { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 8px !important; box-sizing: border-box; }
            *, *::before, *::after { box-sizing: border-box; }
            body > div, body > table, .page, .page-img { max-width: 100% !important; width: 100% !important; }
            img { max-width: 100% !important; height: auto !important; }
            table { width: 100% !important; max-width: 100% !important; }
        </style>';
        if (stripos($html, '</head>') !== false) {
            return str_ireplace('</head>', $styles . '</head>', $html);
        }
        if (preg_match('/<html[^>]*>/i', $html)) {
            return preg_replace('/(<html[^>]*>)/i', '$1<head><meta charset="utf-8">' . $styles . '</head>', $html, 1);
        }
        return '<!DOCTYPE html><html><head><meta charset="utf-8">' . $styles . '</head><body>' . $html . '</body></html>';
    }

    /**
     * Traspasa un equipo a equipos_debaja o material_didactico.
     */
    public function traspasar(Request $request, Equipo $equipo): RedirectResponse
    {
        $request->validate([
            'destino' => 'required|in:equipos_debaja,material_didactico,auditoria',
            'numero_codigo' => 'required|integer|min:1',
        ]);

        $codigoOriginal = $equipo->codigo;
        $empresaId = $equipo->empresa_id;

        // Determinar prefijo según el destino
        $prefijo = match($request->destino) {
            'equipos_debaja' => 'DB',
            'material_didactico' => 'MD',
            'auditoria' => 'AU',
            default => 'DB'
        };

        // Generar código con prefijo automático y número manual
        $codigoNuevo = $prefijo . $request->numero_codigo;

        // Detectar códigos saltados para el nuevo código y agregarlos automáticamente
        $codigosSaltados = $this->detectarCodigosSaltados($codigoNuevo, $empresaId);
        if (!empty($codigosSaltados)) {
            foreach ($codigosSaltados as $codigoSaltado) {
                CodigoDisponible::firstOrCreate(
                    ['codigo' => $codigoSaltado],
                    [
                        'origen_tipo' => $request->destino === 'equipos_debaja' ? 'equipos_debaja' : ($request->destino === 'material_didactico' ? 'material_didactico' : 'auditoria'),
                        'equipo_original_id' => null,
                        'descripcion_original' => 'Código saltado automáticamente',
                        'utilizado' => false,
                    ]
                );
            }
        }

        // Verificar que el código nuevo no exista en la misma empresa
        // Para auditoría, solo verificar en equipos con tipo_registro = 'auditoria' o sin tipo_registro (normal)
        // Para otros destinos, verificar en todas las tablas
        
        if ($request->destino === 'auditoria') {
            // Para auditoría, verificar que no exista otro equipo con el mismo código
            // Excluir el equipo actual y solo verificar equipos normales o de auditoría
        $existeEnEquipos = Equipo::where('codigo', $codigoNuevo)
            ->where('empresa_id', $empresaId)
                ->where('id', '!=', $equipo->id)
                ->where(function($query) {
                    $query->where('tipo_registro', 'auditoria')
                          ->orWhereNull('tipo_registro')
                          ->orWhere('tipo_registro', 'normal');
                })
                ->exists();
            
            if ($existeEnEquipos) {
                return redirect()->back()->withErrors(['numero_codigo' => 'El código ' . $codigoNuevo . ' ya está en uso para esta empresa.']);
            }
        } else {
            // Para equipos_debaja y material_didactico, verificar en todas las tablas
            $existeEnEquipos = Equipo::where('codigo', $codigoNuevo)
                ->where('empresa_id', $empresaId)
                ->where('id', '!=', $equipo->id)
            ->exists();
        $existeEnDebaja = EquipoDebaja::where('codigo', $codigoNuevo)
            ->where('empresa_id', $empresaId)
            ->exists();
        $existeEnMaterial = MaterialDidactico::where('codigo', $codigoNuevo)
            ->where('empresa_id', $empresaId)
            ->exists();

        if ($existeEnEquipos || $existeEnDebaja || $existeEnMaterial) {
                return redirect()->back()->withErrors(['numero_codigo' => 'El código ' . $codigoNuevo . ' ya está en uso para esta empresa.']);
        }
        }

        // Preservar rutas de imágenes y archivos antes de eliminar el equipo
        // Las rutas se preservan tal como están para mantener compatibilidad
        $imagenGeneral = $equipo->imagen_general;
        $imagenEtiqueta = $equipo->imagen_etiqueta;
        $manualFabricante = $equipo->manual_fabricante;
        $certificacionFabricante = $equipo->certificacion_fabricante;

        // Obtener todos los datos del equipo (solo campos fillable)
        $datosEquipo = [
            'empresa_id' => $equipo->empresa_id,
            'tipo_item_id' => $equipo->tipo_item_id,
            'tipo_equipo_id' => $equipo->tipo_equipo_id,
            'codigo_bloqueado' => $equipo->codigo_bloqueado,
            'estado_remision_id' => $equipo->estado_remision_id,
            'descripcion' => $equipo->descripcion,
            'marca' => $equipo->marca,
            'proveedor_id' => $equipo->proveedor_id,
            'fabricante_id' => $equipo->fabricante_id,
            'modelo' => $equipo->modelo,
            'sede_id' => $equipo->sede_id,
            'bodega_id' => $equipo->bodega_id,
            'vida_util' => $equipo->vida_util,
            'fecha_fabricacion' => $equipo->fecha_fabricacion,
            'fecha_uso' => $equipo->fecha_uso,
            'uso_item_id' => $equipo->uso_item_id,
            'es_kit' => $equipo->es_kit,
            'nombre_kit' => $equipo->nombre_kit,
            'componentes_kit' => $equipo->componentes_kit,
            'valor' => $equipo->valor,
            'numero_factura' => $equipo->numero_factura,
            'capacidades_resistencia' => $equipo->capacidades_resistencia,
            'lote' => $equipo->lote,
            'fecha_compra' => $equipo->fecha_compra,
            'tiene_manual_fabricante' => $equipo->tiene_manual_fabricante,
            'manual_fabricante' => $manualFabricante,
            'tiene_certificacion' => $equipo->tiene_certificacion,
            'certificacion_fabricante' => $certificacionFabricante,
            'tiene_imagen_general' => $equipo->tiene_imagen_general,
            'imagen_general' => $imagenGeneral,
            'tiene_imagen_etiqueta' => $equipo->tiene_imagen_etiqueta,
            'imagen_etiqueta' => $imagenEtiqueta,
            'equipo_original_id' => $equipo->id,
            'codigo_original' => $codigoOriginal,
            'codigo' => $codigoNuevo,
        ];

        // Guardar código original en códigos disponibles
        CodigoDisponible::create([
            'codigo' => $codigoOriginal,
            'origen_tipo' => 'equipos',
            'equipo_original_id' => $equipo->id,
            'descripcion_original' => $equipo->descripcion,
            'utilizado' => false,
        ]);

        // Crear en la tabla destino PRIMERO, antes de eliminar el original
        // Usar transacción para asegurar que todo se haga correctamente
        $nuevoEquipoCreado = false;
        $mensaje = '';
        
        try {
            DB::beginTransaction();
            
        if ($request->destino === 'equipos_debaja') {
            EquipoDebaja::create($datosEquipo);
                $mensaje = "Equipo traspasado a Equipos debaja correctamente. Nuevo código: {$codigoNuevo}";
                $nuevoEquipoCreado = true;
            } elseif ($request->destino === 'material_didactico') {
            MaterialDidactico::create($datosEquipo);
                $mensaje = "Equipo traspasado a Material didáctico correctamente. Nuevo código: {$codigoNuevo}";
                $nuevoEquipoCreado = true;
            } elseif ($request->destino === 'auditoria') {
                // Auditoría se crea en la tabla equipos con tipo_registro = 'auditoria'
                // Crear un nuevo array solo con los campos que están en el fillable de Equipo
                $datosEquipoAuditoria = [
                    'empresa_id' => $equipo->empresa_id,
                    'tipo_item_id' => $equipo->tipo_item_id,
                    'tipo_equipo_id' => $equipo->tipo_equipo_id,
                    'codigo' => $codigoNuevo,
                    'nombre' => $equipo->nombre ?? '',
                    'codigo_bloqueado' => $equipo->codigo_bloqueado,
                    'estado_remision_id' => $equipo->estado_remision_id,
                    'tipo_registro' => 'auditoria',
                    'descripcion' => $equipo->descripcion,
                    'marca' => $equipo->marca,
                    'proveedor_id' => $equipo->proveedor_id,
                    'fabricante_id' => $equipo->fabricante_id,
                    'modelo' => $equipo->modelo,
                    'sede_id' => $equipo->sede_id,
                    'bodega_id' => $equipo->bodega_id,
                    'vida_util' => $equipo->vida_util,
                    'fecha_fabricacion' => $equipo->fecha_fabricacion,
                    'fecha_uso' => $equipo->fecha_uso,
                    'uso_item_id' => $equipo->uso_item_id,
                    'es_kit' => $equipo->es_kit,
                    'nombre_kit' => $equipo->nombre_kit,
                    'componentes_kit' => $equipo->componentes_kit,
                    'valor' => $equipo->valor,
                    'numero_factura' => $equipo->numero_factura,
                    'capacidades_resistencia' => $equipo->capacidades_resistencia,
                    'lote' => $equipo->lote,
                    'fecha_compra' => $equipo->fecha_compra,
                    'tiene_manual_fabricante' => $equipo->tiene_manual_fabricante,
                    'manual_fabricante' => $manualFabricante,
                    'tiene_certificacion' => $equipo->tiene_certificacion,
                    'certificacion_fabricante' => $certificacionFabricante,
                    'tiene_imagen_general' => $equipo->tiene_imagen_general,
                    'imagen_general' => $imagenGeneral,
                    'tiene_imagen_etiqueta' => $equipo->tiene_imagen_etiqueta,
                    'imagen_etiqueta' => $imagenEtiqueta,
                ];
                
                // Agregar id_item_legacy si existe
                if ($equipo->id_item_legacy) {
                    $datosEquipoAuditoria['id_item_legacy'] = $equipo->id_item_legacy;
                }
                
                \Log::info("Intentando crear equipo de auditoría con datos: " . json_encode($datosEquipoAuditoria));
                
                $nuevoEquipoAuditoria = Equipo::create($datosEquipoAuditoria);
                \Log::info("Equipo de auditoría creado exitosamente con ID: " . $nuevoEquipoAuditoria->id);
                $mensaje = "Equipo traspasado a Auditoría correctamente. Nuevo código: {$codigoNuevo}";
                $nuevoEquipoCreado = true;
        }

            // Solo eliminar el equipo original si el nuevo se creó correctamente
            if ($nuevoEquipoCreado) {
                // Eliminar el equipo original SIN eliminar las imágenes físicas
                // Solo eliminar el registro de la base de datos, no los archivos
                $equipo->imagen_general = null;
                $equipo->imagen_etiqueta = null;
                $equipo->manual_fabricante = null;
                $equipo->certificacion_fabricante = null;
                $equipo->save();
        $equipo->delete();
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al traspasar equipo: " . $e->getMessage());
            \Log::error("Destino: " . $request->destino);
            \Log::error("Datos del equipo: " . json_encode($datosEquipo));
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return redirect()->back()->withErrors(['numero_codigo' => 'Error al traspasar el equipo: ' . $e->getMessage()]);
        }


        // Redirigir a la pestaña correcta según el destino
        $ruta = match($request->destino) {
            'equipos_debaja' => route('equipos.debaja'),
            'material_didactico' => route('equipos.material-didactico'),
            'auditoria' => route('equipos.auditoria'),
            default => route('equipos.index')
        };

        return redirect($ruta)->with('success', $mensaje);
    }

    /**
     * Obtiene el último equipo traspasado para mostrar en el modal.
     */
    public function getUltimoEquipoTraspasado(Request $request): \Illuminate\Http\JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        $equipoId = $request->get('equipo_id');
        $destino = $request->get('destino');
        
        if (!$empresaId) {
            return response()->json(['ultimo_equipo' => null, 'codigos_disponibles' => []]);
        }

        $ultimo = null;
        $destinoEncontrado = null;

        // Si hay equipo_id, buscar el último traspaso de ese equipo específico
        if ($equipoId) {
            // Buscar en equipos_debaja
            $ultimoDebaja = EquipoDebaja::where('equipo_original_id', $equipoId)
                ->where('codigo', 'not like', 'AU%')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Buscar en material_didactico
            $ultimoMaterial = MaterialDidactico::where('equipo_original_id', $equipoId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Buscar en auditoría (buscar por código original en CodigoDisponible)
            $codigoOriginal = Equipo::find($equipoId)?->codigo;
            $ultimoAuditoria = null;
            if ($codigoOriginal) {
                $codigoDisponible = CodigoDisponible::where('codigo', $codigoOriginal)
                    ->where('origen_tipo', 'equipos')
                    ->where('equipo_original_id', $equipoId)
                    ->first();
                if ($codigoDisponible) {
                    $ultimoAuditoria = Equipo::where('empresa_id', $empresaId)
                        ->where('tipo_registro', 'auditoria')
                        ->where('codigo', 'like', 'AU%')
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
            }
            
            $ultimo = collect([$ultimoDebaja, $ultimoMaterial, $ultimoAuditoria])
                ->filter()
                ->sortByDesc('created_at')
                ->first();
            
            if ($ultimo instanceof EquipoDebaja) {
                $destinoEncontrado = 'equipos_debaja';
            } elseif ($ultimo instanceof MaterialDidactico) {
                $destinoEncontrado = 'material_didactico';
            } elseif ($ultimo instanceof Equipo && $ultimo->tipo_registro === 'auditoria') {
                $destinoEncontrado = 'auditoria';
            }
        } else {
            // Si no hay equipo_id, buscar el último traspaso general de la empresa
            $ultimoDebaja = EquipoDebaja::where('empresa_id', $empresaId)
                ->whereNotNull('equipo_original_id')
                ->where('codigo', 'not like', 'AU%')
                ->orderBy('created_at', 'desc')
                ->first();

            $ultimoMaterial = MaterialDidactico::where('empresa_id', $empresaId)
                ->whereNotNull('equipo_original_id')
                ->orderBy('created_at', 'desc')
                ->first();

            $ultimoAuditoria = Equipo::where('empresa_id', $empresaId)
                ->where('tipo_registro', 'auditoria')
                ->orderBy('created_at', 'desc')
                ->first();

            $ultimo = collect([$ultimoDebaja, $ultimoMaterial, $ultimoAuditoria])
                ->filter()
                ->sortByDesc('created_at')
                ->first();

            if ($ultimo instanceof EquipoDebaja) {
                $destinoEncontrado = 'equipos_debaja';
            } elseif ($ultimo instanceof MaterialDidactico) {
                $destinoEncontrado = 'material_didactico';
            } elseif ($ultimo instanceof Equipo && $ultimo->tipo_registro === 'auditoria') {
                $destinoEncontrado = 'auditoria';
            }
        }

        // Obtener códigos disponibles para el destino seleccionado
        $codigosDisponibles = [];
        if ($destino) {
            $prefijo = match($destino) {
                'equipos_debaja' => 'DB',
                'material_didactico' => 'MD',
                'auditoria' => 'AU',
                default => ''
            };
            
            if ($prefijo) {
                // Buscar códigos disponibles con ese prefijo
                $codigos = CodigoDisponible::where('utilizado', false)
                    ->where('codigo', 'like', $prefijo . '%')
                    ->orderBy('codigo')
                    ->limit(20)
                    ->get(['codigo', 'descripcion_original']);
                
                $codigosDisponibles = $codigos->map(function($c) {
                    return [
                        'codigo' => $c->codigo,
                        'descripcion' => $c->descripcion_original
                    ];
                })->toArray();
            }
        }

        return response()->json([
            'ultimo_equipo' => $ultimo ? [
                'codigo' => $ultimo->codigo,
                'codigo_original' => $ultimo->codigo_original ?? null,
                'descripcion' => $ultimo->descripcion,
                'destino' => $destinoEncontrado,
                'fecha' => $ultimo->created_at->format('d/m/Y H:i'),
            ] : null,
            'codigos_disponibles' => $codigosDisponibles
        ]);
    }

    /**
     * Genera el siguiente código disponible con el prefijo especificado.
     * Busca en todas las tablas (equipos, equipos_debaja, material_didactico) para encontrar el último número usado.
     */
    private function generarCodigoConPrefijo(string $prefijo, int $empresaId): string
    {
        // Buscar el último código con este prefijo en todas las tablas
        $ultimoEquipo = Equipo::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->get()
            ->map(function ($item) use ($prefijo) {
                if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max();

        $ultimoDebaja = EquipoDebaja::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->get()
            ->map(function ($item) use ($prefijo) {
                if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max();

        $ultimoMaterial = MaterialDidactico::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->get()
            ->map(function ($item) use ($prefijo) {
                if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max();

        // Obtener el número más alto (si no hay ninguno, empezar desde 1)
        $numeros = array_filter([$ultimoEquipo, $ultimoDebaja, $ultimoMaterial], fn($n) => $n !== null && $n > 0);
        $ultimoNumero = !empty($numeros) ? max($numeros) : 0;

        // Generar el siguiente código
        $siguienteNumero = $ultimoNumero + 1;
        return $prefijo . $siguienteNumero;
    }

    /**
     * Obtiene la lista de códigos disponibles, incluyendo códigos faltantes en la secuencia.
     */
    public function codigosDisponibles(Request $request): \Illuminate\Http\JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        
        // Obtener códigos disponibles de la tabla
        $codigos = CodigoDisponible::where('utilizado', false)
            ->with('equipoOriginal')
            ->orderBy('created_at', 'desc')
            ->get();

        $codigosList = $codigos->map(function ($codigo) {
            return [
                'id' => $codigo->id,
                'codigo' => $codigo->codigo,
                'origen_tipo' => $codigo->origen_tipo,
                'descripcion_original' => $codigo->descripcion_original,
                'fecha_disponible' => $codigo->created_at->format('d/m/Y'),
                'es_secuencia' => false, // Código de la tabla
            ];
        })->toArray();

        // Si hay empresa seleccionada, detectar códigos faltantes en la secuencia
        if ($empresaId) {
            $codigosFaltantes = $this->detectarCodigosFaltantes($empresaId);
            
            // Agregar códigos faltantes a la lista
            foreach ($codigosFaltantes as $codigoFaltante) {
                // Verificar que no esté ya en la lista
                $existe = false;
                foreach ($codigosList as $cod) {
                    if ($cod['codigo'] === $codigoFaltante) {
                        $existe = true;
                        break;
                    }
                }
                
                if (!$existe) {
                    $codigosList[] = [
                        'id' => null, // No tiene ID porque no está en la tabla
                        'codigo' => $codigoFaltante,
                        'origen_tipo' => 'secuencia',
                        'descripcion_original' => 'Código faltante en la secuencia',
                        'fecha_disponible' => '—',
                        'es_secuencia' => true, // Código detectado de la secuencia
                    ];
                }
            }
        }

        // Ordenar por código (numéricamente)
        usort($codigosList, function ($a, $b) {
            // Extraer números de los códigos para ordenar numéricamente
            preg_match('/(\d+)$/', $a['codigo'], $matchesA);
            preg_match('/(\d+)$/', $b['codigo'], $matchesB);
            
            $numA = isset($matchesA[1]) ? (int)$matchesA[1] : 0;
            $numB = isset($matchesB[1]) ? (int)$matchesB[1] : 0;
            
            if ($numA === $numB) {
                return strcmp($a['codigo'], $b['codigo']);
            }
            
            return $numA <=> $numB;
        });

        return response()->json([
            'codigos' => $codigosList,
        ]);
    }
    
    /**
     * Detecta códigos faltantes en la secuencia para una empresa.
     */
    private function detectarCodigosFaltantes(int $empresaId): array
    {
        // Obtener todos los códigos de equipos, equipos_debaja y material_didactico para esta empresa
        $equipos = Equipo::where('empresa_id', $empresaId)->pluck('codigo')->toArray();
        $equiposDebaja = \App\Models\EquipoDebaja::where('empresa_id', $empresaId)->pluck('codigo')->toArray();
        $materialDidactico = \App\Models\MaterialDidactico::where('empresa_id', $empresaId)->pluck('codigo')->toArray();
        
        // Combinar todos los códigos
        $todosLosCodigos = array_merge($equipos, $equiposDebaja, $materialDidactico);
        
        if (empty($todosLosCodigos)) {
            return [];
        }
        
        // Agrupar códigos por prefijo
        $codigosPorPrefijo = [];
        foreach ($todosLosCodigos as $codigo) {
            // Manejar códigos que son solo números (sin prefijo)
            if (preg_match('/^(\d+)$/', $codigo, $matches)) {
                // Código es solo un número
                $prefijo = '';
                $numero = (int)$matches[1];
                
                if (!isset($codigosPorPrefijo[$prefijo])) {
                    $codigosPorPrefijo[$prefijo] = [];
                }
                $codigosPorPrefijo[$prefijo][] = $numero;
            } elseif (preg_match('/^(.+?)(\d+)$/', $codigo, $matches)) {
                // Código tiene prefijo y número
                $prefijo = $matches[1];
                $numero = (int)$matches[2];
                
                if (!isset($codigosPorPrefijo[$prefijo])) {
                    $codigosPorPrefijo[$prefijo] = [];
                }
                $codigosPorPrefijo[$prefijo][] = $numero;
            }
        }
        
        $codigosFaltantes = [];
        
        // Para cada prefijo, detectar números faltantes
        foreach ($codigosPorPrefijo as $prefijo => $numeros) {
            if (empty($numeros)) {
                continue;
            }
            
            // Eliminar duplicados
            $numeros = array_unique($numeros);
            sort($numeros);
            $min = min($numeros);
            $max = max($numeros);
            
            // Obtener el padding del código original (buscar un código con este prefijo)
            $codigoEjemplo = null;
            foreach ($todosLosCodigos as $cod) {
                if ($prefijo === '') {
                    // Código solo numérico
                    if (preg_match('/^(\d+)$/', $cod, $match)) {
                        $codigoEjemplo = $cod;
                        break;
                    }
                } else {
                    // Código con prefijo
                    if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)$/', $cod, $match)) {
                        $codigoEjemplo = $cod;
                        break;
                    }
                }
            }
            
            $padding = 1; // Por defecto sin padding
            if ($codigoEjemplo) {
                if (preg_match('/(\d+)$/', $codigoEjemplo, $matchEjemplo)) {
                    $padding = strlen($matchEjemplo[1]);
                }
            }
            
            // Encontrar números faltantes entre min y max
            for ($i = $min; $i <= $max; $i++) {
                if (!in_array($i, $numeros)) {
                    // Formatear el código con el mismo padding que el original
                    $numeroFormateado = str_pad((string)$i, $padding, '0', STR_PAD_LEFT);
                    $codigoFaltante = $prefijo === '' ? $numeroFormateado : $prefijo . $numeroFormateado;
                    $codigosFaltantes[] = $codigoFaltante;
                }
            }
        }
        
        return $codigosFaltantes;
    }

    /**
     * Prepara un código disponible para crear un equipo (no lo marca como utilizado todavía).
     * Si el código tiene un equipo original, carga todos sus datos.
     */
    public function crearConCodigoDisponible(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'codigo_disponible_id' => 'required|exists:codigos_disponibles,id',
        ]);

        $codigoDisponible = CodigoDisponible::with('equipoOriginal')->findOrFail($request->codigo_disponible_id);

        if ($codigoDisponible->utilizado) {
            return response()->json(['error' => 'Este código ya fue utilizado.'], 400);
        }

        $response = [
            'success' => true,
            'codigo' => $codigoDisponible->codigo,
            'message' => 'Código seleccionado correctamente.',
            'datos_equipo' => null
        ];

        // Si el código tiene un equipo original, cargar todos sus datos
        if ($codigoDisponible->equipo_original_id && $codigoDisponible->equipoOriginal) {
            $equipoOriginal = $codigoDisponible->equipoOriginal;
            
            // Cargar todas las relaciones necesarias
            $equipoOriginal->load(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'empresa', 'sede', 'bodega', 'usoItem']);
            
            // Preparar todos los datos del equipo original
            $response['datos_equipo'] = [
                'codigo' => $codigoDisponible->codigo, // Usar el código disponible, no el original
                'codigo_bloqueado' => $equipoOriginal->codigo_bloqueado ?? false,
                'empresa_id' => $equipoOriginal->empresa_id,
                'tipo_item_id' => $equipoOriginal->tipo_item_id,
                'tipo_equipo_id' => $equipoOriginal->tipo_equipo_id,
                'estado_remision_id' => $equipoOriginal->estado_remision_id,
                'nombre' => $equipoOriginal->nombre ?? '',
                'descripcion' => $equipoOriginal->descripcion ?? '',
                'marca' => $equipoOriginal->marca ?? '',
                'proveedor_id' => $equipoOriginal->proveedor_id,
                'fabricante_id' => $equipoOriginal->fabricante_id,
                'modelo' => $equipoOriginal->modelo ?? '',
                'sede_id' => $equipoOriginal->sede_id,
                'bodega_id' => $equipoOriginal->bodega_id,
                'vida_util' => $equipoOriginal->vida_util ?? 0,
                'fecha_fabricacion' => $equipoOriginal->fecha_fabricacion ? $equipoOriginal->fecha_fabricacion->format('Y-m-d') : null,
                'fecha_uso' => $equipoOriginal->fecha_uso ? $equipoOriginal->fecha_uso->format('Y-m-d') : null,
                'uso_item_id' => $equipoOriginal->uso_item_id,
                'es_kit' => $equipoOriginal->es_kit ?? false,
                'nombre_kit' => $equipoOriginal->nombre_kit ?? '',
                'componentes_kit' => $equipoOriginal->componentes_kit ?? '',
                'valor' => $equipoOriginal->valor ?? 0,
                'numero_factura' => $equipoOriginal->numero_factura ?? '',
                'capacidades_resistencia' => $equipoOriginal->capacidades_resistencia ?? '',
                'lote' => $equipoOriginal->lote ?? '',
                'fecha_compra' => $equipoOriginal->fecha_compra ? $equipoOriginal->fecha_compra->format('Y-m-d') : null,
                'tiene_manual_fabricante' => $equipoOriginal->tiene_manual_fabricante ?? false,
                'tiene_certificacion' => $equipoOriginal->tiene_certificacion ?? false,
                'tiene_imagen_general' => $equipoOriginal->tiene_imagen_general ?? false,
                'tiene_imagen_etiqueta' => $equipoOriginal->tiene_imagen_etiqueta ?? false,
                'imagen_general' => $equipoOriginal->imagen_general ?? '',
                'imagen_etiqueta' => $equipoOriginal->imagen_etiqueta ?? '',
            ];
        }

        // Guardar el código en sesión para prellenarlo en el formulario
        session(['codigo_prellenado' => $codigoDisponible->codigo]);

        return response()->json($response);
    }

    /**
     * Obtiene el último código usado para una empresa.
     */
    public function getUltimoCodigo(Request $request): \Illuminate\Http\JsonResponse
    {
        $empresaId = $request->get('empresa_id');
        
        if (!$empresaId) {
            return response()->json(['ultimo_codigo' => null]);
        }

        // Buscar el último código usado en equipos (incluyendo auditoría), equipos_debaja y material_didactico
        // Ordenar por el número del código, no por ID
        // Incluir equipos normales y de auditoría
        $ultimoEquipo = Equipo::where('empresa_id', $empresaId)
            ->where(function($q) {
                $q->where('tipo_registro', 'normal')
                  ->orWhere('tipo_registro', 'auditoria')
                  ->orWhereNull('tipo_registro');
            })
            ->get()
            ->sortByDesc(function ($item) {
                if (preg_match('/(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->first();
        
        // Excluir equipos de auditoría (códigos AU) de equipos_debaja
        $ultimoDebaja = \App\Models\EquipoDebaja::where('empresa_id', $empresaId)
            ->where('codigo', 'not like', 'AU%')
            ->get()
            ->sortByDesc(function ($item) {
                if (preg_match('/(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->first();
        
        $ultimoMaterial = \App\Models\MaterialDidactico::where('empresa_id', $empresaId)
            ->get()
            ->sortByDesc(function ($item) {
                if (preg_match('/(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->first();

        $ultimo = collect([$ultimoEquipo, $ultimoDebaja, $ultimoMaterial])
            ->filter()
            ->sortByDesc(function ($item) {
                if (preg_match('/(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->first();

        if (!$ultimo) {
            return response()->json(['ultimo_codigo' => null]);
        }

        // Obtener imagen del equipo
        $imagenUrl = null;
        if ($ultimo instanceof Equipo) {
            if ($ultimo->imagen_general) {
                $imagenUrl = \App\Helpers\ImageHelper::asset($ultimo->imagen_general);
            } else {
                // Intentar obtener imagen por código
                $imagenPath = self::primeraImagenEnCarpetaPorCodigo($ultimo->codigo);
                if ($imagenPath) {
                    $relativePath = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $imagenPath);
                    $imagenUrl = asset(str_replace('\\', '/', $relativePath));
                }
            }
        } elseif ($ultimo) {
            // Para EquipoDebaja o MaterialDidactico, intentar obtener imagen por código
            $imagenPath = self::primeraImagenEnCarpetaPorCodigo($ultimo->codigo);
            if ($imagenPath) {
                $relativePath = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $imagenPath);
                $imagenUrl = asset(str_replace('\\', '/', $relativePath));
            }
        }

        return response()->json([
            'ultimo_codigo' => [
                'codigo' => $ultimo->codigo,
                'nombre' => $ultimo->nombre ?? 'Sin nombre',
                'descripcion' => $ultimo->descripcion ?? 'Sin descripción',
                'imagen_url' => $imagenUrl,
            ]
        ]);
    }

    /**
     * Lista equipos debaja.
     */
    public function indexDebaja(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        $empresaId = $request->get('empresa_id');
        $sedeId = $request->get('sede_id');
        $bodegaId = $request->get('bodega_id');
        
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $equipos = EquipoDebaja::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'empresa', 'sede', 'bodega', 'usoItem'])
            // Excluir equipos de auditoría (códigos que empiezan con AU) - estos deben estar en la tabla equipos con tipo_registro = 'auditoria'
            ->where('codigo', 'not like', 'AU%')
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

        // Precalcular URLs de imágenes para carga directa
        foreach ($equipos as $equipo) {
            $equipo->imagen_url = self::getImagenUrlDebaja($equipo);
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

        $tab = 'debaja';
        
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

    /**
     * Lista material didáctico.
     */
    public function indexMaterialDidactico(Request $request): View
    {
        $search = $request->get('q', '');
        $perPage = (int) $request->get('per_page', 10);
        $empresaId = $request->get('empresa_id');
        $sedeId = $request->get('sede_id');
        $bodegaId = $request->get('bodega_id');
        
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $equipos = MaterialDidactico::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'sede', 'bodega', 'usoItem'])
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

        // Precalcular URLs de imágenes para carga directa
        foreach ($equipos as $equipo) {
            $equipo->imagen_url = self::getImagenUrlMaterialDidactico($equipo);
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

        $tab = 'material';
        
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

    /**
     * Detecta códigos saltados entre el último código usado y el código ingresado.
     * Retorna un array con los códigos saltados.
     */
    private function detectarCodigosSaltados(string $codigoIngresado, int $empresaId): array
    {
        // Extraer el número del código (puede ser solo número o con prefijo)
        if (!preg_match('/(\d+)$/', $codigoIngresado, $matches)) {
            return []; // Si no tiene número al final, no podemos detectar saltos
        }
        
        $numeroIngresado = (int) $matches[1];
        $prefijo = preg_replace('/\d+$/', '', $codigoIngresado);
        
        // Buscar todos los códigos con el mismo prefijo en todas las tablas
        $todosLosCodigos = [];
        
        // Equipos normales
        $equipos = Equipo::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->pluck('codigo')
            ->toArray();
        $todosLosCodigos = array_merge($todosLosCodigos, $equipos);
        
        // Equipos de baja
        $equiposDebaja = EquipoDebaja::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->pluck('codigo')
            ->toArray();
        $todosLosCodigos = array_merge($todosLosCodigos, $equiposDebaja);
        
        // Material didáctico
        $materialDidactico = MaterialDidactico::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->pluck('codigo')
            ->toArray();
        $todosLosCodigos = array_merge($todosLosCodigos, $materialDidactico);
        
        // Extraer números de todos los códigos
        $numeros = [];
        foreach ($todosLosCodigos as $codigo) {
            if (preg_match('/(\d+)$/', $codigo, $m)) {
                $numeros[] = (int) $m[1];
                }
        }
        
        if (empty($numeros)) {
            return []; // No hay códigos anteriores
        }
        
        // Encontrar el número más alto
        $ultimoNumero = max($numeros);
        
        // Si el número ingresado es mayor al último + 1, hay códigos saltados
        if ($numeroIngresado > $ultimoNumero + 1) {
            $codigosSaltados = [];
            // Generar todos los códigos faltantes entre el último y el ingresado
            for ($i = $ultimoNumero + 1; $i < $numeroIngresado; $i++) {
                // Si el código original tiene padding (ej: 001, 002), mantenerlo
                if (strlen($matches[1]) > 1 && $matches[1][0] === '0') {
                $codigosSaltados[] = $prefijo . str_pad($i, strlen($matches[1]), '0', STR_PAD_LEFT);
                } else {
                    // Si es solo número, mantener el formato simple
                    $codigosSaltados[] = $prefijo . $i;
                }
            }
            return $codigosSaltados;
        }
        
        return [];
    }

    /**
     * Guarda las imágenes del equipo en el almacén con etiquetas automáticas.
     */
    private function guardarImagenesEnAlmacen(Equipo $equipo, Request $request): void
    {
        // Mapeo de campos a nombres de etiquetas
        $mapeoImagenes = [
            'imagen_general_file' => 'Imagen General',
            'imagen_etiqueta_file' => 'Imagen de Etiqueta',
        ];

        $mapeoArchivos = [
            'manual_fabricante_file' => 'Manual de Fabricante',
            'certificacion_file' => 'Certificación de Fabricante',
        ];

        // Procesar imágenes
        foreach ($mapeoImagenes as $campo => $nombreEtiqueta) {
            if ($request->hasFile($campo)) {
                // Crear o obtener etiqueta
                $etiqueta = EtiquetaAlmacen::firstOrCreate(
                    ['nombre' => $nombreEtiqueta, 'tipo' => 'imagen'],
                    ['nombre' => $nombreEtiqueta, 'tipo' => 'imagen']
                );

                // Eliminar imagen anterior si existe para este equipo y etiqueta
                AlmacenImagen::where('etiqueta_id', $etiqueta->id)
                    ->where('equipo_id', $equipo->id)
                    ->each(function ($img) {
                        if (file_exists(public_path($img->ruta))) {
                            unlink(public_path($img->ruta));
                        } elseif (str_starts_with($img->ruta, 'storage/')) {
                            Storage::disk('public')->delete(str_replace('storage/', '', $img->ruta));
                        }
                        $img->delete();
                    });

                // Guardar imagen en almacén
                $archivo = $request->file($campo);
                $filename = time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
                
                // Crear directorio si no existe
                $directorio = public_path('img/almacen/imagenes');
                if (!File::exists($directorio)) {
                    File::makeDirectory($directorio, 0755, true);
                }
                
                $archivo->move($directorio, $filename);
                $rutaAlmacen = 'img/almacen/imagenes/' . $filename;
                
                AlmacenImagen::create([
                    'etiqueta_id' => $etiqueta->id,
                    'equipo_id' => $equipo->id,
                    'ruta' => $rutaAlmacen,
                    'nombre_original' => $archivo->getClientOriginalName(),
                ]);
            }
        }

        // Procesar archivos (PDFs, etc.)
        foreach ($mapeoArchivos as $campo => $nombreEtiqueta) {
            if ($request->hasFile($campo)) {
                // Crear o obtener etiqueta de archivo
                $etiqueta = EtiquetaAlmacen::firstOrCreate(
                    ['nombre' => $nombreEtiqueta, 'tipo' => 'archivo'],
                    ['nombre' => $nombreEtiqueta, 'tipo' => 'archivo']
                );

                // Eliminar archivo anterior si existe para este equipo y etiqueta
                AlmacenArchivo::where('etiqueta_id', $etiqueta->id)
                    ->where('equipo_id', $equipo->id)
                    ->where('nombre', $nombreEtiqueta)
                    ->each(function ($arch) {
                        if (file_exists(public_path($arch->ruta))) {
                            unlink(public_path($arch->ruta));
                        } elseif (str_starts_with($arch->ruta, 'storage/')) {
                            Storage::disk('public')->delete(str_replace('storage/', '', $arch->ruta));
                        }
                        $arch->delete();
                    });

                // Guardar archivo en almacén
                $archivo = $request->file($campo);
                $filename = time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
                
                // Crear directorio si no existe
                $directorio = public_path('img/almacen/archivos');
                if (!File::exists($directorio)) {
                    File::makeDirectory($directorio, 0755, true);
                }
                
                $archivo->move($directorio, $filename);
                $rutaAlmacen = 'img/almacen/archivos/' . $filename;
                
                AlmacenArchivo::create([
                    'nombre' => $nombreEtiqueta,
                    'ruta' => $rutaAlmacen,
                    'etiqueta_id' => $etiqueta->id,
                    'equipo_id' => $equipo->id,
                    'mime_type' => $archivo->getMimeType(),
                ]);
            }
        }
    }

    /**
     * Verifica la contraseña para desbloquear código de equipo.
     */
    public function verificarPasswordCodigo(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $seguridad = SeguridadCodigo::obtener();
        
        if (!$seguridad->password_edicion || !Hash::check($request->password, $seguridad->password_edicion)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta.'
            ], 400);
        }

        // Contraseña correcta, generar nueva automáticamente
        $nuevaPassword = bin2hex(random_bytes(8));
        $seguridad->update([
            'password_edicion' => Hash::make($nuevaPassword),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña correcta.'
        ]);
    }
}
