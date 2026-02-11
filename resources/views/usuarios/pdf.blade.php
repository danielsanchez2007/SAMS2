<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; }
        .logos { text-align: center; margin-bottom: 15px; }
        .logos img { max-height: 40px; margin: 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 3px; text-align: left; }
        th { background: #1e3a5f; color: #fff; font-size: 8px; }
        h1 { font-size: 12px; color: #1e3a5f; margin-bottom: 8px; }
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
    <h1>Usuarios</h1>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Username</th>
                <th>Empresa</th>
                <th>Cargo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $i => $u)
                <tr>
                    <td>{{ $u->cedula }}</td>
                    <td>{{ $u->nombre }}</td>
                    <td>{{ $u->apellidos }}</td>
                    <td>{{ $u->username }}</td>
                    <td>{{ $u->empresa?->nombre ?? '—' }}</td>
                    <td>{{ $u->cargo?->nombre ?? '—' }}</td>
                    <td>{{ $u->activo ? 'Activo' : 'Inactivo' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
