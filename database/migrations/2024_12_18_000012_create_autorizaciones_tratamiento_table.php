<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autorizaciones_tratamiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tratamiento_id')->constrained('tratamientos')->onDelete('cascade');
            $table->foreignId('apoderado_usuario_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo_autorizacion', ['Inicial', 'Modificacion', 'Suspension', 'Emergencia']);
            $table->enum('estado', ['Pendiente', 'Autorizada', 'Rechazada'])->default('Pendiente');
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->timestamp('fecha_respuesta')->nullable();
            $table->text('observaciones_apoderado')->nullable();
            $table->string('documento_adjunto', 255)->nullable();
            
            // Índices para optimización
            $table->index(['tratamiento_id']);
            $table->index(['apoderado_usuario_id']);
            $table->index(['paciente_id']);
            $table->index(['estado']);
            $table->index(['tipo_autorizacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizaciones_tratamiento');
    }
}; 