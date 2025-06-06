<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla de respaldo para los medicamentos actuales
        Schema::create('medicamentos_backup', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('medida');
            $table->string('unidad_medida');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
        
        // Copiar datos existentes al backup
        DB::statement('INSERT INTO medicamentos_backup SELECT * FROM medicamentos');
        
        // Eliminar tabla actual
        Schema::dropIfExists('medicamentos');
        
        // Crear nueva tabla de medicamentos con estructura compleja
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('principio_activo_id')->constrained('principios_activos')->onDelete('restrict');
            $table->string('nombre_comercial', 100);
            $table->foreignId('forma_farmaceutica_id')->constrained('formas_farmaceuticas')->onDelete('restrict');
            $table->decimal('concentracion', 10, 3);
            $table->foreignId('unidad_concentracion_id')->constrained('unidades_medida')->onDelete('restrict');
            $table->foreignId('via_administracion_id')->constrained('vias_administracion')->onDelete('restrict');
            $table->string('laboratorio', 100)->nullable();
            $table->string('registro_sanitario', 50)->nullable();
            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->boolean('requiere_receta')->default(true);
            $table->boolean('controlado')->default(false);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            
            // Índices para optimización
            $table->index(['principio_activo_id']);
            $table->index(['forma_farmaceutica_id']);
            $table->index(['via_administracion_id']);
            $table->index(['registro_sanitario']);
            $table->index(['activo']);
            $table->index(['requiere_receta']);
            $table->index(['controlado']);
        });
    }

    public function down(): void
    {
        // Eliminar nueva tabla
        Schema::dropIfExists('medicamentos');
        
        // Recrear tabla original
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('medida');
            $table->string('unidad_medida');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
        
        // Restaurar datos desde backup
        DB::statement('INSERT INTO medicamentos SELECT * FROM medicamentos_backup');
        
        // Eliminar backup
        Schema::dropIfExists('medicamentos_backup');
    }
}; 