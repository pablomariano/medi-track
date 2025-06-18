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
        Schema::create('permisos_temporales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('permiso', 100);
            $table->text('motivo')->nullable();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            
            // Usuario que otorgó el permiso
            $table->foreignId('otorgado_por')->constrained('users')->onDelete('cascade');
            $table->dateTime('fecha_otorgamiento')->default(now());
            
            // Campos de auditoría
            $table->string('ip_otorgamiento', 45)->nullable();
            $table->text('notas_adicionales')->nullable();
            $table->dateTime('ultimo_uso')->nullable();
            $table->integer('veces_usado')->default(0);
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['usuario_id', 'permiso', 'activo']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('otorgado_por');
            
            $table->comment('Permisos temporales otorgados a usuarios por tiempo limitado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos_temporales');
    }
};
