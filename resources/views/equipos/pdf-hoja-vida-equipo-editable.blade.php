<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 10px 18px;
            background: white;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .header-table td {
            border: 0;
        }
        .logo-cell {
            width: 30%;
            text-align: left;
            vertical-align: middle;
        }
        .logo-cell img {
            max-height: 60px;
            width: auto;
        }
        .title-cell {
            width: 40%;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        .meta-cell {
            width: 30%;
            font-size: 10px;
        }
        .meta-cell table td {
            border: 1px solid #000;
            padding: 2px 3px;
        }
        .meta-label {
            background-color: #d3d3d3;
            font-weight: bold;
        }
        .section-title {
            background-color: #d3d3d3;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            padding: 4px 0;
            margin-top: 6px;
            font-size: 11px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            font-size: 10px;
        }
        .label-cell {
            background-color: #00a0df;
            color: #fff;
            font-weight: bold;
            width: 28%;
        }
        .value-cell {
            background-color: #fdfdfd;
        }
        .value-cell[contenteditable="true"] {
            min-height: 20px;
            outline: 2px solid #3b82f6;
            outline-offset: -2px;
            cursor: text;
        }
        .value-cell[contenteditable="true"]:focus {
            background-color: #f0f9ff;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .small {
            font-size: 9px;
        }
        .checkbox-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 9px;
            text-align: center;
        }
        .checkbox-label {
            background-color: #00a0df;
            color: #fff;
            font-weight: bold;
        }
    </style>
</head>
@php
    $e = $equipo;
    $d = $datosEquipo ?? [];
    $val = fn($key) => trim((string)($d[$key] ?? ''));
@endphp
<body>
    {{-- Encabezado --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if(!empty($logoPreventionBase64))
                    <img src="{{ $logoPreventionBase64 }}" alt="Prevention World">
                @endif
            </td>
            <td class="title-cell">
                PROGRAMA DE TRAZABILIDAD DE EQUIPOS DE<br>
                TAREAS CRÍTICAS
            </td>
            <td class="meta-cell">
                <table>
                    <tr>
                        <td class="meta-label">Versión:</td>
                        <td>04</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Fecha:</td>
                        <td>{{ date('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Código:</td>
                        <td>GG03</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Página:</td>
                        <td>1 de 1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Descripción general --}}
    <div class="section-title">
        DESCRIPCIÓN GENERAL DEL EQUIPO
    </div>

    <table class="data-table" style="margin-top: 2px;">
        <tr>
            <td class="label-cell">NOMBRE DEL EQUIPO:</td>
            <td class="value-cell" colspan="3" contenteditable="true" data-field="nombre">{{ $val('nombre') }}</td>
        </tr>
        <tr>
            <td class="label-cell">CÓDIGO:</td>
            <td class="value-cell" contenteditable="true" data-field="codigo">{{ $val('codigo') }}</td>
            <td class="label-cell">UBICACIÓN:</td>
            <td class="value-cell" contenteditable="true" data-field="ubicacion">{{ $val('ubicacion') }}</td>
        </tr>
        <tr>
            <td class="label-cell small">No. DE SERIAL, SERIE Ó INSPECCIÓN:</td>
            <td class="value-cell" contenteditable="true" data-field="serial">{{ '' }}</td>
            <td class="label-cell">MODELO:</td>
            <td class="value-cell" contenteditable="true" data-field="modelo">{{ $val('modelo') }}</td>
        </tr>
        <tr>
            <td class="label-cell">MARCA:</td>
            <td class="value-cell" contenteditable="true" data-field="marca">{{ $val('marca') }}</td>
            <td class="label-cell">LOTE:</td>
            <td class="value-cell" contenteditable="true" data-field="lote">{{ $val('lote') }}</td>
        </tr>
        <tr>
            <td class="label-cell">FABRICANTE:</td>
            <td class="value-cell" contenteditable="true" data-field="fabricante">{{ $val('fabricante') }}</td>
            <td class="label-cell">PROVEEDOR:</td>
            <td class="value-cell" contenteditable="true" data-field="proveedor">{{ $val('proveedor') }}</td>
        </tr>
        <tr>
            <td class="label-cell">USO:</td>
            <td class="value-cell" colspan="3" contenteditable="true" data-field="uso">{{ $val('uso') }}</td>
        </tr>
        <tr>
            <td class="label-cell">FECHA DE COMPRA:</td>
            <td class="value-cell" contenteditable="true" data-field="fecha_compra">{{ $val('fecha_compra') }}</td>
            <td class="label-cell">FECHA DE FABRICACIÓN:</td>
            <td class="value-cell" contenteditable="true" data-field="fecha_fabricacion">{{ $val('fecha_fabricacion') }}</td>
        </tr>
        <tr>
            <td class="label-cell">PUNTOS DE ANCLAJE:</td>
            <td class="value-cell" contenteditable="true" data-field="puntos_anclaje">{{ $val('puntos_anclaje') }}</td>
            <td class="label-cell">TALLA:</td>
            <td class="value-cell" contenteditable="true" data-field="talla">{{ $val('talla') }}</td>
        </tr>
        <tr>
            <td class="label-cell">CUMPLE NORMAS:</td>
            <td class="value-cell" colspan="3" contenteditable="true" data-field="cumple_normas">{{ $val('cumple_normas') }}</td>
        </tr>
        <tr>
            <td class="label-cell small">CAPACIDAD / RESISTENCIA GENERAL:</td>
            <td class="value-cell" contenteditable="true" data-field="capacidades_resistencia">{{ $val('capacidades_resistencia') }}</td>
            <td class="label-cell">MATERIAL:</td>
            <td class="value-cell" contenteditable="true" data-field="material">{{ $val('material') }}</td>
        </tr>
        <tr>
            <td class="label-cell">COLOR(ES):</td>
            <td class="value-cell" contenteditable="true" data-field="color">{{ $val('color') }}</td>
            <td class="label-cell small">OTRAS ESPECIFICACIONES TÉCNICAS:</td>
            <td class="value-cell" contenteditable="true" data-field="otras_especificaciones">{{ $val('otras_especificaciones') }}</td>
        </tr>
    </table>

    {{-- Sección ficha técnica y certificación --}}
    <table class="data-table" style="margin-top: 6px;">
        <tr>
            <td class="label-cell center" colspan="2">CUENTA CON FICHA TÉCNICA EXPEDIDA POR EL FABRICANTE</td>
            <td class="checkbox-table" style="width: 8%;">
                <table class="checkbox-table" style="width: 100%;">
                    <tr>
                        <td class="checkbox-label">SI</td>
                        <td class="checkbox-label">NO</td>
                    </tr>
                    <tr>
                        <td>{{ $d['tiene_ficha_tecnica'] ? 'X' : '' }}</td>
                        <td>{{ !$d['tiene_ficha_tecnica'] ? 'X' : '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label-cell center" colspan="2">CUENTA CON CERTIFICACIÓN DE CONFORMIDAD DE CALIDAD</td>
            <td class="checkbox-table" style="width: 8%;">
                <table class="checkbox-table" style="width: 100%;">
                    <tr>
                        <td class="checkbox-label">SI</td>
                        <td class="checkbox-label">NO</td>
                    </tr>
                    <tr>
                        <td>{{ $d['tiene_certificacion'] ? 'X' : '' }}</td>
                        <td>{{ !$d['tiene_certificacion'] ? 'X' : '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Descripción general inspecciones --}}
    <div class="section-title" style="margin-top: 8px;">
        DESCRIPCIÓN GENERAL DE LAS INSPECCIONES Y MANTENIMIENTO PREVENTIVO
    </div>
    <table class="data-table">
        <tr>
            <td style="height: 40px;" contenteditable="true" data-field="descripcion_inspecciones">{{ $val('descripcion_inspecciones') }}</td>
        </tr>
    </table>

    {{-- Pie: fechas --}}
    <table class="data-table" style="margin-top: 8px;">
        <tr>
            <td class="label-cell center">Fecha de la inspección</td>
            <td class="label-cell center">Validez de la inspección</td>
        </tr>
        <tr>
            <td class="center" style="height: 18px;" contenteditable="true" data-field="fecha_inspeccion">{{ $val('fecha_inspeccion') }}</td>
            <td class="center" contenteditable="true" data-field="validez_inspeccion">{{ $val('validez_inspeccion') }}</td>
        </tr>
    </table>

    <script>
        // Sincronizar cambios editados con el formulario padre
        document.querySelectorAll('[contenteditable="true"]').forEach(cell => {
            cell.addEventListener('blur', function() {
                const field = this.getAttribute('data-field');
                const value = this.innerText.trim();
                if (window.parent && window.parent.syncPdfField) {
                    window.parent.syncPdfField(field, value);
                }
            });
        });
    </script>
</body>
</html>
