<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administraciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicamento_tratamiento_id');
            $table->unsignedBigInteger('horario_programado_id')->nullable()->comment('NULL para PRN');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('cuidador_usuario_id')->nullable();
            
            // Tiempos
            $table->timestamp('fecha_hora_programada')->nullable()->comment('NULL para PRN');
            $table->timestamp('fecha_hora_administrada');
            
            // Datos de administración
            $table->decimal('dosis_administrada', 10, 3);
            $table->enum('estado', ['Administrada', 'Omitida', 'Tardía'])->default('Administrada');
            $table->boolean('es_dentro_ventana_tolerancia')->default(true);
            $table->integer('minutos_diferencia')->nullable()->comment('Diferencia respecto al horario programado');
            
            // Datos específicos PRN
            $table->unsignedBigInteger('sintoma_reportado_id')->nullable()->comment('Síntoma que motivó la administración PRN');
            $table->string('intensidad_sintoma', 20)->nullable()->comment('Escala del síntoma: leve, moderado, severo o valor numérico');
            $table->text('criterio_cumplido')->nullable()->comment('Descripción del criterio cumplido para PRN');
            
            // Observaciones
            $table->text('observaciones')->nullable();
            $table->text('efectos_adversos')->nullable();
            $table->timestamps();
            
            // Índices para consultas frecuentes
            $table->index(['paciente_id', 'fecha_hora_administrada']);
            $table->index(['medicamento_tratamiento_id', 'estado']);
            $table->index(['cuidador_usuario_id']);
            $table->index(['sintoma_reportado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administraciones');
    }
}; 