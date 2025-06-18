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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Usuario que realizó la acción
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Información de la acción
            $table->enum('accion', ['create', 'update', 'delete', 'access', 'login', 'logout', 'permission_grant', 'permission_revoke']);
            $table->string('tabla_afectada', 100)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            
            // Datos del cambio
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            
            // Contexto de la petición
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('metodo_http', 10)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('ruta', 200)->nullable();
            
            // Contexto adicional
            $table->json('contexto_adicional')->nullable();
            $table->string('session_id', 100)->nullable();
            $table->enum('severidad', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Timestamps y metadata
            $table->timestamp('created_at')->nullable();
            $table->string('created_by_name', 100)->nullable(); // Respaldo del nombre del usuario
            
            // Índices para optimizar consultas
            $table->index(['usuario_id', 'created_at']);
            $table->index(['tabla_afectada', 'registro_id']);
            $table->index(['accion', 'created_at']);
            $table->index(['severidad', 'created_at']);
            $table->index('session_id');
            
            $table->comment('Registro de auditoría para todas las acciones críticas del sistema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
