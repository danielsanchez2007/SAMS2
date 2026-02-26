<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EquipoDebaja;
use App\Models\CodigoDisponible;
use App\Models\Equipo;
use App\Models\MaterialDidactico;

class TestCodigoAsignacion extends Command
{
    protected $signature = 'test:codigo-asignacion';
    protected $description = 'Test the code assignment for transfer workflow';

    public function handle()
    {
        $this->info('=== Testing Code Assignment ===');
        
        $empresaId = 1;
        $destino = 'equipos_debaja';
        $prefijo = 'DB';
        
        // Test 1: Check existing codes in table
        $this->info("\n1. Checking codigos_disponibles table:");
        $codigosEnTabla = CodigoDisponible::where('utilizado', false)
            ->where('codigo', 'like', $prefijo . '%')
            ->get(['codigo']);
        
        $this->info("   Found " . count($codigosEnTabla) . " available codes with prefix DB");
        $codigosEnTabla->each(function($c) {
            $this->line("     - " . $c->codigo);
        });
        
        // Test 2: Generate next code if none available
        $this->info("\n2. Generating next code:");
        
        $ultimoEquipo = Equipo::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->get()
            ->map(function ($item) use ($prefijo) {
                if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max();
        
        $ultimoDebaja = EquipoDebaja::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->get()
            ->map(function ($item) use ($prefijo) {
                if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max();
        
        $ultimoMaterial = MaterialDidactico::where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefijo . '%')
            ->get()
            ->map(function ($item) use ($prefijo) {
                if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)$/', $item->codigo, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max();
        
        $this->info("   ultimoEquipo: " . ($ultimoEquipo ?? "null"));
        $this->info("   ultimoDebaja: " . ($ultimoDebaja ?? "null"));
        $this->info("   ultimoMaterial: " . ($ultimoMaterial ?? "null"));
        
        $numeros = array_filter([$ultimoEquipo, $ultimoDebaja, $ultimoMaterial], fn($n) => $n !== null && $n > 0);
        $ultimoNumero = !empty($numeros) ? max($numeros) : 0;
        $proximoCodigo = $prefijo . ($ultimoNumero + 1);
        
        $this->info("   Next code to assign: " . $proximoCodigo);
        
        // Test 3: Simulate API response
        $this->info("\n3. API Response structure:");
        $response = [
            'ultimo_equipo' => null,
            'codigos_disponibles' => [
                [
                    'codigo' => $proximoCodigo,
                    'descripcion' => null
                ]
            ]
        ];
        
        $this->info("   " . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        $this->info("\n✓ Test completed successfully!");
        
        return 0;
    }
}
