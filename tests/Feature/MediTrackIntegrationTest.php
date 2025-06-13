<?php

use App\Models\User;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Medicamento;
use App\Models\Administracion;
use App\Models\HorarioProgramado;
use App\Models\MedicamentoTratamiento;
use App\Models\Alerta;
use App\Models\Role;
use App\Services\AlertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

describe('Medi-Track Complete Integration Flow', function () {
    
    beforeEach(function () {
        // Create roles
        $this->medicoRole = Role::factory()->create(['name' => 'medico']);
        $this->cuidadorRole = Role::factory()->create(['name' => 'cuidador']);
        $this->apoderadoRole = Role::factory()->create(['name' => 'apoderado']);
        
        // Create users
        $this->medico = User::factory()->create(['name' => 'Dr. García']);
        $this->medico->roles()->attach($this->medicoRole);
        
        $this->cuidador = User::factory()->create(['name' => 'Enfermera López']);
        $this->cuidador->roles()->attach($this->cuidadorRole);
        
        $this->apoderado = User::factory()->create(['name' => 'María Pérez']);
        $this->apoderado->roles()->attach($this->apoderadoRole);
        
        // Create patient
        $this->paciente = Paciente::factory()->create([
            'nombre' => 'Juan Rodríguez',
            'fecha_nacimiento' => '1980-05-15',
            'alergias' => 'Penicilina'
        ]);
        
        // Create medications
        $this->paracetamol = Medicamento::factory()->create([
            'nombre' => 'Paracetamol',
            'principio_activo' => 'Acetaminofén',
            'concentracion' => '500',
            'unidad_concentracion' => 'mg',
            'forma_farmaceutica' => 'Tableta'
        ]);
        
        $this->ibuprofeno = Medicamento::factory()->create([
            'nombre' => 'Ibuprofeno',
            'principio_activo' => 'Ibuprofeno',
            'concentracion' => '400',
            'unidad_concentracion' => 'mg',
            'forma_farmaceutica' => 'Tableta'
        ]);
        
        Notification::fake();
    });

    describe('Complete Programmed Treatment Flow', function () {
        it('handles complete workflow from prescription to completion', function () {
            // STEP 1: Medical doctor creates programmed treatment
            $this->actingAs($this->medico);
            
            $treatmentData = [
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id,
                'nombre' => 'Tratamiento Antibiótico Post-Cirugía',
                'diagnostico' => 'Infección post-quirúrgica',
                'tipo' => Tratamiento::TIPO_PROGRAMADO,
                'objetivo' => 'Prevenir infección y controlar dolor',
                'fecha_inicio' => today()->format('Y-m-d'),
                'fecha_fin' => today()->addDays(7)->format('Y-m-d'),
                'estado' => Tratamiento::ESTADO_ACTIVO,
                'medicamentos' => [
                    [
                        'medicamento_id' => $this->paracetamol->id,
                        'dosis_cantidad' => 500,
                        'unidad_dosis' => 'mg',
                        'frecuencia_horas' => 8,
                        'tolerancia_antes_minutos' => 30,
                        'tolerancia_despues_minutos' => 30,
                        'instrucciones_especiales' => 'Tomar con alimentos'
                    ]
                ]
            ];

            $response = $this->post(route('tratamientos.store'), $treatmentData);
            $response->assertStatus(302);
            
            // Verify treatment was created
            $tratamiento = Tratamiento::where('nombre', 'Tratamiento Antibiótico Post-Cirugía')->first();
            expect($tratamiento)->not->toBeNull();
            expect($tratamiento->estado)->toBe(Tratamiento::ESTADO_ACTIVO);
            
            // Verify medication was attached with pivot data
            $medicamentoTratamiento = $tratamiento->medicamentoTratamientos->first();
            expect($medicamentoTratamiento->medicamento_id)->toBe($this->paracetamol->id);
            expect($medicamentoTratamiento->dosis_cantidad)->toBe(500);
            expect($medicamentoTratamiento->frecuencia_horas)->toBe(8);

            // STEP 2: System generates scheduled administrations
            $schedulingService = app(\App\Services\SchedulingService::class);
            $schedules = $schedulingService->generateSchedules($tratamiento);
            
            expect($schedules)->toBeGreaterThan(0);
            
            // Verify horarios programados were created
            $horarios = HorarioProgramado::where('medicamento_tratamiento_id', $medicamentoTratamiento->id)->get();
            expect($horarios)->toHaveCount(21); // 7 days × 3 doses per day
            
            // Verify administraciones were created
            $administraciones = Administracion::where('paciente_id', $this->paciente->id)
                ->where('estado', Administracion::ESTADO_PENDIENTE)
                ->get();
            expect($administraciones->count())->toBeGreaterThan(0);

            // STEP 3: Caregiver views daily schedule
            $this->actingAs($this->cuidador);
            
            $response = $this->get(route('cronograma.diario', ['fecha' => today()->format('Y-m-d')]));
            $response->assertStatus(200);
            $response->assertSee($this->paciente->nombre);
            $response->assertSee($this->paracetamol->nombre);
            
            $todayAdministrations = $response->viewData('administraciones');
            expect($todayAdministrations->count())->toBe(3); // 3 doses today

            // STEP 4: Caregiver administers first dose on time
            $firstDose = $administraciones->where('fecha_hora_programada', '>=', today()->setTime(0, 0))
                                          ->where('fecha_hora_programada', '<', today()->setTime(23, 59))
                                          ->first();
            
            $administrationTime = $firstDose->fecha_hora_programada->addMinutes(10); // 10 minutes after scheduled
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $firstDose->id,
                'fecha_hora_administrada' => $administrationTime->format('Y-m-d H:i:s'),
                'dosis_administrada' => 500,
                'observaciones' => 'Administrada sin problemas, paciente toleró bien'
            ]);
            
            $response->assertStatus(201);
            
            // Verify administration was recorded correctly
            $firstDose->refresh();
            expect($firstDose->estado)->toBe(Administracion::ESTADO_ADMINISTRADA);
            expect($firstDose->es_dentro_ventana_tolerancia)->toBeTrue();
            expect($firstDose->dosis_administrada)->toBe(500);

            // STEP 5: Caregiver misses second dose (outside tolerance window)
            $secondDose = $administraciones->where('fecha_hora_programada', '>', $firstDose->fecha_hora_programada)
                                           ->sortBy('fecha_hora_programada')
                                           ->first();
            
            // Simulate time passing - dose becomes overdue
            Carbon::setTestNow($secondDose->fecha_hora_programada->addHours(2)); // 2 hours late
            
            // Alert service should detect missed dose
            $alertService = app(AlertService::class);
            $alertResult = $alertService->checkMissedDoses();
            
            expect($alertResult['created_alerts'])->toBeGreaterThan(0);
            
            // Verify alert was created
            $missedDoseAlert = Alerta::where('tipo', 'dosis_omitida')
                                    ->where('paciente_id', $this->paciente->id)
                                    ->where('administracion_id', $secondDose->id)
                                    ->first();
            
            expect($missedDoseAlert)->not->toBeNull();
            expect($missedDoseAlert->nivel_prioridad)->toBe('critica');

            // STEP 6: Caregiver administers late dose and marks it
            $lateAdministrationTime = $secondDose->fecha_hora_programada->addHours(2, 30);
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $secondDose->id,
                'fecha_hora_administrada' => $lateAdministrationTime->format('Y-m-d H:i:s'),
                'dosis_administrada' => 500,
                'observaciones' => 'Administrada tardíamente debido a emergencia médica'
            ]);
            
            $response->assertStatus(201);
            
            $secondDose->refresh();
            expect($secondDose->estado)->toBe(Administracion::ESTADO_TARDIA);
            expect($secondDose->es_dentro_ventana_tolerancia)->toBeFalse();
            expect($secondDose->minutos_diferencia)->toBe(150); // 2.5 hours

            // Alert should be auto-resolved
            $alertService->autoResolveAlerts($secondDose);
            $missedDoseAlert->refresh();
            expect($missedDoseAlert->estado)->toBe('auto_resuelta');

            // STEP 7: Third dose causes adverse effects
            $thirdDose = $administraciones->where('fecha_hora_programada', '>', $secondDose->fecha_hora_programada)
                                          ->sortBy('fecha_hora_programada')
                                          ->first();
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $thirdDose->id,
                'fecha_hora_administrada' => $thirdDose->fecha_hora_programada->format('Y-m-d H:i:s'),
                'dosis_administrada' => 500,
                'efectos_adversos' => 'Náuseas leves y mareo',
                'observaciones' => 'Paciente reporta malestar estomacal'
            ]);
            
            $response->assertStatus(201);
            
            // Process adverse effects
            $thirdDose->refresh();
            $adverseEffectResult = $alertService->processAdverseEffects($thirdDose);
            
            expect($adverseEffectResult['alert_created'])->toBeTrue();
            
            // Verify adverse effect alert
            $adverseAlert = Alerta::where('tipo', 'efectos_adversos')
                                  ->where('administracion_id', $thirdDose->id)
                                  ->first();
            
            expect($adverseAlert)->not->toBeNull();
            expect($adverseAlert->nivel_prioridad)->toBe('advertencia'); // Mild effects

            // STEP 8: Medical doctor reviews alerts and adjusts treatment
            $this->actingAs($this->medico);
            
            $response = $this->get(route('alertas.index'));
            $response->assertStatus(200);
            
            $alerts = $response->viewData('alertas');
            expect($alerts->count())->toBeGreaterThan(0);

            // Doctor processes adverse effect alert
            $response = $this->patch(route('alertas.process', $adverseAlert), [
                'observaciones_procesamiento' => 'Reducir dosis a 250mg por efectos adversos',
                'accion_requerida' => 'ajustar_dosis'
            ]);
            
            $response->assertStatus(200);
            
            $adverseAlert->refresh();
            expect($adverseAlert->estado)->toBe('procesada');
            expect($adverseAlert->procesada_por_usuario_id)->toBe($this->medico->id);

            // STEP 9: Medical doctor modifies treatment
            $updatedTreatmentData = [
                'medicamentos' => [
                    [
                        'medicamento_tratamiento_id' => $medicamentoTratamiento->id,
                        'dosis_cantidad' => 250, // Reduced dose
                        'observaciones_cambio' => 'Reducción de dosis por efectos adversos leves'
                    ]
                ]
            ];
            
            $response = $this->put(route('tratamientos.update', $tratamiento), $updatedTreatmentData);
            $response->assertStatus(302);
            
            // Verify new version was created
            $newTreatmentVersion = Tratamiento::where('paciente_id', $this->paciente->id)
                                              ->where('created_at', '>', $tratamiento->created_at)
                                              ->first();
            
            expect($newTreatmentVersion)->not->toBeNull();
            expect($tratamiento->fresh()->estado)->toBe('superseded');

            // STEP 10: Generate adherence report
            $response = $this->get(route('reportes.adherencia', [
                'paciente_id' => $this->paciente->id,
                'fecha_inicio' => today()->format('Y-m-d'),
                'fecha_fin' => today()->format('Y-m-d')
            ]));
            
            $response->assertStatus(200);
            $reportData = $response->viewData('reporte');
            
            expect($reportData['total_dosis'])->toBe(3);
            expect($reportData['dosis_administradas'])->toBe(3);
            expect($reportData['dosis_a_tiempo'])->toBe(1);
            expect($reportData['dosis_tardias'])->toBe(1);
            expect($reportData['adherencia_porcentaje'])->toBe(100.0); // All doses given
            expect($reportData['puntualidad_porcentaje'])->toBe(33.33); // Only 1 of 3 on time

            // STEP 11: Apoderado receives notifications and views summary
            $this->actingAs($this->apoderado);
            
            $response = $this->get(route('pacientes.resumen', $this->paciente));
            $response->assertStatus(200);
            $response->assertSee('Juan Rodríguez');
            $response->assertSee('Tratamiento Antibiótico');
            
            // Verify notifications were sent
            Notification::assertSentTo($this->apoderado, \App\Notifications\TreatmentUpdateNotification::class);

            // STEP 12: Complete treatment after 7 days
            Carbon::setTestNow(today()->addDays(7));
            
            $this->actingAs($this->medico);
            
            $response = $this->patch(route('tratamientos.status', $newTreatmentVersion), [
                'estado' => Tratamiento::ESTADO_FINALIZADO,
                'motivo' => 'Tratamiento completado según plan terapéutico',
                'observaciones_finales' => 'Paciente mostró buena respuesta con dosis ajustada'
            ]);
            
            $response->assertStatus(200);
            
            $newTreatmentVersion->refresh();
            expect($newTreatmentVersion->estado)->toBe(Tratamiento::ESTADO_FINALIZADO);

            // Generate final report
            $response = $this->get(route('reportes.tratamiento', $newTreatmentVersion));
            $response->assertStatus(200);
            
            $finalReport = $response->viewData('reporte');
            expect($finalReport['tratamiento_completado'])->toBeTrue();
            expect($finalReport['efectos_adversos_reportados'])->toBeGreaterThan(0);
            expect($finalReport['ajustes_realizados'])->toBeGreaterThan(0);
        });
    });

    describe('Complete PRN Treatment Flow', function () {
        it('handles complete PRN workflow with symptom evaluation', function () {
            // STEP 1: Create PRN treatment for pain management
            $this->actingAs($this->medico);
            
            $prnTreatmentData = [
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id,
                'nombre' => 'Analgesia PRN Post-Quirúrgica',
                'diagnostico' => 'Dolor post-operatorio',
                'tipo' => Tratamiento::TIPO_PRN,
                'objetivo' => 'Control del dolor según necesidad',
                'fecha_inicio' => today()->format('Y-m-d'),
                'estado' => Tratamiento::ESTADO_ACTIVO,
                'medicamentos' => [
                    [
                        'medicamento_id' => $this->ibuprofeno->id,
                        'dosis_cantidad' => 400,
                        'unidad_dosis' => 'mg',
                        'intervalo_minimo_horas' => 8,
                        'dosis_maxima_dia' => 1200,
                        'dosis_maxima_consecutiva' => 2,
                        'criterios_prn' => [
                            [
                                'sintoma' => 'Dolor',
                                'criterio' => 'Escala >5/10',
                                'instrucciones' => 'Evaluar intensidad antes de administrar'
                            ]
                        ]
                    ]
                ]
            ];

            $response = $this->post(route('tratamientos.store'), $prnTreatmentData);
            $response->assertStatus(302);
            
            $prnTreatment = Tratamiento::where('nombre', 'Analgesia PRN Post-Quirúrgica')->first();
            expect($prnTreatment->tipo)->toBe(Tratamiento::TIPO_PRN);
            
            $medicamentoPrn = $prnTreatment->medicamentoTratamientos->first();
            expect($medicamentoPrn->intervalo_minimo_horas)->toBe(8);
            expect($medicamentoPrn->dosis_maxima_dia)->toBe(1200);

            // STEP 2: Patient reports pain - caregiver evaluates
            $this->actingAs($this->cuidador);
            
            // First PRN administration - criteria met
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor abdominal',
                'intensidad_sintoma' => 7, // Above threshold
                'criterio_cumplido' => true,
                'dosis_administrada' => 400,
                'observaciones' => 'Paciente reporta dolor intenso después de fisioterapia'
            ]);
            
            $response->assertStatus(201);
            
            $firstPrnAdmin = Administracion::where('medicamento_tratamiento_id', $medicamentoPrn->id)
                                           ->where('paciente_id', $this->paciente->id)
                                           ->first();
            
            expect($firstPrnAdmin->estado)->toBe(Administracion::ESTADO_ADMINISTRADA);
            expect($firstPrnAdmin->intensidad_sintoma)->toBe(7);
            expect($firstPrnAdmin->criterio_cumplido)->toBeTrue();

            // STEP 3: Patient reports pain again too soon - should be blocked
            Carbon::setTestNow(now()->addHours(4)); // Only 4 hours later, minimum is 8
            
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor persistente',
                'intensidad_sintoma' => 8,
                'dosis_administrada' => 400
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['intervalo_minimo']);

            // STEP 4: Valid second PRN after minimum interval
            Carbon::setTestNow(now()->addHours(5)); // Total 9 hours from first dose
            
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor recurrente',
                'intensidad_sintoma' => 6,
                'criterio_cumplido' => true,
                'dosis_administrada' => 400,
                'observaciones' => 'Dolor moderado durante movilización'
            ]);
            
            $response->assertStatus(201);

            // STEP 5: Third PRN would exceed daily limit
            Carbon::setTestNow(now()->addHours(8));
            
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor nocturno',
                'intensidad_sintoma' => 7,
                'dosis_administrada' => 400 // Would total 1200mg (daily limit)
            ]);
            
            $response->assertStatus(201); // Should pass as it reaches but doesn't exceed limit

            // STEP 6: Fourth PRN attempt - should be blocked for exceeding daily limit
            Carbon::setTestNow(now()->addHours(8));
            
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor severo',
                'intensidad_sintoma' => 9,
                'dosis_administrada' => 400
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['dosis_maxima_dia']);

            // Verify alert for reaching daily limit
            $alertService = app(AlertService::class);
            $prnLimitResult = $alertService->checkPrnLimits();
            
            $limitAlert = Alerta::where('tipo', 'limite_prn_cercano')
                                ->where('paciente_id', $this->paciente->id)
                                ->first();
            
            expect($limitAlert)->not->toBeNull();

            // STEP 7: Generate PRN usage report
            $this->actingAs($this->medico);
            
            $response = $this->get(route('reportes.prn', [
                'paciente_id' => $this->paciente->id,
                'fecha_inicio' => today()->format('Y-m-d'),
                'fecha_fin' => today()->format('Y-m-d')
            ]));
            
            $response->assertStatus(200);
            $prnReport = $response->viewData('reporte');
            
            expect($prnReport['total_administraciones'])->toBe(3);
            expect($prnReport['dosis_total_dia'])->toBe(1200);
            expect($prnReport['intensidad_promedio'])->toBe(6.67); // (7+6+7)/3
            expect($prnReport['sintomas_mas_frecuentes'])->toContain('Dolor');
        });
    });

    afterEach(function () {
        Carbon::setTestNow(); // Reset time
    });
}); 