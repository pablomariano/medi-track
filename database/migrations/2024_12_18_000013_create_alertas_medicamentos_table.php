<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tratamiento_id')->nullable()->constrained('tratamientos')->onDelete('set null');
            $table->enum('tipo_alerta', ['Interaccion', 'Alergia', 'Dosis_Excedida', 'Vencimiento', 'Falta_Stock']);
            $table->enum('nivel_severidad', ['Baja', 'Media', 'Alta', 'Critica']);
            $table->text('mensaje');
            $table->json('medicamentos_involucrados')->nullable();
            $table->timestamp('fecha_generada')->useCurrent();
            $table->boolean('revisada')->default(false);
            $table->foreignId('revisada_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_revision')->nullable();
            $table->text('accion_tomada')->nullable();
            
            // Índices para optimización
            $table->index(['paciente_id']);
            $table->index(['tratamiento_id']);
            $table->index(['tipo_alerta']);
            $table->index(['nivel_severidad']);
            $table->index(['revisada']);
            $table->index(['fecha_generada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_medicamentos');
    }
}; 