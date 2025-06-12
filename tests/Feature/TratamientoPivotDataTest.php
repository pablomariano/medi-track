<?php

namespace Tests\Feature;

use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\Medicamento;
use App\Models\User;
use App\Models\Role;
use App\Services\HorarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TratamientoPivotDataTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $medico;
    protected $paciente;
    protected $medicamento;

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
    }

    /** @test */
    public function it_correctly_loads_pivot_data_when_accessing_medicamentos_relationship()
    {
        // Crear tratamiento con medicamento
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Test Treatment',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        // Asociar medicamento con datos del pivot
        $tratamiento->medicamentos()->attach($this->medicamento->id, [
            'dosis_cantidad' => 2.5,
            'unidad_dosis' => 'comprimidos',
            'frecuencia_horas' => 12,
            'tolerancia_antes_minutos' => 45,
            'tolerancia_despues_minutos' => 90,
            'instrucciones_especiales' => 'Con alimentos',
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        // Recargar con medicamentos (como hace el controlador)
        $tratamiento->load('medicamentos');
        
        // Verificar que el pivot data está disponible
        $medicamento = $tratamiento->medicamentos->first();
        $this->assertNotNull($medicamento);
        $this->assertNotNull($medicamento->pivot);
        
        // Verificar los datos específicos del pivot
        $this->assertEquals(2.5, $medicamento->pivot->dosis_cantidad);
        $this->assertEquals('comprimidos', $medicamento->pivot->unidad_dosis);
        $this->assertEquals(12, $medicamento->pivot->frecuencia_horas);
        $this->assertEquals(45, $medicamento->pivot->tolerancia_antes_minutos);
        $this->assertEquals(90, $medicamento->pivot->tolerancia_despues_minutos);
        $this->assertEquals('Con alimentos', $medicamento->pivot->instrucciones_especiales);
        $this->assertEquals('Activo', $medicamento->pivot->estado);
        $this->assertEquals(1, $medicamento->pivot->orden);
    }

    /** @test */
    public function it_handles_horario_service_with_loaded_pivot_data()
    {
        // Crear tratamiento programado
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Test Programado',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        // Asociar medicamento con frecuencia
        $tratamiento->medicamentos()->attach($this->medicamento->id, [
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'frecuencia_horas' => 8,
            'tolerancia_antes_minutos' => 30,
            'tolerancia_despues_minutos' => 60,
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        // Recargar medicamentos (importante para pivot data)
        $tratamiento->load('medicamentos');
        
        // Esto no debería dar error ahora
        $horarioService = new HorarioService();
        
        // El método debería funcionar sin errores
        $this->expectNotToPerformAssertions();
        $horarioService->generarHorariosProgramados($tratamiento);
    }

    /** @test */
    public function it_can_access_pivot_properties_after_treatment_load()
    {
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Test Properties',
            'tipo' => 'PRN',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        // Asociar con configuración PRN
        $tratamiento->medicamentos()->attach($this->medicamento->id, [
            'dosis_cantidad' => 0.5,
            'unidad_dosis' => 'ml',
            'intervalo_minimo_horas' => 4,
            'dosis_maxima_dia' => 6,
            'dosis_maxima_consecutiva' => 2,
            'instrucciones_especiales' => 'Solo si es necesario',
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        // Simular carga desde controlador
        $tratamiento = Tratamiento::with('medicamentos')->find($tratamiento->id);
        
        // Verificar acceso a propiedades PRN específicas
        $medicamento = $tratamiento->medicamentos->first();
        $this->assertEquals(0.5, $medicamento->pivot->dosis_cantidad);
        $this->assertEquals('ml', $medicamento->pivot->unidad_dosis);
        $this->assertEquals(4, $medicamento->pivot->intervalo_minimo_horas);
        $this->assertEquals(6, $medicamento->pivot->dosis_maxima_dia);
        $this->assertEquals(2, $medicamento->pivot->dosis_maxima_consecutiva);
        $this->assertEquals('Solo si es necesario', $medicamento->pivot->instrucciones_especiales);
    }

    /** @test */
    public function it_handles_multiple_medications_with_different_pivot_configurations()
    {
        // Crear segundo medicamento
        $medicamento2 = Medicamento::create([
            'nombre' => 'Ibuprofeno 400mg',
            'medida' => '400',
            'unidad_medida' => 'mg',
            'descripcion' => 'Antiinflamatorio no esteroideo',
        ]);
        
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Multiple Medications',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        // Asociar primer medicamento
        $tratamiento->medicamentos()->attach($this->medicamento->id, [
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'frecuencia_horas' => 8,
            'tolerancia_antes_minutos' => 30,
            'tolerancia_despues_minutos' => 60,
            'instrucciones_especiales' => 'Con comida',
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        // Asociar segundo medicamento con configuración diferente
        $tratamiento->medicamentos()->attach($medicamento2->id, [
            'dosis_cantidad' => 2,
            'unidad_dosis' => 'comprimidos',
            'frecuencia_horas' => 12,
            'tolerancia_antes_minutos' => 15,
            'tolerancia_despues_minutos' => 30,
            'instrucciones_especiales' => 'Con abundante agua',
            'estado' => 'Activo',
            'orden' => 2,
        ]);
        
        // Cargar tratamiento con medicamentos
        $tratamiento->load('medicamentos');
        
        // Verificar que ambos medicamentos tienen pivot data correcta
        $this->assertEquals(2, $tratamiento->medicamentos->count());
        
        $medicamentos = $tratamiento->medicamentos->keyBy('id');
        
        // Verificar primer medicamento
        $med1 = $medicamentos[$this->medicamento->id];
        $this->assertEquals(1, $med1->pivot->dosis_cantidad);
        $this->assertEquals('tableta', $med1->pivot->unidad_dosis);
        $this->assertEquals(8, $med1->pivot->frecuencia_horas);
        $this->assertEquals('Con comida', $med1->pivot->instrucciones_especiales);
        
        // Verificar segundo medicamento
        $med2 = $medicamentos[$medicamento2->id];
        $this->assertEquals(2, $med2->pivot->dosis_cantidad);
        $this->assertEquals('comprimidos', $med2->pivot->unidad_dosis);
        $this->assertEquals(12, $med2->pivot->frecuencia_horas);
        $this->assertEquals('Con abundante agua', $med2->pivot->instrucciones_especiales);
    }

    /** @test */
    public function it_properly_handles_empty_medicamentos_collection()
    {
        // Crear tratamiento sin medicamentos
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Empty Treatment',
            'tipo' => 'PRN',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        // Cargar medicamentos (colección vacía)
        $tratamiento->load('medicamentos');
        
        $this->assertEquals(0, $tratamiento->medicamentos->count());
        $this->assertTrue($tratamiento->medicamentos->isEmpty());
        
        // HorarioService debería manejar esto sin problemas
        $horarioService = new HorarioService();
        $this->expectNotToPerformAssertions();
        $horarioService->generarHorariosProgramados($tratamiento);
    }

    /** @test */
    public function it_correctly_updates_pivot_data_when_modifying_treatment()
    {
        $tratamiento = Tratamiento::create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'nombre' => 'Update Test',
            'tipo' => 'Programado',
            'estado' => 'Activo',
            'fecha_inicio' => now()->format('Y-m-d'),
        ]);
        
        // Configuración inicial
        $tratamiento->medicamentos()->attach($this->medicamento->id, [
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'frecuencia_horas' => 8,
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        // Simular actualización (detach + attach)
        $tratamiento->medicamentos()->detach();
        $tratamiento->medicamentos()->attach($this->medicamento->id, [
            'dosis_cantidad' => 2,
            'unidad_dosis' => 'comprimidos',
            'frecuencia_horas' => 12,
            'estado' => 'Activo',
            'orden' => 1,
        ]);
        
        // Recargar y verificar datos actualizados
        $tratamiento->load('medicamentos');
        $medicamento = $tratamiento->medicamentos->first();
        
        $this->assertEquals(2, $medicamento->pivot->dosis_cantidad);
        $this->assertEquals('comprimidos', $medicamento->pivot->unidad_dosis);
        $this->assertEquals(12, $medicamento->pivot->frecuencia_horas);
    }
} 