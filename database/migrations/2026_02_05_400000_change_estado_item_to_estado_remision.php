<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar en equipos
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropForeign(['estado_item_id']);
            $table->renameColumn('estado_item_id', 'estado_remision_id');
        });
        
        Schema::table('equipos', function (Blueprint $table) {
            $table->foreignId('estado_remision_id')->nullable()->change()->constrained('estado_remisiones')->nullOnDelete();
        });

        // Cambiar en equipos_debaja
        Schema::table('equipos_debaja', function (Blueprint $table) {
            $table->dropForeign(['estado_item_id']);
            $table->renameColumn('estado_item_id', 'estado_remision_id');
        });
        
        Schema::table('equipos_debaja', function (Blueprint $table) {
            $table->foreignId('estado_remision_id')->nullable()->change()->constrained('estado_remisiones')->nullOnDelete();
        });

        // Cambiar en material_didactico
        Schema::table('material_didactico', function (Blueprint $table) {
            $table->dropForeign(['estado_item_id']);
            $table->renameColumn('estado_item_id', 'estado_remision_id');
        });
        
        Schema::table('material_didactico', function (Blueprint $table) {
            $table->foreignId('estado_remision_id')->nullable()->change()->constrained('estado_remisiones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Revertir en equipos
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropForeign(['estado_remision_id']);
            $table->renameColumn('estado_remision_id', 'estado_item_id');
        });
        
        Schema::table('equipos', function (Blueprint $table) {
            $table->foreignId('estado_item_id')->nullable()->change()->constrained('estado_items')->nullOnDelete();
        });

        // Revertir en equipos_debaja
        Schema::table('equipos_debaja', function (Blueprint $table) {
            $table->dropForeign(['estado_remision_id']);
            $table->renameColumn('estado_remision_id', 'estado_item_id');
        });
        
        Schema::table('equipos_debaja', function (Blueprint $table) {
            $table->foreignId('estado_item_id')->nullable()->change()->constrained('estado_items')->nullOnDelete();
        });

        // Revertir en material_didactico
        Schema::table('material_didactico', function (Blueprint $table) {
            $table->dropForeign(['estado_remision_id']);
            $table->renameColumn('estado_remision_id', 'estado_item_id');
        });
        
        Schema::table('material_didactico', function (Blueprint $table) {
            $table->foreignId('estado_item_id')->nullable()->change()->constrained('estado_items')->nullOnDelete();
        });
    }
};
