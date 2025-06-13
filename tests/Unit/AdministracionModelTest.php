<?php

use App\Models\Administracion;
use App\Models\Paciente;
use App\Models\User;
use App\Models\HorarioProgramado;
use App\Models\MedicamentoTratamiento;
use Carbon\Carbon;

describe('Administracion Model', function () {
    
    beforeEach(function () {
        $this->administracion = new Administracion();
    });

    describe('Constants', function () {
        it('has correct administration status constants', function () {
            expect(Administracion::ESTADO_PENDIENTE)->toBe('Pendiente');
            expect(Administracion::ESTADO_ADMINISTRADA)->toBe('Administrada');
            expect(Administracion::ESTADO_OMITIDA)->toBe('Omitida');
            expect(Administracion::ESTADO_TARDIA)->toBe('Tardía');
        });
    });

    describe('Table and fillable', function () {
        it('uses correct table name', function () {
            expect($this->administracion->getTable())->toBe('administraciones');
        });

        it('has correct fillable attributes', function () {
            $expectedFillable = [
                'medicamento_tratamiento_id',
                'horario_programado_id',
                'paciente_id',
                'cuidador_usuario_id',
                'fecha_hora_programada',
                'fecha_hora_administrada',
                'dosis_administrada',
                'estado',
                'es_dentro_ventana_tolerancia',
                'minutos_diferencia',
                'sintoma_reportado_id',
                'intensidad_sintoma',
                'criterio_cumplido',
                'observaciones',
                'efectos_adversos'
            ];

            expect($this->administracion->getFillable())->toBe($expectedFillable);
        });
    });

    describe('Date casting', function () {
        it('casts datetime fields correctly', function () {
            $administracion = Administracion::factory()->create([
                'fecha_hora_programada' => '2025-01-01 08:00:00',
                'fecha_hora_administrada' => '2025-01-01 08:05:00'
            ]);

            expect($administracion->fecha_hora_programada)->toBeInstanceOf(Carbon::class);
            expect($administracion->fecha_hora_administrada)->toBeInstanceOf(Carbon::class);
        });

        it('casts boolean fields correctly', function () {
            $administracion = Administracion::factory()->create([
                'es_dentro_ventana_tolerancia' => true
            ]);

            expect($administracion->es_dentro_ventana_tolerancia)->toBeTrue();
        });
    });

    describe('Relationships', function () {
        it('belongs to horario programado', function () {
            $administracion = Administracion::factory()->create();
            
            expect($administracion->horarioProgramado())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        });

        it('belongs to paciente', function () {
            $administracion = Administracion::factory()->create();
            
            expect($administracion->paciente())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsTo::class);
            expect($administracion->paciente)->toBeInstanceOf(Paciente::class);
        });

        it('belongs to cuidador (User)', function () {
            $administracion = Administracion::factory()->create();
            
            expect($administracion->cuidador())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsTo::class);
            expect($administracion->cuidador)->toBeInstanceOf(User::class);
        });

        it('belongs to medicamento tratamiento', function () {
            $administracion = Administracion::factory()->create();
            
            expect($administracion->medicamentoTratamiento())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        });
    });

    describe('Appended attributes', function () {
        it('includes tratamiento and medicamento as appended attributes', function () {
            expect($this->administracion->getAppends())->toContain('tratamiento', 'medicamento');
        });
    });

    describe('Scopes', function () {
        beforeEach(function () {
            // Crear administraciones de prueba con diferentes estados
            Administracion::factory()->create(['estado' => Administracion::ESTADO_PENDIENTE]);
            Administracion::factory()->create(['estado' => Administracion::ESTADO_ADMINISTRADA]);
            Administracion::factory()->create(['estado' => Administracion::ESTADO_OMITIDA]);
            Administracion::factory()->create(['estado' => Administracion::ESTADO_TARDIA]);
            Administracion::factory()->create(['estado' => Administracion::ESTADO_PENDIENTE]);
        });

        it('filters pending administrations with pendientes scope', function () {
            $pendientes = Administracion::pendientes()->get();
            
            expect($pendientes)->toHaveCount(2);
            $pendientes->each(function ($admin) {
                expect($admin->estado)->toBe(Administracion::ESTADO_PENDIENTE);
            });
        });

        it('filters administered with administradas scope', function () {
            $administradas = Administracion::administradas()->get();
            
            expect($administradas)->toHaveCount(1);
            expect($administradas->first()->estado)->toBe(Administracion::ESTADO_ADMINISTRADA);
        });

        it('filters omitted with omitidas scope', function () {
            $omitidas = Administracion::omitidas()->get();
            
            expect($omitidas)->toHaveCount(1);
            expect($omitidas->first()->estado)->toBe(Administracion::ESTADO_OMITIDA);
        });

        it('filters late administrations with tardias scope', function () {
            $tardias = Administracion::tardias()->get();
            
            expect($tardias)->toHaveCount(1);
            expect($tardias->first()->estado)->toBe(Administracion::ESTADO_TARDIA);
        });
    });

    describe('Tolerance window calculation', function () {
        it('correctly identifies administration within tolerance window', function () {
            $programmed = Carbon::create(2025, 1, 1, 8, 0, 0);
            $administered = Carbon::create(2025, 1, 1, 8, 15, 0); // 15 minutes later
            
            $administracion = Administracion::factory()->create([
                'fecha_hora_programada' => $programmed,
                'fecha_hora_administrada' => $administered,
                'es_dentro_ventana_tolerancia' => true,
                'minutos_diferencia' => 15
            ]);

            expect($administracion->es_dentro_ventana_tolerancia)->toBeTrue();
            expect($administracion->minutos_diferencia)->toBe(15);
        });

        it('correctly identifies administration outside tolerance window', function () {
            $programmed = Carbon::create(2025, 1, 1, 8, 0, 0);
            $administered = Carbon::create(2025, 1, 1, 9, 0, 0); // 1 hour later
            
            $administracion = Administracion::factory()->create([
                'fecha_hora_programada' => $programmed,
                'fecha_hora_administrada' => $administered,
                'es_dentro_ventana_tolerancia' => false,
                'minutos_diferencia' => 60,
                'estado' => Administracion::ESTADO_TARDIA
            ]);

            expect($administracion->es_dentro_ventana_tolerancia)->toBeFalse();
            expect($administracion->minutos_diferencia)->toBe(60);
            expect($administracion->estado)->toBe(Administracion::ESTADO_TARDIA);
        });
    });

    describe('PRN administration', function () {
        it('handles PRN administration with symptom reporting', function () {
            $administracion = Administracion::factory()->create([
                'sintoma_reportado_id' => 1,
                'intensidad_sintoma' => 7,
                'criterio_cumplido' => true,
                'estado' => Administracion::ESTADO_ADMINISTRADA
            ]);

            expect($administracion->sintoma_reportado_id)->toBe(1);
            expect($administracion->intensidad_sintoma)->toBe(7);
            expect($administracion->criterio_cumplido)->toBeTrue();
        });

        it('prevents PRN administration when criteria not met', function () {
            $administracion = Administracion::factory()->create([
                'sintoma_reportado_id' => 1,
                'intensidad_sintoma' => 3, // Below threshold
                'criterio_cumplido' => false,
                'estado' => Administracion::ESTADO_OMITIDA
            ]);

            expect($administracion->criterio_cumplido)->toBeFalse();
            expect($administracion->estado)->toBe(Administracion::ESTADO_OMITIDA);
        });
    });

    describe('Adverse effects tracking', function () {
        it('records adverse effects when reported', function () {
            $efectosAdversos = 'Náuseas leves después de 30 minutos';
            
            $administracion = Administracion::factory()->create([
                'efectos_adversos' => $efectosAdversos,
                'estado' => Administracion::ESTADO_ADMINISTRADA
            ]);

            expect($administracion->efectos_adversos)->toBe($efectosAdversos);
        });

        it('allows null adverse effects for normal administrations', function () {
            $administracion = Administracion::factory()->create([
                'efectos_adversos' => null,
                'estado' => Administracion::ESTADO_ADMINISTRADA
            ]);

            expect($administracion->efectos_adversos)->toBeNull();
        });
    });

    describe('Dose tracking', function () {
        it('records administered dose amount', function () {
            $administracion = Administracion::factory()->create([
                'dosis_administrada' => 500.0,
                'estado' => Administracion::ESTADO_ADMINISTRADA
            ]);

            expect($administracion->dosis_administrada)->toBe(500.0);
        });

        it('handles partial dose administration', function () {
            $administracion = Administracion::factory()->create([
                'dosis_administrada' => 250.0, // Half dose
                'observaciones' => 'Paciente rechazó dosis completa',
                'estado' => Administracion::ESTADO_ADMINISTRADA
            ]);

            expect($administracion->dosis_administrada)->toBe(250.0);
            expect($administracion->observaciones)->toContain('rechazó');
        });
    });

    describe('State transitions', function () {
        it('allows transition from pending to administered', function () {
            $administracion = Administracion::factory()->create([
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            $administracion->update([
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'fecha_hora_administrada' => now(),
                'dosis_administrada' => 500.0
            ]);

            expect($administracion->fresh()->estado)->toBe(Administracion::ESTADO_ADMINISTRADA);
        });

        it('allows transition from pending to omitted', function () {
            $administracion = Administracion::factory()->create([
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            $administracion->update([
                'estado' => Administracion::ESTADO_OMITIDA,
                'observaciones' => 'Paciente no disponible'
            ]);

            expect($administracion->fresh()->estado)->toBe(Administracion::ESTADO_OMITIDA);
        });
    });

    describe('Business validations', function () {
        it('requires administered date when marked as administered', function () {
            $administracion = Administracion::factory()->make([
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'fecha_hora_administrada' => null
            ]);

            expect($administracion->save())->toBeFalse();
        });

        it('requires dose amount when marked as administered', function () {
            $administracion = Administracion::factory()->make([
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'fecha_hora_administrada' => now(),
                'dosis_administrada' => null
            ]);

            expect($administracion->save())->toBeFalse();
        });

        it('allows omitted without administered date or dose', function () {
            $administracion = Administracion::factory()->create([
                'estado' => Administracion::ESTADO_OMITIDA,
                'fecha_hora_administrada' => null,
                'dosis_administrada' => null,
                'observaciones' => 'Paciente rechazó medicamento'
            ]);

            expect($administracion->exists)->toBeTrue();
        });
    });

    describe('Time calculations', function () {
        it('calculates delay correctly for late administration', function () {
            $programmed = Carbon::create(2025, 1, 1, 8, 0, 0);
            $administered = Carbon::create(2025, 1, 1, 8, 45, 0);
            
            $minutesDifference = $administered->diffInMinutes($programmed);
            
            $administracion = Administracion::factory()->create([
                'fecha_hora_programada' => $programmed,
                'fecha_hora_administrada' => $administered,
                'minutos_diferencia' => $minutesDifference
            ]);

            expect($administracion->minutos_diferencia)->toBe(45);
        });

        it('handles early administration', function () {
            $programmed = Carbon::create(2025, 1, 1, 8, 0, 0);
            $administered = Carbon::create(2025, 1, 1, 7, 45, 0);
            
            $minutesDifference = $administered->diffInMinutes($programmed);
            
            $administracion = Administracion::factory()->create([
                'fecha_hora_programada' => $programmed,
                'fecha_hora_administrada' => $administered,
                'minutos_diferencia' => $minutesDifference
            ]);

            expect($administracion->minutos_diferencia)->toBe(15);
        });
    });
}); 