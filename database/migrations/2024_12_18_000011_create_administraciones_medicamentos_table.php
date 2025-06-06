<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administraciones_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_tratamiento_id')->constrained('medicamentos_tratamientos')->onDelete('cascade');
            $table->foreignId('cuidador_usuario_id')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->foreignId('paciente_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_hora_programada');
            $table->timestamp('fecha_hora_administrada')->nullable();
            $table->decimal('dosis_administrada', 10, 3)->nullable();
            $table->enum('estado', ['Programada', 'Administrada', 'Omitida', 'Retrasada'])->default('Programada');
            $table->text('observaciones')->nullable();
            $table->text('efectos_adversos')->nullable();
            $table->foreignId('creado_por')->constrained('users')->onDelete('restrict');
            $table->timestamp('creado_en')->useCurrent();
            
            // Índices para optimización
            $table->index(['medicamento_tratamiento_id']);
            $table->index(['cuidador_usuario_id']);
            $table->index(['paciente_id']);
            $table->index(['fecha_hora_programada']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administraciones_medicamentos');
    }
}; 