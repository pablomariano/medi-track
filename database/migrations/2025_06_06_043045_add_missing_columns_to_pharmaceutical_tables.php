<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $timestamp = Carbon::now();

        // Agregar columnas faltantes a formas_farmaceuticas
        Schema::table('formas_farmaceuticas', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('descripcion');
            $table->timestamp('creado_en')->nullable()->after('activo');
        });
        
        // Actualizar timestamp manualmente para SQLite
        DB::table('formas_farmaceuticas')->update(['creado_en' => $timestamp]);

        // Agregar columnas faltantes a vias_administracion
        Schema::table('vias_administracion', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('descripcion');
            $table->timestamp('creado_en')->nullable()->after('activo');
        });
        
        DB::table('vias_administracion')->update(['creado_en' => $timestamp]);

        // Agregar columnas faltantes a unidades_medida
        Schema::table('unidades_medida', function (Blueprint $table) {
            $table->string('simbolo', 20)->nullable()->after('nombre');
            $table->string('tipo_unidad', 30)->nullable()->after('simbolo');
            $table->decimal('factor_conversion_base', 15, 6)->nullable()->after('tipo_unidad');
            $table->string('unidad_base', 20)->nullable()->after('factor_conversion_base');
            $table->boolean('activo')->default(true)->after('unidad_base');
            $table->timestamp('creado_en')->nullable()->after('activo');
        });
        
        // Migrar datos existentes de unidades_medida
        $unidades = DB::table('unidades_medida')->get();
        foreach ($unidades as $unidad) {
            DB::table('unidades_medida')
                ->where('id', $unidad->id)
                ->update([
                    'simbolo' => $unidad->nombre,
                    'tipo_unidad' => $unidad->tipo ?? 'peso',
                    'factor_conversion_base' => $unidad->equivalencia_base ?? 1.0,
                    'unidad_base' => $unidad->nombre,
                    'creado_en' => $timestamp
                ]);
        }
        
        // Ahora eliminar las columnas anteriores
        Schema::table('unidades_medida', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'equivalencia_base', 'unidad_base_id']);
        });

        // Agregar columnas faltantes a esquemas_posologicos
        Schema::table('esquemas_posologicos', function (Blueprint $table) {
            $table->string('nombre', 100)->nullable()->after('id');
            $table->text('descripcion')->nullable()->after('nombre');
            $table->integer('dosis_diarias')->nullable()->after('descripcion');
            $table->boolean('activo')->default(true)->after('dosis_diarias');
            $table->timestamp('creado_en')->nullable()->after('activo');
        });
        
        DB::table('esquemas_posologicos')->update(['creado_en' => $timestamp]);
        
        // Eliminar columnas de la estructura anterior
        Schema::table('esquemas_posologicos', function (Blueprint $table) {
            $table->dropForeign(['medicamento_tratamiento_id']);
            $table->dropColumn([
                'medicamento_tratamiento_id',
                'secuencia',
                'dia_inicio',
                'dia_fin',
                'dosis_cantidad',
                'observaciones'
            ]);
        });

        // Agregar columnas faltantes a interacciones_medicamentos  
        Schema::table('interacciones_medicamentos', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('tipo_interaccion');
            $table->string('gravedad', 20)->nullable()->after('descripcion');
            $table->timestamp('creado_en')->nullable()->after('activo');
        });
        
        DB::table('interacciones_medicamentos')->update(['creado_en' => $timestamp]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en orden inverso
        Schema::table('interacciones_medicamentos', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'gravedad', 'creado_en']);
        });

        Schema::table('esquemas_posologicos', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'descripcion', 'dosis_diarias', 'activo', 'creado_en']);
            
            // Restaurar estructura anterior
            $table->foreignId('medicamento_tratamiento_id')->after('id');
            $table->integer('secuencia')->after('medicamento_tratamiento_id');
            $table->integer('dia_inicio')->after('secuencia');
            $table->integer('dia_fin')->nullable()->after('dia_inicio');
            $table->decimal('dosis_cantidad', 10, 3)->after('dia_fin');
            $table->text('observaciones')->nullable()->after('frecuencia_horas');
        });

        Schema::table('unidades_medida', function (Blueprint $table) {
            $table->dropColumn(['simbolo', 'tipo_unidad', 'factor_conversion_base', 'unidad_base', 'activo', 'creado_en']);
            
            // Restaurar estructura anterior
            $table->string('tipo', 20)->after('nombre');
            $table->decimal('equivalencia_base', 15, 6)->after('tipo');
            $table->foreignId('unidad_base_id')->nullable()->after('equivalencia_base');
        });

        Schema::table('vias_administracion', function (Blueprint $table) {
            $table->dropColumn(['activo', 'creado_en']);
        });

        Schema::table('formas_farmaceuticas', function (Blueprint $table) {
            $table->dropColumn(['activo', 'creado_en']);
        });
    }
};
