<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('medico_usuario_id')->nullable()->constrained('personal_medico', 'usuario_id')->onDelete('set null');
            $table->string('nombre', 100);
            $table->string('diagnostico', 200)->nullable();
            $table->text('objetivo_terapeutico')->nullable();
            $table->enum('estado', ['Activo', 'Pausado', 'Completado', 'Suspendido', 'Modificado'])->default('Activo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin_estimada')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->string('medico_prescriptor', 100)->nullable();
            $table->string('institucion', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('modificado_en')->useCurrent()->useCurrentOnUpdate();
            
            // Índices para optimización
            $table->index(['paciente_id']);
            $table->index(['medico_usuario_id']);
            $table->index(['estado']);
            $table->index(['fecha_inicio']);
            $table->index(['fecha_fin_estimada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
}; 