<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sintomas_prn', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('categoria', 50);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['categoria', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sintomas_prn');
    }
}; 