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
        Schema::table('medicamentos', function (Blueprint $table) {
            // Campos de presentación
            $table->string('presentacion', 100)->nullable()->after('descripcion');
            $table->integer('unidades_por_presentacion')->nullable()->after('presentacion');
            
            // Información del laboratorio
            $table->string('laboratorio', 100)->nullable()->after('unidades_por_presentacion');
            
            // Clasificación terapéutica
            $table->string('categoria_terapeutica', 100)->nullable()->after('laboratorio');
            
            // Control de receta
            $table->boolean('requiere_receta')->default(false)->after('categoria_terapeutica');
            
            // Información médica adicional
            $table->text('contraindicaciones')->nullable()->after('requiere_receta');
            $table->text('efectos_secundarios')->nullable()->after('contraindicaciones');
            $table->text('interacciones')->nullable()->after('efectos_secundarios');
            
            // Identificación comercial
            $table->string('codigo_barras', 50)->nullable()->after('interacciones');
            $table->string('registro_sanitario', 50)->nullable()->after('codigo_barras');
            
            // Índices para búsquedas frecuentes
            $table->index(['categoria_terapeutica']);
            $table->index(['laboratorio']);
            $table->index(['requiere_receta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            // Eliminar índices primero
            $table->dropIndex(['categoria_terapeutica']);
            $table->dropIndex(['laboratorio']);
            $table->dropIndex(['requiere_receta']);
            
            // Eliminar columnas
            $table->dropColumn([
                'presentacion',
                'unidades_por_presentacion',
                'laboratorio',
                'categoria_terapeutica',
                'requiere_receta',
                'contraindicaciones',
                'efectos_secundarios',
                'interacciones',
                'codigo_barras',
                'registro_sanitario'
            ]);
        });
    }
};
