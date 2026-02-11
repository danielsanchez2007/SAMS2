<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Agregar empresa_id
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
            
            // Eliminar el unique del código
            $table->dropUnique(['codigo']);
        });

        // Crear índice único compuesto: código + empresa_id
        Schema::table('equipos', function (Blueprint $table) {
            $table->unique(['codigo', 'empresa_id'], 'equipos_codigo_empresa_unique');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropUnique('equipos_codigo_empresa_unique');
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
            $table->string('codigo')->unique()->change();
        });
    }
};
