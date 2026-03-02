<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_equipos', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_equipos', 'formato_html')) {
                $table->longText('formato_html')->nullable()->after('formato_archivo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipo_equipos', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_equipos', 'formato_html')) {
                $table->dropColumn('formato_html');
            }
        });
    }
};
