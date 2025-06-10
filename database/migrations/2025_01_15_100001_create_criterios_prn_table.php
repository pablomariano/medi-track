<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterios_prn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sintoma_id')->constrained('sintomas_prn')->onDelete('cascade');
            $table->string('descripcion', 255);
            $table->string('valor_minimo', 50)->nullable();
            $table->string('valor_maximo', 50)->nullable();
            $table->string('unidad', 20)->nullable();
            $table->timestamps();
            
            $table->index(['sintoma_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterios_prn');
    }
}; 