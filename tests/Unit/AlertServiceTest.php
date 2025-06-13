<?php

use App\Services\AlertService;
use App\Models\Alerta;
use App\Models\Administracion;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Tratamiento;
use App\Models\MedicamentoTratamiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

describe('Alert Service', function () {
    
    beforeEach(function () {
        $this->alertService = new AlertService();
        
        // Setup test data
        $this->paciente = Paciente::factory()->create();
        $this->medico = User::factory()->create();
        $this->cuidador = User::factory()->create();
        
        $this->tratamiento = Tratamiento::factory()->create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'estado' => Tratamiento::ESTADO_ACTIVO
        ]);

        $this->medicamentoTratamiento = MedicamentoTratamiento::factory()->create([
            'tratamiento_id' => $this->tratamiento->id
        ]);
    });

    describe('Missed Dose Alerts', function () {
        it('creates critical alert for missed dose outside tolerance window', function () {
            $administracion = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_programada' => now()->subHours(2), // 2 hours overdue
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            $result = $this->alertService->checkMissedDoses();

            expect($result)->toBeArray();
            expect($result['created_alerts'])->toBeGreaterThan(0);
            
            $this->assertDatabaseHas('alertas', [
                'tipo' => 'dosis_omitida',
                'nivel_prioridad' => 'critica',
                'paciente_id' => $this->paciente->id,
                'estado' => 'pendiente'
            ]);
        });

        it('creates warning alert for dose approaching tolerance limit', function () {
            $administracion = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_programada' => now()->subMinutes(45), // Close to 1-hour limit
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            $result = $this->alertService->checkApproachingDeadlines();

            expect($result['warning_alerts'])->toBeGreaterThan(0);
            
            $this->assertDatabaseHas('alertas', [
                'tipo' => 'dosis_proxima_vencer',
                'nivel_prioridad' => 'advertencia',
                'paciente_id' => $this->paciente->id
            ]);
        });

        it('does not create duplicate alerts for same missed dose', function () {
            $administracion = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_programada' => now()->subHours(2),
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            // Create existing alert
            Alerta::factory()->create([
                'tipo' => 'dosis_omitida',
                'administracion_id' => $administracion->id,
                'paciente_id' => $this->paciente->id,
                'estado' => 'pendiente'
            ]);

            $initialCount = Alerta::count();
            
            $this->alertService->checkMissedDoses();
            
            expect(Alerta::count())->toBe($initialCount); // No new alerts created
        });
    });

    describe('PRN Overdose Alerts', function () {
        beforeEach(function () {
            $this->medicamentoPrn = MedicamentoTratamiento::factory()->create([
                'tratamiento_id' => $this->tratamiento->id,
                'dosis_maxima_dia' => 3000, // 3000mg daily limit
                'intervalo_minimo_horas' => 8
            ]);
        });

        it('creates critical alert when daily PRN limit exceeded', function () {
            // Create 6 administrations of 500mg each (3000mg total)
            Administracion::factory()->count(6)->create([
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now()->subHours(rand(1, 23)),
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 500
            ]);

            $result = $this->alertService->checkPrnOverdoses();

            expect($result['critical_alerts'])->toBeGreaterThan(0);
            
            $this->assertDatabaseHas('alertas', [
                'tipo' => 'sobredosis_prn',
                'nivel_prioridad' => 'critica',
                'paciente_id' => $this->paciente->id,
                'descripcion' => 'Límite diario de medicamento PRN excedido'
            ]);
        });

        it('creates warning alert when approaching daily PRN limit', function () {
            // Create 5 administrations (2500mg, close to 3000mg limit)
            Administracion::factory()->count(5)->create([
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now()->subHours(rand(1, 23)),
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 500
            ]);

            $result = $this->alertService->checkPrnLimits();

            expect($result['warning_alerts'])->toBeGreaterThan(0);
            
            $this->assertDatabaseHas('alertas', [
                'tipo' => 'limite_prn_cercano',
                'nivel_prioridad' => 'advertencia',
                'paciente_id' => $this->paciente->id
            ]);
        });

        it('creates alert for PRN administration below minimum interval', function () {
            // Create recent administration
            Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now()->subHours(4), // 4 hours ago, minimum is 8
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 500
            ]);

            // Attempt new administration
            $newAdministration = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now(),
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 500
            ]);

            $result = $this->alertService->checkPrnIntervals();

            $this->assertDatabaseHas('alertas', [
                'tipo' => 'intervalo_prn_violado',
                'nivel_prioridad' => 'critica',
                'administracion_id' => $newAdministration->id
            ]);
        });
    });

    describe('Adverse Effects Alerts', function () {
        it('creates alert when adverse effects reported', function () {
            $administracion = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'efectos_adversos' => 'Náuseas severas y vómitos'
            ]);

            $result = $this->alertService->processAdverseEffects($administracion);

            expect($result['alert_created'])->toBeTrue();
            
            $this->assertDatabaseHas('alertas', [
                'tipo' => 'efectos_adversos',
                'nivel_prioridad' => 'critica',
                'administracion_id' => $administracion->id,
                'paciente_id' => $this->paciente->id
            ]);
        });

        it('categorizes adverse effects by severity', function () {
            $mildEffects = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'efectos_adversos' => 'Náuseas leves'
            ]);

            $severeEffects = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'efectos_adversos' => 'Reacción alérgica severa, dificultad respiratoria'
            ]);

            $this->alertService->processAdverseEffects($mildEffects);
            $this->alertService->processAdverseEffects($severeEffects);

            $this->assertDatabaseHas('alertas', [
                'administracion_id' => $mildEffects->id,
                'nivel_prioridad' => 'advertencia'
            ]);

            $this->assertDatabaseHas('alertas', [
                'administracion_id' => $severeEffects->id,
                'nivel_prioridad' => 'critica'
            ]);
        });
    });

    describe('Treatment Adherence Alerts', function () {
        it('creates alert for poor adherence pattern', function () {
            // Create multiple missed doses for the same patient
            Administracion::factory()->count(5)->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_programada' => now()->subDays(rand(1, 7)),
                'estado' => Administracion::ESTADO_OMITIDA
            ]);

            $result = $this->alertService->checkAdherencePatterns();

            $this->assertDatabaseHas('alertas', [
                'tipo' => 'adherencia_baja',
                'nivel_prioridad' => 'advertencia',
                'paciente_id' => $this->paciente->id,
                'descripcion' => 'Patrón de baja adherencia detectado'
            ]);
        });

        it('calculates adherence percentage correctly', function () {
            // Create 8 total doses: 6 administered, 2 omitted (75% adherence)
            Administracion::factory()->count(6)->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'estado' => Administracion::ESTADO_ADMINISTRADA
            ]);

            Administracion::factory()->count(2)->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'estado' => Administracion::ESTADO_OMITIDA
            ]);

            $adherence = $this->alertService->calculateAdherence($this->paciente->id, now()->subWeek(), now());

            expect($adherence)->toBe(75.0);
        });
    });

    describe('Alert Notifications', function () {
        beforeEach(function () {
            Notification::fake();
        });

        it('notifies medical doctor for critical alerts', function () {
            $alert = Alerta::factory()->create([
                'tipo' => 'efectos_adversos',
                'nivel_prioridad' => 'critica',
                'paciente_id' => $this->paciente->id,
                'estado' => 'pendiente'
            ]);

            $this->alertService->sendAlertNotifications($alert);

            Notification::assertSentTo(
                $this->medico,
                \App\Notifications\CriticalAlertNotification::class
            );
        });

        it('notifies caregivers for medication alerts', function () {
            $alert = Alerta::factory()->create([
                'tipo' => 'dosis_omitida',
                'nivel_prioridad' => 'critica',
                'paciente_id' => $this->paciente->id
            ]);

            $this->alertService->sendAlertNotifications($alert);

            Notification::assertSentTo(
                $this->cuidador,
                \App\Notifications\MedicationAlertNotification::class
            );
        });

        it('sends different notification channels based on priority', function () {
            $criticalAlert = Alerta::factory()->create([
                'tipo' => 'sobredosis_prn',
                'nivel_prioridad' => 'critica',
                'paciente_id' => $this->paciente->id
            ]);

            $warningAlert = Alerta::factory()->create([
                'tipo' => 'limite_prn_cercano',
                'nivel_prioridad' => 'advertencia',
                'paciente_id' => $this->paciente->id
            ]);

            $this->alertService->sendAlertNotifications($criticalAlert);
            $this->alertService->sendAlertNotifications($warningAlert);

            // Critical should send email + SMS
            // Warning should send only email
            Notification::assertSentTo($this->medico, \App\Notifications\CriticalAlertNotification::class);
            Notification::assertSentTo($this->medico, \App\Notifications\WarningAlertNotification::class);
        });
    });

    describe('Alert Processing and Resolution', function () {
        it('marks alert as processed when resolved', function () {
            $alert = Alerta::factory()->create([
                'tipo' => 'dosis_omitida',
                'estado' => 'pendiente',
                'paciente_id' => $this->paciente->id
            ]);

            $result = $this->alertService->processAlert($alert->id, $this->medico->id, 'Dosis administrada tardíamente');

            expect($result['success'])->toBeTrue();
            
            $this->assertDatabaseHas('alertas', [
                'id' => $alert->id,
                'estado' => 'procesada',
                'procesada_por_usuario_id' => $this->medico->id,
                'observaciones_procesamiento' => 'Dosis administrada tardíamente'
            ]);
        });

        it('auto-resolves alerts when underlying issue is fixed', function () {
            $administracion = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'paciente_id' => $this->paciente->id,
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            $alert = Alerta::factory()->create([
                'tipo' => 'dosis_omitida',
                'administracion_id' => $administracion->id,
                'estado' => 'pendiente'
            ]);

            // Administer the missed dose
            $administracion->update([
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'fecha_hora_administrada' => now(),
                'dosis_administrada' => 500
            ]);

            $this->alertService->autoResolveAlerts($administracion);

            $this->assertDatabaseHas('alertas', [
                'id' => $alert->id,
                'estado' => 'auto_resuelta',
                'fecha_resolucion' => now()->toDateString()
            ]);
        });

        it('escalates unprocessed critical alerts', function () {
            $oldAlert = Alerta::factory()->create([
                'tipo' => 'efectos_adversos',
                'nivel_prioridad' => 'critica',
                'estado' => 'pendiente',
                'created_at' => now()->subHours(2) // 2 hours old, unprocessed
            ]);

            $result = $this->alertService->escalateUnprocessedAlerts();

            expect($result['escalated_count'])->toBeGreaterThan(0);
            
            $this->assertDatabaseHas('alertas', [
                'id' => $oldAlert->id,
                'nivel_prioridad' => 'urgente',
                'escalada' => true
            ]);
        });
    });

    describe('Alert Analytics and Reporting', function () {
        it('generates alert summary by time period', function () {
            // Create alerts of different types and priorities
            Alerta::factory()->count(3)->create([
                'tipo' => 'dosis_omitida',
                'nivel_prioridad' => 'critica',
                'created_at' => now()->subDays(1)
            ]);

            Alerta::factory()->count(2)->create([
                'tipo' => 'efectos_adversos',
                'nivel_prioridad' => 'advertencia',
                'created_at' => now()->subDays(2)
            ]);

            $summary = $this->alertService->getAlertSummary(now()->subWeek(), now());

            expect($summary)->toHaveKey('total_alerts');
            expect($summary)->toHaveKey('by_type');
            expect($summary)->toHaveKey('by_priority');
            expect($summary['total_alerts'])->toBe(5);
            expect($summary['by_type']['dosis_omitida'])->toBe(3);
            expect($summary['by_priority']['critica'])->toBe(3);
        });

        it('identifies alert trends and patterns', function () {
            // Create alert pattern over multiple days
            for ($i = 0; $i < 7; $i++) {
                Alerta::factory()->count(rand(1, 5))->create([
                    'created_at' => now()->subDays($i),
                    'paciente_id' => $this->paciente->id
                ]);
            }

            $trends = $this->alertService->analyzeAlertTrends($this->paciente->id);

            expect($trends)->toHaveKey('daily_average');
            expect($trends)->toHaveKey('trend_direction');
            expect($trends)->toHaveKey('most_common_type');
        });

        it('calculates alert response times', function () {
            $processedAlert = Alerta::factory()->create([
                'estado' => 'procesada',
                'created_at' => now()->subHours(2),
                'fecha_procesamiento' => now()
            ]);

            $responseTime = $this->alertService->calculateResponseTime($processedAlert);

            expect($responseTime)->toBe(120); // 2 hours in minutes
        });
    });
}); 