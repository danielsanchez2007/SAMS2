<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('cargo_id')->constrained('roles')->nullOnDelete();
            $table->string('imagen_usuario')->nullable()->after('nombre_firma');
            $table->string('firma_imagen')->nullable()->after('imagen_usuario');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'imagen_usuario', 'firma_imagen']);
        });
    }
};
