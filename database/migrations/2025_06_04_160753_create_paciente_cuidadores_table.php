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
        Schema::create('paciente_cuidadores', function (Blueprint $table) {
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('cuidador_usuario_id')->constrained('cuidadores', 'usuario_id');
            $table->date('fecha_asignacion');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->comment('Asignación de cuidadores a pacientes');

            $table->primary(['paciente_id', 'cuidador_usuario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paciente_cuidadores');
    }
};
