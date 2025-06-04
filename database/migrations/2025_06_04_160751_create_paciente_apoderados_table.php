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
        Schema::create('paciente_apoderados', function (Blueprint $table) {
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('apoderado_usuario_id')->constrained('apoderados', 'usuario_id');
            $table->boolean('es_principal')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->comment('Un paciente puede tener múltiples apoderados');

            $table->primary(['paciente_id', 'apoderado_usuario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paciente_apoderados');
    }
};
