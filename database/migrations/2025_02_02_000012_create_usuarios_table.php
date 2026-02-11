<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento');
            $table->string('cedula')->unique();
            $table->string('nombre');
            $table->string('apellidos');
            $table->date('fecha_nacimiento');
            $table->text('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo_electronico')->nullable();
            $table->boolean('tiene_correo_corporativo')->default(false);
            $table->string('correo_corporativo')->nullable();
            $table->boolean('tiene_telefono_corporativo')->default(false);
            $table->string('telefono_corporativo')->nullable();
            $table->string('departamento')->nullable();
            $table->string('departamento_otro')->nullable();
            $table->string('municipio')->nullable();
            $table->string('municipio_otro')->nullable();
            $table->string('tratamiento')->nullable();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('activo')->default(true);
            $table->string('firma')->nullable();
            $table->string('nombre_firma')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
