<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .logos { text-align: center; margin-bottom: 20px; }
        .logos img { max-height: 50px; margin: 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #1e3a5f; color: #fff; }
        h1 { font-size: 14px; color: #1e3a5f; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="logos">
        @if(!empty($logoPreventionBase64))
            <img src="{{ $logoPreventionBase64 }}" alt="Prevention World">
        @endif
        @if(!empty($logoSamsBase64))
            <img src="{{ $logoSamsBase64 }}" alt="SAMS">
        @endif
    </div>
    @if($tab === 'empresa')
        <h1>Empresas</h1>
        <table>
            <thead><tr><th>Nombre</th><th>País</th></tr></thead>
            <tbody>
                @foreach($empresas as $i => $e)
                    <tr><td>{{ $e->nombre }}</td><td>{{ $e->pais ?? '-' }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @elseif($tab === 'sede')
        <h1>Sedes</h1>
        <table>
            <thead><tr><th>Nombre</th><th>País</th><th>Municipio</th><th>Departamento</th><th>Empresa</th></tr></thead>
            <tbody>
                @foreach($sedes as $i => $s)
                    <tr>
                        <td>{{ $s->nombre }}</td>
                        <td>{{ $s->pais ?? '-' }}</td>
                        <td>{{ $s->municipio ?? '-' }}</td>
                        <td>{{ $s->departamento ?? '-' }}</td>
                        <td>{{ $s->empresa->nombre ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <h1>Bodegas</h1>
        <table>
            <thead><tr><th>Nombre</th><th>Sede</th><th>Ubicación</th></tr></thead>
            <tbody>
                @foreach($bodegas as $i => $b)
                    @php
                        $ubicacion = $b->sede ? trim(implode(', ', array_filter([$b->sede->municipio, $b->sede->departamento, $b->sede->pais]))) : '-';
                    @endphp
                    <tr><td>{{ $b->nombre }}</td><td>{{ $b->sede->nombre ?? '-' }}</td><td>{{ $ubicacion }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
