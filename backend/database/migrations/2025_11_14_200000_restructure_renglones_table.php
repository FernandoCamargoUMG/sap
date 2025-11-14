<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reestructurar tabla renglones - eliminar campos de montos
     * Los renglones ahora serán solo registros de clasificación
     */
    public function up(): void
    {
        Schema::table('renglones', function (Blueprint $table) {
            // Eliminar campos de montos - ya no se manejan a nivel de renglón
            $table->dropColumn(['monto_inicial', 'saldo_actual']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('renglones', function (Blueprint $table) {
            // Restaurar campos en caso de rollback
            $table->decimal('monto_inicial', 14, 2)->default(0)->comment('Presupuesto asignado');
            $table->decimal('saldo_actual', 14, 2)->default(0)->comment('Saldo restante');
        });
    }
};