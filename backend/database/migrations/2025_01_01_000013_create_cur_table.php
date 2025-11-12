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
        Schema::create('cur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renglon_id')->constrained('renglones')->onDelete('restrict')->comment('Renglón presupuestario');
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('restrict')->comment('Proveedor');
            $table->decimal('monto', 14, 2)->default(0)->comment('Monto del compromiso');
            $table->unsignedBigInteger('documento_id')->nullable()->comment('Soporte PDF');
            $table->timestamp('fecha')->useCurrent()->comment('Fecha del compromiso');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('restrict')->comment('Usuario responsable');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index('renglon_id');
            $table->index('proveedor_id');
            $table->index('documento_id');
            $table->index('fecha');
            $table->index('usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cur');
    }
};
