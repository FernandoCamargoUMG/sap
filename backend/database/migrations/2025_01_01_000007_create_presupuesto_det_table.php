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
        Schema::create('presupuesto_det', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuesto_cab')->onDelete('cascade')->comment('Presupuesto asociado');
            $table->foreignId('renglon_id')->constrained('renglones')->onDelete('restrict')->comment('Renglón presupuestario');
            $table->decimal('monto_asignado', 14, 2)->default(0)->comment('Presupuesto asignado');
            $table->decimal('monto_ejecutado', 14, 2)->default(0)->comment('Presupuesto ejecutado');
            $table->string('descripcion', 255)->nullable()->comment('Nota o referencia');
            $table->unsignedBigInteger('documento_id')->nullable()->comment('Soporte PDF');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Índices
            $table->index('presupuesto_id');
            $table->index('renglon_id');
            $table->index('documento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_det');
    }
};
