<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_programados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicamento_tratamiento_id');
            $table->unsignedBigInteger('paciente_id');
            $table->time('hora_programada');
            $table->string('dias_semana', 20)->comment('L,M,X,J,V,S,D o Daily');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices para consultas frecuentes
            $table->index(['paciente_id', 'activo']);
            $table->index(['medicamento_tratamiento_id', 'activo']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            
            // Note: Foreign key constraints will be added later
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_programados');
    }
}; 