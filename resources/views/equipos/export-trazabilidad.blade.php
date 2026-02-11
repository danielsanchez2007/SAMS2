<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PROGRAMA DE TRAZABILIDAD DE EQUIPOS DE TAREAS CRITICAS</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; }
        .header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .logos { display: flex; gap: 24px; align-items: center; }
        .logos img { max-height: 50px; }
        .doc-info { text-align: right; font-size: 9px; }
        h1 { font-size: 14px; font-weight: bold; text-align: center; margin: 12px 0 8px; }
        .objetivo { font-size: 9px; margin-bottom: 8px; text-align: justify; }
        .meta { font-weight: bold; margin-bottom: 4px; }
        .indicador { font-size: 9px; margin-bottom: 12px; }
        .subtitle { font-size: 10px; font-weight: bold; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 3px 5px; }
        th { background: #5B9BD5; color: #fff; font-size: 8px; font-weight: bold; text-align: center; }
        tr:nth-child(even) { background: #D6DCE4; }
        tr:nth-child(odd) { background: #fff; }
        .codigo-link { color: #0563C1; text-decoration: underline; }
        .celda-p { background: #FFEB9C; text-align: center; }
        .celda-e { background: #C6EFCE; text-align: center; }
    </style>
</head>
<body>
    <div class="header-row">
        <div class="logos">
            @if(!empty($logoPreventionBase64))
                <img src="{{ $logoPreventionBase64 }}" alt="Prevention World">
            @endif
            @if(!empty($logoSamsBase64))
                <img src="{{ $logoSamsBase64 }}" alt="SAMS">
            @endif
        </div>
        <div class="doc-info">
            Versión: 04<br>
            Fecha: {{ date('d/m/Y') }}<br>
            Código: 6603<br>
            Página 1 de 1
        </div>
    </div>

    <h1>PROGRAMA DE TRAZABILIDAD DE EQUIPOS DE TAREAS CRITICAS</h1>
    <div class="objetivo">
        Asegurar la trazabilidad y garantizar la vida útil de los EPCC, estructuras y prevenir accidentes laborales y pérdidas de días de trabajo, manteniendo en óptimas condiciones los equipos de protección contra caídas.
    </div>
    <div class="meta">Meta: 100%</div>
    <div class="indicador">
        Indicadores: Mantenimientos Preventivos ejecutados / Total mantenimiento preventivo programado x 100 | Inspecciones Ejecutadas / Total Inspecciones
    </div>
    <div class="subtitle">Para la programación de los mantenimientos e inspecciones</div>

    <table>
        <thead>
            <tr>
                <th>ITEM</th>
                <th>CÓDIGO INTERNO</th>
                <th>EQUIPO</th>
                <th>SERIAL/MODELO</th>
                <th>FECHA PROG. INSPECCIÓN</th>
                <th>P</th>
                <th>E</th>
                <th>FECHA PROG. MANT.</th>
                <th>P</th>
                <th>E</th>
                <th>FECHA PROG. MANT.</th>
                <th>P</th>
                <th>E</th>
                <th>MARCA</th>
                <th>LOTE</th>
                <th>MODELO</th>
                <th>FECHA FABRICACIÓN</th>
                <th>FECHA COMPRA</th>
                <th>FABRICANTE</th>
                <th>PROVEEDOR</th>
                <th>USO</th>
                <th>CUMPLE NORMAS</th>
                <th>CAPACIDAD ESTRUCT.</th>
                <th>CAPACIDAD RESISTENCIA</th>
                <th>COLOR</th>
                <th>MATERIAL</th>
                <th>UBICACIÓN</th>
                <th>PUNTOS ANCLAJE</th>
                <th>TALLA/LONGITUD</th>
                <th>OTRAS ESPECIFICACIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipos as $i => $e)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="codigo-link">{{ $e->codigo }}</td>
                    <td>{{ $e->descripcion }}</td>
                    <td>{{ $e->modelo ?? '—' }}</td>
                    <td>—</td>
                    <td class="celda-p">—</td>
                    <td class="celda-e">—</td>
                    <td>—</td>
                    <td class="celda-p">—</td>
                    <td class="celda-e">—</td>
                    <td>—</td>
                    <td class="celda-p">—</td>
                    <td class="celda-e">—</td>
                    <td>{{ $e->marca ?? '—' }}</td>
                    <td>{{ $e->lote ?? '—' }}</td>
                    <td>{{ $e->modelo ?? '—' }}</td>
                    <td>{{ $e->fecha_fabricacion ? $e->fecha_fabricacion->format('d/m/Y') : '—' }}</td>
                    <td>{{ $e->fecha_compra ? $e->fecha_compra->format('d/m/Y') : '—' }}</td>
                    <td>{{ $e->fabricante?->nombre ?? '—' }}</td>
                    <td>{{ $e->proveedor?->nombre ?? '—' }}</td>
                    <td>{{ $e->usoItem?->nombre ?? '—' }}</td>
                    <td>—</td>
                    <td>{{ $e->capacidades_resistencia ?? '—' }}</td>
                    <td>{{ $e->capacidades_resistencia ?? '—' }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>{{ $e->bodega?->nombre ?? $e->sede?->nombre ?? '—' }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
