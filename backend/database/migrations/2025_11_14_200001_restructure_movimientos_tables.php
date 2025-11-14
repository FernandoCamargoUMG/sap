<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reestructurar tablas de movimientos para ejecuciones presupuestarias
     */
    public function up(): void
    {
        // Modificar movimiento_cab para registros de ejecución presupuestaria
        Schema::table('movimiento_cab', function (Blueprint $table) {
            // Cambiar tipo para ser más específico
            $table->dropColumn('tipo');
            $table->enum('tipo_movimiento', ['ejecucion_presupuestaria', 'ajuste', 'traslado'])
                  ->default('ejecucion_presupuestaria')
                  ->comment('Tipo de movimiento presupuestario');
            
            // Agregar referencia al presupuesto si es ejecución
            $table->foreignId('presupuesto_cab_id')
                  ->nullable()
                  ->constrained('presupuesto_cab')
                  ->onDelete('restrict')
                  ->comment('Presupuesto al que pertenece la ejecución');
            
            // Agregar número de documento/referencia
            $table->string('numero_documento', 50)->nullable()->comment('Número de factura, recibo, etc.');
            $table->string('proveedor', 200)->nullable()->comment('Proveedor o beneficiario');
        });

        // Modificar movimiento_det para detalles de ejecución
        Schema::table('movimiento_det', function (Blueprint $table) {
            // Eliminar tipo_afectacion (no aplica para presupuestos)
            $table->dropColumn('tipo_afectacion');
            
            // Agregar referencia al detalle del presupuesto
            $table->foreignId('presupuesto_det_id')
                  ->nullable()
                  ->constrained('presupuesto_det')
                  ->onDelete('restrict')
                  ->comment('Detalle del presupuesto ejecutado');
            
            // El monto será siempre positivo (monto ejecutado)
            $table->string('descripcion_detalle', 255)->nullable()->comment('Detalle específico del gasto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimiento_det', function (Blueprint $table) {
            $table->dropForeign(['presupuesto_det_id']);
            $table->dropColumn(['presupuesto_det_id', 'descripcion_detalle']);
            $table->enum('tipo_afectacion', ['debe', 'haber'])->comment('Tipo de movimiento contable');
        });

        Schema::table('movimiento_cab', function (Blueprint $table) {
            $table->dropForeign(['presupuesto_cab_id']);
            $table->dropColumn(['tipo_movimiento', 'presupuesto_cab_id', 'numero_documento', 'proveedor']);
            $table->enum('tipo', ['ingreso', 'egreso'])->comment('Tipo de movimiento');
        });
    }
};