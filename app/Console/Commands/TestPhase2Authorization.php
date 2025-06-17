<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Medicine;
use Illuminate\Support\Facades\Gate;

class TestPhase2Authorization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:phase2-auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el sistema de autorización de la Fase 2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 INICIANDO PRUEBAS DE AUTORIZACIÓN - FASE 2');
        $this->info('===========================================');

        // Obtener usuarios de prueba
        $admin = User::whereHas('role', function($q) { $q->where('nombre', 'admin'); })->first();
        $medico = User::whereHas('role', function($q) { $q->where('nombre', 'medico'); })->first();
        $cuidador = User::whereHas('role', function($q) { $q->where('nombre', 'cuidador'); })->first();
        $pacienteUser = User::whereHas('role', function($q) { $q->where('nombre', 'paciente'); })->first();

        if (!$admin || !$medico || !$cuidador || !$pacienteUser) {
            $this->error('No se encontraron usuarios de prueba. Ejecute primero el seeder.');
            return;
        }

        $this->info("\n📋 Usuarios de prueba encontrados:");
        $this->info("- Admin: {$admin->name} (ID: {$admin->id})");
        $this->info("- Médico: {$medico->name} (ID: {$medico->id})");
        $this->info("- Cuidador: {$cuidador->name} (ID: {$cuidador->id})");
        $this->info("- Paciente: {$pacienteUser->name} (ID: {$pacienteUser->id})");

        // Probar políticas de Paciente
        $this->testPacientePolicy($admin, $medico, $cuidador, $pacienteUser);

        // Probar políticas de Tratamiento
        $this->testTratamientoPolicy($admin, $medico, $cuidador, $pacienteUser);

        // Probar políticas de Medicine
        $this->testMedicinePolicy($admin, $medico, $cuidador, $pacienteUser);

        // Probar Gates específicos
        $this->testCustomGates($admin, $medico, $cuidador, $pacienteUser);

        $this->info("\n✅ PRUEBAS DE AUTORIZACIÓN COMPLETADAS");
    }

    private function testPacientePolicy($admin, $medico, $cuidador, $pacienteUser)
    {
        $this->info("\n🏥 PROBANDO POLÍTICA DE PACIENTES");
        $this->info("================================");

        $paciente = Paciente::first();
        if (!$paciente) {
            $this->error('No hay pacientes en la base de datos');
            return;
        }

        $this->info("Probando con Paciente ID: {$paciente->id}");

        // Test viewAny
        $this->testAuthorization('Paciente viewAny - Admin', $admin, 'viewAny', Paciente::class);
        $this->testAuthorization('Paciente viewAny - Médico', $medico, 'viewAny', Paciente::class);
        $this->testAuthorization('Paciente viewAny - Cuidador', $cuidador, 'viewAny', Paciente::class);
        $this->testAuthorization('Paciente viewAny - Paciente', $pacienteUser, 'viewAny', Paciente::class);

        // Test view specific
        $this->testAuthorization('Paciente view - Admin', $admin, 'view', $paciente);
        $this->testAuthorization('Paciente view - Médico', $medico, 'view', $paciente);
        $this->testAuthorization('Paciente view - Cuidador', $cuidador, 'view', $paciente);
        $this->testAuthorization('Paciente view - Paciente', $pacienteUser, 'view', $paciente);

        // Test create
        $this->testAuthorization('Paciente create - Admin', $admin, 'create', Paciente::class);
        $this->testAuthorization('Paciente create - Médico', $medico, 'create', Paciente::class);
        $this->testAuthorization('Paciente create - Cuidador', $cuidador, 'create', Paciente::class);

        // Test update
        $this->testAuthorization('Paciente update - Admin', $admin, 'update', $paciente);
        $this->testAuthorization('Paciente update - Médico', $medico, 'update', $paciente);
        $this->testAuthorization('Paciente update - Cuidador', $cuidador, 'update', $paciente);

        // Test delete
        $this->testAuthorization('Paciente delete - Admin', $admin, 'delete', $paciente);
        $this->testAuthorization('Paciente delete - Médico', $medico, 'delete', $paciente);
        $this->testAuthorization('Paciente delete - Cuidador', $cuidador, 'delete', $paciente);
    }

    private function testTratamientoPolicy($admin, $medico, $cuidador, $pacienteUser)
    {
        $this->info("\n💊 PROBANDO POLÍTICA DE TRATAMIENTOS");
        $this->info("===================================");

        $tratamiento = Tratamiento::first();
        if (!$tratamiento) {
            $this->error('No hay tratamientos en la base de datos');
            return;
        }

        $this->info("Probando con Tratamiento ID: {$tratamiento->id}");

        // Test viewAny
        $this->testAuthorization('Tratamiento viewAny - Admin', $admin, 'viewAny', Tratamiento::class);
        $this->testAuthorization('Tratamiento viewAny - Médico', $medico, 'viewAny', Tratamiento::class);
        $this->testAuthorization('Tratamiento viewAny - Cuidador', $cuidador, 'viewAny', Tratamiento::class);

        // Test view specific
        $this->testAuthorization('Tratamiento view - Admin', $admin, 'view', $tratamiento);
        $this->testAuthorization('Tratamiento view - Médico', $medico, 'view', $tratamiento);
        $this->testAuthorization('Tratamiento view - Cuidador', $cuidador, 'view', $tratamiento);

        // Test create
        $this->testAuthorization('Tratamiento create - Admin', $admin, 'create', Tratamiento::class);
        $this->testAuthorization('Tratamiento create - Médico', $medico, 'create', Tratamiento::class);
        $this->testAuthorization('Tratamiento create - Cuidador', $cuidador, 'create', Tratamiento::class);

        // Test update
        $this->testAuthorization('Tratamiento update - Admin', $admin, 'update', $tratamiento);
        $this->testAuthorization('Tratamiento update - Médico', $medico, 'update', $tratamiento);
        $this->testAuthorization('Tratamiento update - Cuidador', $cuidador, 'update', $tratamiento);

        // Test changeStatus
        $this->testAuthorization('Tratamiento changeStatus - Admin', $admin, 'changeStatus', $tratamiento);
        $this->testAuthorization('Tratamiento changeStatus - Médico', $medico, 'changeStatus', $tratamiento);
        $this->testAuthorization('Tratamiento changeStatus - Cuidador', $cuidador, 'changeStatus', $tratamiento);
    }

    private function testMedicinePolicy($admin, $medico, $cuidador, $pacienteUser)
    {
        $this->info("\n💉 PROBANDO POLÍTICA DE MEDICAMENTOS");
        $this->info("====================================");

        $medicine = Medicine::first();
        if (!$medicine) {
            $this->error('No hay medicamentos en la base de datos');
            return;
        }

        $this->info("Probando con Medicine ID: {$medicine->id}");

        // Test viewAny
        $this->testAuthorization('Medicine viewAny - Admin', $admin, 'viewAny', Medicine::class);
        $this->testAuthorization('Medicine viewAny - Médico', $medico, 'viewAny', Medicine::class);
        $this->testAuthorization('Medicine viewAny - Cuidador', $cuidador, 'viewAny', Medicine::class);
        $this->testAuthorization('Medicine viewAny - Paciente', $pacienteUser, 'viewAny', Medicine::class);

        // Test view specific
        $this->testAuthorization('Medicine view - Admin', $admin, 'view', $medicine);
        $this->testAuthorization('Medicine view - Médico', $medico, 'view', $medicine);
        $this->testAuthorization('Medicine view - Cuidador', $cuidador, 'view', $medicine);

        // Test create
        $this->testAuthorization('Medicine create - Admin', $admin, 'create', Medicine::class);
        $this->testAuthorization('Medicine create - Médico', $medico, 'create', Medicine::class);
        $this->testAuthorization('Medicine create - Cuidador', $cuidador, 'create', Medicine::class);

        // Test manageInventory
        $this->testAuthorization('Medicine manageInventory - Admin', $admin, 'manageInventory', $medicine);
        $this->testAuthorization('Medicine manageInventory - Médico', $medico, 'manageInventory', $medicine);
        $this->testAuthorization('Medicine manageInventory - Cuidador', $cuidador, 'manageInventory', $medicine);
    }

    private function testCustomGates($admin, $medico, $cuidador, $pacienteUser)
    {
        $this->info("\n🔐 PROBANDO GATES PERSONALIZADOS");
        $this->info("===============================");

        // Test admin-access
        $this->testGate('admin-access - Admin', $admin, 'admin-access');
        $this->testGate('admin-access - Médico', $medico, 'admin-access');
        $this->testGate('admin-access - Cuidador', $cuidador, 'admin-access');

        // Test medical-access
        $this->testGate('medical-access - Admin', $admin, 'medical-access');
        $this->testGate('medical-access - Médico', $medico, 'medical-access');
        $this->testGate('medical-access - Cuidador', $cuidador, 'medical-access');

        // Test caregiver-management
        $this->testGate('caregiver-management - Admin', $admin, 'caregiver-management');
        $this->testGate('caregiver-management - Médico', $medico, 'caregiver-management');
        $this->testGate('caregiver-management - Cuidador', $cuidador, 'caregiver-management');

        // Test medicine-administration
        $this->testGate('medicine-administration - Admin', $admin, 'medicine-administration');
        $this->testGate('medicine-administration - Médico', $medico, 'medicine-administration');
        $this->testGate('medicine-administration - Cuidador', $cuidador, 'medicine-administration');

        // Test schedule-access
        $this->testGate('schedule-access - Admin', $admin, 'schedule-access');
        $this->testGate('schedule-access - Médico', $medico, 'schedule-access');
        $this->testGate('schedule-access - Cuidador', $cuidador, 'schedule-access');
    }

    private function testAuthorization($description, $user, $ability, $model)
    {
        try {
            $result = $user->can($ability, $model);
            $status = $result ? '✅ PERMITIDO' : '❌ DENEGADO';
            $this->info("  {$description}: {$status}");
        } catch (\Exception $e) {
            $this->error("  {$description}: ERROR - {$e->getMessage()}");
        }
    }

    private function testGate($description, $user, $gateName)
    {
        try {
            $result = Gate::forUser($user)->allows($gateName);
            $status = $result ? '✅ PERMITIDO' : '❌ DENEGADO';
            $this->info("  {$description}: {$status}");
        } catch (\Exception $e) {
            $this->error("  {$description}: ERROR - {$e->getMessage()}");
        }
    }
}
