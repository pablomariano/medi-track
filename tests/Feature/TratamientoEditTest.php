<?php

namespace Tests\Feature;

use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\Medicamento;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class TratamientoEditTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $medico;
    protected $paciente;
    protected $medicamento;
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
        
        // Crear medicamento
        $this->medicamento = Medicamento::create([
            'nombre' => 'Paracetamol 500mg',
            'medida' => '500',
            'unidad_medida' => 'mg',
            'descripcion' => 'Analgésico y antipirético',
        ]);
        
        // Crear tratamiento de prueba
        $this->tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control de Dolor',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'objetivo' => 'Controlar dolor crónico',
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addDays(30)->format('Y-m-d'),
        ]);
        
        // Asociar medicamento con configuración pivot
        $this->tratamiento->medicamentos()->attach($this->medicamento->id, [
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'frecuencia_horas' => 8,
            'tolerancia_antes_minutos' => 30,
            'tolerancia_despues_minutos' => 60,
            'instrucciones_especiales' => 'Tomar con comida',
            'estado' => 'Activo',
            'orden' => 1,
        ]);
    }

    /** @test */
    public function it_can_display_edit_form_for_treatment()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('tratamientos.edit', $this->tratamiento->id));
        
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Tratamientos/Edit')
                ->has('tratamiento')
                ->has('pacientes')
                ->has('medicos')
                ->has('medicamentos')
        );
    }

    /** @test */
    public function it_loads_treatment_with_medications_and_pivot_data()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('tratamientos.edit', $this->tratamiento->id));
        
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Tratamientos/Edit')
                ->where('tratamiento.id', $this->tratamiento->id)
                ->where('tratamiento.nombre', 'Control de Dolor')
                ->where('tratamiento.tipo', 'Programado')
                ->has('tratamiento.medicamentos', 1)
                ->where('tratamiento.medicamentos.0.id', $this->medicamento->id)
                ->where('tratamiento.medicamentos.0.nombre', 'Paracetamol 500mg')
                ->has('tratamiento.medicamentos.0.pivot')
                ->where('tratamiento.medicamentos.0.pivot.dosis_cantidad', '1.000')
                ->where('tratamiento.medicamentos.0.pivot.unidad_dosis', 'tableta')
                ->where('tratamiento.medicamentos.0.pivot.frecuencia_horas', 8)
                ->where('tratamiento.medicamentos.0.pivot.instrucciones_especiales', 'Tomar con comida')
        );
    }

    /** @test */
    public function it_can_update_treatment_basic_information()
    {
        $this->actingAs($this->user);
        
        $updateData = [
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control de Dolor Actualizado',
            'tipo' => 'PRN',
            'estado' => 'Pausado',
            'objetivo' => 'Objetivo actualizado',
            'diagnostico' => 'Diagnóstico actualizado',
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addDays(60)->format('Y-m-d'),
            'observaciones' => 'Observaciones actualizadas',
            'medicamentos' => [],
        ];
        
        $response = $this->patch(route('tratamientos.update', $this->tratamiento->id), $updateData);
        
        $response->assertRedirect(route('tratamientos.show', $this->tratamiento->id));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('tratamientos', [
            'id' => $this->tratamiento->id,
            'nombre' => 'Control de Dolor Actualizado',
            'tipo' => 'PRN',
            'estado' => 'Pausado',
            'objetivo' => 'Objetivo actualizado',
        ]);
    }

    /** @test */
    public function it_can_update_treatment_with_new_medications()
    {
        $this->actingAs($this->user);
        
        // Crear otro medicamento
        $medicamento2 = Medicamento::create([
            'nombre' => 'Ibuprofeno 400mg',
            'medida' => '400',
            'unidad_medida' => 'mg',
            'descripcion' => 'Antiinflamatorio no esteroideo',
        ]);
        
        $updateData = [
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control de Dolor',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
            'medicamentos' => [
                [
                    'medicamento_id' => $this->medicamento->id,
                    'dosis_cantidad' => '2',
                    'unidad_dosis' => 'tabletas',
                    'frecuencia_horas' => '12',
                    'tolerancia_antes_minutos' => '15',
                    'tolerancia_despues_minutos' => '30',
                    'instrucciones_especiales' => 'Tomar con abundante agua',
                    'orden' => '1',
                ],
                [
                    'medicamento_id' => $medicamento2->id,
                    'dosis_cantidad' => '1',
                    'unidad_dosis' => 'tableta',
                    'frecuencia_horas' => '8',
                    'tolerancia_antes_minutos' => '30',
                    'tolerancia_despues_minutos' => '60',
                    'instrucciones_especiales' => 'Tomar después de comida',
                    'orden' => '2',
                ],
            ],
        ];
        
        $response = $this->patch(route('tratamientos.update', $this->tratamiento->id), $updateData);
        
        $response->assertRedirect(route('tratamientos.show', $this->tratamiento->id));
        
        // Verificar que los medicamentos se actualizaron
        $this->tratamiento->refresh();
        $this->assertEquals(2, $this->tratamiento->medicamentos->count());
        
        $firstMedicamento = $this->tratamiento->medicamentos->where('id', $this->medicamento->id)->first();
        $this->assertEquals(2, $firstMedicamento->pivot->dosis_cantidad);
        $this->assertEquals('tabletas', $firstMedicamento->pivot->unidad_dosis);
        $this->assertEquals(12, $firstMedicamento->pivot->frecuencia_horas);
        
        $secondMedicamento = $this->tratamiento->medicamentos->where('id', $medicamento2->id)->first();
        $this->assertEquals(1, $secondMedicamento->pivot->dosis_cantidad);
        $this->assertEquals(8, $secondMedicamento->pivot->frecuencia_horas);
    }

    /** @test */
    public function it_validates_required_fields_on_update()
    {
        $this->actingAs($this->user);
        
        $response = $this->patch(route('tratamientos.update', $this->tratamiento->id), [
            // Enviando datos vacíos/inválidos
            'paciente_id' => '',
            'medico_usuario_id' => '',
            'nombre' => '',
            'tipo' => 'InvalidType',
            'fecha_inicio' => '',
        ]);
        
        $response->assertSessionHasErrors([
            'paciente_id',
            'medico_usuario_id', 
            'nombre',
            'tipo',
            'fecha_inicio',
        ]);
    }

    /** @test */
    public function it_validates_medication_data_on_update()
    {
        $this->actingAs($this->user);
        
        $updateData = [
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control de Dolor',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
            'medicamentos' => [
                [
                    'medicamento_id' => 999, // ID inexistente
                    'dosis_cantidad' => 'invalid', // Cantidad inválida
                    'unidad_dosis' => '',
                ],
            ],
        ];
        
        $response = $this->patch(route('tratamientos.update', $this->tratamiento->id), $updateData);
        
        $response->assertSessionHasErrors([
            'medicamentos.0.medicamento_id',
            'medicamentos.0.dosis_cantidad',
            'medicamentos.0.unidad_dosis',
        ]);
    }

    /** @test */
    public function it_handles_treatment_without_medications()
    {
        // Crear tratamiento sin medicamentos
        $tratamientoSinMeds = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Tratamiento Sin Medicamentos',
            'tipo' => 'PRN',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        $this->actingAs($this->user);
        
        $response = $this->get(route('tratamientos.edit', $tratamientoSinMeds->id));
        
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Tratamientos/Edit')
                ->where('tratamiento.id', $tratamientoSinMeds->id)
                ->where('tratamiento.medicamentos', [])
        );
    }

    /** @test */
    public function it_removes_all_medications_when_updating_with_empty_medications()
    {
        $this->actingAs($this->user);
        
        // Verificar que inicialmente tiene medicamentos
        $this->assertEquals(1, $this->tratamiento->medicamentos->count());
        
        $updateData = [
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Control de Dolor',
            'tipo' => 'PRN',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
            'medicamentos' => [], // Sin medicamentos
        ];
        
        $response = $this->patch(route('tratamientos.update', $this->tratamiento->id), $updateData);
        
        $response->assertRedirect();
        
        // Verificar que se removieron todos los medicamentos
        $this->tratamiento->refresh();
        $this->assertEquals(0, $this->tratamiento->medicamentos->count());
    }

    /** @test */
    public function it_requires_authentication_to_edit_treatment()
    {
        $response = $this->get(route('tratamientos.edit', $this->tratamiento->id));
        
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_requires_authentication_to_update_treatment()
    {
        $updateData = [
            'nombre' => 'Test Update',
        ];
        
        $response = $this->patch(route('tratamientos.update', $this->tratamiento->id), $updateData);
        
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_handles_nonexistent_treatment_on_edit()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('tratamientos.edit', 999));
        
        $response->assertStatus(404);
    }

    /** @test */
    public function it_handles_nonexistent_treatment_on_update()
    {
        $this->actingAs($this->user);
        
        $response = $this->patch(route('tratamientos.update', 999), [
            'nombre' => 'Test',
        ]);
        
        $response->assertStatus(404);
    }
} 