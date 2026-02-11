<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SincronizarIdItemLegacy extends Command
{
    protected $signature = 'sams:sincronizar-id-item-legacy 
                            {--sql= : Ruta al archivo sams_19_01_2026.sql}
                            {--from-folders : Rellenar id_item_legacy desde carpetas Items/{id} (id = equipo->id)}';

    protected $description = 'Extrae id_item->codigo del SQL y actualiza id_item_legacy en equipos; o rellena desde carpetas Items/';

    public function handle(): int
    {
        if ($this->option('from-folders')) {
            return $this->sincronizarDesdeCarpetas();
        }

        $sqlPath = $this->option('sql') ?? base_path('sams_19_01_2026.sql');
        if (!File::exists($sqlPath)) {
            $this->error("No se encontró el archivo SQL: {$sqlPath}");
            $this->info('Usa --from-folders para rellenar id_item_legacy según carpeta Items/{equipo->id}.');
            return Command::FAILURE;
        }

        $content = File::get($sqlPath);
        $mapping = $this->extraerMapeoDesdeSql($content);
        $this->info('Mapeo encontrado: ' . count($mapping) . ' items (id_item -> codigo)');

        $actualizados = 0;
        foreach ($mapping as $idItem => $codigo) {
            $n = Equipo::where('codigo', $codigo)->update(['id_item_legacy' => $idItem]);
            $actualizados += $n;
        }

        $this->info("Equipos actualizados con id_item_legacy: {$actualizados}");
        return Command::SUCCESS;
    }

    /**
     * Rellena id_item_legacy cuando existe la carpeta resources/css/img/Items/{equipo->id}.
     * Así todos los equipos cuya carpeta coincide con su ID tendrán imagen legacy.
     */
    protected function sincronizarDesdeCarpetas(): int
    {
        $basePath = base_path('resources/css/img/Items');
        if (!File::isDirectory($basePath)) {
            $this->error('No existe la carpeta resources/css/img/Items');
            return Command::FAILURE;
        }

        $actualizados = 0;
        $bar = $this->output->createProgressBar(Equipo::count());
        $bar->start();

        Equipo::orderBy('id')->chunk(200, function ($equipos) use ($basePath, &$actualizados, $bar) {
            foreach ($equipos as $equipo) {
                $carpeta = $basePath . DIRECTORY_SEPARATOR . $equipo->id;
                if (File::isDirectory($carpeta) && $this->carpetaTieneImagen($carpeta)) {
                    $equipo->id_item_legacy = $equipo->id;
                    $equipo->save();
                    $actualizados++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Equipos actualizados con id_item_legacy (desde carpetas): {$actualizados}");
        return Command::SUCCESS;
    }

    protected function carpetaTieneImagen(string $carpeta): bool
    {
        $archivos = File::files($carpeta);
        foreach ($archivos as $archivo) {
            $ext = strtolower($archivo->getExtension());
            $filename = $archivo->getFilename();
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                && !str_starts_with($filename, '.')
                && !str_starts_with(strtolower($filename), 'thumb')) {
                return true;
            }
        }
        return false;
    }

    protected function extraerMapeoDesdeSql(string $content): array
    {
        $mapping = [];
        if (!preg_match("/insert\s+into\s+`items`[^v]+values\s*(.+?);\s*(?=\s*(?:\/\*|insert|create|DROP)|\$)/is", $content, $block)) {
            return $mapping;
        }
        $valuesBlock = $block[1];
        // Formato: (id_item,bodega,proveedor,marca,usuario,tipoItem,'codigo_item',...
        if (preg_match_all("/\((\d+),\d+,\d+,\d+,\d+,\d+,['\"]([^'\"]+)['\"]/", $valuesBlock, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $idItem = (int) $m[1];
                $codigo = trim($m[2]);
                if ($idItem > 0 && $codigo !== '' && $codigo !== 'null') {
                    $mapping[$idItem] = $codigo;
                }
            }
        }
        return $mapping;
    }
}
