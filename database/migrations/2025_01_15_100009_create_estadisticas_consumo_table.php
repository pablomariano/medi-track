<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estadisticas_consumo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('medicamento_id');
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->enum('tipo_periodo', ['Diario', 'Semanal', 'Mensual']);
            
            // Métricas de adherencia
            $table->integer('dosis_programadas')->default(0);
            $table->integer('dosis_administradas')->default(0);
            $table->integer('dosis_omitidas')->default(0);
            $table->integer('dosis_tardias')->default(0);
            $table->integer('dosis_prn')->default(0);
            
            // Métricas PRN específicas
            $table->json('administraciones_prn_por_sintoma')->nullable()->comment('Contador por tipo de síntoma');
            $table->decimal('promedio_intensidad_sintomas', 3, 1)->nullable()->comment('Intensidad promedio de síntomas PRN');
            
            // Métricas de calidad
            $table->decimal('adherencia_porcentaje', 5, 2)->nullable()->comment('administradas/programadas * 100');
            $table->decimal('puntualidad_porcentaje', 5, 2)->nullable()->comment('dentro ventana/administradas * 100');
            
            // Consumo total
            $table->decimal('cantidad_total_administrada', 10, 3)->default(0);
            $table->string('unidad', 50)->nullable();
            
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['paciente_id', 'medicamento_id', 'periodo_inicio', 'tipo_periodo'], 'unique_estadistica');
            $table->index(['tipo_periodo', 'periodo_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estadisticas_consumo');
    }
}; 