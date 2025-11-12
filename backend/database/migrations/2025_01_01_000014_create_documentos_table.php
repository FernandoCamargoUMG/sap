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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            
            // Campos polimórficos para relación con cualquier entidad
            $table->string('documentable_type', 50)->comment('Tipo de entidad: FacturaCab, Cur, PresupuestoCab, MovimientoCab, Intra');
            $table->unsignedBigInteger('documentable_id')->comment('ID de la entidad relacionada');
            
            // Información del archivo
            $table->string('nombre_archivo', 255)->comment('Nombre del archivo');
            $table->string('ruta_archivo', 500)->comment('Ruta del archivo en el servidor');
            $table->string('tipo_archivo', 100)->nullable()->comment('Extensión o tipo MIME del archivo');
            $table->bigInteger('tamanio')->nullable()->comment('Tamaño del archivo en bytes');
            $table->text('descripcion')->nullable()->comment('Descripción o comentario del documento');
            
            // Usuario y estado
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('restrict')->comment('Usuario que subió el archivo');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar búsquedas
            $table->index(['documentable_type', 'documentable_id'], 'idx_documentable');
            $table->index('usuario_id');
            $table->index('tipo_archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
