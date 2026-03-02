<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipos_debaja', function (Blueprint $table) {
            if (!Schema::hasColumn('equipos_debaja', 'fecha_inspeccion')) {
                $table->date('fecha_inspeccion')->nullable()->comment('Fecha de la inspección/acta de baja');
            }
            if (!Schema::hasColumn('equipos_debaja', 'motivo_baja')) {
                $table->text('motivo_baja')->nullable()->comment('Motivo por el cual se da de baja el equipo');
            }
            if (!Schema::hasColumn('equipos_debaja', 'responsable_nombre')) {
                $table->string('responsable_nombre')->nullable()->comment('Nombre del responsable del inventario');
            }
            if (!Schema::hasColumn('equipos_debaja', 'responsable_cedula')) {
                $table->string('responsable_cedula')->nullable()->comment('Cédula del responsable del inventario');
            }
            if (!Schema::hasColumn('equipos_debaja', 'gerente_nombre')) {
                $table->string('gerente_nombre')->nullable()->comment('Nombre del gerente/aprobador');
            }
            if (!Schema::hasColumn('equipos_debaja', 'gerente_cedula')) {
                $table->string('gerente_cedula')->nullable()->comment('Cédula del gerente/aprobador');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos_debaja', function (Blueprint $table) {
            $table->dropColumnIfExists('fecha_inspeccion');
            $table->dropColumnIfExists('motivo_baja');
            $table->dropColumnIfExists('responsable_nombre');
            $table->dropColumnIfExists('responsable_cedula');
            $table->dropColumnIfExists('gerente_nombre');
            $table->dropColumnIfExists('gerente_cedula');
        });
    }
};
