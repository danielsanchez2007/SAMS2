<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>ACTA DE BAJA - {{ $equipo->codigo ?? 'Equipo' }}</title>
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            gap: 10px;
        }
        .logo {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .title {
            flex: 1;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 0 10px;
        }
        .version-box {
            flex: 0 0 auto;
            border: 1px solid #1e40af;
            padding: 8px;
            font-size: 9px;
            text-align: left;
        }
        .version-box div {
            margin: 2px 0;
        }
        .act-details {
            margin: 12px 0;
            font-size: 11px;
        }
        .act-details span {
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin: 8px 0;
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
            font-weight: 600;
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
        .certification {
            margin: 12px 0;
            font-size: 10px;
            text-align: justify;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .signature-box {
            flex: 1;
            margin: 0 10px;
        }
        .signature-box label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .signature-box input {
            width: 100%;
            border: 1px solid #4b5563;
            padding: 4px;
            font-size: 10px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="logo">
                @if(!empty($logoMainDataUri))
                    <img src="{{ $logoMainDataUri }}" alt="Logo principal" style="max-height: 48px; max-width: 160px;">
                @endif
                @if(!empty($logoSecondaryDataUri))
                    <img src="{{ $logoSecondaryDataUri }}" alt="Logo secundario" style="max-height: 48px; max-width: 160px;">
                @endif
            </div>
            <div class="title">ACTA DE BAJA DE ELEMENTOS DEVOLUTIVOS, INSERVIBLES U OBSOLETOS</div>
            <div class="version-box">
                <div>Versión: 03</div>
                <div>Fecha: <span class="editable" contenteditable="true">17/02/2024</span></div>
                <div>Código: CF10</div>
                <div>Página: 1 de 1</div>
            </div>
        </div>

        <div class="act-details">
            <span>Fecha:</span> <span class="editable" contenteditable="true">{{ \Carbon\Carbon::now()->format('F') }} de {{ \Carbon\Carbon::now()->format('Y') }}</span>
            <span style="margin-left: 20px;">Acta No.:</span> <span class="editable" contenteditable="true">1</span>
        </div>

        <table>
            <tr class="section-header">
                <th>NOMBRE DE LOS ASISTENTES</th>
                <th>CARGOS</th>
            </tr>
            <tr>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
            </tr>
            <tr>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
            </tr>
        </table>

        <div class="certification">
            <p>El Responsable del Inventario certifica que realizó inspección a los bienes relacionados y fue verificado su estado, determinando que son elementos servibles no útiles o inservibles y no pueden seguir en servicio; por lo tanto es procedente iniciar el proceso de <strong>BAJA DEFINITIVA</strong> y retiro de los inventarios.</p>
        </div>

        <table>
            <tr class="section-header">
                <th>Código interno</th>
                <th>Descripcion del elemento a dar de baja</th>
                <th>Marca</th>
                <th>Serial</th>
                <th>Fecha de fabricacion</th>
                <th>Calificación o Estado</th>
                <th>Procedencia u Origen</th>
                <th>Destino final</th>
            </tr>
            <tr>
                <td class="editable" contenteditable="true">{{ $equipo->codigo ?? '' }}</td>
                <td class="editable" contenteditable="true">{{ $equipo->descripcion ?? '' }}</td>
                <td class="editable" contenteditable="true">{{ $equipo->marca ?? '' }}</td>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true">{{ $equipo->fecha_fabricacion ? $equipo->fecha_fabricacion->format('Y-m-d') : 'N/A' }}</td>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true">{{ $equipo->sede->nombre ?? '' }}</td>
                <td class="editable" contenteditable="true"></td>
            </tr>
            <tr>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
                <td class="editable" contenteditable="true"></td>
            </tr>
        </table>

        <div class="signature-section">
            <div class="signature-box">
                <label>FIRMA RESPONSABLE INVENTARIO:</label>
                <input type="text" placeholder="Nombre:" class="editable" contenteditable="true">
                <input type="text" placeholder="C.C. No.:" class="editable" contenteditable="true">
            </div>
            <div class="signature-box">
                <label>FIRMA GERENTE ADMINISTRATIVA:</label>
                <input type="text" placeholder="Nombre:" class="editable" contenteditable="true">
                <input type="text" placeholder="C.C. No.:" class="editable" contenteditable="true">
            </div>
        </div>
    </div>
</body>
</html>
