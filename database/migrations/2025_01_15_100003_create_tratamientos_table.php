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
            $table->unsignedBigInteger('paciente_id');
            $table->string('nombre', 255);
            $table->text('diagnostico')->nullable();
            $table->enum('tipo', ['Programado'])->default('Programado');
            $table->enum('estado', ['Activo', 'Pausado', 'Completado', 'Suspendido'])->default('Activo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['paciente_id', 'estado']);
            $table->index(['tipo', 'estado']);
            
            // Note: Foreign key constraint for paciente_id will be added later
            // after the pacientes table is created
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
}; 