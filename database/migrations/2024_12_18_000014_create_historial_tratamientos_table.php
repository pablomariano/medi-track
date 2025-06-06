<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_tratamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tratamiento_id')->constrained('tratamientos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('restrict');
            $table->enum('accion', ['Creado', 'Modificado', 'Pausado', 'Reanudado', 'Finalizado', 'Cancelado']);
            $table->string('campo_modificado', 50)->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->text('motivo')->nullable();
            $table->timestamp('fecha_cambio')->useCurrent();
            
            // Índices para optimización
            $table->index(['tratamiento_id']);
            $table->index(['usuario_id']);
            $table->index(['accion']);
            $table->index(['fecha_cambio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_tratamientos');
    }
}; 