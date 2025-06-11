<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            // Agregar campos que necesitamos si no existen
            if (!Schema::hasColumn('medicamentos', 'nombre')) {
                $table->string('nombre', 255)->after('id');
            }
            if (!Schema::hasColumn('medicamentos', 'principio_activo')) {
                $table->string('principio_activo', 255)->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('medicamentos', 'forma_farmaceutica')) {
                $table->string('forma_farmaceutica', 100)->nullable()->after('principio_activo');
            }
            if (!Schema::hasColumn('medicamentos', 'via_administracion')) {
                $table->string('via_administracion', 100)->nullable()->after('forma_farmaceutica');
            }
            if (!Schema::hasColumn('medicamentos', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }
            if (!Schema::hasColumn('medicamentos', 'activo')) {
                $table->boolean('activo')->default(true)->after('descripcion');
            }
            if (!Schema::hasColumn('medicamentos', 'created_at')) {
                $table->timestamps();
            }
            
            // Índices para búsqueda si no existen
            if (!Schema::hasIndex('medicamentos', ['activo'])) {
                $table->index(['activo']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            // Solo eliminar las columnas que agregamos nosotros
            if (Schema::hasColumn('medicamentos', 'nombre')) {
                $table->dropColumn('nombre');
            }
            if (Schema::hasColumn('medicamentos', 'principio_activo')) {
                $table->dropColumn('principio_activo');
            }
            if (Schema::hasColumn('medicamentos', 'forma_farmaceutica')) {
                $table->dropColumn('forma_farmaceutica');
            }
            if (Schema::hasColumn('medicamentos', 'via_administracion')) {
                $table->dropColumn('via_administracion');
            }
            if (Schema::hasColumn('medicamentos', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            if (Schema::hasColumn('medicamentos', 'activo')) {
                $table->dropColumn('activo');
            }
        });
    }
}; 