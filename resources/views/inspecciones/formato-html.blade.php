<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Formato de inspección - {{ $tipoEquipo->nombre }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 16px;
            background: #f5f5f5;
        }
        .page {
            width: 210mm;
            max-width: 100%;
            margin: 0 auto;
            background: white;
            border: 1px solid #d0d0d0;
            padding: 16px 20px;
        }
        h1 {
            font-size: 16px;
            text-align: center;
            margin: 0 0 4px;
        }
        .subtitle {
            font-size: 11px;
            text-align: center;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #4b5563;
            padding: 4px 6px;
            vertical-align: top;
        }
        th {
            background-color: #1e40af;
            color: white;
            text-align: center;
        }
        .section-header {
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            text-align: center;
        }
        .editable {
            min-height: 14px;
            cursor: text;
        }
        .editable:focus {
            outline: 2px solid #22c55e;
            outline-offset: -2px;
            background-color: #ecfdf5;
        }
    </style>
</head>
<body>
    <div class="page">
        <h1>INSPECCIÓN TÉCNICA DEL EQUIPO</h1>
        <p class="subtitle">Tipo de equipo: {{ $tipoEquipo->nombre }}</p>

        <table>
            <tr>
                <th colspan="2">Fecha de la inspección</th>
                <th colspan="2">Validez de la inspección</th>
            </tr>
            <tr>
                <td colspan="2" class="editable" contenteditable="true"></td>
                <td colspan="2" class="editable" contenteditable="true"></td>
            </tr>
            <tr>
                <th colspan="2">CRITERIOS DE INSPECCIÓN</th>
                <th colspan="1">CUMPLIMIENTO<br>C / NC</th>
                <th colspan="1">HALLAZGOS</th>
            </tr>
            @php
                $criterios = [
                    'El equipo cuenta con la etiqueta con la información del equipo de acuerdo con requerimientos normativos',
                    'Resistencia integral mínima a la rotura de 5000 lb',
                    'Capacidad integral mínima de 140 kg',
                    'Las fibras de las correas e hilos de las costuras cumplen criterios normativos',
                    'Todas las argollas cumplen con la resistencia mínima a la rotura',
                    'El ancho de las correas cumple los requerimientos normativos',
                    'El arnés y sus herrajes cumplen con los criterios normativos de marcación',
                    'Las correas de hombros, dorso, pecho, pelvis, piernas cumplen con normativas',
                ];
            @endphp
            @foreach($criterios as $texto)
                <tr>
                    <td colspan="2">{{ $texto }}</td>
                    <td class="editable" contenteditable="true"></td>
                    <td class="editable" contenteditable="true"></td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>

