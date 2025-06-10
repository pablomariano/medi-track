<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicaciones_prn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_tratamiento_id')->constrained('medicamentos_tratamientos')->onDelete('cascade');
            $table->foreignId('sintoma_id')->constrained('sintomas_prn')->onDelete('cascade');
            $table->foreignId('criterio_id')->nullable()->constrained('criterios_prn')->onDelete('set null');
            $table->text('descripcion_personalizada')->nullable();
            $table->boolean('es_criterio_principal')->default(false);
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['medicamento_tratamiento_id', 'sintoma_id']);
            $table->index(['sintoma_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicaciones_prn');
    }
}; 