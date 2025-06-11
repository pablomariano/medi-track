<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La tabla alertas ya existe, solo verificamos y agregamos campos si es necesario
        if (Schema::hasTable('alertas')) {
            Schema::table('alertas', function (Blueprint $table) {
                // Verificar y agregar campos necesarios si no existen
                if (!Schema::hasColumn('alertas', 'tipo')) {
                    $table->enum('tipo', [
                        'Dosis_Omitida', 
                        'Fuera_Ventana', 
                        'Exceso_PRN', 
                        'Intervalo_Corto_PRN', 
                        'Efecto_Adverso'
                    ])->after('administracion_id');
                }
                
                if (!Schema::hasColumn('alertas', 'nivel')) {
                    $table->enum('nivel', ['Info', 'Advertencia', 'Critica'])->default('Info')->after('tipo');
                }
                
                if (!Schema::hasColumn('alertas', 'revisada')) {
                    $table->boolean('revisada')->default(false)->after('fecha_generada');
                }
                
                if (!Schema::hasColumn('alertas', 'revisada_por')) {
                    $table->unsignedBigInteger('revisada_por')->nullable()->after('revisada');
                }
                
                if (!Schema::hasColumn('alertas', 'fecha_revision')) {
                    $table->timestamp('fecha_revision')->nullable()->after('revisada_por');
                }
                
                // Agregar índices si no existen
                $indexExists = collect(Schema::getIndexes('alertas'))->contains(function ($index) {
                    return $index['name'] === 'alertas_paciente_id_revisada_index';
                });
                
                if (!$indexExists) {
                    $table->index(['paciente_id', 'revisada']);
                }
            });
        } else {
            // Si no existe, crear la tabla completa
            Schema::create('alertas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('paciente_id');
                $table->unsignedBigInteger('tratamiento_id')->nullable();
                $table->unsignedBigInteger('administracion_id')->nullable();
                $table->enum('tipo', [
                    'Dosis_Omitida', 
                    'Fuera_Ventana', 
                    'Exceso_PRN', 
                    'Intervalo_Corto_PRN', 
                    'Efecto_Adverso'
                ]);
                $table->enum('nivel', ['Info', 'Advertencia', 'Critica'])->default('Info');
                $table->text('mensaje');
                $table->timestamp('fecha_generada');
                $table->boolean('revisada')->default(false);
                $table->unsignedBigInteger('revisada_por')->nullable();
                $table->timestamp('fecha_revision')->nullable();
                $table->timestamps();
                
                // Índices para consultas frecuentes
                $table->index(['paciente_id', 'revisada']);
                $table->index(['tipo', 'nivel']);
                $table->index(['fecha_generada']);
            });
        }
    }

    public function down(): void
    {
        // En el down solo eliminamos campos que agregamos nosotros
        if (Schema::hasTable('alertas')) {
            Schema::table('alertas', function (Blueprint $table) {
                if (Schema::hasColumn('alertas', 'revisada_por')) {
                    $table->dropColumn('revisada_por');
                }
                if (Schema::hasColumn('alertas', 'fecha_revision')) {
                    $table->dropColumn('fecha_revision');
                }
            });
        }
    }
}; 