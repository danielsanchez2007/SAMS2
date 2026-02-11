<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; }
        .logos { text-align: center; margin-bottom: 15px; }
        .logos img { max-height: 40px; margin: 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #1e3a5f; color: #fff; font-size: 8px; }
        h1 { font-size: 12px; color: #1e3a5f; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="logos">
        @if(!empty($logoPreventionBase64))<img src="{{ $logoPreventionBase64 }}" alt="Prevention World">@endif
        @if(!empty($logoSamsBase64))<img src="{{ $logoSamsBase64 }}" alt="SAMS">@endif
    </div>
    <h1>Equipos</h1>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Tipo Item</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Marca</th>
                <th>Sede</th>
                <th>Bodega</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipos as $i => $e)
                <tr>
                    <td>{{ $e->codigo }}</td>
                    <td>{{ $e->tipoItem?->nombre ?? '—' }}</td>
                    <td>{{ Str::limit($e->descripcion, 40) }}</td>
                    <td>{{ $e->estadoRemision?->nombre ?? '—' }}</td>
                    <td>{{ $e->marca ?? '—' }}</td>
                    <td>{{ $e->sede?->nombre ?? '—' }}</td>
                    <td>{{ $e->bodega?->nombre ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
