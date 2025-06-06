<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formas_farmaceuticas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('tipo', 30);
            $table->text('descripcion')->nullable();
            
            $table->index(['tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formas_farmaceuticas');
    }
}; 