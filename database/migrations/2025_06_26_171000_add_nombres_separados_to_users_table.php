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
        Schema::table('users', function (Blueprint $table) {
            // Agregar los nuevos campos
            $table->string('nombre', 100)->nullable()->after('name');
            $table->string('apellido_paterno', 100)->nullable()->after('nombre');
            $table->string('apellido_materno', 100)->nullable()->after('apellido_paterno');
        });

        // Migrar datos existentes del campo 'name' a los nuevos campos
        // Asumiendo que los nombres están en formato "Nombre ApellidoPaterno ApellidoMaterno"
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            if ($user->name) {
                $nombreCompleto = trim($user->name);
                $partes = explode(' ', $nombreCompleto);
                
                $nombre = $partes[0] ?? '';
                $apellidoPaterno = $partes[1] ?? '';
                $apellidoMaterno = $partes[2] ?? '';
                
                // Si hay más de 3 partes, asignar el resto al apellido materno
                if (count($partes) > 3) {
                    $apellidoMaterno = implode(' ', array_slice($partes, 2));
                }
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'nombre' => $nombre,
                        'apellido_paterno' => $apellidoPaterno,
                        'apellido_materno' => $apellidoMaterno,
                    ]);
            }
        }

        // Hacer los campos requeridos después de la migración de datos
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre', 100)->nullable(false)->change();
            $table->string('apellido_paterno', 100)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar el campo 'name' con los datos de los nuevos campos antes de eliminarlos
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            $nombreCompleto = trim(
                ($user->nombre ?? '') . ' ' . 
                ($user->apellido_paterno ?? '') . ' ' . 
                ($user->apellido_materno ?? '')
            );
            
            DB::table('users')
                ->where('id', $user->id)
                ->update(['name' => $nombreCompleto]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'apellido_paterno', 'apellido_materno']);
        });
    }
};
