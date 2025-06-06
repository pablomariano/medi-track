<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('principios_activos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_generico', 100)->unique();
            $table->string('nombre_comercial', 100)->nullable();
            $table->string('clasificacion_atc', 10)->nullable();
            $table->string('grupo_farmacologico', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            
            $table->index(['activo']);
            $table->index(['grupo_farmacologico']);
            $table->index(['clasificacion_atc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('principios_activos');
    }
}; 