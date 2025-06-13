<?php

use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Medicamento;
use App\Models\MedicamentoTratamiento;

describe('Tratamiento Model', function () {
    
    beforeEach(function () {
        $this->tratamiento = new Tratamiento();
    });

    describe('Constants', function () {
        it('has correct treatment type constants', function () {
            expect(Tratamiento::TIPO_PROGRAMADO)->toBe('Programado');
            expect(Tratamiento::TIPO_PRN)->toBe('PRN');
        });

        it('has correct status constants', function () {
            expect(Tratamiento::ESTADO_ACTIVO)->toBe('Activo');
            expect(Tratamiento::ESTADO_PAUSADO)->toBe('Pausado');
            expect(Tratamiento::ESTADO_FINALIZADO)->toBe('Completado');
            expect(Tratamiento::ESTADO_CANCELADO)->toBe('Suspendido');
        });
    });

    describe('Fillable fields', function () {
        it('has correct fillable attributes', function () {
            $expectedFillable = [
                'paciente_id',
                'medico_usuario_id',
                'nombre',
                'diagnostico',
                'tipo',
                'objetivo',
                'fecha_inicio',
                'fecha_fin',
                'estado',
                'observaciones'
            ];

            expect($this->tratamiento->getFillable())->toBe($expectedFillable);
        });
    });

    describe('Date casting', function () {
        it('casts fecha_inicio and fecha_fin as dates', function () {
            $tratamiento = Tratamiento::factory()->create([
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-01-15'
            ]);

            expect($tratamiento->fecha_inicio)->toBeInstanceOf(Carbon\Carbon::class);
            expect($tratamiento->fecha_fin)->toBeInstanceOf(Carbon\Carbon::class);
        });
    });

    describe('Relationships', function () {
        it('belongs to a paciente', function () {
            $tratamiento = Tratamiento::factory()->create();
            
            expect($tratamiento->paciente())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsTo::class);
            expect($tratamiento->paciente)->toBeInstanceOf(Paciente::class);
        });

        it('belongs to a medico (User)', function () {
            $tratamiento = Tratamiento::factory()->create();
            
            expect($tratamiento->medico())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsTo::class);
            expect($tratamiento->medico)->toBeInstanceOf(User::class);
        });

        it('has many medicamentos through pivot table', function () {
            $tratamiento = Tratamiento::factory()->create();
            $medicamento = Medicamento::factory()->create();
            
            $tratamiento->medicamentos()->attach($medicamento->id, [
                'dosis_cantidad' => 500,
                'unidad_dosis' => 'mg',
                'frecuencia_horas' => 8
            ]);

            expect($tratamiento->medicamentos())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
            expect($tratamiento->medicamentos->first())->toBeInstanceOf(Medicamento::class);
            expect($tratamiento->medicamentos->first()->pivot->dosis_cantidad)->toBe(500);
        });

        it('has many medicamento tratamientos', function () {
            $tratamiento = Tratamiento::factory()->create();
            
            expect($tratamiento->medicamentoTratamientos())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
        });
    });

    describe('Scopes', function () {
        beforeEach(function () {
            // Crear tratamientos de prueba
            Tratamiento::factory()->create(['estado' => Tratamiento::ESTADO_ACTIVO, 'tipo' => Tratamiento::TIPO_PROGRAMADO]);
            Tratamiento::factory()->create(['estado' => Tratamiento::ESTADO_PAUSADO, 'tipo' => Tratamiento::TIPO_PRN]);
            Tratamiento::factory()->create(['estado' => Tratamiento::ESTADO_ACTIVO, 'tipo' => Tratamiento::TIPO_PRN]);
        });

        it('filters active treatments with activos scope', function () {
            $activeTreatments = Tratamiento::activos()->get();
            
            expect($activeTreatments)->toHaveCount(2);
            $activeTreatments->each(function ($tratamiento) {
                expect($tratamiento->estado)->toBe(Tratamiento::ESTADO_ACTIVO);
            });
        });

        it('filters by type with tipo scope', function () {
            $programados = Tratamiento::tipo(Tratamiento::TIPO_PROGRAMADO)->get();
            $prn = Tratamiento::tipo(Tratamiento::TIPO_PRN)->get();
            
            expect($programados)->toHaveCount(1);
            expect($prn)->toHaveCount(2);
        });

        it('filters programmed treatments with programados scope', function () {
            $programados = Tratamiento::programados()->get();
            
            expect($programados)->toHaveCount(1);
            expect($programados->first()->tipo)->toBe(Tratamiento::TIPO_PROGRAMADO);
        });

        it('filters PRN treatments with prn scope', function () {
            $prn = Tratamiento::prn()->get();
            
            expect($prn)->toHaveCount(2);
            $prn->each(function ($tratamiento) {
                expect($tratamiento->tipo)->toBe(Tratamiento::TIPO_PRN);
            });
        });
    });

    describe('Utility methods', function () {
        it('correctly identifies active treatment with isActivo', function () {
            $activeTreatment = Tratamiento::factory()->create(['estado' => Tratamiento::ESTADO_ACTIVO]);
            $inactiveTreatment = Tratamiento::factory()->create(['estado' => Tratamiento::ESTADO_PAUSADO]);
            
            expect($activeTreatment->isActivo())->toBeTrue();
            expect($inactiveTreatment->isActivo())->toBeFalse();
        });

        it('correctly identifies PRN treatment with isPrn', function () {
            $prnTreatment = Tratamiento::factory()->create(['tipo' => Tratamiento::TIPO_PRN]);
            $programmedTreatment = Tratamiento::factory()->create(['tipo' => Tratamiento::TIPO_PROGRAMADO]);
            
            expect($prnTreatment->isPrn())->toBeTrue();
            expect($programmedTreatment->isPrn())->toBeFalse();
        });
    });

    describe('Data validation', function () {
        it('requires essential fields', function () {
            $tratamiento = new Tratamiento();
            
            expect($tratamiento->save())->toBeFalse();
        });

        it('validates treatment type is valid', function () {
            $tratamiento = Tratamiento::factory()->make(['tipo' => 'InvalidType']);
            
            expect($tratamiento->save())->toBeFalse();
        });

        it('validates treatment status is valid', function () {
            $tratamiento = Tratamiento::factory()->make(['estado' => 'InvalidStatus']);
            
            expect($tratamiento->save())->toBeFalse();
        });

        it('validates fecha_fin is after fecha_inicio', function () {
            $tratamiento = Tratamiento::factory()->make([
                'fecha_inicio' => '2025-01-15',
                'fecha_fin' => '2025-01-01'
            ]);
            
            expect($tratamiento->save())->toBeFalse();
        });
    });

    describe('Complex relationships', function () {
        it('can retrieve horarios through medicamento tratamientos', function () {
            $tratamiento = Tratamiento::factory()->create();
            $medicamento = Medicamento::factory()->create();
            
            $tratamiento->medicamentos()->attach($medicamento->id, [
                'dosis_cantidad' => 500,
                'unidad_dosis' => 'mg',
                'frecuencia_horas' => 8
            ]);

            $horarios = $tratamiento->horarios();
            
            expect($horarios)->toBeInstanceOf(Illuminate\Database\Eloquent\Builder::class);
        });

        it('can retrieve administraciones through medicamento tratamientos', function () {
            $tratamiento = Tratamiento::factory()->create();
            $medicamento = Medicamento::factory()->create();
            
            $tratamiento->medicamentos()->attach($medicamento->id, [
                'dosis_cantidad' => 500,
                'unidad_dosis' => 'mg',
                'frecuencia_horas' => 8
            ]);

            $administraciones = $tratamiento->administraciones();
            
            expect($administraciones)->toBeInstanceOf(Illuminate\Database\Eloquent\Builder::class);
        });
    });

    describe('Business logic validation', function () {
        it('prevents overlapping active treatments for same condition', function () {
            $paciente = Paciente::factory()->create();
            
            // Crear primer tratamiento activo
            $tratamiento1 = Tratamiento::factory()->create([
                'paciente_id' => $paciente->id,
                'diagnostico' => 'Hipertensión',
                'estado' => Tratamiento::ESTADO_ACTIVO,
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-01-31'
            ]);

            // Intentar crear segundo tratamiento que se superpone
            $tratamiento2 = Tratamiento::factory()->make([
                'paciente_id' => $paciente->id,
                'diagnostico' => 'Hipertensión',
                'estado' => Tratamiento::ESTADO_ACTIVO,
                'fecha_inicio' => '2025-01-15',
                'fecha_fin' => '2025-02-15'
            ]);
            
            // Debería fallar la validación
            expect($tratamiento2->save())->toBeFalse();
        });

        it('allows multiple PRN treatments for different symptoms', function () {
            $paciente = Paciente::factory()->create();
            
            $tratamiento1 = Tratamiento::factory()->create([
                'paciente_id' => $paciente->id,
                'tipo' => Tratamiento::TIPO_PRN,
                'diagnostico' => 'Dolor',
                'estado' => Tratamiento::ESTADO_ACTIVO
            ]);

            $tratamiento2 = Tratamiento::factory()->create([
                'paciente_id' => $paciente->id,
                'tipo' => Tratamiento::TIPO_PRN,
                'diagnostico' => 'Fiebre',
                'estado' => Tratamiento::ESTADO_ACTIVO
            ]);
            
            expect($tratamiento1->exists)->toBeTrue();
            expect($tratamiento2->exists)->toBeTrue();
        });
    });
}); 