<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        .logos { text-align: center; margin-bottom: 15px; }
        .logos img { max-height: 45px; margin: 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #1e3a5f; color: #fff; }
        h1 { font-size: 13px; color: #1e3a5f; margin-bottom: 8px; }
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
    <h1>Roles</h1>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Módulos / permisos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $i => $r)
                <tr>
                    <td>{{ $r->nombre }}</td>
                    <td>
                        @php $permisos = $r->permisos ?? []; @endphp
                        @foreach($permisos as $key => $p)
                            @if(!empty($p['acceso']))
                                {{ $modulos[$key] ?? $key }}
                                @if(!empty($p['agregar']) || !empty($p['editar']) || !empty($p['eliminar']))
                                    ({{ implode(', ', array_filter([
                                        !empty($p['agregar']) ? 'Agregar' : null,
                                        !empty($p['editar']) ? 'Editar' : null,
                                        !empty($p['eliminar']) ? 'Eliminar' : null,
                                    ])) }})
                                @endif
                                @if(!$loop->last) · @endif
                            @endif
                        @endforeach
                        @if(empty(array_filter($permisos, fn($p) => !empty($p['acceso'])))) — @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
