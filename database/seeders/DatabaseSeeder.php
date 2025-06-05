<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seedear roles y permisos primero
        $this->call([
            RolesAndPermissionsSeeder::class,
            TestUsersSeeder::class,
        ]);

        echo "\n🎉 Base de datos inicializada con datos coherentes\n";
        echo "👤 Usuarios de prueba:\n";
        echo "   - admin@meditrack.com (Administrador)\n";
        echo "   - medico@meditrack.com (Personal Médico)\n";
        echo "   - cuidador@meditrack.com (Cuidador)\n";
        echo "   - apoderado@meditrack.com (Apoderado)\n";
        echo "   - paciente@meditrack.com (Paciente)\n";
        echo "🔑 Contraseña para todos: password\n\n";
    }
}
