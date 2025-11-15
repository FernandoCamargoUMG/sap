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
        Schema::table('cur', function (Blueprint $table) {
            $table->string('numero_cur', 50)->unique()->after('id');
            $table->text('descripcion')->after('monto');
            $table->renameColumn('fecha', 'fecha_compromiso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cur', function (Blueprint $table) {
            $table->dropColumn(['numero_cur', 'descripcion']);
            $table->renameColumn('fecha_compromiso', 'fecha');
        });
    }
};
