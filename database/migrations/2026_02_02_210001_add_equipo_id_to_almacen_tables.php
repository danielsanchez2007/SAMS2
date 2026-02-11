<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('almacen_imagenes', function (Blueprint $table) {
            $table->foreignId('equipo_id')->nullable()->after('etiqueta_id')->constrained('equipos')->nullOnDelete();
        });
        Schema::table('almacen_archivos', function (Blueprint $table) {
            $table->foreignId('equipo_id')->nullable()->after('etiqueta_id')->constrained('equipos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('almacen_imagenes', function (Blueprint $table) {
            $table->dropForeign(['equipo_id']);
        });
        Schema::table('almacen_archivos', function (Blueprint $table) {
            $table->dropForeign(['equipo_id']);
        });
    }
};
