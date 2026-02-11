<?php

namespace App\Console\Commands;

use App\Models\Bodega;
use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\EquipoDebaja;
use App\Models\EstadoRemision;
use App\Models\MaterialDidactico;
use App\Models\Sede;
use App\Models\TipoEquipo;
use App\Models\TipoItem;
use App\Models\UsoItem;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerarEquiposPrueba extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sams:generar-equipos-prueba
                            {cantidad=20 : Cantidad de equipos de prueba}
                            {--empresa_id= : ID de empresa destino}
                            {--prefijo=IN : Prefijo de código (ej: IN, DB, MD)}
                            {--registro=normal : tipo_registro (normal|debaja|material|auditoria)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera equipos de prueba con códigos secuenciales';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cantidad = (int) $this->argument('cantidad');
        if ($cantidad < 1 || $cantidad > 500) {
            $this->error('La cantidad debe estar entre 1 y 500.');
            return self::FAILURE;
        }

        $registro = Str::lower((string) $this->option('registro'));
        if (!in_array($registro, ['normal', 'debaja', 'material', 'auditoria'], true)) {
            $this->error('El tipo de registro debe ser: normal, debaja, material o auditoria.');
            return self::FAILURE;
        }

        $prefijo = strtoupper(trim((string) $this->option('prefijo')));
        if ($prefijo === '') {
            $prefijo = $registro === 'normal' ? 'IN' : 'DB';
        }

        $empresa = $this->resolverEmpresa();
        if (!$empresa) {
            return self::FAILURE;
        }

        $tipoItem = TipoItem::query()->orderBy('id')->first();
        $tipoEquipo = TipoEquipo::query()->orderBy('id')->first();
        $estadoRemision = EstadoRemision::query()
            ->orderBy('id')
            ->get()
            ->first(function ($estado) {
                return !Str::contains(Str::lower((string) $estado->nombre), 'baja');
            }) ?? EstadoRemision::query()->orderBy('id')->first();

        $sede = Sede::query()
            ->where('empresa_id', $empresa->id)
            ->orderBy('id')
            ->first() ?? Sede::query()->orderBy('id')->first();

        $bodega = $sede
            ? Bodega::query()->where('sede_id', $sede->id)->orderBy('id')->first()
            : null;

        if (!$bodega) {
            $bodega = Bodega::query()->orderBy('id')->first();
        }

        $usoItem = UsoItem::query()->orderBy('id')->first();

        if (!$tipoItem || !$tipoEquipo || !$estadoRemision || !$sede || !$bodega) {
            $this->error('Faltan datos base. Verifica que existan: tipo_items, tipo_equipos, estado_remisiones, sedes y bodegas.');
            return self::FAILURE;
        }

        $siguienteNumero = $this->obtenerSiguienteNumero($prefijo, (int) $empresa->id);
        $creados = [];

        $this->info("Generando {$cantidad} equipos de prueba en empresa: {$empresa->nombre} (ID {$empresa->id})");
        $this->line("Prefijo: {$prefijo} | Tipo registro: {$registro}");

        $bar = $this->output->createProgressBar($cantidad);
        $bar->start();

        for ($i = 1; $i <= $cantidad; $i++) {
            do {
                $codigo = $prefijo . $siguienteNumero;
                $siguienteNumero++;
            } while ($this->codigoExiste($codigo, (int) $empresa->id));

            $equipo = Equipo::create([
                'empresa_id' => $empresa->id,
                'tipo_item_id' => $tipoItem->id,
                'tipo_equipo_id' => $tipoEquipo->id,
                'codigo' => $codigo,
                'nombre' => 'Equipo Prueba ' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'codigo_bloqueado' => false,
                'estado_remision_id' => $estadoRemision->id,
                'tipo_registro' => $registro,
                'descripcion' => 'Equipo de prueba autogenerado #' . $i,
                'marca' => 'SAMS',
                'modelo' => 'PRUEBA-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'sede_id' => $sede->id,
                'bodega_id' => $bodega->id,
                'vida_util' => 12,
                'fecha_compra' => now()->toDateString(),
                'uso_item_id' => $usoItem?->id,
                'es_kit' => false,
                'tiene_manual_fabricante' => false,
                'tiene_certificacion' => false,
                'tiene_imagen_general' => false,
                'tiene_imagen_etiqueta' => false,
            ]);

            $creados[] = $equipo->codigo;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Equipos de prueba generados correctamente.');
        $this->line('Códigos creados: ' . implode(', ', $creados));

        return self::SUCCESS;
    }

    private function resolverEmpresa(): ?Empresa
    {
        $empresaId = $this->option('empresa_id');
        if ($empresaId) {
            $empresa = Empresa::find((int) $empresaId);
            if (!$empresa) {
                $this->error("No existe la empresa con ID {$empresaId}.");
                return null;
            }

            return $empresa;
        }

        $empresa = Empresa::query()->orderBy('id')->first();
        if (!$empresa) {
            $this->error('No hay empresas registradas para crear equipos de prueba.');
            return null;
        }

        return $empresa;
    }

    private function obtenerSiguienteNumero(string $prefijo, int $empresaId): int
    {
        $maximo = 0;

        $codigosEquipos = Equipo::query()
            ->where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->pluck('codigo');
        foreach ($codigosEquipos as $codigo) {
            $maximo = max($maximo, $this->extraerNumero($prefijo, (string) $codigo));
        }

        $codigosDebaja = EquipoDebaja::query()
            ->where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->pluck('codigo');
        foreach ($codigosDebaja as $codigo) {
            $maximo = max($maximo, $this->extraerNumero($prefijo, (string) $codigo));
        }

        $codigosMaterial = MaterialDidactico::query()
            ->where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->pluck('codigo');
        foreach ($codigosMaterial as $codigo) {
            $maximo = max($maximo, $this->extraerNumero($prefijo, (string) $codigo));
        }

        return $maximo + 1;
    }

    private function codigoExiste(string $codigo, int $empresaId): bool
    {
        return Equipo::query()->where('empresa_id', $empresaId)->where('codigo', $codigo)->exists()
            || EquipoDebaja::query()->where('empresa_id', $empresaId)->where('codigo', $codigo)->exists()
            || MaterialDidactico::query()->where('empresa_id', $empresaId)->where('codigo', $codigo)->exists();
    }

    private function extraerNumero(string $prefijo, string $codigo): int
    {
        if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)$/', $codigo, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}
