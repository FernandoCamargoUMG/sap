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
        Schema::create('actas_baja_cuantia', function (Blueprint $table) {
            $table->id();
            $table->string('numero_acta', 50)->unique();
            $table->date('fecha_acta');
            
            // Relación con proveedor
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            
            // Datos de la compra
            $table->text('descripcion_compra');
            $table->decimal('monto_total', 12, 2);
            
            // Detalle resumido de lo comprado
            $table->text('detalle');
            
            // Motivo o explicación del acta
            $table->text('contenido_acta');
            
            // Firmas o responsables
            $table->string('responsable', 150);
            $table->string('cargo_responsable', 150);
            
            // Usuario que registra
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            
            // Estado del acta
            $table->tinyInteger('estado')->default(1)->comment('1: Activa, 0: Inactiva');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actas_baja_cuantia');
    }
};
