<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LimpiarBaseDatos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:limpiar {--force : Ejecutar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia toda la base de datos excepto la tabla de roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('¿Estás seguro de que deseas limpiar toda la base de datos excepto los roles? Esta acción no se puede deshacer.')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $this->info('Iniciando limpieza de la base de datos...');

        try {
            DB::beginTransaction();

            // Lista de tablas a excluir (roles y tablas del sistema)
            $tablasExcluidas = [
                'roles',
                'migrations',
                'cache',
                'cache_locks',
                'failed_jobs',
                'job_batches',
                'jobs',
                'sessions',
                'password_reset_tokens',
                'password_reset_codes',
            ];

            $driver = DB::getDriverName();
            $tablasLimpieza = [];

            // Obtener todas las tablas según el driver
            if ($driver === 'sqlite') {
                $tablas = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($tablas as $tabla) {
                    $nombreTabla = $tabla->name;
                    if (!in_array($nombreTabla, $tablasExcluidas)) {
                        $tablasLimpieza[] = $nombreTabla;
                    }
                }
            } else {
                // MySQL/MariaDB
                $tablas = DB::select('SHOW TABLES');
                $databaseName = DB::getDatabaseName();
                $key = "Tables_in_{$databaseName}";
                foreach ($tablas as $tabla) {
                    $nombreTabla = $tabla->$key;
                    if (!in_array($nombreTabla, $tablasExcluidas)) {
                        $tablasLimpieza[] = $nombreTabla;
                    }
                }
            }

            // Limpiar cada tabla
            foreach ($tablasLimpieza as $tabla) {
                $this->line("Limpiando tabla: {$tabla}...");
                
                if ($driver === 'sqlite') {
                    // SQLite: desactivar foreign keys temporalmente
                    DB::statement('PRAGMA foreign_keys = OFF;');
                    DB::table($tabla)->truncate();
                    DB::statement('PRAGMA foreign_keys = ON;');
                } else {
                    // MySQL/MariaDB: desactivar foreign keys temporalmente
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::table($tabla)->truncate();
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
                
                $this->info("  ✓ Tabla {$tabla} limpiada.");
            }

            DB::commit();

            $this->info('');
            $this->info('✓ Base de datos limpiada exitosamente.');
            $this->info('✓ La tabla de roles se mantuvo intacta.');
            
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error al limpiar la base de datos: ' . $e->getMessage());
            return 1;
        }
    }
}
