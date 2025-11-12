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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->string('tabla_afectada', 100)->comment('Módulo afectado');
            $table->unsignedBigInteger('registro_id')->comment('ID del registro afectado');
            $table->enum('accion', ['creado', 'modificado', 'eliminado', 'restaurado'])->comment('Acción realizada');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('restrict');
            $table->timestamp('fecha_accion')->useCurrent()->comment('Fecha y hora de la acción');
            $table->text('detalle')->nullable()->comment('Descripción contextual de la acción');
            $table->timestamps();
            
            // Índices para mejorar búsquedas
            $table->index(['tabla_afectada', 'registro_id']);
            $table->index(['usuario_id', 'fecha_accion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
