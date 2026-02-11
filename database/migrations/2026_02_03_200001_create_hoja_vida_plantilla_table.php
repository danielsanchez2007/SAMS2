<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoja_vida_plantilla', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->default('Hoja de vida');
            $table->longText('contenido_html')->nullable();
            $table->timestamps();
        });

        Schema::create('hoja_vida_valores_fijos', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoja_vida_valores_fijos');
        Schema::dropIfExists('hoja_vida_plantilla');
    }
};
