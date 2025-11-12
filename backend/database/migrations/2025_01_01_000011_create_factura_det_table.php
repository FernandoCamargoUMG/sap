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
        Schema::create('factura_det', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('factura_cab')->onDelete('cascade')->comment('Factura asociada');
            $table->foreignId('renglon_id')->constrained('renglones')->onDelete('restrict')->comment('Renglón presupuestario');
            $table->string('item', 100)->comment('Descripción del ítem');
            $table->integer('cantidad')->default(1)->comment('Cantidad de ítems');
            $table->decimal('precio_unitario', 12, 2)->default(0)->comment('Precio unitario');
            $table->decimal('subtotal', 14, 2)->default(0)->comment('Subtotal (cantidad * precio_unitario)');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index('factura_id');
            $table->index('renglon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_det');
    }
};
