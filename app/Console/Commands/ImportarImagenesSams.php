<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use App\Services\ItemSqlParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportarImagenesSams extends Command
{
    protected $signature = 'sams:importar-imagenes 
                            {--dry-run : Solo mostrar qué se importaría, sin copiar ni guardar}
                            {--origen= : Ruta base (por defecto: resources/css/img y public/img)}
                            {--sql= : Ruta al SQL de items (por defecto: sams_19_01_2026.sql) para mapeo codigo→carpeta}';

    protected $description = 'Copia imágenes desde img/Items (resources/css/img y public/img) a storage y las vincula con los equipos usando el mapeo codigo→carpeta y id_item_legacy';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Modo dry-run: no se copiarán archivos ni se actualizará la base de datos.');
        }

        $storageBase = storage_path('app/public');
        $dirs = ['equipos', 'equipos/imagenes', 'equipos/etiquetas', 'usuarios', 'logos'];
        foreach ($dirs as $dir) {
            $path = "{$storageBase}/" . trim($dir, '/');
            if (!File::exists($path)) {
                if (!$dryRun) {
                    File::makeDirectory($path, 0755, true);
                }
            }
        }

        $this->info('Importando imágenes de equipos desde img/Items...');
        $actualizados = $this->importarImagenesEquipos($dryRun);
        $this->info("Equipos actualizados con imagen: {$actualizados}");

        $origenBase = base_path('resources/css/img');
        $this->info('Importando logos...');
        $this->importarLogos($origenBase, $storageBase, $dryRun);

        $this->info('✅ Importación de imágenes completada.');
        return Command::SUCCESS;
    }

    protected function importarImagenesEquipos(bool $dryRun): int
    {
        $mapPath = config_path('equipo_image_map.php');
        $codigoToFolderId = file_exists($mapPath) ? require $mapPath : [];
        if (!is_array($codigoToFolderId)) {
            $codigoToFolderId = [];
        }

        // Mapa desde SQL: codigo → id_item (carpeta img/Items/{id_item}) para alcanzar ~95% cobertura
        $sqlPath = $this->option('sql') ? base_path($this->option('sql')) : base_path('sams_19_01_2026.sql');
        $codigoToIdItemSql = [];
        if (File::exists($sqlPath)) {
            $this->info('Cargando mapa codigo→id_item desde SQL...');
            $codigoToIdItemSql = ItemSqlParser::codigoToIdItem($sqlPath);
            $this->info('Códigos en SQL: ' . count($codigoToIdItemSql));
        }

        // folderId => equipo. Prioridad: id_item_legacy, luego config, luego SQL
        $folderToEquipo = [];
        foreach (Equipo::select('id', 'codigo', 'id_item_legacy')->get() as $equipo) {
            $folderId = $equipo->id_item_legacy
                ?? ($codigoToFolderId[$equipo->codigo] ?? null)
                ?? ($codigoToIdItemSql[trim((string) $equipo->codigo)] ?? null);
            if ($folderId !== null) {
                $folderToEquipo[(int) $folderId] = $equipo;
            }
        }
        // Equipos que no entraron por id_legacy pero sí por código en config (evitar huecos)
        foreach ($codigoToFolderId as $codigo => $folderId) {
            $folderId = (int) $folderId;
            if (!isset($folderToEquipo[$folderId])) {
                $equipo = Equipo::where('codigo', $codigo)->first();
                if ($equipo) {
                    $folderToEquipo[$folderId] = $equipo;
                }
            }
        }

        $extensiones = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $actualizados = 0;

        foreach ($folderToEquipo as $folderId => $equipo) {
            $archivos = $this->obtenerImagenesEnCarpetaItems($folderId, $extensiones);
            if ($archivos->isEmpty()) {
                continue;
            }
            // Ordenar: primera = mejor imagen "general" del equipo (no etiqueta/serial/doc)
            $ordenados = $this->ordenarImagenesGeneralPrimero($archivos);
            $imagenGeneral = null;
            $imagenEtiqueta = null;
            $equiposPath = storage_path('app/public/equipos');
            $destDir = "{$equiposPath}/{$equipo->id}";

            if (!$dryRun && !File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }

            foreach ($ordenados->take(2) as $i => $file) {
                $nombre = $file->getFilename();
                $rutaRelativa = "equipos/{$equipo->id}/{$nombre}";
                if ($i === 0) {
                    $imagenGeneral = $rutaRelativa;
                }
                if ($i === 1) {
                    $imagenEtiqueta = $rutaRelativa;
                }
                if (!$dryRun) {
                    $destPath = "{$destDir}/{$nombre}";
                    File::copy($file->getPathname(), $destPath);
                }
            }

            if ($imagenGeneral || $imagenEtiqueta) {
                if (!$dryRun) {
                    $equipo->tiene_imagen_general = (bool) $imagenGeneral;
                    $equipo->imagen_general = $imagenGeneral ?? $equipo->imagen_general;
                    $equipo->tiene_imagen_etiqueta = (bool) $imagenEtiqueta;
                    $equipo->imagen_etiqueta = $imagenEtiqueta ?? $equipo->imagen_etiqueta;
                    // Dejar id_item_legacy guardado si se asignó por mapa SQL/config
                    if ($equipo->id_item_legacy === null) {
                        $equipo->id_item_legacy = $folderId;
                    }
                    $equipo->save();
                }
                $actualizados++;
            }
        }

        return $actualizados;
    }

    /**
     * Devuelve colección de archivos de imagen en la carpeta Items/{folderId},
     * buscando en public/img/Items y luego en resources/css/img/Items.
     */
    protected function obtenerImagenesEnCarpetaItems(int $folderId, array $extensiones): \Illuminate\Support\Collection
    {
        $carpetas = [
            public_path("img/Items/{$folderId}"),
            base_path("resources/css/img/Items/{$folderId}"),
        ];

        foreach ($carpetas as $dir) {
            if (!File::isDirectory($dir)) {
                continue;
            }
            $archivos = collect(File::files($dir))->filter(function ($file) use ($extensiones) {
                $nombre = $file->getFilename();
                if (str_starts_with($nombre, '.')) {
                    return false;
                }
                if (stripos($nombre, 'thumb') !== false) {
                    return false;
                }
                if (strpos($nombre, '.Metadatos') !== false) {
                    return false;
                }
                $ext = strtolower($file->getExtension());
                return in_array($ext, $extensiones);
            })->values();
            if ($archivos->isNotEmpty()) {
                return $archivos;
            }
        }

        return collect();
    }

    /**
     * Ordena archivos para que la primera sea la mejor "imagen general" del equipo
     * (evita etiqueta, serial, documento, logo). Excluye logos (SAI, icono, etc.) de imagen general.
     */
    protected function ordenarImagenesGeneralPrimero(\Illuminate\Support\Collection $archivos): \Illuminate\Support\Collection
    {
        $logoPalabras = ['logo', 'icon', 'icono', 'sai', 'sams', 'marca', 'purafil', 'label'];
        $etiquetaPalabras = ['etiqueta', 'tag', 'serial', 'normatividad', 'certific', 'doc', 'imagen etiqueta'];
        $generalPalabras = ['general', 'equipo', 'producto', 'imagen', 'foto', 'principal'];
        return $archivos->filter(function ($file) use ($logoPalabras) {
            $n = strtolower($file->getFilename());
            foreach ($logoPalabras as $p) {
                if (strpos($n, $p) !== false) {
                    return false;
                }
            }
            return true;
        })->sortByDesc(function ($file) use ($etiquetaPalabras, $generalPalabras) {
            $n = strtolower($file->getFilename());
            $score = 0;
            foreach ($etiquetaPalabras as $p) {
                if (strpos($n, $p) !== false) $score -= 2;
            }
            foreach ($generalPalabras as $p) {
                if (strpos($n, $p) !== false) $score += 2;
            }
            return $score * 1000 + $file->getSize();
        })->values();
    }

    protected function importarLogos(string $origenBase, string $storageBase, bool $dryRun): void
    {
        $logosOrigen = "{$origenBase}/logos";
        $logosDestino = "{$storageBase}/logos";

        if (File::isDirectory($logosOrigen) && !$dryRun) {
            if (!File::exists($logosDestino)) {
                File::makeDirectory($logosDestino, 0755, true);
            }
            foreach (File::files($logosOrigen) as $file) {
                $dest = "{$logosDestino}/{$file->getFilename()}";
                if (!File::exists($dest)) {
                    File::copy($file->getPathname(), $dest);
                }
            }
        }

        $logosPrincipales = ['Logo-sams.png', 'Logo-sams-light.png', 'logo.png', 'Perfil.png', 'yedi.png'];
        foreach ($logosPrincipales as $logo) {
            $origen = "{$origenBase}/{$logo}";
            if (File::exists($origen) && !$dryRun) {
                $dest = "{$storageBase}/logos/{$logo}";
                if (!File::exists($dest)) {
                    File::copy($origen, $dest);
                }
            }
        }
    }
}
