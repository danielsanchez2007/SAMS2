<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguridad_codigo', function (Blueprint $table) {
            $table->id();
            $table->text('codigo')->nullable();
            $table->boolean('bloqueado')->default(true);
            $table->timestamps();
        });

        // Insertar registro inicial
        DB::table('seguridad_codigo')->insert([
            'codigo' => null,
            'bloqueado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_codigo');
    }
};
