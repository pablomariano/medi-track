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
        Schema::create('personal_medico', function (Blueprint $table) {
            $table->foreignId('usuario_id')->primary()->constrained('users');
            $table->string('especialidad', 100)->nullable();
            $table->string('numero_colegiatura', 50)->unique()->nullable();
            $table->string('institucion', 100)->nullable();
            $table->integer('anos_experiencia')->nullable();
            $table->comment('Información específica del personal médico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_medico');
    }
};
