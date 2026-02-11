<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Parsea el archivo SQL de la base legacy (tabla items) para extraer
 * id_item y codigo_item y usarlos en sincronización e imágenes.
 */
class ItemSqlParser
{
    protected static array $columnas = [
        'id_item', 'bodega_item', 'proveedor_item', 'marca_item', 'usuario_item', 'tipoItem_item',
        'codigo_item', 'formato_item', 'fechaCompra_item', 'fechaVencimiento_item', 'numeroFactura_item',
        'descripcion_item', 'modelo_item', 'serial_item', 'lote_item', 'valor_item', 'vidaUtil_item',
        'fechaFabricacion_item', 'fechaPuestaUso_item', 'cumpleNormas_item', 'capacidad_item',
        'otrasEspecificacionesTecnicas_item', 'manualFabricante_item', 'certificacionFabricante_item',
        'estado_item', 'asignacion_item', 'responsableMantenimiento_item', 'tipoEquipo_item', 'uso_item',
        'otroUso_item', 'nombreKit_item', 'informacionKit_item', 'periodicidadInspeccion_item',
        'periodicidadMantenimiento_item', 'periodicidadAlmacenamiento_item', 'estadoInpe',
        'fechaCrea_item', 'fechaEdit_item', 'usuarioModi_item', 'codigo_anterior',
    ];

    /**
     * Extrae todas las filas de la tabla items del SQL.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function extraerItems(string $ruta): array
    {
        $content = File::get($ruta);
        if (!preg_match('/insert\s+into\s+`items`\s*\([^)]+\)\s*values\s*(.+);/is', $content, $m)) {
            return [];
        }
        $rows = self::parsearValoresInsert($m[1]);
        $out = [];
        foreach ($rows as $row) {
            $assoc = [];
            foreach (self::$columnas as $i => $col) {
                $assoc[$col] = $row[$i] ?? null;
            }
            $out[] = $assoc;
        }
        return $out;
    }

    /**
     * Devuelve mapa codigo_item => id_item para usar en imágenes (carpeta Items/{id_item}).
     *
     * @return array<string, int>
     */
    public static function codigoToIdItem(string $ruta): array
    {
        $items = self::extraerItems($ruta);
        $map = [];
        foreach ($items as $item) {
            $codigo = $item['codigo_item'] ?? null;
            $id = isset($item['id_item']) ? (int) $item['id_item'] : null;
            if ($codigo !== null && $codigo !== '' && $id !== null) {
                $map[trim((string) $codigo)] = $id;
            }
        }
        return $map;
    }

    private static function parsearValoresInsert(string $valuesBlock): array
    {
        $rows = [];
        $len = strlen($valuesBlock);
        $pos = 0;
        while ($pos < $len) {
            $pos = self::saltarBlanco($valuesBlock, $pos);
            if ($pos >= $len) {
                break;
            }
            if ($valuesBlock[$pos] === '(') {
                $pos++;
                $row = [];
                while ($pos < $len) {
                    $pos = self::saltarBlanco($valuesBlock, $pos);
                    if ($pos >= $len) {
                        break;
                    }
                    if ($valuesBlock[$pos] === ')') {
                        $pos++;
                        $rows[] = $row;
                        break;
                    }
                    if ($valuesBlock[$pos] === ',') {
                        $pos++;
                        continue;
                    }
                    $val = self::extraerValor($valuesBlock, $pos, $pos);
                    $row[] = $val;
                }
            } else {
                $pos++;
            }
        }
        return $rows;
    }

    private static function saltarBlanco(string $s, int $pos): int
    {
        while ($pos < strlen($s) && ctype_space($s[$pos])) {
            $pos++;
        }
        return $pos;
    }

    private static function extraerValor(string $s, int $start, int &$end): ?string
    {
        $end = self::saltarBlanco($s, $start);
        if ($end >= strlen($s)) {
            return null;
        }
        if (strtoupper(substr($s, $end, 4)) === 'NULL') {
            $end += 4;
            return null;
        }
        if ($s[$end] === "'") {
            $end++;
            $out = '';
            while ($end < strlen($s)) {
                $c = $s[$end];
                if ($c === '\\' && $end + 1 < strlen($s)) {
                    $end++;
                    $out .= $s[$end];
                    $end++;
                    continue;
                }
                if ($c === "'") {
                    $end++;
                    return $out;
                }
                $out .= $c;
                $end++;
            }
            return $out;
        }
        $out = '';
        while ($end < strlen($s) && $s[$end] !== ',' && $s[$end] !== ')') {
            $out .= $s[$end];
            $end++;
        }
        $out = trim($out);
        return $out === '' ? null : $out;
    }
}
