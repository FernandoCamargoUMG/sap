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
        Schema::create('presupuesto_cab', function (Blueprint $table) {
            $table->id();
            $table->year('anio')->comment('Año fiscal');
            $table->tinyInteger('mes')->comment('Mes (1-12)');
            $table->string('descripcion', 255)->nullable();
            $table->foreignId('creado_por')->constrained('usuarios')->onDelete('restrict')->comment('Usuario creador');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de creación');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=cerrado/inactivo');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index(['anio', 'mes']);
            $table->index('creado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_cab');
    }
};
