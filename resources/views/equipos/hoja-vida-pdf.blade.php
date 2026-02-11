<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm; }
        html, body { width: 100%; max-width: 100%; margin: 0; padding: 12px; font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; box-sizing: border-box; }
        *, *::before, *::after { box-sizing: border-box; }
        h1 { font-size: 16px; color: #1e3a5f; margin-bottom: 16px; border-bottom: 2px solid #1e3a5f; padding-bottom: 8px; }
        .imagen-equipo { max-width: 200px; max-height: 200px; object-fit: contain; border: 1px solid #ccc; }
        table.datos { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.datos th, table.datos td { border: 1px solid #999; padding: 8px; text-align: left; }
        table.datos th { background: #1e3a5f; color: #fff; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Hoja de vida — {{ $placeholders['codigo'] }}</h1>

    @if(!empty($placeholders['imagen_equipo']))
    <div style="margin-bottom: 16px;">
        <img src="{{ $placeholders['imagen_equipo'] }}" alt="" class="imagen-equipo">
    </div>
    @endif

    <table class="datos">
        <tr><th>Código</th><td>{{ $placeholders['codigo'] }}</td><th>Marca</th><td>{{ $placeholders['marca'] }}</td></tr>
        <tr><th>Descripción</th><td colspan="3">{{ $placeholders['descripcion'] }}</td></tr>
        <tr><th>Tipo ítem</th><td>{{ $placeholders['tipo_item'] }}</td><th>Estado</th><td>{{ $placeholders['estado_item'] }}</td></tr>
        <tr><th>Modelo</th><td>{{ $placeholders['modelo'] }}</td><th>Fabricante</th><td>{{ $placeholders['fabricante'] }}</td></tr>
        <tr><th>Proveedor</th><td>{{ $placeholders['proveedor'] }}</td><th>Uso</th><td>{{ $placeholders['uso_item'] }}</td></tr>
        <tr><th>Fecha fabricación</th><td>{{ $placeholders['fecha_fabricacion'] }}</td><th>Fecha compra</th><td>{{ $placeholders['fecha_compra'] }}</td></tr>
        <tr><th>Valor</th><td>{{ $placeholders['valor'] }}</td><th>Nº factura</th><td>{{ $placeholders['numero_factura'] }}</td></tr>
        <tr><th>Capacidades / Resistencia</th><td colspan="3">{{ $placeholders['capacidades_resistencia'] }}</td></tr>
        <tr><th>Lote</th><td>{{ $placeholders['lote'] }}</td><th>Vida útil</th><td>{{ $placeholders['vida_util'] }}</td></tr>
        <tr><th>Sede</th><td>{{ $placeholders['sede'] }}</td><th>Bodega</th><td>{{ $placeholders['bodega'] }}</td></tr>
    </table>

    @if(!empty($placeholders['empresa']) || !empty($placeholders['direccion']) || !empty($placeholders['texto_legal']))
    <div style="margin-top: 20px; padding-top: 12px; border-top: 1px solid #ccc; font-size: 9px; color: #666;">
        @if(!empty($placeholders['empresa']))<strong>{{ $placeholders['empresa'] }}</strong><br>@endif
        @if(!empty($placeholders['direccion'])){{ $placeholders['direccion'] }}<br>@endif
        @if(!empty($placeholders['telefono']))Tel: {{ $placeholders['telefono'] }}<br>@endif
        @if(!empty($placeholders['texto_legal']))<br>{{ $placeholders['texto_legal'] }}@endif
    </div>
    @endif
</body>
</html>
