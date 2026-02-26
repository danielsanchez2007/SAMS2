<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formato de Equipos de Baja</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
            background: white;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            border: 0;
            padding: 5px;
            vertical-align: middle;
        }
        .logo-cell {
            width: 25%;
            text-align: left;
        }
        .logo-cell img {
            max-height: 50px;
            width: auto;
        }
        .title-cell {
            width: 50%;
            text-align: center;
        }
        .meta-cell {
            width: 25%;
            text-align: right;
            font-size: 10px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .subtitle {
            font-size: 12px;
            color: #666;
            margin: 5px 0 0 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th,
        .info-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .info-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .equipos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .equipos-table th,
        .equipos-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }
        .equipos-table th {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    {{-- Encabezado con logos --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoSecondary)
                    <img src="{{ asset('storage/' . $logoSecondary) }}" alt="Logo Secundario" style="max-width: 120px;">
                @endif
            </td>
            <td class="title-cell">
                <h1 class="title">FORMATO DE EQUIPOS DE BAJA</h1>
                <p class="subtitle">Reporte de Equipos Dados de Baja</p>
            </td>
            <td class="meta-cell">
                @if($logoMain)
                    <img src="{{ asset('storage/' . $logoMain) }}" alt="Logo Principal" style="max-width: 80px;">
                @endif
            </td>
        </tr>
    </table>

    {{-- Información general --}}
    <table class="info-table">
        <tr>
            <th colspan="4" style="text-align: center; background-color: #d0d0d0; font-size: 12px;">
                INFORMACIÓN DE EQUIPOS DE BAJA
            </th>
        </tr>
        <tr>
            <th width="5%">No.</th>
            <th width="15%">Código Original</th>
            <th width="10%">Código Actual</th>
            <th width="25%">Descripción</th>
            <th width="10%">Marca</th>
            <th width="10%">Fecha Baja</th>
            <th width="25%">Motivo de Baja</th>
        </tr>
        @php($counter = 1)@endphp
        @foreach($equiposBaja as $equipoBaja)
            <tr class="page-break">
                <td style="text-align: center;">{{ $counter++ }}</td>
                <td>{{ $equipoBaja->equipo->codigo ?? 'N/A' }}</td>
                <td>{{ $equipoBaja->equipo->codigo ?? 'N/A' }}</td>
                <td>{{ $equipoBaja->equipo->descripcion ?? 'N/A' }}</td>
                <td>{{ $equipoBaja->equipo->marca ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ \Carbon\Carbon::parse($equipoBaja->fecha_baja)->format('d/m/Y') }}</td>
                <td>{{ $equipoBaja->resumen_baja ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </table>

    {{-- Tabla de responsables --}}
    <table class="info-table">
        <tr>
            <th colspan="4" style="text-align: center; background-color: #d0d0d0; font-size: 12px;">
                RESPONSABLES DE LA BAJA
            </th>
        </tr>
        @foreach($equiposBaja as $equipoBaja)
            <tr>
                <td width="20%"><strong>Responsable Inventario:</strong></td>
                <td width="30%">{{ $equipoBaja->responsable_inventario_nombre ?? 'N/A' }}</td>
                <td width="20%"><strong>C.C.:</strong></td>
                <td width="30%">{{ $equipoBaja->responsable_inventario_cc ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td width="20%"><strong>Gerente Administrativa:</strong></td>
                <td width="30%">{{ $equipoBaja->gerente_administrativa_nombre ?? 'N/A' }}</td>
                <td width="20%"><strong>C.C.:</strong></td>
                <td width="30%">{{ $equipoBaja->gerente_administrativa_cc ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="4" style="height: 15px; border: none;"></td>
            </tr>
        @endforeach
    </table>

    {{-- Pie de página --}}
    <div class="footer">
        <p>Formato generado el día {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        <p>Sistema de Administración y Mantenimiento de Equipos (SAMS)</p>
    </div>
</body>
</html>
