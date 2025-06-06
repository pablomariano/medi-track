<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosis_prn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_tratamiento_id')->constrained('medicamentos_tratamientos')->onDelete('cascade');
            $table->decimal('dosis_maxima_dia', 10, 3)->nullable();
            $table->integer('intervalo_minimo_horas');
            $table->text('condicion_uso');
            $table->integer('dosis_maxima_consecutiva')->nullable();
            $table->integer('periodo_descanso_horas')->nullable();
            
            // Índices para optimización
            $table->index(['medicamento_tratamiento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosis_prn');
    }
}; 