<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use App\Models\TipoEquipo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConsolidarTiposEquipo extends Command
{
    protected $signature = 'sams:consolidar-tipos-equipo 
                            {--dry-run : Solo mostrar qué se haría, sin modificar}
                            {--force : No pedir confirmación}';

    protected $description = 'Consolida tipos de equipo: unifica variantes (ej. Arnés de X, Arnés multipropósito) en un solo nombre (Arnés)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('Modo dry-run: no se modificará la base de datos.');
        }

        $tipos = TipoEquipo::orderBy('nombre')->get();
        if ($tipos->isEmpty()) {
            $this->info('No hay tipos de equipo.');
            return 0;
        }

        // Agrupar por "nombre canónico" = primera palabra (ej. "Arnés de cuerpo entero" -> "Arnés")
        $grupos = [];
        foreach ($tipos as $tipo) {
            $nombre = trim($tipo->nombre);
            $palabras = preg_split('/\s+/u', $nombre, 2);
            $canonico = $palabras ? mb_convert_case($palabras[0], MB_CASE_TITLE, 'UTF-8') : $nombre;
            $grupos[$canonico][] = $tipo;
        }

        $acciones = [];
        foreach ($grupos as $canonico => $lista) {
            if (count($lista) <= 1) {
                $t = $lista[0];
                if ($t->nombre !== $canonico) {
                    $acciones[] = ['renombrar', $t->nombre, $canonico, 0];
                }
                continue;
            }
            // Ordenar: preferir el que ya se llama exactamente igual al canónico, luego el de nombre más corto
            usort($lista, function ($a, $b) use ($canonico) {
                $aExact = ($a->nombre === $canonico) ? 0 : 1;
                $bExact = ($b->nombre === $canonico) ? 0 : 1;
                if ($aExact !== $bExact) {
                    return $aExact - $bExact;
                }
                return strlen($a->nombre) - strlen($b->nombre);
            });
            $mantener = $lista[0];
            $eliminar = array_slice($lista, 1);
            $equiposAReasignar = 0;
            foreach ($eliminar as $t) {
                $equiposAReasignar += Equipo::where('tipo_equipo_id', $t->id)->count();
            }
            $acciones[] = ['fusionar', $mantener->nombre . ' (+ ' . count($eliminar) . ' variantes)', $canonico, $equiposAReasignar];
        }

        $this->table(
            ['Acción', 'Actual', 'Nombre final', 'Equipos afectados'],
            array_map(fn ($a) => [$a[0], $a[1], $a[2], $a[3]], $acciones)
        );

        if (!$force && !$dryRun && !$this->confirm('¿Aplicar estos cambios en la base de datos?')) {
            return 0;
        }

        $renombrados = 0;
        $fusionados = 0;
        $eliminados = 0;

        foreach ($grupos as $canonico => $lista) {
            if (count($lista) === 1) {
                $t = $lista[0];
                if ($t->nombre !== $canonico && !$dryRun) {
                    $t->update(['nombre' => $canonico]);
                    $renombrados++;
                }
                continue;
            }

            usort($lista, function ($a, $b) use ($canonico) {
                $aExact = ($a->nombre === $canonico) ? 0 : 1;
                $bExact = ($b->nombre === $canonico) ? 0 : 1;
                if ($aExact !== $bExact) {
                    return $aExact - $bExact;
                }
                return strlen($a->nombre) - strlen($b->nombre);
            });
            $mantener = $lista[0];
            $eliminar = array_slice($lista, 1);
            $idsEliminar = array_map(fn ($t) => $t->id, $eliminar);

            if (!$dryRun) {
                DB::transaction(function () use ($mantener, $idsEliminar, $canonico, &$fusionados, &$eliminados) {
                    Equipo::whereIn('tipo_equipo_id', $idsEliminar)->update(['tipo_equipo_id' => $mantener->id]);
                    TipoEquipo::whereIn('id', $idsEliminar)->delete();
                    if ($mantener->nombre !== $canonico) {
                        $mantener->update(['nombre' => $canonico]);
                    }
                    $fusionados++;
                    $eliminados += count($idsEliminar);
                });
            }
        }

        if ($dryRun) {
            $this->info('Dry-run completado. Ejecuta sin --dry-run para aplicar.');
        } else {
            $this->info("Listo. Renombrados: {$renombrados}, fusionados: {$fusionados} grupos, tipos eliminados: {$eliminados}.");
        }

        return 0;
    }
}
