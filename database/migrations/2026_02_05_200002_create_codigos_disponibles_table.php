<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codigos_disponibles', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('origen_tipo')->nullable(); // 'equipos', 'equipos_debaja', 'material_didactico'
            $table->foreignId('equipo_original_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->text('descripcion_original')->nullable();
            $table->boolean('utilizado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_disponibles');
    }
};
