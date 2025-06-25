<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminar tablas PRN si existen
        Schema::dropIfExists('indicaciones_prn');
        Schema::dropIfExists('criterios_prn');
        Schema::dropIfExists('sintomas_prn');
        
        // Eliminar campos PRN de medicamentos_tratamientos si existen
        if (Schema::hasTable('medicamentos_tratamientos')) {
            Schema::table('medicamentos_tratamientos', function (Blueprint $table) {
                if (Schema::hasColumn('medicamentos_tratamientos', 'es_prn')) {
                    $table->dropColumn('es_prn');
                }
                if (Schema::hasColumn('medicamentos_tratamientos', 'intervalo_minimo_horas')) {
                    $table->dropColumn('intervalo_minimo_horas');
                }
                if (Schema::hasColumn('medicamentos_tratamientos', 'max_dosis_dia')) {
                    $table->dropColumn('max_dosis_dia');
                }
                if (Schema::hasColumn('medicamentos_tratamientos', 'max_dosis_semana')) {
                    $table->dropColumn('max_dosis_semana');
                }
                if (Schema::hasColumn('medicamentos_tratamientos', 'dosis_maxima_consecutiva')) {
                    $table->dropColumn('dosis_maxima_consecutiva');
                }
            });
        }
        
        // Actualizar tratamientos existentes de tipo PRN a Programado
        if (Schema::hasTable('tratamientos')) {
            DB::table('tratamientos')
                ->where('tipo', 'PRN')
                ->update(['tipo' => 'Programado']);
        
            // Modificar el enum de tipo para eliminar PRN
            DB::statement("ALTER TABLE tratamientos MODIFY COLUMN tipo ENUM('Programado') DEFAULT 'Programado'");
        }
        
        // Hacer frecuencia_horas no nullable en medicamentos_tratamientos
        if (Schema::hasTable('medicamentos_tratamientos')) {
            // Actualizar registros null a 24 horas por defecto
            DB::table('medicamentos_tratamientos')
                ->whereNull('frecuencia_horas')
                ->update(['frecuencia_horas' => 24]);
                
            Schema::table('medicamentos_tratamientos', function (Blueprint $table) {
                $table->integer('frecuencia_horas')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear el enum con PRN
        if (Schema::hasTable('tratamientos')) {
            DB::statement("ALTER TABLE tratamientos MODIFY COLUMN tipo ENUM('Programado', 'PRN') DEFAULT 'Programado'");
        }
        
        // Recrear campos PRN en medicamentos_tratamientos
        if (Schema::hasTable('medicamentos_tratamientos')) {
            Schema::table('medicamentos_tratamientos', function (Blueprint $table) {
                $table->boolean('es_prn')->default(false)->comment('True si es medicamento PRN (por necesidad)');
                $table->integer('intervalo_minimo_horas')->nullable()->comment('PRN: tiempo mínimo entre dosis');
                $table->integer('max_dosis_dia')->nullable()->comment('PRN: máximo por día');
                $table->integer('max_dosis_semana')->nullable()->comment('PRN: máximo por semana');
                $table->integer('dosis_maxima_consecutiva')->nullable()->comment('PRN: máximo seguidas sin pausa');
                $table->integer('frecuencia_horas')->nullable()->change();
            });
        }
        
        // Recrear tablas PRN
        Schema::create('sintomas_prn', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('categoria', 50);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('criterios_prn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sintoma_id')->constrained('sintomas_prn')->onDelete('cascade');
            $table->string('descripcion', 255);
            $table->string('valor_minimo', 50)->nullable();
            $table->string('valor_maximo', 50)->nullable();
            $table->string('unidad', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('indicaciones_prn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_tratamiento_id')->constrained('medicamentos_tratamientos')->onDelete('cascade');
            $table->foreignId('sintoma_id')->constrained('sintomas_prn')->onDelete('cascade');
            $table->foreignId('criterio_id')->nullable()->constrained('criterios_prn')->onDelete('set null');
            $table->text('descripcion_personalizada')->nullable();
            $table->boolean('es_criterio_principal')->default(false);
            $table->timestamps();
            
            $table->unique(['medicamento_tratamiento_id', 'sintoma_id']);
            $table->index(['sintoma_id']);
        });
    }
};
