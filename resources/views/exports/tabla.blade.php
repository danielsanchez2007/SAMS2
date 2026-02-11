<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        .logos { text-align: center; margin-bottom: 16px; }
        .logos img { max-height: 60px; margin: 0 12px; border: 1px solid #0f172a; border-radius: 8px; padding: 6px 12px; background: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #0f172a; padding: 6px 8px; text-align: left; }
        th { background: #e2e8f0; color: #0f172a; font-weight: 600; }
        tr:nth-child(even) { background: #f8fafc; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 12px; color: #0f172a; }
    </style>
</head>
<body>
    <div class="logos">
        @if(!empty($logoPreventionBase64))
            <img src="{{ $logoPreventionBase64 }}" alt="Logo secundario">
        @endif
        @if(!empty($logoSamsBase64))
            <img src="{{ $logoSamsBase64 }}" alt="Logo principal">
        @endif
    </div>
    <h1>{{ $titulo }}</h1>
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; font-style: italic;">Sin datos</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
