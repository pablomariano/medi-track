<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        echo "\n✅ Usuario de prueba creado exitosamente:\n";
        echo "\n🔐 Credenciales de acceso:\n";
        echo "   • Email: test@example.com\n";
        echo "   • Password: password\n\n";
        echo "🌐 Para probar el dashboard de reportes:\n";
        echo "   • Accede a: http://localhost/login\n";
        echo "   • Luego ve a: http://localhost/reportes/dashboard\n";
        echo "   • El dashboard mostrará un mensaje de 'No hay datos' hasta que se creen tratamientos y administraciones\n\n";
    }
}
