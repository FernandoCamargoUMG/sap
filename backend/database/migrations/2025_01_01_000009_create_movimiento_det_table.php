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
        Schema::create('movimiento_det', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimiento_id')->constrained('movimiento_cab')->onDelete('cascade')->comment('Movimiento asociado');
            $table->foreignId('renglon_id')->constrained('renglones')->onDelete('restrict')->comment('Renglón afectado');
            $table->decimal('monto', 14, 2)->default(0)->comment('Monto del movimiento');
            $table->enum('tipo_afectacion', ['debe', 'haber'])->comment('Tipo de movimiento contable');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index('movimiento_id');
            $table->index('renglon_id');
            $table->index('tipo_afectacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_det');
    }
};
