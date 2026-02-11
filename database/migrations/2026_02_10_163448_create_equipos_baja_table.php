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
        Schema::create('equipos_baja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');
            $table->date('fecha_baja');
            $table->string('acta_numero')->nullable();
            $table->text('resumen_baja')->nullable(); // Motivo/resumen del porqué se da de baja
            $table->string('acta_pdf')->nullable(); // Ruta al PDF generado
            $table->string('responsable_inventario_nombre')->nullable();
            $table->string('responsable_inventario_cc')->nullable();
            $table->string('gerente_administrativa_nombre')->nullable();
            $table->string('gerente_administrativa_cc')->nullable();
            $table->json('asistentes')->nullable(); // Lista de asistentes con cargos
            $table->json('items_baja')->nullable(); // Items de la tabla (código, descripción, marca, serial, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos_baja');
    }
};
