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
        Schema::table('users', function (Blueprint $table) {
            // Agregamos los campos adicionales que necesitamos
            $table->string('telefono', 20)->nullable()->after('email');
            $table->foreignId('rol_id')->nullable()->after('telefono')->constrained('roles');
            $table->boolean('activo')->default(true)->after('rol_id');
            $table->boolean('email_verificado')->default(false)->after('activo');
            $table->timestamp('ultimo_acceso')->nullable()->after('email_verificado');
            $table->comment('Tabla central de usuarios del sistema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropColumn([
                'telefono',
                'rol_id',
                'activo',
                'email_verificado',
                'ultimo_acceso'
            ]);
        });
    }
};
