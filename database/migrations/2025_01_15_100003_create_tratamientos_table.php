<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tratamientos', function (Blueprint $table) {
            // Agregar el campo tipo si no existe
            if (!Schema::hasColumn('tratamientos', 'tipo')) {
                $table->enum('tipo', ['Programado', 'PRN'])->default('Programado')->after('diagnostico');
            }
            
            // Modificar el campo estado si es necesario
            if (Schema::hasColumn('tratamientos', 'estado')) {
                $table->enum('estado', ['Activo', 'Pausado', 'Completado', 'Suspendido'])->default('Activo')->change();
            } else {
                $table->enum('estado', ['Activo', 'Pausado', 'Completado', 'Suspendido'])->default('Activo');
            }
            
            // Agregar created_at y updated_at si no existen
            if (!Schema::hasColumn('tratamientos', 'created_at')) {
                $table->timestamps();
            }
            
            // Agregar índices si no existen
            if (!Schema::hasIndex('tratamientos', ['paciente_id', 'estado'])) {
                $table->index(['paciente_id', 'estado']);
            }
            if (!Schema::hasIndex('tratamientos', ['tipo', 'estado'])) {
                $table->index(['tipo', 'estado']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tratamientos', function (Blueprint $table) {
            if (Schema::hasColumn('tratamientos', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });
    }
}; 