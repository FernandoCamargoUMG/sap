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
        // Agregar foreign keys para documento_id en tablas que lo referencian
        Schema::table('presupuesto_det', function (Blueprint $table) {
            $table->foreign('documento_id')->references('id')->on('documentos')->onDelete('set null');
        });

        Schema::table('movimiento_cab', function (Blueprint $table) {
            $table->foreign('documento_id')->references('id')->on('documentos')->onDelete('set null');
        });

        Schema::table('factura_cab', function (Blueprint $table) {
            $table->foreign('documento_id')->references('id')->on('documentos')->onDelete('set null');
        });

        Schema::table('cur', function (Blueprint $table) {
            $table->foreign('documento_id')->references('id')->on('documentos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuesto_det', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
        });

        Schema::table('movimiento_cab', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
        });

        Schema::table('factura_cab', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
        });

        Schema::table('cur', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
        });
    }
};
