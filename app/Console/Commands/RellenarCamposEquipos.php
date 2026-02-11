<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RellenarCamposEquipos extends Command
{
    protected $signature = 'sams:rellenar-campos-equipos 
                            {--dry-run : Solo mostrar qué se rellenaría}
                            {--force : No pedir confirmación}';

    protected $description = 'Rellena campos vacíos de equipos de forma profesional (descripción, marca, modelo, etc.) usando datos de otros equipos del mismo tipo cuando sea posible';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('Modo dry-run: no se modificará la base de datos.');
        }

        $equipos = Equipo::with(['tipoItem', 'tipoEquipo', 'estadoRemision', 'proveedor', 'fabricante', 'sede', 'bodega', 'usoItem'])
            ->orderBy('codigo')
            ->get();

        $campos = [
            'descripcion' => 'Descripción',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'capacidades_resistencia' => 'Capacidades/resistencia',
            'lote' => 'Lote',
            'numero_factura' => 'Nº factura',
            'componentes_kit' => 'Componentes kit',
            'nombre_kit' => 'Nombre kit',
        ];
        $actualizados = 0;
        $porCampo = array_fill_keys(array_keys($campos), 0);

        foreach ($equipos as $equipo) {
            $cambios = [];
            $tipoEquipoId = $equipo->tipo_equipo_id;
            $tipoItemId = $equipo->tipo_item_id;

            foreach ($campos as $campo => $etiqueta) {
                $valor = $equipo->{$campo};
                if ($valor !== null && $valor !== '') {
                    continue;
                }

                $sustituto = $this->obtenerSustituto($equipos, $equipo->id, $campo, $tipoEquipoId, $tipoItemId);
                if ($sustituto !== null) {
                    $cambios[$campo] = $sustituto;
                    $porCampo[$campo]++;
                } else {
                    $cambios[$campo] = $this->valorPorDefecto($campo, $equipo);
                    $porCampo[$campo]++;
                }
            }

            if (!empty($cambios) && !$dryRun) {
                $equipo->update($cambios);
                $actualizados++;
            } elseif (!empty($cambios) && $dryRun) {
                $actualizados++;
                foreach ($cambios as $c => $v) {
                    $this->line("  [{$equipo->codigo}] {$c}: " . \Illuminate\Support\Str::limit((string) $v, 50));
                }
            }
        }

        $this->info("Equipos con al menos un campo rellenado: {$actualizados}");
        foreach ($campos as $campo => $etiqueta) {
            if ($porCampo[$campo] > 0) {
                $this->line("  - {$etiqueta}: {$porCampo[$campo]} rellenados");
            }
        }

        if ($dryRun && $actualizados > 0) {
            $this->info('Ejecuta sin --dry-run para aplicar los cambios.');
        }

        return 0;
    }

    private function obtenerSustituto($equipos, $excluirId, string $campo, $tipoEquipoId, $tipoItemId)
    {
        $candidatos = $equipos->filter(function ($e) use ($excluirId, $campo, $tipoEquipoId, $tipoItemId) {
            if ($e->id === $excluirId) return false;
            $v = $e->{$campo};
            if ($v === null || $v === '') return false;
            if ($tipoEquipoId && $e->tipo_equipo_id == $tipoEquipoId) return true;
            if ($tipoItemId && $e->tipo_item_id == $tipoItemId) return true;
            return false;
        });
        if ($candidatos->isNotEmpty()) {
            return $candidatos->first()->{$campo};
        }
        $cualquiera = $equipos->first(fn ($e) => $e->id !== $excluirId && $e->{$campo} !== null && $e->{$campo} !== '');
        return $cualquiera ? $cualquiera->{$campo} : null;
    }

    private function valorPorDefecto(string $campo, Equipo $equipo): string
    {
        if ($campo === 'descripcion') {
            $nombreTipo = $equipo->tipoEquipo?->nombre ?? $equipo->tipoItem?->nombre ?? 'Equipo';
            return $nombreTipo . ' - ' . $equipo->codigo;
        }
        if (in_array($campo, ['componentes_kit', 'nombre_kit', 'numero_factura'], true)) {
            return '—';
        }
        return '—';
    }
}
