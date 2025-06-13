<?php

use App\Models\User;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Medicamento;
use App\Models\Role;

describe('Treatment Management Feature', function () {
    
    beforeEach(function () {
        // Crear roles básicos
        $this->medicoRole = Role::factory()->create(['name' => 'medico']);
        $this->cuidadorRole = Role::factory()->create(['name' => 'cuidador']);
        
        // Crear usuarios de prueba
        $this->medico = User::factory()->create();
        $this->medico->roles()->attach($this->medicoRole);
        
        $this->cuidador = User::factory()->create();
        $this->cuidador->roles()->attach($this->cuidadorRole);
        
        // Crear paciente y medicamento de prueba
        $this->paciente = Paciente::factory()->create();
        $this->medicamento = Medicamento::factory()->create();
    });

    describe('Creating Programmed Treatments', function () {
        it('allows medical doctor to create programmed treatment', function () {
            $this->actingAs($this->medico);
            
            $treatmentData = [
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id,
                'nombre' => 'Tratamiento Antibiótico',
                'diagnostico' => 'Infección respiratoria',
                'tipo' => Tratamiento::TIPO_PROGRAMADO,
                'objetivo' => 'Eliminar infección',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-01-07',
                'estado' => Tratamiento::ESTADO_ACTIVO,
                'medicamentos' => [
                    [
                        'medicamento_id' => $this->medicamento->id,
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

            $response->assertStatus(302); // Redirect after creation
            
            $this->assertDatabaseHas('tratamientos', [
                'nombre' => 'Tratamiento Antibiótico',
                'tipo' => Tratamiento::TIPO_PROGRAMADO,
                'paciente_id' => $this->paciente->id
            ]);
        });

        it('prevents non-medical users from creating treatments', function () {
            $this->actingAs($this->cuidador);
            
            $treatmentData = [
                'paciente_id' => $this->paciente->id,
                'nombre' => 'Tratamiento No Autorizado',
                'tipo' => Tratamiento::TIPO_PROGRAMADO
            ];

            $response = $this->post(route('tratamientos.store'), $treatmentData);

            $response->assertStatus(403); // Forbidden
        });

        it('validates required fields for programmed treatment', function () {
            $this->actingAs($this->medico);
            
            $response = $this->post(route('tratamientos.store'), []);

            $response->assertSessionHasErrors([
                'paciente_id',
                'nombre',
                'diagnostico',
                'tipo'
            ]);
        });

        it('validates medication data for programmed treatment', function () {
            $this->actingAs($this->medico);
            
            $treatmentData = [
                'paciente_id' => $this->paciente->id,
                'nombre' => 'Test Treatment',
                'diagnostico' => 'Test Diagnosis',
                'tipo' => Tratamiento::TIPO_PROGRAMADO,
                'medicamentos' => [
                    [
                        'medicamento_id' => $this->medicamento->id,
                        // Missing required fields
                    ]
                ]
            ];

            $response = $this->post(route('tratamientos.store'), $treatmentData);

            $response->assertSessionHasErrors([
                'medicamentos.0.dosis_cantidad',
                'medicamentos.0.frecuencia_horas'
            ]);
        });
    });

    describe('Creating PRN Treatments', function () {
        it('allows medical doctor to create PRN treatment', function () {
            $this->actingAs($this->medico);
            
            $treatmentData = [
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id,
                'nombre' => 'Analgesia PRN',
                'diagnostico' => 'Dolor crónico',
                'tipo' => Tratamiento::TIPO_PRN,
                'objetivo' => 'Control del dolor',
                'fecha_inicio' => '2025-01-01',
                'estado' => Tratamiento::ESTADO_ACTIVO,
                'medicamentos' => [
                    [
                        'medicamento_id' => $this->medicamento->id,
                        'dosis_cantidad' => 500,
                        'unidad_dosis' => 'mg',
                        'intervalo_minimo_horas' => 8,
                        'dosis_maxima_dia' => 3000,
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

            $response = $this->post(route('tratamientos.store'), $treatmentData);

            $response->assertStatus(302);
            
            $this->assertDatabaseHas('tratamientos', [
                'nombre' => 'Analgesia PRN',
                'tipo' => Tratamiento::TIPO_PRN,
                'paciente_id' => $this->paciente->id
            ]);
        });

        it('validates PRN specific restrictions', function () {
            $this->actingAs($this->medico);
            
            $treatmentData = [
                'paciente_id' => $this->paciente->id,
                'nombre' => 'PRN Test',
                'diagnostico' => 'Test',
                'tipo' => Tratamiento::TIPO_PRN,
                'medicamentos' => [
                    [
                        'medicamento_id' => $this->medicamento->id,
                        'dosis_cantidad' => 500,
                        // Missing PRN required fields
                    ]
                ]
            ];

            $response = $this->post(route('tratamientos.store'), $treatmentData);

            $response->assertSessionHasErrors([
                'medicamentos.0.intervalo_minimo_horas',
                'medicamentos.0.dosis_maxima_dia'
            ]);
        });
    });

    describe('Editing Treatments', function () {
        beforeEach(function () {
            $this->tratamiento = Tratamiento::factory()->create([
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id,
                'tipo' => Tratamiento::TIPO_PROGRAMADO,
                'estado' => Tratamiento::ESTADO_ACTIVO
            ]);

            $this->tratamiento->medicamentos()->attach($this->medicamento->id, [
                'dosis_cantidad' => 500,
                'unidad_dosis' => 'mg',
                'frecuencia_horas' => 8
            ]);
        });

        it('allows medical doctor to edit own treatment', function () {
            $this->actingAs($this->medico);
            
            $updatedData = [
                'nombre' => 'Tratamiento Actualizado',
                'dosis_cantidad' => 750,
                'observaciones' => 'Incremento de dosis por respuesta insuficiente'
            ];

            $response = $this->put(route('tratamientos.update', $this->tratamiento), $updatedData);

            $response->assertStatus(302);
            
            $this->assertDatabaseHas('tratamientos', [
                'id' => $this->tratamiento->id,
                'nombre' => 'Tratamiento Actualizado'
            ]);
        });

        it('prevents editing treatment by other medical doctors', function () {
            $otherMedico = User::factory()->create();
            $otherMedico->roles()->attach($this->medicoRole);
            
            $this->actingAs($otherMedico);
            
            $response = $this->put(route('tratamientos.update', $this->tratamiento), [
                'nombre' => 'Edición no autorizada'
            ]);

            $response->assertStatus(403);
        });

        it('creates new version when editing active treatment', function () {
            $this->actingAs($this->medico);
            
            $originalCount = Tratamiento::count();
            
            $response = $this->put(route('tratamientos.update', $this->tratamiento), [
                'dosis_cantidad' => 750,
                'observaciones' => 'Ajuste de dosis'
            ]);

            // Should create new version, preserving history
            expect(Tratamiento::count())->toBe($originalCount + 1);
            
            // Original should be marked as superseded
            expect($this->tratamiento->fresh()->estado)->toBe('Superseded');
        });
    });

    describe('Treatment Status Management', function () {
        beforeEach(function () {
            $this->tratamiento = Tratamiento::factory()->create([
                'medico_usuario_id' => $this->medico->id,
                'estado' => Tratamiento::ESTADO_ACTIVO
            ]);
        });

        it('allows medical doctor to pause treatment', function () {
            $this->actingAs($this->medico);
            
            $response = $this->patch(route('tratamientos.status', $this->tratamiento), [
                'estado' => Tratamiento::ESTADO_PAUSADO,
                'motivo' => 'Efectos adversos reportados'
            ]);

            $response->assertStatus(200);
            
            expect($this->tratamiento->fresh()->estado)->toBe(Tratamiento::ESTADO_PAUSADO);
        });

        it('allows medical doctor to complete treatment', function () {
            $this->actingAs($this->medico);
            
            $response = $this->patch(route('tratamientos.status', $this->tratamiento), [
                'estado' => Tratamiento::ESTADO_FINALIZADO,
                'motivo' => 'Objetivo terapéutico alcanzado'
            ]);

            $response->assertStatus(200);
            
            expect($this->tratamiento->fresh()->estado)->toBe(Tratamiento::ESTADO_FINALIZADO);
        });

        it('prevents unauthorized status changes', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->patch(route('tratamientos.status', $this->tratamiento), [
                'estado' => Tratamiento::ESTADO_CANCELADO
            ]);

            $response->assertStatus(403);
        });
    });

    describe('Treatment Viewing and Filtering', function () {
        beforeEach(function () {
            // Crear tratamientos de diferentes tipos y estados
            Tratamiento::factory()->create([
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id,
                'tipo' => Tratamiento::TIPO_PROGRAMADO,
                'estado' => Tratamiento::ESTADO_ACTIVO
            ]);

            Tratamiento::factory()->create([
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id,
                'tipo' => Tratamiento::TIPO_PRN,
                'estado' => Tratamiento::ESTADO_ACTIVO
            ]);

            Tratamiento::factory()->create([
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id,
                'tipo' => Tratamiento::TIPO_PROGRAMADO,
                'estado' => Tratamiento::ESTADO_PAUSADO
            ]);
        });

        it('displays treatment list for authorized users', function () {
            $this->actingAs($this->medico);
            
            $response = $this->get(route('tratamientos.index'));
            
            $response->assertStatus(200);
            $response->assertViewIs('tratamientos.index');
            $response->assertViewHas('tratamientos');
        });

        it('filters treatments by status', function () {
            $this->actingAs($this->medico);
            
            $response = $this->get(route('tratamientos.index', ['estado' => Tratamiento::ESTADO_ACTIVO]));
            
            $response->assertStatus(200);
            
            $treatments = $response->viewData('tratamientos');
            expect($treatments->every(fn($t) => $t->estado === Tratamiento::ESTADO_ACTIVO))->toBeTrue();
        });

        it('filters treatments by type', function () {
            $this->actingAs($this->medico);
            
            $response = $this->get(route('tratamientos.index', ['tipo' => Tratamiento::TIPO_PRN]));
            
            $response->assertStatus(200);
            
            $treatments = $response->viewData('tratamientos');
            expect($treatments->every(fn($t) => $t->tipo === Tratamiento::TIPO_PRN))->toBeTrue();
        });

        it('filters treatments by patient', function () {
            $this->actingAs($this->medico);
            
            $response = $this->get(route('tratamientos.index', ['paciente_id' => $this->paciente->id]));
            
            $response->assertStatus(200);
            
            $treatments = $response->viewData('tratamientos');
            expect($treatments->every(fn($t) => $t->paciente_id === $this->paciente->id))->toBeTrue();
        });
    });

    describe('Treatment Detail View', function () {
        beforeEach(function () {
            $this->tratamiento = Tratamiento::factory()->create([
                'paciente_id' => $this->paciente->id,
                'medico_usuario_id' => $this->medico->id
            ]);

            $this->tratamiento->medicamentos()->attach($this->medicamento->id, [
                'dosis_cantidad' => 500,
                'unidad_dosis' => 'mg',
                'frecuencia_horas' => 8
            ]);
        });

        it('shows complete treatment details to authorized users', function () {
            $this->actingAs($this->medico);
            
            $response = $this->get(route('tratamientos.show', $this->tratamiento));
            
            $response->assertStatus(200);
            $response->assertViewIs('tratamientos.show');
            $response->assertSee($this->tratamiento->nombre);
            $response->assertSee($this->medicamento->nombre);
        });

        it('shows treatment schedule for programmed treatments', function () {
            $programmedTreatment = Tratamiento::factory()->create([
                'tipo' => Tratamiento::TIPO_PROGRAMADO,
                'medico_usuario_id' => $this->medico->id
            ]);

            $this->actingAs($this->medico);
            
            $response = $this->get(route('tratamientos.show', $programmedTreatment));
            
            $response->assertStatus(200);
            $response->assertSee('Cronograma');
        });

        it('shows PRN criteria for PRN treatments', function () {
            $prnTreatment = Tratamiento::factory()->create([
                'tipo' => Tratamiento::TIPO_PRN,
                'medico_usuario_id' => $this->medico->id
            ]);

            $this->actingAs($this->medico);
            
            $response = $this->get(route('tratamientos.show', $prnTreatment));
            
            $response->assertStatus(200);
            $response->assertSee('Criterios PRN');
        });

        it('restricts access to unauthorized users', function () {
            $otherUser = User::factory()->create();
            
            $this->actingAs($otherUser);
            
            $response = $this->get(route('tratamientos.show', $this->tratamiento));
            
            $response->assertStatus(403);
        });
    });

    describe('Treatment Deletion', function () {
        beforeEach(function () {
            $this->tratamiento = Tratamiento::factory()->create([
                'medico_usuario_id' => $this->medico->id,
                'estado' => Tratamiento::ESTADO_ACTIVO
            ]);
        });

        it('prevents deletion of active treatments', function () {
            $this->actingAs($this->medico);
            
            $response = $this->delete(route('tratamientos.destroy', $this->tratamiento));
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['estado']);
        });

        it('allows soft deletion of completed treatments', function () {
            $this->tratamiento->update(['estado' => Tratamiento::ESTADO_FINALIZADO]);
            
            $this->actingAs($this->medico);
            
            $response = $this->delete(route('tratamientos.destroy', $this->tratamiento));
            
            $response->assertStatus(200);
            
            expect($this->tratamiento->fresh())->toBeNull(); // Soft deleted
        });

        it('prevents deletion by unauthorized users', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->delete(route('tratamientos.destroy', $this->tratamiento));
            
            $response->assertStatus(403);
        });
    });
}); 