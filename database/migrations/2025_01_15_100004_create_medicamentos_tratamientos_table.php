<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicamentos_tratamientos', function (Blueprint $table) {
            // Agregar campo unidad_dosis como string si no existe
            if (!Schema::hasColumn('medicamentos_tratamientos', 'unidad_dosis')) {
                $table->string('unidad_dosis', 50)->after('dosis_cantidad');
            }
            
            // Agregar configuración para ventana de tolerancia
            if (!Schema::hasColumn('medicamentos_tratamientos', 'tolerancia_antes_minutos')) {
                $table->integer('tolerancia_antes_minutos')->default(30)->comment('Ventana antes del horario')->after('frecuencia_horas');
            }
            if (!Schema::hasColumn('medicamentos_tratamientos', 'tolerancia_despues_minutos')) {
                $table->integer('tolerancia_despues_minutos')->default(60)->comment('Ventana después del horario')->after('tolerancia_antes_minutos');
            }
            
            // Agregar configuración PRN
            if (!Schema::hasColumn('medicamentos_tratamientos', 'intervalo_minimo_horas')) {
                $table->integer('intervalo_minimo_horas')->nullable()->comment('PRN: tiempo mínimo entre dosis')->after('tolerancia_despues_minutos');
            }
            if (!Schema::hasColumn('medicamentos_tratamientos', 'dosis_maxima_dia')) {
                $table->decimal('dosis_maxima_dia', 10, 3)->nullable()->comment('PRN: máximo por día')->after('intervalo_minimo_horas');
            }
            if (!Schema::hasColumn('medicamentos_tratamientos', 'dosis_maxima_consecutiva')) {
                $table->integer('dosis_maxima_consecutiva')->nullable()->comment('PRN: máximo seguidas sin pausa')->after('dosis_maxima_dia');
            }
            
            // Agregar orden si no existe
            if (!Schema::hasColumn('medicamentos_tratamientos', 'orden')) {
                $table->integer('orden')->default(1)->after('motivo_suspension');
            }
            
            // Agregar timestamps si no existen
            if (!Schema::hasColumn('medicamentos_tratamientos', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicamentos_tratamientos', function (Blueprint $table) {
            $table->dropColumn([
                'unidad_dosis',
                'tolerancia_antes_minutos',
                'tolerancia_despues_minutos',
                'intervalo_minimo_horas',
                'dosis_maxima_dia',
                'dosis_maxima_consecutiva',
                'orden'
            ]);
        });
    }
}; 