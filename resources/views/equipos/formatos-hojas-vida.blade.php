<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formatos de Hojas de Vida - {{ $equipo->codigo }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        .formato-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .formato-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-secondary:hover {
            background: #e5e7eb;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Formatos de Hojas de Vida</h1>
                    <p class="text-gray-600">Equipo: <span class="font-semibold">{{ $equipo->codigo }}</span> - {{ $equipo->descripcion }}</p>
                    <p class="text-gray-600">Tipo: <span class="font-semibold">{{ $equipo->tipoEquipo->nombre ?? 'No asignado' }}</span></p>
                </div>
                <div class="text-right">
                    <a href="{{ url('/equipos/debaja') }}" class="btn-secondary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Formatos Disponibles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @forelse($hojasVida as $hojaVida)
                <div class="formato-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $hojaVida->etiqueta->nombre }}</h3>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                            {{ $hojaVida->estado ?? 'activo' }}
                        </span>
                    </div>
                    
                    <p class="text-gray-600 mb-4">
                        {{ $hojaVida->descripcion ?? 'Formato de hoja de vida para este tipo de equipo' }}
                    </p>
                    
                    <div class="text-sm text-gray-500 mb-4">
                        <p>Creado: {{ $hojaVida->created_at?->format('d/m/Y H:i') }}</p>
                        @if($hojaVida->updated_at)
                        <p>Actualizado: {{ $hojaVida->updated_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                    
                    <div class="flex gap-2">
                        @if($hojaVida->contenido_html)
                        <a href="{{ route('equipos.hoja-vida-pdf', $equipo->id) }}?hoja_vida_id={{ $hojaVida->id }}" 
                           target="_blank" 
                           class="btn-primary flex-1 justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver Formato
                        </a>
                        @endif
                        
                        @if($hojaVida->archivo_pdf)
                        <a href="{{ asset('storage/' . $hojaVida->archivo_pdf) }}" 
                           target="_blank" 
                           class="btn-secondary flex-1 justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2v-4a2 2 0 012-2h6l2 2z"/>
                            </svg>
                            Descargar PDF
                        </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center">
                        <svg class="w-16 h-16 mx-auto text-yellow-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-yellow-800 mb-2">No hay formatos disponibles</h3>
                        <p class="text-yellow-700">No se encontraron formatos de hojas de vida asignados para este tipo de equipo.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Botón para crear nuevo formato -->
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">¿Necesitas un nuevo formato?</h3>
            <p class="text-gray-600 mb-6">Puedes crear y asignar nuevos formatos de hojas de vida para este tipo de equipo.</p>
            <a href="{{ url('/almacen') }}" class="btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ir al Almacén
            </a>
        </div>
    </div>

    <script>
        // Función para cerrar y volver a la página anterior
        function closeAndGoBack() {
            window.close();
            // Si la ventana no se cierra (porque fue abierta manualmente), redirigir
            setTimeout(() => {
                window.location.href = '{{ url("/equipos/debaja") }}';
            }, 1000);
        }
    </script>
</body>
</html>
