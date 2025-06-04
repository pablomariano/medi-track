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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->string('nombre', 100);
            $table->date('fecha_nacimiento')->nullable();
            $table->char('genero_id', 1)->nullable();
            $table->string('numero_documento', 20)->unique()->nullable();
            $table->string('tipo_documento', 10)->nullable();
            $table->string('tipo_sangre', 10)->nullable();
            $table->decimal('altura', 5, 2)->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono_emergencia', 20)->nullable();
            $table->text('observaciones_medicas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->comment('Pacientes del sistema - pueden o no tener cuenta de usuario');

            $table->foreign('genero_id')->references('id')->on('generos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
