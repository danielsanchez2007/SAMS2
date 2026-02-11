<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoja_vida_plantilla', function (Blueprint $table) {
            $table->dropForeign(['tipo_item_id']);
            $table->dropColumn('tipo_item_id');
        });

        Schema::table('hoja_vida_plantilla', function (Blueprint $table) {
            $table->foreignId('tipo_equipo_id')->nullable()->after('id')->constrained('tipo_equipos')->nullOnDelete();
            $table->unique('tipo_equipo_id');
        });
    }

    public function down(): void
    {
        Schema::table('hoja_vida_plantilla', function (Blueprint $table) {
            $table->dropForeign(['tipo_equipo_id']);
            $table->dropColumn('tipo_equipo_id');
        });
        Schema::table('hoja_vida_plantilla', function (Blueprint $table) {
            $table->foreignId('tipo_item_id')->nullable()->after('id')->constrained('tipo_items')->nullOnDelete();
        });
    }
};
