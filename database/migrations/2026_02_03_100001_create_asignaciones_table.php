<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->unique('equipo_id'); // Un equipo solo puede estar asignado a un usuario a la vez
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
