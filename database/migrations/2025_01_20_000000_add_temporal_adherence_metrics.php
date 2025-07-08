<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administraciones', function (Blueprint $table) {
            // Verificar si las columnas ya existen antes de agregarlas
            if (!Schema::hasColumn('administraciones', 'minutos_adelanto')) {
                $table->integer('minutos_adelanto')->nullable()->after('minutos_diferencia')->comment('Minutos de adelanto (positivo si antes de tiempo)');
            }
            if (!Schema::hasColumn('administraciones', 'minutos_retraso')) {
                $table->integer('minutos_retraso')->nullable()->after('minutos_adelanto')->comment('Minutos de retraso (positivo si después de tiempo)');
            }
            if (!Schema::hasColumn('administraciones', 'score_puntualidad')) {
                $table->decimal('score_puntualidad', 5, 2)->nullable()->after('minutos_retraso')->comment('Score de puntualidad 0-100');
            }
            if (!Schema::hasColumn('administraciones', 'categoria_temporal')) {
                $table->enum('categoria_temporal', ['muy_temprano', 'temprano', 'puntual', 'tardio', 'muy_tardio'])->nullable()->after('score_puntualidad');
            }
        });

        // Agregar índices solo si no existen
        Schema::table('administraciones', function (Blueprint $table) {
            // Solo agregar índices si las columnas existen
            if (Schema::hasColumn('administraciones', 'score_puntualidad')) {
                try {
                    $table->index(['paciente_id', 'fecha_hora_programada', 'score_puntualidad'], 'idx_admin_temporal_metrics');
                } catch (\Exception $e) {
                    // Índice ya existe, continuar
                }
            }
            if (Schema::hasColumn('administraciones', 'categoria_temporal')) {
                try {
                    $table->index(['categoria_temporal', 'paciente_id'], 'idx_admin_categoria_temporal');
                } catch (\Exception $e) {
                    // Índice ya existe, continuar
                }
            }
        });
        
        // Agregar tabla para métricas temporales precalculadas solo si no existe
        if (!Schema::hasTable('metricas_adherencia_temporal')) {
            Schema::create('metricas_adherencia_temporal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('medicamento_id')->nullable();
            $table->unsignedBigInteger('tratamiento_id')->nullable();
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->enum('tipo_periodo', ['diario', 'semanal', 'mensual']);
            
            // Métricas temporales
            $table->decimal('puntualidad_promedio', 5, 2)->default(0)->comment('Score promedio de puntualidad');
            $table->integer('dosis_puntuales')->default(0)->comment('Dosis dentro de ventana ideal');
            $table->integer('dosis_tempranas')->default(0)->comment('Dosis tomadas antes de tiempo');
            $table->integer('dosis_tardias')->default(0)->comment('Dosis tomadas tarde');
            $table->decimal('tiempo_promedio_adelanto', 8, 2)->default(0)->comment('Promedio minutos de adelanto');
            $table->decimal('tiempo_promedio_retraso', 8, 2)->default(0)->comment('Promedio minutos de retraso');
            $table->decimal('variabilidad_horaria', 8, 2)->default(0)->comment('Desviación estándar de tiempos');
            
            // Distribución temporal
            $table->json('distribucion_por_horas')->nullable()->comment('Distribución de tomas por hora del día');
            $table->json('patrones_semanales')->nullable()->comment('Patrones de adherencia por día de semana');
            
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            // Índices únicos y de consulta
            $table->unique(['paciente_id', 'medicamento_id', 'tratamiento_id', 'periodo_inicio', 'tipo_periodo'], 'unique_metrica_temporal');
            $table->index(['paciente_id', 'periodo_inicio']);
            $table->index(['puntualidad_promedio']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('administraciones', function (Blueprint $table) {
            $table->dropColumn([
                'minutos_adelanto', 
                'minutos_retraso', 
                'score_puntualidad', 
                'categoria_temporal'
            ]);
        });
        
        Schema::dropIfExists('metricas_adherencia_temporal');
    }
}; 