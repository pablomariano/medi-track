<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 20)->unique();
            $table->string('tipo', 20);
            $table->decimal('equivalencia_base', 15, 6)->nullable();
            $table->foreignId('unidad_base_id')->nullable()->constrained('unidades_medida')->onDelete('restrict');
            
            $table->index(['tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
    }
}; 