<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esquemas_posologicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_tratamiento_id')->constrained('medicamentos_tratamientos')->onDelete('cascade');
            $table->integer('secuencia');
            $table->integer('dia_inicio');
            $table->integer('dia_fin')->nullable();
            $table->decimal('dosis_cantidad', 10, 3);
            $table->integer('frecuencia_horas');
            $table->text('observaciones')->nullable();
            
            // Índices para optimización
            $table->index(['medicamento_tratamiento_id']);
            $table->unique(['medicamento_tratamiento_id', 'secuencia']);
            $table->index(['dia_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esquemas_posologicos');
    }
}; 