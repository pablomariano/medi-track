<?php

namespace Tests;

use App\Models\User;
use App\Models\Role;
use App\Models\Paciente;
use App\Models\Medicamento;
use App\Models\Tratamiento;
use App\Models\Administracion;
use App\Models\MedicamentoTratamiento;
use Carbon\Carbon;

class TestHelper
{
    /**
     * Create a complete user with specific role
     */
    public static function createUserWithRole(string $roleName, array $userData = []): User
    {
        $role = Role::factory()->create(['name' => $roleName]);
        $user = User::factory()->create($userData);
        $user->roles()->attach($role);
        
        return $user;
    }

    /**
     * Create a complete medical team setup
     */
    public static function createMedicalTeam(): array
    {
        return [
            'medico' => self::createUserWithRole('medico', ['name' => 'Dr. Test']),
            'cuidador' => self::createUserWithRole('cuidador', ['name' => 'Enfermera Test']),
            'apoderado' => self::createUserWithRole('apoderado', ['name' => 'Apoderado Test'])
        ];
    }

    /**
     * Create a patient with full medical profile
     */
    public static function createPatientWithProfile(array $patientData = []): Paciente
    {
        $defaultData = [
            'nombre' => 'Paciente Test',
            'fecha_nacimiento' => '1980-01-01',
            'genero_id' => 1,
            'tipo_documento' => 'DNI',
            'numero_documento' => '12345678',
            'alergias' => 'Ninguna conocida',
            'altura' => 170,
            'peso' => 70
        ];

        return Paciente::factory()->create(array_merge($defaultData, $patientData));
    }

    /**
     * Create a medication with complete information
     */
    public static function createMedication(array $medicationData = []): Medicamento
    {
        $defaultData = [
            'nombre' => 'Medicamento Test',
            'principio_activo' => 'Principio Activo Test',
            'concentracion' => '500',
            'unidad_concentracion' => 'mg',
            'forma_farmaceutica' => 'Tableta',
            'laboratorio' => 'Lab Test'
        ];

        return Medicamento::factory()->create(array_merge($defaultData, $medicationData));
    }

    /**
     * Create a complete programmed treatment
     */
    public static function createProgrammedTreatment(User $medico, Paciente $paciente, Medicamento $medicamento, array $treatmentData = []): Tratamiento
    {
        $defaultData = [
            'paciente_id' => $paciente->id,
            'medico_usuario_id' => $medico->id,
            'nombre' => 'Tratamiento Test Programado',
            'diagnostico' => 'Diagnóstico Test',
            'tipo' => Tratamiento::TIPO_PROGRAMADO,
            'objetivo' => 'Objetivo terapéutico test',
            'fecha_inicio' => today(),
            'fecha_fin' => today()->addDays(7),
            'estado' => Tratamiento::ESTADO_ACTIVO
        ];

        $tratamiento = Tratamiento::factory()->create(array_merge($defaultData, $treatmentData));

        // Attach medication with pivot data
        $pivotData = [
            'dosis_cantidad' => 500,
            'unidad_dosis' => 'mg',
            'frecuencia_horas' => 8,
            'tolerancia_antes_minutos' => 30,
            'tolerancia_despues_minutos' => 30,
            'instrucciones_especiales' => 'Tomar con alimentos'
        ];

        $tratamiento->medicamentos()->attach($medicamento->id, $pivotData);

        return $tratamiento;
    }

    /**
     * Create a complete PRN treatment
     */
    public static function createPrnTreatment(User $medico, Paciente $paciente, Medicamento $medicamento, array $treatmentData = []): Tratamiento
    {
        $defaultData = [
            'paciente_id' => $paciente->id,
            'medico_usuario_id' => $medico->id,
            'nombre' => 'Tratamiento Test PRN',
            'diagnostico' => 'Diagnóstico Test PRN',
            'tipo' => Tratamiento::TIPO_PRN,
            'objetivo' => 'Control sintomático',
            'fecha_inicio' => today(),
            'estado' => Tratamiento::ESTADO_ACTIVO
        ];

        $tratamiento = Tratamiento::factory()->create(array_merge($defaultData, $treatmentData));

        // Attach medication with PRN specific data
        $pivotData = [
            'dosis_cantidad' => 400,
            'unidad_dosis' => 'mg',
            'intervalo_minimo_horas' => 8,
            'dosis_maxima_dia' => 1200,
            'dosis_maxima_consecutiva' => 2
        ];

        $tratamiento->medicamentos()->attach($medicamento->id, $pivotData);

        return $tratamiento;
    }

    /**
     * Create pending administrations for a treatment
     */
    public static function createPendingAdministrations(Tratamiento $tratamiento, int $count = 3): array
    {
        $medicamentoTratamiento = $tratamiento->medicamentoTratamientos->first();
        $administraciones = [];

        for ($i = 0; $i < $count; $i++) {
            $administraciones[] = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $medicamentoTratamiento->id,
                'paciente_id' => $tratamiento->paciente_id,
                'fecha_hora_programada' => today()->setTime(8 + ($i * 8), 0), // 8:00, 16:00, 00:00
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);
        }

        return $administraciones;
    }

    /**
     * Simulate medication administration
     */
    public static function simulateAdministration(Administracion $administracion, array $data = []): Administracion
    {
        $defaultData = [
            'fecha_hora_administrada' => $administracion->fecha_hora_programada->addMinutes(10),
            'dosis_administrada' => 500,
            'estado' => Administracion::ESTADO_ADMINISTRADA,
            'es_dentro_ventana_tolerancia' => true,
            'minutos_diferencia' => 10
        ];

        $administracion->update(array_merge($defaultData, $data));
        
        return $administracion;
    }

    /**
     * Simulate late administration
     */
    public static function simulateLateAdministration(Administracion $administracion, int $delayMinutes = 90): Administracion
    {
        return self::simulateAdministration($administracion, [
            'fecha_hora_administrada' => $administracion->fecha_hora_programada->addMinutes($delayMinutes),
            'estado' => Administracion::ESTADO_TARDIA,
            'es_dentro_ventana_tolerancia' => false,
            'minutos_diferencia' => $delayMinutes,
            'observaciones' => 'Administrada tardíamente'
        ]);
    }

    /**
     * Simulate missed administration
     */
    public static function simulateMissedAdministration(Administracion $administracion, string $reason = 'Paciente no disponible'): Administracion
    {
        $administracion->update([
            'estado' => Administracion::ESTADO_OMITIDA,
            'observaciones' => $reason
        ]);

        return $administracion;
    }

    /**
     * Create PRN administration with symptom data
     */
    public static function createPrnAdministration(MedicamentoTratamiento $medicamentoTratamiento, Paciente $paciente, array $data = []): Administracion
    {
        $defaultData = [
            'medicamento_tratamiento_id' => $medicamentoTratamiento->id,
            'paciente_id' => $paciente->id,
            'fecha_hora_administrada' => now(),
            'dosis_administrada' => 400,
            'estado' => Administracion::ESTADO_ADMINISTRADA,
            'sintoma_reportado_id' => 1,
            'intensidad_sintoma' => 7,
            'criterio_cumplido' => true,
            'observaciones' => 'Administración PRN por síntomas'
        ];

        return Administracion::factory()->create(array_merge($defaultData, $data));
    }

    /**
     * Calculate expected adherence percentage
     */
    public static function calculateExpectedAdherence(int $totalDoses, int $administeredDoses): float
    {
        if ($totalDoses === 0) {
            return 0.0;
        }

        return round(($administeredDoses / $totalDoses) * 100, 2);
    }

    /**
     * Assert adherence statistics
     */
    public static function assertAdherenceStats(array $stats, int $expectedTotal, int $expectedAdministered): void
    {
        expect($stats['total_dosis'])->toBe($expectedTotal);
        expect($stats['dosis_administradas'])->toBe($expectedAdministered);
        expect($stats['adherencia_porcentaje'])->toBe(
            self::calculateExpectedAdherence($expectedTotal, $expectedAdministered)
        );
    }

    /**
     * Create time-based test scenario
     */
    public static function createTimeScenario(Carbon $startTime, array $administrations): void
    {
        Carbon::setTestNow($startTime);
        
        foreach ($administrations as $minutesFromStart => $administrationData) {
            Carbon::setTestNow($startTime->copy()->addMinutes($minutesFromStart));
            // Process administration data
        }
    }

    /**
     * Reset test environment
     */
    public static function resetTestEnvironment(): void
    {
        Carbon::setTestNow();
        \Illuminate\Support\Facades\Notification::fake();
    }

    /**
     * Create complete test scenario with all entities
     */
    public static function createCompleteScenario(): array
    {
        $team = self::createMedicalTeam();
        $paciente = self::createPatientWithProfile();
        $medicamento = self::createMedication();
        
        $tratamientoProgramado = self::createProgrammedTreatment(
            $team['medico'], 
            $paciente, 
            $medicamento
        );
        
        $tratamientoPrn = self::createPrnTreatment(
            $team['medico'], 
            $paciente, 
            $medicamento
        );

        return compact(
            'team', 
            'paciente', 
            'medicamento', 
            'tratamientoProgramado', 
            'tratamientoPrn'
        );
    }

    /**
     * Generate test data for reports
     */
    public static function generateReportTestData(Paciente $paciente, int $days = 7): array
    {
        $data = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = today()->subDays($i);
            
            // Create various administrations for each day
            $data[$date->format('Y-m-d')] = [
                'administradas' => rand(2, 4),
                'omitidas' => rand(0, 1),
                'tardias' => rand(0, 2),
                'prn' => rand(0, 3)
            ];
        }

        return $data;
    }

    /**
     * Assert database state after operations
     */
    public static function assertDatabaseState(array $expectations): void
    {
        foreach ($expectations as $table => $conditions) {
            if (isset($conditions['has'])) {
                foreach ($conditions['has'] as $condition) {
                    expect(\Illuminate\Support\Facades\DB::table($table)->where($condition)->exists())
                        ->toBeTrue("Expected record in {$table} table");
                }
            }

            if (isset($conditions['count'])) {
                $actualCount = \Illuminate\Support\Facades\DB::table($table)->count();
                expect($actualCount)->toBe($conditions['count'], 
                    "Expected {$conditions['count']} records in {$table}, got {$actualCount}");
            }
        }
    }
} 