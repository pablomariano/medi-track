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
        // 1. TRATAMIENTOS - Agregar foreign keys faltantes
        Schema::table('tratamientos', function (Blueprint $table) {
            // Relación con pacientes
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade');
                  
            // Relación con médico (si existe la columna)
            if (Schema::hasColumn('tratamientos', 'medico_usuario_id')) {
                $table->foreign('medico_usuario_id')
                      ->references('usuario_id')
                      ->on('personal_medico')
                      ->onDelete('restrict');
            }
        });

        // 2. HORARIOS_PROGRAMADOS - Agregar foreign keys faltantes
        Schema::table('horarios_programados', function (Blueprint $table) {
            // Relación con medicamentos_tratamientos
            $table->foreign('medicamento_tratamiento_id')
                  ->references('id')
                  ->on('medicamentos_tratamientos')
                  ->onDelete('cascade');
                  
            // Relación con pacientes
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade');
        });

        // 3. ADMINISTRACIONES - Agregar foreign keys faltantes
        Schema::table('administraciones', function (Blueprint $table) {
            // Relación con medicamentos_tratamientos
            $table->foreign('medicamento_tratamiento_id')
                  ->references('id')
                  ->on('medicamentos_tratamientos')
                  ->onDelete('cascade');
                  
            // Relación con horarios_programados (nullable para PRN)
            $table->foreign('horario_programado_id')
                  ->references('id')
                  ->on('horarios_programados')
                  ->onDelete('set null');
                  
            // Relación con pacientes
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade');
                  
            // Relación con cuidadores (nullable)
            $table->foreign('cuidador_usuario_id')
                  ->references('usuario_id')
                  ->on('cuidadores')
                  ->onDelete('set null');
        });

        // 4. ALERTAS - Agregar foreign keys faltantes
        Schema::table('alertas', function (Blueprint $table) {
            // Relación con pacientes
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade');
                  
            // Relación con tratamientos (nullable)
            $table->foreign('tratamiento_id')
                  ->references('id')
                  ->on('tratamientos')
                  ->onDelete('set null');
                  
            // Relación con administraciones (nullable)
            $table->foreign('administracion_id')
                  ->references('id')
                  ->on('administraciones')
                  ->onDelete('set null');
                  
            // Relación con usuario que revisó (nullable)
            if (Schema::hasColumn('alertas', 'revisada_por')) {
                $table->foreign('revisada_por')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }
        });

        // 5. ESTADISTICAS_CONSUMO - Agregar foreign keys faltantes
        Schema::table('estadisticas_consumo', function (Blueprint $table) {
            // Relación con pacientes
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade');
                  
            // Relación con medicamentos
            $table->foreign('medicamento_id')
                  ->references('id')
                  ->on('medicamentos')
                  ->onDelete('cascade');
        });

        // 6. RESUMEN_ADHERENCIA_PACIENTE - Agregar foreign keys faltantes
        Schema::table('resumen_adherencia_paciente', function (Blueprint $table) {
            // Relación con pacientes
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar foreign keys en orden inverso para evitar conflictos de dependencias
        
        // 6. RESUMEN_ADHERENCIA_PACIENTE
        Schema::table('resumen_adherencia_paciente', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
        });

        // 5. ESTADISTICAS_CONSUMO
        Schema::table('estadisticas_consumo', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            $table->dropForeign(['medicamento_id']);
        });

        // 4. ALERTAS
        Schema::table('alertas', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            $table->dropForeign(['tratamiento_id']);
            $table->dropForeign(['administracion_id']);
            if (Schema::hasColumn('alertas', 'revisada_por')) {
                $table->dropForeign(['revisada_por']);
            }
        });

        // 3. ADMINISTRACIONES
        Schema::table('administraciones', function (Blueprint $table) {
            $table->dropForeign(['medicamento_tratamiento_id']);
            $table->dropForeign(['horario_programado_id']);
            $table->dropForeign(['paciente_id']);
            $table->dropForeign(['cuidador_usuario_id']);
        });

        // 2. HORARIOS_PROGRAMADOS
        Schema::table('horarios_programados', function (Blueprint $table) {
            $table->dropForeign(['medicamento_tratamiento_id']);
            $table->dropForeign(['paciente_id']);
        });

        // 1. TRATAMIENTOS
        Schema::table('tratamientos', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            if (Schema::hasColumn('tratamientos', 'medico_usuario_id')) {
                $table->dropForeign(['medico_usuario_id']);
            }
        });
    }
}; 