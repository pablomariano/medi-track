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

        // Seedear catálogos básicos
        $this->call([
            MedicamentosSeeder::class,
        ]);

        // Seedear tratamientos y configuraciones
        $this->call([
            TratamientosSeeder::class,
            MedicamentosTratamientosSeeder::class,
        ]);

        // Seedear horarios programados y historial de administraciones con métricas temporales
        $this->call([
            HorariosProgramadosSeeder::class,
            TemporalAdherenceSeeder::class,
            EstadisticasYAlertasSeeder::class,
            AnaLopezRealisticAdherenceSeeder::class,
        ]);

        echo "\n🎉 Base de datos inicializada con datos coherentes\n";
        echo "👤 Usuarios de prueba:\n";
        echo "   - admin@meditrack.com (Administrador)\n";
        echo "   - medico@meditrack.com (Personal Médico)\n";
        echo "   - cuidador@meditrack.com (Cuidador)\n";
        echo "   - apoderado@meditrack.com (Apoderado)\n";
        echo "   - paciente@meditrack.com (Paciente)\n";
        echo "🔑 Contraseña para todos: password\n\n";
        
        echo "💊 Datos de prueba incluidos:\n";
        echo "   - 15 medicamentos de muestra\n";
        echo "   - Tratamientos programados con horarios fijos\n";
        echo "   - Sistema completo de tolerancias y validaciones\n";
        echo "   - Configuraciones de frecuencia personalizables\n\n";
        
        echo "📊 Historial con métricas temporales (últimas 3 semanas):\n";
        echo "   - Administraciones con variaciones temporales realistas\n";
        echo "   - Perfiles de comportamiento temporal por paciente\n";
        echo "   - Scores de puntualidad calculados (0-100)\n";
        echo "   - Categorías temporales: muy_temprano, temprano, puntual, tardío, muy_tardío\n";
        echo "   - Métricas de adelanto/retraso en minutos\n";
        echo "   - Datos optimizados para gráficos de líneas y barras\n";
        echo "   - Sistema completo de adherencia temporal implementado\n\n";
    }
}
