<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use App\Services\ItemSqlParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SincronizarEquiposDesdeSql extends Command
{
    protected $signature = 'sams:sincronizar-desde-sql 
                            {archivo=sams_19_01_2026.sql : Ruta al archivo SQL (items)}
                            {--dry-run : Solo mostrar qué se actualizaría}
                            {--force : No pedir confirmación}';

    protected $description = 'Rellena equipos desde la base sams_19_01_2026.sql (tabla items) por código; asegura que campos e imágenes concuerden';

    public function handle(): int
    {
        $archivo = $this->argument('archivo');
        $ruta = base_path($archivo);
        if (!File::exists($ruta)) {
            $this->error("No se encontró el archivo: {$ruta}");
            return 1;
        }

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        if ($dryRun) {
            $this->warn('Modo dry-run: no se modificará la base de datos.');
        }

        $this->info('Leyendo items desde SQL...');
        $items = ItemSqlParser::extraerItems($ruta);
        if (empty($items)) {
            $this->error('No se pudieron extraer items del SQL.');
            return 1;
        }
        $this->info('Items encontrados en SQL: ' . count($items));

        $actualizados = 0;
        $porCampo = [];

        foreach ($items as $item) {
            $codigo = $item['codigo_item'] ?? null;
            if (!$codigo) {
                continue;
            }
            $equipo = Equipo::where('codigo', $codigo)->first();
            if (!$equipo) {
                continue;
            }

            $cambios = [];
            // id_item del SQL → id_item_legacy en Equipo (para que img/Items/{id_item} concuerde)
            $idItemLegacy = isset($item['id_item']) ? (int) $item['id_item'] : null;
            if ($idItemLegacy !== null && ($equipo->id_item_legacy === null || $equipo->id_item_legacy != $idItemLegacy)) {
                $cambios['id_item_legacy'] = $idItemLegacy;
                $porCampo['id_item_legacy'] = ($porCampo['id_item_legacy'] ?? 0) + 1;
            }

            $mapeo = [
                'descripcion_item' => 'descripcion',
                'modelo_item' => 'modelo',
                'serial_item' => 'modelo', // si modelo está vacío, usar serial
                'lote_item' => 'lote',
                'numeroFactura_item' => 'numero_factura',
                'capacidad_item' => 'capacidades_resistencia',
                'nombreKit_item' => 'nombre_kit',
                'informacionKit_item' => 'componentes_kit',
            ];

            foreach ($mapeo as $colSql => $colEquipo) {
                $v = $this->valorLimpio($item[$colSql] ?? null);
                if ($v === null || $v === '') {
                    continue;
                }
                $actual = $equipo->{$colEquipo};
                if ($colSql === 'serial_item' && ($actual !== null && $actual !== '')) {
                    continue;
                }
                if ($colSql === 'serial_item') {
                    if (($item['modelo_item'] ?? null) !== null && ($item['modelo_item'] ?? '') !== '') {
                        continue;
                    }
                }
                $cambios[$colEquipo] = $v;
                $porCampo[$colEquipo] = ($porCampo[$colEquipo] ?? 0) + 1;
            }

            if (!empty($cambios) && !$dryRun) {
                $equipo->update($cambios);
                $actualizados++;
            } elseif (!empty($cambios)) {
                $actualizados++;
            }
        }

        $this->info("Equipos actualizados desde SQL: {$actualizados}");
        foreach ($porCampo as $campo => $n) {
            $this->line("  - {$campo}: {$n}");
        }

        if (!$dryRun) {
            $codigoToId = [];
            foreach ($items as $item) {
                $codigo = $item['codigo_item'] ?? null;
                $id = isset($item['id_item']) ? (int) $item['id_item'] : null;
                if ($codigo !== null && $codigo !== '' && $id !== null) {
                    $codigoToId[trim((string) $codigo)] = $id;
                }
            }
            $cachePath = storage_path('app/equipo_codigo_to_id_sql.php');
            File::put($cachePath, '<?php return ' . var_export($codigoToId, true) . ';');
            $this->info('Mapa codigo→id_item guardado en storage/app para imágenes (' . count($codigoToId) . ' códigos).');
        }

        if (!$dryRun && $actualizados > 0) {
            $this->info('Ejecuta sams:importar-imagenes para que las imágenes en img/Items concuerden con cada equipo.');
        }

        return 0;
    }

    private function valorLimpio($v)
    {
        if ($v === null) {
            return null;
        }
        $v = trim((string) $v);
        if (strtoupper($v) === 'NULL' || $v === '' || strtolower($v) === 'n/r' || strtolower($v) === 'n/a') {
            return null;
        }
        return $v;
    }

}
