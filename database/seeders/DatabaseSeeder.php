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

        // Seedear catálogos básicos para PRN
        $this->call([
            SintomasPrnSeeder::class,
            CriteriosPrnSeeder::class,
            MedicamentosSeeder::class,
        ]);

        // Seedear tratamientos y configuraciones
        $this->call([
            TratamientosSeeder::class,
            MedicamentosTratamientosSeeder::class,
            IndicacionesPrnSeeder::class,
        ]);

        // Seedear horarios programados y historial de administraciones
        $this->call([
            HorariosProgramadosSeeder::class,
            AdministracionesHistorialSeeder::class,
            EstadisticasYAlertasSeeder::class,
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
        echo "   - 16 síntomas PRN categorizados\n";
        echo "   - 18 criterios específicos de administración\n";
        echo "   - 15 medicamentos de muestra\n";
        echo "   - 11 tratamientos (4 programados + 7 PRN)\n";
        echo "   - 11 configuraciones medicamento-tratamiento\n";
        echo "   - 11 indicaciones PRN específicas\n";
        echo "   - Sistema completo de tolerancias y validaciones\n\n";
        
        echo "📊 Historial de administraciones (último mes):\n";
        echo "   - Horarios programados para medicamentos\n";
        echo "   - Administraciones programadas con adherencia realista (75-95%)\n";
        echo "   - Administraciones PRN con síntomas y criterios\n";
        echo "   - Estadísticas de consumo y adherencia\n";
        echo "   - Alertas automáticas por patrones de riesgo\n";
        echo "   - Datos listos para gráficos de dashboard\n\n";
    }
}
