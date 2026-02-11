<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 6px; }
        .header-row { margin-bottom: 10px; }
        .logos { text-align: center; margin-bottom: 8px; }
        .logos img { max-height: 36px; margin: 0 6px; }
        .doc-info { text-align: right; font-size: 6px; }
        h1 { font-size: 11px; text-align: center; margin: 8px 0 6px; }
        .objetivo { font-size: 6px; margin-bottom: 6px; }
        .meta { font-weight: bold; margin-bottom: 2px; }
        .indicador { font-size: 5px; margin-bottom: 6px; }
        .subtitle { font-size: 7px; font-weight: bold; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 1px 2px; }
        th { background: #5B9BD5; color: #fff; font-size: 5px; }
        .celda-p { background: #FFEB9C; }
        .celda-e { background: #C6EFCE; }
    </style>
</head>
<body>
    <div class="header-row">
        <div class="logos">
            @if(!empty($logoPreventionBase64))<img src="{{ $logoPreventionBase64 }}" alt="Prevention World">@endif
            @if(!empty($logoSamsBase64))<img src="{{ $logoSamsBase64 }}" alt="SAMS">@endif
        </div>
        <div class="doc-info">Versión: 04 | Fecha: {{ date('d/m/Y') }} | Código: 6603 | Página 1 de 1</div>
    </div>
    <h1>PROGRAMA DE TRAZABILIDAD DE EQUIPOS DE TAREAS CRITICAS</h1>
    <div class="objetivo">Asegurar la trazabilidad y garantizar la vida útil de los EPCC y equipos de protección contra caídas.</div>
    <div class="meta">Meta: 100%</div>
    <div class="indicador">Mantenimientos Preventivos ejecutados / Total programado x 100 | Inspecciones Ejecutadas / Total Inspecciones</div>
    <div class="subtitle">Para la programación de los mantenimientos e inspecciones</div>
    <table>
        <thead>
            <tr>
                <th>ITEM</th>
                <th>CODIGO</th>
                <th>EQUIPO</th>
                <th>SERIAL/MOD</th>
                <th>F.PROG.INS</th>
                <th>P</th>
                <th>E</th>
                <th>F.PROG.MANT</th>
                <th>P</th>
                <th>E</th>
                <th>F.PROG.MANT</th>
                <th>P</th>
                <th>E</th>
                <th>MARCA</th>
                <th>LOTE</th>
                <th>MODELO</th>
                <th>F.FAB</th>
                <th>F.COMPRA</th>
                <th>FABRIC.</th>
                <th>PROV.</th>
                <th>USO</th>
                <th>NORMAS</th>
                <th>CAP.EST</th>
                <th>CAP.RES</th>
                <th>COLOR</th>
                <th>MAT.</th>
                <th>UBIC.</th>
                <th>P.ANCL.</th>
                <th>TALLA</th>
                <th>OTRAS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipos as $i => $e)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $e->codigo }}</td>
                    <td>{{ Str::limit($e->descripcion, 40) }}</td>
                    <td>{{ Str::limit($e->modelo ?? '—', 12) }}</td>
                    <td>—</td>
                    <td class="celda-p">—</td>
                    <td class="celda-e">—</td>
                    <td>—</td>
                    <td class="celda-p">—</td>
                    <td class="celda-e">—</td>
                    <td>—</td>
                    <td class="celda-p">—</td>
                    <td class="celda-e">—</td>
                    <td>{{ Str::limit($e->marca ?? '—', 10) }}</td>
                    <td>{{ Str::limit($e->lote ?? '—', 8) }}</td>
                    <td>{{ Str::limit($e->modelo ?? '—', 10) }}</td>
                    <td>{{ $e->fecha_fabricacion ? $e->fecha_fabricacion->format('d/m/Y') : '—' }}</td>
                    <td>{{ $e->fecha_compra ? $e->fecha_compra->format('d/m/Y') : '—' }}</td>
                    <td>{{ Str::limit($e->fabricante?->nombre ?? '—', 10) }}</td>
                    <td>{{ Str::limit($e->proveedor?->nombre ?? '—', 10) }}</td>
                    <td>{{ Str::limit($e->usoItem?->nombre ?? '—', 8) }}</td>
                    <td>—</td>
                    <td>{{ Str::limit($e->capacidades_resistencia ?? '—', 8) }}</td>
                    <td>{{ Str::limit($e->capacidades_resistencia ?? '—', 8) }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>{{ Str::limit($e->bodega?->nombre ?? $e->sede?->nombre ?? '—', 10) }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
