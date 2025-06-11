<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumen_adherencia_paciente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->date('fecha');
            
            // Resumen general del día
            $table->integer('total_medicamentos_activos')->default(0);
            $table->integer('total_dosis_programadas')->default(0);
            $table->integer('total_dosis_administradas')->default(0);
            $table->integer('total_dosis_omitidas')->default(0);
            $table->integer('total_dosis_prn')->default(0);
            
            // Resumen PRN específico
            $table->json('sintomas_mas_frecuentes')->nullable()->comment('Top 3 síntomas del día');
            $table->integer('total_medicamentos_prn_activos')->default(0);
            
            // Indicadores de calidad
            $table->decimal('adherencia_general_porcentaje', 5, 2)->nullable();
            $table->decimal('puntualidad_general_porcentaje', 5, 2)->nullable();
            
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['paciente_id', 'fecha']);
            $table->index(['fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumen_adherencia_paciente');
    }
}; 