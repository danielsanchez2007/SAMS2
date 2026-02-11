<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoja_vida_valores_fijos', function (Blueprint $table) {
            $table->string('tipo', 50)->default('texto')->after('clave');
            $table->json('config')->nullable()->after('valor');
        });
    }

    public function down(): void
    {
        Schema::table('hoja_vida_valores_fijos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'config']);
        });
    }
};
