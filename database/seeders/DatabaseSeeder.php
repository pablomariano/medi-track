<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "\n🌱 === INICIANDO SEED COMPLETO DEL SISTEMA MEDI-TRACK === 🌱\n\n";
        
        // 1. Primero roles y permisos (base del sistema)
        echo "📋 PASO 1: Configurando roles y permisos...\n";
        $this->call(RolesAndPermissionsSeeder::class);
        
        // 2. Usuarios de prueba con roles asignados
        echo "\n👥 PASO 2: Creando usuarios de prueba...\n";
        $this->call(TestUsersSeeder::class);
        
        // 3. Catálogos farmacéuticos (formas, vías, unidades)
        echo "\n🏥 PASO 3: Poblando catálogos farmacéuticos...\n";
        $this->call(CatalogosFarmaceuticosSeeder::class);
        
        // 4. Medicamentos completos con principios activos
        echo "\n💊 PASO 4: Creando catálogo completo de medicamentos...\n";
        $this->call(MedicamentosCompletos::class);
        
        // 5. Tratamientos y sistema de administración
        echo "\n💉 PASO 5: Configurando tratamientos y administraciones...\n";
        $this->call(TratamientosYAdministracionesSeeder::class);
        
        echo "\n✅ === SEED COMPLETO FINALIZADO EXITOSAMENTE === ✅\n";
        echo "\n🎯 SISTEMA COMPLETAMENTE FUNCIONAL CON:\n";
        echo "   • Usuarios completos (admin, médico, cuidador, apoderado, pacientes)\n";
        echo "   • Catálogos farmacéuticos profesionales\n";
        echo "   • Base de datos de medicamentos reales\n";
        echo "   • Tratamientos activos y administraciones\n";
        echo "   • Sistema de alertas y autorizaciones\n";
        echo "   • Interacciones medicamentosas\n";
        echo "   • Historial clínico completo\n\n";
        echo "🔐 CREDENCIALES DE ACCESO:\n";
        echo "   • Admin: admin@meditrack.com / password\n";
        echo "   • Médico: medico@meditrack.com / password\n";
        echo "   • Cuidador: cuidador@meditrack.com / password\n";
        echo "   • Apoderado: apoderado@meditrack.com / password\n";
        echo "   • Paciente: paciente@meditrack.com / password\n\n";
        echo "🌐 ACCESO AL SISTEMA:\n";
        echo "   • Dashboard: http://localhost:8000/dashboard\n";
        echo "   • Test: http://localhost:8000/test/medicamentos\n";
        echo "   • Login: http://localhost:8000/login\n\n";
    }
}
