<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguridad_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->boolean('puede_editar')->default(false);
            $table->timestamps();

            // Un usuario o rol solo puede tener un permiso
            $table->unique(['usuario_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad_permisos');
    }
};
