<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoja_vida_plantilla', function (Blueprint $table) {
            $table->string('archivo_pdf', 500)->nullable()->after('contenido_html');
        });

        Schema::create('equipo_hoja_vida_pdf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->string('ruta_archivo', 500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_hoja_vida_pdf');
        Schema::table('hoja_vida_plantilla', function (Blueprint $table) {
            $table->dropColumn('archivo_pdf');
        });
    }
};
