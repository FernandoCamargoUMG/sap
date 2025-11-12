<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimiento_cab', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['ingreso', 'egreso'])->comment('Tipo de movimiento');
            $table->timestamp('fecha')->useCurrent()->comment('Fecha de registro');
            $table->string('descripcion', 255)->nullable()->comment('Detalle del movimiento');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('restrict')->comment('Usuario responsable');
            $table->unsignedBigInteger('documento_id')->nullable()->comment('Soporte PDF');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=anulado');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index('tipo');
            $table->index('fecha');
            $table->index('usuario_id');
            $table->index('documento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_cab');
    }
};
