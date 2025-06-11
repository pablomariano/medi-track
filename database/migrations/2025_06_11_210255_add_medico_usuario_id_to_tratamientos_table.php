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
        Schema::table('tratamientos', function (Blueprint $table) {
            $table->unsignedBigInteger('medico_usuario_id')->after('paciente_id');
            $table->string('objetivo')->after('nombre')->nullable();
            
            // Agregar índice para la búsqueda por médico
            $table->index('medico_usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tratamientos', function (Blueprint $table) {
            $table->dropIndex(['medico_usuario_id']);
            $table->dropColumn(['medico_usuario_id', 'objetivo']);
        });
    }
};
