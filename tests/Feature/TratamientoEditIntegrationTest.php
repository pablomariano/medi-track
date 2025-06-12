<?php

namespace Tests\Feature;

use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\Medicamento;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TratamientoEditIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $medico;
    protected $paciente;
    protected $medicamentos;
    protected $tratamiento;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear géneros necesarios
        \App\Models\Genero::create(['id' => 'M', 'nombre' => 'Masculino']);
        \App\Models\Genero::create(['id' => 'F', 'nombre' => 'Femenino']);
        
        // Crear roles necesarios
        $adminRole = Role::create(['nombre' => 'admin', 'descripcion' => 'Administrador']);
        $medicoRole = Role::create(['nombre' => 'medico', 'descripcion' => 'Médico']);
        
        // Crear usuario autenticado (admin)
        $this->user = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol_id' => $adminRole->id,
        ]);
        
        // Crear médico
        $this->medico = User::factory()->create([
            'name' => 'Dr. Test',
            'email' => 'medico@test.com',
            'rol_id' => $medicoRole->id,
        ]);
        
        // Crear paciente
        $this->paciente = Paciente::create([
            'nombre' => 'Juan Pérez',
            'numero_documento' => '12345678',
            'tipo_documento' => 'CI',
            'fecha_nacimiento' => '1980-01-01',
            'genero_id' => 'M',
            'activo' => true,
        ]);
        
        // Crear medicamentos para tests
        $this->medicamentos = collect([
            Medicamento::create([
                'nombre' => 'Paracetamol 500mg',
                'medida' => '500',
                'unidad_medida' => 'mg',
                'descripcion' => 'Analgésico y antipirético',
            ]),
            Medicamento::create([
                'nombre' => 'Ibuprofeno 400mg',
                'medida' => '400',
                'unidad_medida' => 'mg',
                'descripcion' => 'Antiinflamatorio no esteroideo',
            ]),
            Medicamento::create([
                'nombre' => 'Amoxicilina 500mg',
                'medida' => '500',
                'unidad_medida' => 'mg',
                'descripcion' => 'Antibiótico',
            ])
        ]);
    }

    /** @test */
    public function it_can_edit_programmed_treatment_workflow()
    {
        // Crear tratamiento programado
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control Hipertensión',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'objetivo' => 'Control de presión arterial',
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addDays(30)->format('Y-m-d'),
        ]);
        
        // Asociar medicamentos iniciales
        $tratamiento->medicamentos()->attach($this->medicamentos[0]->id, [
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'frecuencia_horas' => 8,
            'tolerancia_antes_minutos' => 30,
            'tolerancia_despues_minutos' => 60,
            'instrucciones_especiales' => 'Con comida',
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        $this->actingAs($this->user);
        
        // 1. Acceder a la página de edición
        $response = $this->get(route('tratamientos.edit', $tratamiento->id));
        $response->assertStatus(200);
        
        // 2. Verificar que carga los datos existentes
        $response->assertInertia(function ($page) use ($tratamiento) {
            $page->component('Tratamientos/Edit')
                ->where('tratamiento.id', $tratamiento->id)
                ->where('tratamiento.nombre', 'Control Hipertensión')
                ->where('tratamiento.tipo', 'Programado')
                ->has('tratamiento.medicamentos', 1);
        });
        
        // 3. Actualizar el tratamiento modificando medicamentos
        $updateData = [
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control Hipertensión Actualizado',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'objetivo' => 'Control estricto de presión arterial',
            'diagnostico' => 'Hipertensión arterial esencial',
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addDays(60)->format('Y-m-d'),
            'observaciones' => 'Paciente responde bien al tratamiento',
            'medicamentos' => [
                [
                    'medicamento_id' => $this->medicamentos[0]->id,
                    'dosis_cantidad' => '2',
                    'unidad_dosis' => 'tabletas',
                    'frecuencia_horas' => '12',
                    'tolerancia_antes_minutos' => '30',
                    'tolerancia_despues_minutos' => '60',
                    'instrucciones_especiales' => 'Con abundante agua',
                    'orden' => '1',
                ],
                [
                    'medicamento_id' => $this->medicamentos[1]->id,
                    'dosis_cantidad' => '1',
                    'unidad_dosis' => 'tableta',
                    'frecuencia_horas' => '24',
                    'tolerancia_antes_minutos' => '60',
                    'tolerancia_despues_minutos' => '120',
                    'instrucciones_especiales' => 'Después de comida',
                    'orden' => '2',
                ],
            ],
        ];
        
        $response = $this->patch(route('tratamientos.update', $tratamiento->id), $updateData);
        
        // 4. Verificar redirección exitosa
        $response->assertRedirect(route('tratamientos.show', $tratamiento->id));
        $response->assertSessionHas('success');
        
        // 5. Verificar que los datos se actualizaron correctamente
        $tratamiento->refresh();
        $this->assertEquals('Control Hipertensión Actualizado', $tratamiento->nombre);
        $this->assertEquals('Control estricto de presión arterial', $tratamiento->objetivo);
        $this->assertEquals(2, $tratamiento->medicamentos->count());
        
        // Verificar primer medicamento actualizado
        $firstMed = $tratamiento->medicamentos->where('id', $this->medicamentos[0]->id)->first();
        $this->assertEquals(2, $firstMed->pivot->dosis_cantidad);
        $this->assertEquals('tabletas', $firstMed->pivot->unidad_dosis);
        $this->assertEquals(12, $firstMed->pivot->frecuencia_horas);
        
        // Verificar segundo medicamento agregado
        $secondMed = $tratamiento->medicamentos->where('id', $this->medicamentos[1]->id)->first();
        $this->assertEquals(1, $secondMed->pivot->dosis_cantidad);
        $this->assertEquals(24, $secondMed->pivot->frecuencia_horas);
    }

    /** @test */
    public function it_can_edit_prn_treatment_workflow()
    {
        // Crear tratamiento PRN
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control Dolor PRN',
            'tipo' => 'PRN',
            'estado' => 'Activo',
            'objetivo' => 'Manejo del dolor según necesidad',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        // Asociar medicamento PRN inicial
        $tratamiento->medicamentos()->attach($this->medicamentos[0]->id, [
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'intervalo_minimo_horas' => 6,
            'dosis_maxima_dia' => 4,
            'dosis_maxima_consecutiva' => 2,
            'instrucciones_especiales' => 'Solo si dolor severo',
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        $this->actingAs($this->user);
        
        // Editar tratamiento PRN
        $updateData = [
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control Dolor PRN Actualizado',
            'tipo' => 'PRN',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
            'medicamentos' => [
                [
                    'medicamento_id' => $this->medicamentos[0]->id,
                    'dosis_cantidad' => '2',
                    'unidad_dosis' => 'tabletas',
                    'intervalo_minimo_horas' => '4',
                    'dosis_maxima_dia' => '6',
                    'dosis_maxima_consecutiva' => '3',
                    'instrucciones_especiales' => 'Para dolor moderado a severo',
                    'orden' => '1',
                ],
            ],
        ];
        
        $response = $this->patch(route('tratamientos.update', $tratamiento->id), $updateData);
        
        $response->assertRedirect(route('tratamientos.show', $tratamiento->id));
        
        // Verificar datos PRN actualizados
        $tratamiento->refresh();
        $medicamento = $tratamiento->medicamentos->first();
        $this->assertEquals(2, $medicamento->pivot->dosis_cantidad);
        $this->assertEquals(4, $medicamento->pivot->intervalo_minimo_horas);
        $this->assertEquals(6, $medicamento->pivot->dosis_maxima_dia);
        $this->assertEquals(3, $medicamento->pivot->dosis_maxima_consecutiva);
    }

    /** @test */
    public function it_can_change_treatment_type_from_programmed_to_prn()
    {
        // Crear tratamiento programado
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Tratamiento Inicial',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        $tratamiento->medicamentos()->attach($this->medicamentos[0]->id, [
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'frecuencia_horas' => 8,
            'tolerancia_antes_minutos' => 30,
            'tolerancia_despues_minutos' => 60,
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        $this->actingAs($this->user);
        
        // Cambiar a PRN
        $updateData = [
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Tratamiento Convertido a PRN',
            'tipo' => 'PRN',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
            'medicamentos' => [
                [
                    'medicamento_id' => $this->medicamentos[0]->id,
                    'dosis_cantidad' => '1',
                    'unidad_dosis' => 'tableta',
                    'intervalo_minimo_horas' => '6',
                    'dosis_maxima_dia' => '4',
                    'dosis_maxima_consecutiva' => '2',
                    'instrucciones_especiales' => 'Según necesidad',
                    'orden' => '1',
                ],
            ],
        ];
        
        $response = $this->patch(route('tratamientos.update', $tratamiento->id), $updateData);
        
        $response->assertRedirect(route('tratamientos.show', $tratamiento->id));
        
        // Verificar cambio de tipo
        $tratamiento->refresh();
        $this->assertEquals('PRN', $tratamiento->tipo);
        $this->assertEquals('Tratamiento Convertido a PRN', $tratamiento->nombre);
        
        // Verificar que se configuró correctamente para PRN
        $medicamento = $tratamiento->medicamentos->first();
        $this->assertEquals(6, $medicamento->pivot->intervalo_minimo_horas);
        $this->assertEquals(4, $medicamento->pivot->dosis_maxima_dia);
        $this->assertNull($medicamento->pivot->frecuencia_horas);
    }

    /** @test */
    public function it_validates_treatment_edit_data_correctly()
    {
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Test Treatment',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        $this->actingAs($this->user);
        
        // Test con datos inválidos
        $invalidData = [
            'paciente_id' => 999, // ID inexistente
            'medico_usuario_id' => 999, // ID inexistente
            'nombre' => '', // Vacío
            'tipo' => 'InvalidType',
            'estado' => 'InvalidState',
            'fecha_inicio' => 'invalid-date',
            'fecha_fin' => '2020-01-01', // Anterior a fecha inicio
            'medicamentos' => [
                [
                    'medicamento_id' => 999, // ID inexistente
                    'dosis_cantidad' => 'invalid',
                    'unidad_dosis' => '',
                    'frecuencia_horas' => -1, // Negativo
                ],
            ],
        ];
        
        $response = $this->patch(route('tratamientos.update', $tratamiento->id), $invalidData);
        
        $response->assertSessionHasErrors([
            'paciente_id',
            'medico_usuario_id',
            'nombre',
            'tipo',
            'fecha_inicio',
            'medicamentos.0.medicamento_id',
            'medicamentos.0.dosis_cantidad',
            'medicamentos.0.unidad_dosis',
        ]);
    }

    /** @test */
    public function it_preserves_unchanged_data_during_edit()
    {
        $originalData = [
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Tratamiento Original',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'objetivo' => 'Objetivo original',
            'diagnostico' => 'Diagnóstico original',
            'fecha_inicio' => '2024-01-01',
            'fecha_fin' => '2024-02-01',
            'observaciones' => 'Observaciones originales',
        ];
        
        $tratamiento = Tratamiento::create($originalData);
        
        $this->actingAs($this->user);
        
        // Actualizar solo el nombre
        $updateData = array_merge($originalData, [
            'nombre' => 'Tratamiento Actualizado',
            'medicamentos' => [],
        ]);
        
        $response = $this->patch(route('tratamientos.update', $tratamiento->id), $updateData);
        
        $response->assertRedirect(route('tratamientos.show', $tratamiento->id));
        
        // Verificar que solo cambió el nombre
        $tratamiento->refresh();
        $this->assertEquals('Tratamiento Actualizado', $tratamiento->nombre);
        $this->assertEquals('Objetivo original', $tratamiento->objetivo);
        $this->assertEquals('Diagnóstico original', $tratamiento->diagnostico);
        $this->assertEquals('2024-01-01', $tratamiento->fecha_inicio->format('Y-m-d'));
        $this->assertEquals('Observaciones originales', $tratamiento->observaciones);
    }
} 