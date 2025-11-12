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
        Schema::create('renglones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique()->comment('Código o número del renglón');
            $table->string('nombre', 100);
            $table->string('grupo', 50)->nullable()->comment('Clasificación del gasto');
            $table->decimal('monto_inicial', 14, 2)->default(0)->comment('Presupuesto asignado');
            $table->decimal('saldo_actual', 14, 2)->default(0)->comment('Saldo restante');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index('codigo');
            $table->index('grupo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renglones');
    }
};
