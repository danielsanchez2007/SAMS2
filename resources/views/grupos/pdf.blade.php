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
    <h1>Grupo</h1>
    <table>
        <thead><tr><th>Nombre</th></tr></thead>
        <tbody>
            @foreach($grupos as $i => $g)
                <tr><td>{{ $g->nombre }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
