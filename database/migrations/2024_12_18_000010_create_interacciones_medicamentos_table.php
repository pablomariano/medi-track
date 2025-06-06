<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interacciones_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('principio_activo_1_id')->constrained('principios_activos')->onDelete('cascade');
            $table->foreignId('principio_activo_2_id')->constrained('principios_activos')->onDelete('cascade');
            $table->string('tipo_interaccion', 30);
            $table->text('mecanismo')->nullable();
            $table->text('efecto_clinico')->nullable();
            $table->text('recomendacion')->nullable();
            $table->tinyInteger('severidad'); // 1-5 scale
            $table->string('fuente', 100)->nullable();
            $table->boolean('activo')->default(true);
            
            // Índices para optimización
            $table->unique(['principio_activo_1_id', 'principio_activo_2_id']);
            $table->index(['tipo_interaccion']);
            $table->index(['severidad']);
            $table->index(['activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interacciones_medicamentos');
    }
}; 