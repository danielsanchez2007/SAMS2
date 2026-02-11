<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etiquetas_almacen', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo', ['imagen', 'archivo'])->default('imagen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etiquetas_almacen');
    }
};
