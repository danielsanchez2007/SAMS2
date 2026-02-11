@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
@php
    $user = session('sams2_user');
    $nombreUsuario = $user['name'] ?? 'Usuario';
    $rolNombre = 'Usuario';
    
    if (($user['role'] ?? null) === 'mega_admin') {
        $rolNombre = 'Mega Admin';
    } elseif (isset($user['role_id'])) {
        $role = \App\Models\Role::find($user['role_id']);
        $rolNombre = $role ? $role->nombre : 'Usuario';
    }
@endphp

<div class="min-h-screen py-10 px-5 sm:px-6 lg:px-8" x-data="dashboardApp()">
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Tarjeta de bienvenida principal -->
        <div class="tema-card relative overflow-hidden rounded-3xl border-2 border-gray-800 bg-white shadow-lg">
            <div class="relative px-8 py-10 sm:px-12 lg:px-16 lg:py-16 text-center sm:text-left">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight">
                    <span style="background: linear-gradient(to right, var(--tema-from), var(--tema-to)); -webkit-background-clip: text; background-clip: text; color: transparent;">
                        Bienvenido {{ $nombreUsuario }}
                    </span>
                </h1>
                <p class="mt-5 text-lg sm:text-xl text-gray-700 max-w-3xl mx-auto sm:mx-0 leading-relaxed">
                    Tu rol es: <strong>{{ $rolNombre }}</strong><br class="hidden sm:inline">
                    Utiliza el menú lateral para navegar y administrar los módulos disponibles.
                </p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6">
            <div class="flex flex-wrap items-center gap-4">
                <label class="text-sm font-semibold text-gray-700">Filtrar por:</label>
                <select x-model="filtro" @change="aplicarFiltro()" 
                    class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none">
                    <option value="todos">Todos los datos</option>
                    @if($esMegaAdmin || $esAdmin)
                        <option value="usuarios">Usuarios</option>
                        <option value="equipos">Equipos</option>
                        <option value="asignaciones">Asignaciones</option>
                    @endif
                    <option value="mi_empresa">Mi Empresa</option>
                </select>
                <button @click="resetearFiltro()" 
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 transition">
                    Limpiar filtros
                </button>
            </div>
        </div>

        <!-- Mini Cards de Estadísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card Usuarios -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6 hover:shadow-lg transition-all" 
                 x-show="mostrarCard('usuarios')">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Usuarios</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['usuarios'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['usuarios_activos'] }} activos</p>
                    </div>
                    <div class="w-14 h-14 rounded-xl tema-gradient flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Equipos -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6 hover:shadow-lg transition-all"
                 x-show="mostrarCard('equipos')">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Equipos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['equipos'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total en sistema</p>
                    </div>
                    <div class="w-14 h-14 rounded-xl tema-gradient flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Asignaciones -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6 hover:shadow-lg transition-all"
                 x-show="mostrarCard('asignaciones')">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Asignaciones</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['asignaciones'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Equipos asignados</p>
                    </div>
                    <div class="w-14 h-14 rounded-xl tema-gradient flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Empresas -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6 hover:shadow-lg transition-all"
                 x-show="mostrarCard('empresas')">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Empresas</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['empresas'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Registradas</p>
                    </div>
                    <div class="w-14 h-14 rounded-xl tema-gradient flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Gráfica Equipos por Tipo -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6" x-show="mostrarGrafica('equipos')">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Equipos por Tipo</h3>
                <canvas id="chartEquiposPorTipo" class="max-h-64"></canvas>
            </div>

            <!-- Gráfica Usuarios por Rol -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6" x-show="mostrarGrafica('usuarios')">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Usuarios por Rol</h3>
                <canvas id="chartUsuariosPorRol" class="max-h-64"></canvas>
            </div>
        </div>

        <!-- Gráfica de Asignaciones por Mes -->
        <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6" x-show="mostrarGrafica('asignaciones')">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Asignaciones por Mes ({{ date('Y') }})</h3>
            <canvas id="chartAsignacionesPorMes" class="max-h-64"></canvas>
        </div>

        <!-- Mini Tablas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tabla Usuarios Recientes -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6" x-show="mostrarTabla('usuarios')">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Usuarios Recientes</h3>
                    <a href="{{ route('usuarios.index') }}" class="text-sm text-[var(--tema-primary)] hover:underline">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 text-gray-600 font-semibold">Nombre</th>
                                <th class="text-left py-2 text-gray-600 font-semibold">Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuariosRecientes as $usuario)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 text-gray-900">{{ $usuario->nombre }} {{ $usuario->apellidos }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded-full text-xs bg-indigo-100 text-indigo-700">
                                            {{ $usuario->role->nombre ?? 'Sin rol' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-4 text-center text-gray-500">No hay usuarios</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabla Equipos Recientes -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6" x-show="mostrarTabla('equipos')">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Equipos Recientes</h3>
                    <a href="{{ route('equipos.index') }}" class="text-sm text-[var(--tema-primary)] hover:underline">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 text-gray-600 font-semibold">Código</th>
                                <th class="text-left py-2 text-gray-600 font-semibold">Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equiposRecientes as $equipo)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 text-gray-900 font-medium">{{ $equipo->codigo }}</td>
                                    <td class="py-2 text-gray-600">{{ $equipo->tipoEquipo->nombre ?? 'Sin tipo' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-4 text-center text-gray-500">No hay equipos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabla Asignaciones Recientes -->
            <div class="tema-card rounded-2xl border-2 border-gray-800 bg-white p-6" x-show="mostrarTabla('asignaciones')">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Asignaciones Recientes</h3>
                    <a href="{{ route('asignar.index') }}" class="text-sm text-[var(--tema-primary)] hover:underline">Ver todas</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 text-gray-600 font-semibold">Usuario</th>
                                <th class="text-left py-2 text-gray-600 font-semibold">Equipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asignacionesRecientes as $asignacion)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 text-gray-900">{{ $asignacion->usuario->nombre ?? 'N/A' }}</td>
                                    <td class="py-2 text-gray-600">{{ $asignacion->equipo->codigo ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-4 text-center text-gray-500">No hay asignaciones</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Grid de tarjetas de características -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <div class="tema-card group relative rounded-2xl border-2 border-gray-800 bg-white p-7 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                <div class="relative flex items-start gap-6">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl tema-gradient text-white">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900 transition-colors">Acceso Personalizado</h3>
                        <p class="mt-3 text-gray-600 leading-relaxed">
                            Funcionalidades y permisos activados según tu rol: {{ $rolNombre }}.
                        </p>
                    </div>
                </div>
            </div>

            <div class="tema-card group relative rounded-2xl border-2 border-gray-800 bg-white p-7 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                <div class="relative flex items-start gap-6">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl tema-gradient text-white">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900 transition-colors">Rápido & Moderno</h3>
                        <p class="mt-3 text-gray-600 leading-relaxed">
                            Interfaz optimizada con rendimiento actual y diseño contemporáneo (2025/2026).
                        </p>
                    </div>
                </div>
            </div>

            <div class="tema-card group relative rounded-2xl border-2 border-gray-800 bg-white p-7 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                <div class="relative flex items-start gap-6">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl tema-gradient text-white">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900 transition-colors">Alta Seguridad</h3>
                        <p class="mt-3 text-gray-600 leading-relaxed">
                            Autenticación reforzada, auditoría en tiempo real y protección activa permanente.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
function dashboardApp() {
    return {
        filtro: 'todos',
        charts: {},
        
        init() {
            this.inicializarGraficas();
        },
        
        mostrarCard(tipo) {
            if (this.filtro === 'todos') return true;
            return this.filtro === tipo || this.filtro === 'mi_empresa';
        },
        
        mostrarGrafica(tipo) {
            if (this.filtro === 'todos') return true;
            return this.filtro === tipo || this.filtro === 'mi_empresa';
        },
        
        mostrarTabla(tipo) {
            if (this.filtro === 'todos') return true;
            return this.filtro === tipo || this.filtro === 'mi_empresa';
        },
        
        aplicarFiltro() {
            // Las gráficas se actualizan automáticamente por x-show
        },
        
        resetearFiltro() {
            this.filtro = 'todos';
        },
        
        inicializarGraficas() {
            // Gráfica Equipos por Tipo
            const ctxEquipos = document.getElementById('chartEquiposPorTipo');
            if (ctxEquipos) {
                const dataEquipos = @json($equiposPorTipo);
                this.charts.equipos = new Chart(ctxEquipos, {
                    type: 'doughnut',
                    data: {
                        labels: dataEquipos.map(item => item.label),
                        datasets: [{
                            data: dataEquipos.map(item => item.value),
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.8)',
                                'rgba(139, 92, 246, 0.8)',
                                'rgba(236, 72, 153, 0.8)',
                                'rgba(251, 146, 60, 0.8)',
                                'rgba(34, 197, 94, 0.8)',
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            // Gráfica Usuarios por Rol
            const ctxUsuarios = document.getElementById('chartUsuariosPorRol');
            if (ctxUsuarios) {
                const dataUsuarios = @json($usuariosPorRol);
                this.charts.usuarios = new Chart(ctxUsuarios, {
                    type: 'bar',
                    data: {
                        labels: dataUsuarios.map(item => item.label),
                        datasets: [{
                            label: 'Usuarios',
                            data: dataUsuarios.map(item => item.value),
                            backgroundColor: 'rgba(99, 102, 241, 0.8)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Gráfica Asignaciones por Mes
            const ctxAsignaciones = document.getElementById('chartAsignacionesPorMes');
            if (ctxAsignaciones) {
                const dataAsignaciones = @json($asignacionesPorMes);
                this.charts.asignaciones = new Chart(ctxAsignaciones, {
                    type: 'line',
                    data: {
                        labels: dataAsignaciones.map(item => item.label),
                        datasets: [{
                            label: 'Asignaciones',
                            data: dataAsignaciones.map(item => item.value),
                            borderColor: 'rgba(99, 102, 241, 1)',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    };
}
</script>
@endsection
