<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos_debaja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_original_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->string('codigo_original')->nullable(); // Código que tenía antes del traspaso
            $table->string('codigo')->unique();
            $table->foreignId('tipo_item_id')->nullable()->constrained('tipo_items')->nullOnDelete();
            $table->foreignId('tipo_equipo_id')->nullable()->constrained('tipo_equipos')->nullOnDelete();
            $table->boolean('codigo_bloqueado')->default(false);
            $table->foreignId('estado_item_id')->nullable()->constrained('estado_items')->nullOnDelete();
            $table->text('descripcion');
            $table->string('marca')->nullable();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('fabricante_id')->nullable()->constrained('fabricantes')->nullOnDelete();
            $table->string('modelo')->nullable();
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->foreignId('bodega_id')->nullable()->constrained('bodegas')->nullOnDelete();
            $table->integer('vida_util')->default(0);
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_uso')->nullable();
            $table->foreignId('uso_item_id')->nullable()->constrained('uso_items')->nullOnDelete();
            $table->boolean('es_kit')->default(false);
            $table->string('nombre_kit')->nullable();
            $table->text('componentes_kit')->nullable();
            $table->decimal('valor', 15, 2)->nullable();
            $table->string('numero_factura')->nullable();
            $table->text('capacidades_resistencia')->nullable();
            $table->string('lote')->nullable();
            $table->date('fecha_compra')->nullable();
            $table->boolean('tiene_manual_fabricante')->default(false);
            $table->string('manual_fabricante')->nullable();
            $table->boolean('tiene_certificacion')->default(false);
            $table->string('certificacion_fabricante')->nullable();
            $table->boolean('tiene_imagen_general')->default(false);
            $table->string('imagen_general')->nullable();
            $table->boolean('tiene_imagen_etiqueta')->default(false);
            $table->string('imagen_etiqueta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos_debaja');
    }
};
