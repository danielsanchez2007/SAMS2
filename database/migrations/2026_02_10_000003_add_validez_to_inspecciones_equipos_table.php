<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspecciones_equipos', function (Blueprint $table) {
            if (!Schema::hasColumn('inspecciones_equipos', 'validez_inspeccion')) {
                $table->date('validez_inspeccion')->nullable()->after('fecha_inspeccion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inspecciones_equipos', function (Blueprint $table) {
            if (Schema::hasColumn('inspecciones_equipos', 'validez_inspeccion')) {
                $table->dropColumn('validez_inspeccion');
            }
        });
    }
};

