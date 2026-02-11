<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportarEquiposDesdeSql extends Command
{
    protected $signature = 'sams:importar-equipos-desde-sql 
                            {--sql= : Ruta al archivo sams_19_01_2026.sql}
                            {--skip-existing : No actualizar equipos que ya existan por código}';

    protected $description = 'Importa todos los equipos (items) desde el SQL del sistema SAMS antiguo a la tabla equipos';

    public function handle(): int
    {
        $sqlPath = $this->option('sql') ?? base_path('sams_19_01_2026.sql');
        if (!File::exists($sqlPath)) {
            $this->error("No se encontró el archivo SQL: {$sqlPath}");
            return Command::FAILURE;
        }

        $this->info('Leyendo archivo SQL...');
        $content = File::get($sqlPath);

        $rows = $this->extraerItemsDesdeSqlPorPosicion($content);
        if (empty($rows)) {
            $rows = $this->extraerItemsDesdeSql($content);
        }
        $this->info('Registros encontrados en items: ' . count($rows));

        if (empty($rows)) {
            $this->warn('No se encontraron registros en la tabla items del SQL.');
            return Command::SUCCESS;
        }

        $tipoItemId = DB::table('tipo_items')->value('id');
        $estadoRemisionId = DB::table('estado_remisiones')->value('id');
        $sedeId = DB::table('sedes')->value('id');
        $bodegaId = DB::table('bodegas')->value('id');
        $usoItemId = DB::table('uso_items')->value('id');

        if (!$tipoItemId || !$estadoRemisionId || !$sedeId || !$bodegaId) {
            $this->error('Faltan datos base: ejecuta antes los seeders de tipo_items, estado_remisiones, sedes y bodegas.');
            return Command::FAILURE;
        }

        $skipExisting = $this->option('skip-existing');
        $insertados = 0;
        $omitidos = 0;
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            $codigo = $row['codigo'];
            $existe = DB::table('equipos')->where('codigo', $codigo)->exists();
            if ($existe) {
                if ($skipExisting) {
                    $omitidos++;
                    $bar->advance();
                    continue;
                }
                // Actualizar id_item_legacy si ya existe el equipo
                DB::table('equipos')->where('codigo', $codigo)->update([
                    'id_item_legacy' => $row['id_item'],
                    'updated_at' => now(),
                ]);
                $omitidos++;
                $bar->advance();
                continue;
            }

            $fechaCompra = $this->normalizarFecha($row['fecha_compra'] ?? null);
            $fechaFabricacion = $this->normalizarFecha($row['fecha_fabricacion'] ?? null);
            $fechaUso = $this->normalizarFecha($row['fecha_uso'] ?? null);

            DB::table('equipos')->insert([
                'tipo_item_id' => $tipoItemId,
                'codigo' => $codigo,
                'id_item_legacy' => $row['id_item'],
                'codigo_bloqueado' => true,
                'estado_remision_id' => $estadoRemisionId,
                'descripcion' => $row['descripcion'] ?? $codigo,
                'marca' => $row['marca'] ?? null,
                'proveedor_id' => null,
                'fabricante_id' => null,
                'modelo' => $row['modelo'] ?? null,
                'sede_id' => $sedeId,
                'bodega_id' => $bodegaId,
                'vida_util' => (int) ($row['vida_util'] ?? 10),
                'fecha_fabricacion' => $fechaFabricacion,
                'fecha_uso' => $fechaUso,
                'uso_item_id' => $usoItemId,
                'es_kit' => !empty($row['nombre_kit']),
                'nombre_kit' => $row['nombre_kit'] ?? null,
                'componentes_kit' => $row['componentes_kit'] ?? null,
                'valor' => $row['valor'] ?? null,
                'numero_factura' => $row['numero_factura'] ?? null,
                'capacidades_resistencia' => $row['capacidades_resistencia'] ?? null,
                'lote' => $row['lote'] ?? null,
                'fecha_compra' => $fechaCompra,
                'tiene_manual_fabricante' => ($row['manual_fabricante'] ?? '0') === '1',
                'tiene_certificacion' => ($row['certificacion_fabricante'] ?? '0') === '1',
                'tiene_imagen_general' => false,
                'tiene_imagen_etiqueta' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $insertados++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Equipos insertados: {$insertados}");
        $this->info("Omitidos/actualizados (ya existían): {$omitidos}");
        $this->info('Total equipos en BD: ' . DB::table('equipos')->count());

        return Command::SUCCESS;
    }

    protected function normalizarFecha(?string $fecha): ?string
    {
        if ($fecha === null || $fecha === '' || $fecha === '0000-00-00') {
            return null;
        }
        $ts = strtotime($fecha);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    /**
     * Extrae filas de la tabla items del SQL.
     * Columnas: id_item, bodega, proveedor, marca, usuario, tipoItem, codigo, formato,
     * fechaCompra, fechaVencimiento, numeroFactura, descripcion, modelo, serial, lote,
     * valor, vidaUtil, fechaFabricacion, fechaPuestaUso, ..., manual, certificacion, ...
     */
    protected function extraerItemsDesdeSql(string $content): array
    {
        $rows = [];
        // Bloque VALUES: termina en ); seguido de comentario, DROP, insert, create o fin
        if (!preg_match("/insert\s+into\s+`items`[^v]+values\s*(.+?);\s*(?=\s*(?:\/\*|insert|create|DROP)|\$)/is", $content, $block)) {
            return $rows;
        }
        $valuesBlock = $block[1];

        // Cada fila empieza con (id, ... y tiene codigo en posición 7 y descripcion en posición 12
        // Patrón: (id,num,num,num,num,num,'codigo',...,'descripcion',...
        // Capturamos id, codigo, y los campos entre medias para llegar a descripcion (5 strings después de codigo: formato puede ser NULL)
        $pattern = "/\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*'((?:[^']|'')*)',\s*(?:NULL|\d+),\s*'((?:[^']|'')*)',\s*'((?:[^']|'')*)',\s*'((?:[^']|'')*)',\s*'((?:[^']|'')*)',\s*'((?:[^']|'')*)',\s*'((?:[^']|'')*)',\s*'((?:[^']|'')*)',\s*'((?:[^']|'')*)',\s*([^,]+),\s*(\d+),\s*'(?:[^']|'')*',\s*'(?:[^']|'')*',\s*(?:NULL|'(?:[^']|'')*'),\s*(?:NULL|'(?:[^']|'')*'),\s*'([01])',\s*'([01])'/";
        if (preg_match_all($pattern, $valuesBlock, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $rows[] = [
                    'id_item' => (int) $m[1],
                    'codigo' => $this->desescapar($m[7]),
                    'fecha_compra' => $m[8],
                    'fecha_vencimiento' => $m[9],
                    'numero_factura' => $m[10],
                    'descripcion' => $this->desescapar($m[11]),
                    'modelo' => $this->desescapar($m[12]),
                    'serial' => $this->desescapar($m[13]),
                    'lote' => $this->desescapar($m[14]),
                    'valor' => $m[15],
                    'vida_util' => (int) $m[16],
                    'manual_fabricante' => $m[17] ?? '0',
                    'certificacion_fabricante' => $m[18] ?? '0',
                ];
            }
            return $rows;
        }

        // Fallback: solo id y codigo (descripcion = codigo)
        if (preg_match_all("/\((\d+),\d+,\d+,\d+,\d+,\d+,\s*['\"]([^'\"]+)['\"]/", $valuesBlock, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $idItem = (int) $m[1];
                $codigo = trim($m[2]);
                if ($idItem > 0 && $codigo !== '' && $codigo !== 'null') {
                    $rows[] = [
                        'id_item' => $idItem,
                        'codigo' => $codigo,
                        'descripcion' => $codigo,
                        'fecha_compra' => null,
                        'fecha_fabricacion' => null,
                        'fecha_uso' => null,
                        'numero_factura' => null,
                        'modelo' => null,
                        'lote' => null,
                        'valor' => null,
                        'vida_util' => 10,
                        'manual_fabricante' => '0',
                        'certificacion_fabricante' => '0',
                        'nombre_kit' => null,
                        'componentes_kit' => null,
                        'capacidades_resistencia' => null,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * Extrae el bloque VALUES por posiciones (tabla `items` exacta, no items_asignaciones).
     */
    protected function extraerItemsDesdeSqlPorPosicion(string $content): array
    {
        $start = strpos($content, 'into `items`(');
        if ($start === false) {
            return [];
        }
        $valuesStart = strpos($content, 'values ', $start);
        if ($valuesStart === false) {
            return [];
        }
        $valuesStart += 7; // longitud de "values "
        $nextTable = strpos($content, '/*Table structure for table `items_asignaciones`', $valuesStart);
        if ($nextTable === false) {
            return [];
        }
        $chunk = substr($content, $valuesStart, $nextTable - $valuesStart);
        $lastParen = strrpos($chunk, ');');
        if ($lastParen === false) {
            return [];
        }
        $valuesBlock = substr($chunk, 0, $lastParen);

        $rows = [];
        if (preg_match_all("/\((\d+),\d+,\d+,\d+,\d+,\d+,\s*['\"]([^'\"]+)['\"]/", $valuesBlock, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $idItem = (int) $m[1];
                $codigo = trim($m[2]);
                if ($idItem > 0 && $codigo !== '' && $codigo !== 'null') {
                    $rows[] = [
                        'id_item' => $idItem,
                        'codigo' => $codigo,
                        'descripcion' => $codigo,
                        'fecha_compra' => null,
                        'fecha_fabricacion' => null,
                        'fecha_uso' => null,
                        'numero_factura' => null,
                        'modelo' => null,
                        'lote' => null,
                        'valor' => null,
                        'vida_util' => 10,
                        'manual_fabricante' => '0',
                        'certificacion_fabricante' => '0',
                        'nombre_kit' => null,
                        'componentes_kit' => null,
                        'capacidades_resistencia' => null,
                    ];
                }
            }
        }
        return $rows;
    }

    protected function desescapar(string $s): string
    {
        return str_replace("''", "'", trim($s));
    }
}
