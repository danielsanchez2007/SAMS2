<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspecciones_equipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_id');
            $table->unsignedBigInteger('tipo_equipo_id');
            $table->date('fecha_inspeccion');
            $table->string('resultado', 10)->nullable(); // C, NC, etc.
            $table->text('hallazgos')->nullable();
            $table->timestamps();

            $table->foreign('equipo_id')->references('id')->on('equipos')->onDelete('cascade');
            $table->foreign('tipo_equipo_id')->references('id')->on('tipo_equipos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspecciones_equipos');
    }
};

