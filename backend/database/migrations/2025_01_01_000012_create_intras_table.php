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
        Schema::create('intras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renglon_origen')->constrained('renglones')->onDelete('restrict')->comment('Renglón debitado');
            $table->foreignId('renglon_destino')->constrained('renglones')->onDelete('restrict')->comment('Renglón acreditado');
            $table->decimal('monto', 14, 2)->default(0)->comment('Monto de la transferencia');
            $table->timestamp('fecha')->useCurrent()->comment('Fecha de la transferencia');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('restrict')->comment('Usuario responsable');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index('renglon_origen');
            $table->index('renglon_destino');
            $table->index('fecha');
            $table->index('usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intras');
    }
};
