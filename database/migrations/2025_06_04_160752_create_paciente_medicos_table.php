<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paciente_medicos', function (Blueprint $table) {
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('medico_usuario_id')->constrained('personal_medico', 'usuario_id');
            $table->boolean('es_medico_principal')->default(false);
            $table->date('fecha_asignacion');
            $table->date('fecha_fin')->nullable();
            $table->string('especialidad_tratamiento', 100)->nullable();
            $table->comment('Asignación de médicos a pacientes');

            $table->primary(['paciente_id', 'medico_usuario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paciente_medicos');
    }
};
