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
        Schema::create('apoderados', function (Blueprint $table) {
            $table->foreignId('usuario_id')->primary()->constrained('usuarios');
            $table->string('relacion_paciente', 50)->nullable();
            $table->boolean('es_contacto_emergencia')->default(true);
            $table->comment('Información específica de apoderados/tutores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apoderados');
    }
};
