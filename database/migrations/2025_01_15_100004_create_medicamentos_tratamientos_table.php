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
            $table->unsignedBigInteger('medicamento_id');
            $table->unsignedBigInteger('tratamiento_id');
            $table->decimal('dosis_cantidad', 10, 3);
            $table->string('unidad_dosis', 50);
            $table->integer('frecuencia_horas')->comment('Frecuencia en horas para medicamentos programados');
            $table->integer('tolerancia_antes_minutos')->default(30)->comment('Ventana antes del horario');
            $table->integer('tolerancia_despues_minutos')->default(60)->comment('Ventana después del horario');
            $table->integer('duracion_dias')->nullable()->comment('Duración del tratamiento en días');
            $table->text('instrucciones_especiales')->nullable();
            $table->enum('estado', ['Activo', 'Pausado', 'Suspendido'])->default('Activo');
            $table->boolean('activo')->default(true);
            $table->text('motivo_suspension')->nullable();
            $table->integer('orden')->default(1);
            $table->timestamps();
            
            // Índices
            $table->index(['medicamento_id', 'tratamiento_id']);
            $table->index(['tratamiento_id', 'estado']);
            
            // Foreign keys
            $table->foreign('medicamento_id')->references('id')->on('medicamentos')->onDelete('cascade');
            $table->foreign('tratamiento_id')->references('id')->on('tratamientos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamentos_tratamientos');
    }
}; 