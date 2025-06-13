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
        Schema::table('medicamentos_tratamientos', function (Blueprint $table) {
            // Renombrar columnas para que coincidan con lo que esperan los modelos
            $table->renameColumn('max_dosis_dia', 'dosis_maxima_dia');
            $table->renameColumn('max_dosis_semana', 'dosis_maxima_semana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicamentos_tratamientos', function (Blueprint $table) {
            // Revertir los cambios
            $table->renameColumn('dosis_maxima_dia', 'max_dosis_dia');
            $table->renameColumn('dosis_maxima_semana', 'max_dosis_semana');
        });
    }
};
