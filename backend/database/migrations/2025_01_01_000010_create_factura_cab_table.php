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
        Schema::create('factura_cab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('restrict')->comment('Proveedor');
            $table->string('folio', 50)->unique()->comment('Número de factura');
            $table->date('fecha')->comment('Fecha de emisión');
            $table->decimal('total', 14, 2)->default(0)->comment('Total de la factura');
            $table->unsignedBigInteger('documento_id')->nullable()->comment('PDF de la factura');
            $table->enum('tipo', ['inventario', 'bodega', 'despensa'])->comment('Tipo de factura');
            $table->tinyInteger('estado')->default(1)->comment('1=activa, 0=anulada');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index('proveedor_id');
            $table->index('folio');
            $table->index('fecha');
            $table->index('tipo');
            $table->index('documento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_cab');
    }
};
