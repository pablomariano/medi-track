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
        Schema::create('cuidadores', function (Blueprint $table) {
            $table->foreignId('usuario_id')->primary()->constrained('users');
            $table->text('certificaciones')->nullable();
            $table->integer('experiencia_anos')->nullable();
            $table->string('disponibilidad_horaria', 100)->nullable();
            $table->decimal('tarifa_hora', 8, 2)->nullable();
            $table->comment('Información específica de cuidadores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuidadores');
    }
};
