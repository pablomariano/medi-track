<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicamentos_tratamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->onDelete('cascade');
            $table->foreignId('tratamiento_id')->constrained('tratamientos')->onDelete('cascade');
            $table->enum('tipo_esquema', ['Fijo', 'Variable', 'PRN', 'Escalonamiento', 'Reduccion', 'Alterno'])->default('Fijo');
            $table->decimal('dosis_cantidad', 10, 3);
            $table->foreignId('unidad_dosis_id')->constrained('unidades_medida')->onDelete('restrict');
            $table->integer('frecuencia_horas')->nullable();
            $table->decimal('dosis_diaria_total', 10, 3)->nullable();
            $table->integer('duracion_dias')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('indicaciones_uso')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('motivo_suspension')->nullable();
            $table->integer('orden_prescripcion')->default(1);
            
            // Índices para optimización
            $table->index(['medicamento_id', 'tratamiento_id']);
            $table->index(['tipo_esquema']);
            $table->index(['activo']);
            $table->index(['fecha_inicio']);
            $table->index(['fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamentos_tratamientos');
    }
}; 