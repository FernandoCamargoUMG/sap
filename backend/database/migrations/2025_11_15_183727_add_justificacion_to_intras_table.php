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
        Schema::table('intras', function (Blueprint $table) {
            $table->text('justificacion')->nullable()->after('monto')->comment('Justificación de la transferencia presupuestaria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intras', function (Blueprint $table) {
            $table->dropColumn('justificacion');
        });
    }
};
