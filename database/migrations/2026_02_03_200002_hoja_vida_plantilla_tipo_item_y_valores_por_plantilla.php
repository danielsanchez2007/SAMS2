<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoja_vida_plantilla', function (Blueprint $table) {
            $table->foreignId('tipo_item_id')->nullable()->after('id')->constrained('tipo_items')->nullOnDelete();
            $table->unique('tipo_item_id');
        });

        Schema::table('hoja_vida_valores_fijos', function (Blueprint $table) {
            $table->dropUnique(['clave']);
            $table->foreignId('hoja_vida_plantilla_id')->nullable()->after('id')->constrained('hoja_vida_plantilla')->cascadeOnDelete();
        });

        // Asignar valores existentes a la primera plantilla si hay alguna
        $primera = \DB::table('hoja_vida_plantilla')->orderBy('id')->first();
        if ($primera) {
            \DB::table('hoja_vida_valores_fijos')->update(['hoja_vida_plantilla_id' => $primera->id]);
        }

        Schema::table('hoja_vida_valores_fijos', function (Blueprint $table) {
            $table->unique(['hoja_vida_plantilla_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::table('hoja_vida_valores_fijos', function (Blueprint $table) {
            $table->dropUnique(['hoja_vida_plantilla_id', 'clave']);
            $table->dropForeign(['hoja_vida_plantilla_id']);
        });
        Schema::table('hoja_vida_plantilla', function (Blueprint $table) {
            $table->dropUnique(['tipo_item_id']);
            $table->dropForeign(['tipo_item_id']);
        });
    }
};
