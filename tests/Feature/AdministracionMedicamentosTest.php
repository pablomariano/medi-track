<?php

use App\Models\User;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Medicamento;
use App\Models\Administracion;
use App\Models\HorarioProgramado;
use App\Models\MedicamentoTratamiento;
use App\Models\Role;
use Carbon\Carbon;

describe('Medication Administration Feature', function () {
    
    beforeEach(function () {
        // Crear roles
        $this->cuidadorRole = Role::factory()->create(['name' => 'cuidador']);
        $this->medicoRole = Role::factory()->create(['name' => 'medico']);
        
        // Crear usuarios
        $this->cuidador = User::factory()->create();
        $this->cuidador->roles()->attach($this->cuidadorRole);
        
        $this->medico = User::factory()->create();
        $this->medico->roles()->attach($this->medicoRole);
        
        // Crear entidades base
        $this->paciente = Paciente::factory()->create();
        $this->medicamento = Medicamento::factory()->create(['nombre' => 'Paracetamol 500mg']);
        
        // Crear tratamiento programado
        $this->tratamientoProgramado = Tratamiento::factory()->create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'tipo' => Tratamiento::TIPO_PROGRAMADO,
            'estado' => Tratamiento::ESTADO_ACTIVO
        ]);

        // Crear tratamiento PRN
        $this->tratamientoPrn = Tratamiento::factory()->create([
            'paciente_id' => $this->paciente->id,
            'medico_usuario_id' => $this->medico->id,
            'tipo' => Tratamiento::TIPO_PRN,
            'estado' => Tratamiento::ESTADO_ACTIVO
        ]);
    });

    describe('Programmed Medication Administration', function () {
        beforeEach(function () {
            // Configurar medicamento programado
            $this->medicamentoTratamiento = MedicamentoTratamiento::factory()->create([
                'tratamiento_id' => $this->tratamientoProgramado->id,
                'medicamento_id' => $this->medicamento->id,
                'dosis_cantidad' => 500,
                'unidad_dosis' => 'mg',
                'frecuencia_horas' => 8,
                'tolerancia_antes_minutos' => 30,
                'tolerancia_despues_minutos' => 30
            ]);

            // Crear horario programado para hoy
            $this->horario = HorarioProgramado::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'hora_programada' => '08:00:00',
                'fecha_programada' => today()
            ]);

            // Crear administración pendiente
            $this->administracionPendiente = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoTratamiento->id,
                'horario_programado_id' => $this->horario->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_programada' => today()->setTime(8, 0),
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);
        });

        it('allows caregiver to administer scheduled medication on time', function () {
            $this->actingAs($this->cuidador);
            
            $administrationTime = today()->setTime(8, 15); // Within tolerance
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $this->administracionPendiente->id,
                'fecha_hora_administrada' => $administrationTime->format('Y-m-d H:i:s'),
                'dosis_administrada' => 500,
                'observaciones' => 'Administrada sin problemas'
            ]);

            $response->assertStatus(201);
            
            $this->assertDatabaseHas('administraciones', [
                'id' => $this->administracionPendiente->id,
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'es_dentro_ventana_tolerancia' => true,
                'dosis_administrada' => 500
            ]);
        });

        it('marks administration as late when outside tolerance window', function () {
            $this->actingAs($this->cuidador);
            
            $administrationTime = today()->setTime(9, 30); // 1.5 hours late
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $this->administracionPendiente->id,
                'fecha_hora_administrada' => $administrationTime->format('Y-m-d H:i:s'),
                'dosis_administrada' => 500,
                'observaciones' => 'Retraso por emergencia médica'
            ]);

            $response->assertStatus(201);
            
            $this->assertDatabaseHas('administraciones', [
                'id' => $this->administracionPendiente->id,
                'estado' => Administracion::ESTADO_TARDIA,
                'es_dentro_ventana_tolerancia' => false,
                'minutos_diferencia' => 90 // 1.5 hours
            ]);
        });

        it('allows partial dose administration with justification', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $this->administracionPendiente->id,
                'fecha_hora_administrada' => now()->format('Y-m-d H:i:s'),
                'dosis_administrada' => 250, // Half dose
                'observaciones' => 'Paciente vomitó la mitad de la dosis'
            ]);

            $response->assertStatus(201);
            
            $this->assertDatabaseHas('administraciones', [
                'id' => $this->administracionPendiente->id,
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 250
            ]);
        });

        it('allows marking dose as omitted with reason', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->patch(route('administraciones.omit', $this->administracionPendiente), [
                'motivo_omision' => 'Paciente rechazó medicamento',
                'observaciones' => 'Paciente refiere náuseas previas'
            ]);

            $response->assertStatus(200);
            
            $this->assertDatabaseHas('administraciones', [
                'id' => $this->administracionPendiente->id,
                'estado' => Administracion::ESTADO_OMITIDA
            ]);
        });

        it('records adverse effects when reported', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $this->administracionPendiente->id,
                'fecha_hora_administrada' => now()->format('Y-m-d H:i:s'),
                'dosis_administrada' => 500,
                'efectos_adversos' => 'Náuseas leves después de 30 minutos',
                'observaciones' => 'Efectos remitieron con reposo'
            ]);

            $response->assertStatus(201);
            
            $this->assertDatabaseHas('administraciones', [
                'id' => $this->administracionPendiente->id,
                'efectos_adversos' => 'Náuseas leves después de 30 minutos'
            ]);
        });

        it('validates required fields for administration', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $this->administracionPendiente->id,
                // Missing required fields
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors([
                'fecha_hora_administrada',
                'dosis_administrada'
            ]);
        });
    });

    describe('PRN Medication Administration', function () {
        beforeEach(function () {
            // Configurar medicamento PRN
            $this->medicamentoPrn = MedicamentoTratamiento::factory()->create([
                'tratamiento_id' => $this->tratamientoPrn->id,
                'medicamento_id' => $this->medicamento->id,
                'dosis_cantidad' => 500,
                'unidad_dosis' => 'mg',
                'intervalo_minimo_horas' => 8,
                'dosis_maxima_dia' => 3000,
                'dosis_maxima_consecutiva' => 2
            ]);
        });

        it('allows PRN administration when criteria met', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor',
                'intensidad_sintoma' => 7, // Scale 1-10
                'criterio_cumplido' => true,
                'dosis_administrada' => 500,
                'observaciones' => 'Dolor abdominal intenso reportado por paciente'
            ]);

            $response->assertStatus(201);
            
            $this->assertDatabaseHas('administraciones', [
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'intensidad_sintoma' => 7,
                'criterio_cumplido' => true
            ]);
        });

        it('prevents PRN administration when minimum interval not met', function () {
            // Create recent administration
            Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now()->subHours(4), // 4 hours ago, minimum is 8
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 500
            ]);

            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor',
                'intensidad_sintoma' => 8,
                'dosis_administrada' => 500
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['intervalo_minimo']);
        });

        it('prevents PRN administration when daily maximum exceeded', function () {
            // Create administrations that reach daily limit
            Administracion::factory()->count(6)->create([
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now()->subHours(rand(1, 23)),
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 500 // 6 * 500 = 3000mg (daily limit)
            ]);

            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor',
                'intensidad_sintoma' => 9,
                'dosis_administrada' => 500
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['dosis_maxima_dia']);
        });

        it('prevents PRN administration when criteria not met', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor',
                'intensidad_sintoma' => 3, // Below threshold
                'criterio_cumplido' => false,
                'observaciones' => 'Dolor leve, criterio no cumplido'
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['criterio_cumplido']);
        });

        it('tracks consecutive PRN administrations', function () {
            // First PRN administration
            $firstAdmin = Administracion::factory()->create([
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now()->subHours(8),
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 500
            ]);

            $this->actingAs($this->cuidador);
            
            // Second consecutive administration (should be allowed)
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor',
                'intensidad_sintoma' => 8,
                'dosis_administrada' => 500
            ]);

            $response->assertStatus(201);
            
            // Third consecutive attempt (should be blocked)
            $response = $this->post(route('administraciones.prn'), [
                'medicamento_tratamiento_id' => $this->medicamentoPrn->id,
                'paciente_id' => $this->paciente->id,
                'sintoma_reportado' => 'Dolor',
                'intensidad_sintoma' => 9,
                'dosis_administrada' => 500
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['dosis_consecutiva']);
        });
    });

    describe('Administration Schedule View', function () {
        beforeEach(function () {
            // Create multiple scheduled administrations
            $tomorrow = today()->addDay();
            
            Administracion::factory()->count(3)->create([
                'paciente_id' => $this->paciente->id,
                'fecha_hora_programada' => today()->setTime(8, 0),
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            Administracion::factory()->count(2)->create([
                'paciente_id' => $this->paciente->id,
                'fecha_hora_programada' => $tomorrow->setTime(8, 0),
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);
        });

        it('displays daily schedule for caregiver', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->get(route('cronograma.diario', ['fecha' => today()->format('Y-m-d')]));
            
            $response->assertStatus(200);
            $response->assertViewIs('cronograma.diario');
            
            $administraciones = $response->viewData('administraciones');
            expect($administraciones)->toHaveCount(3);
        });

        it('displays weekly schedule with proper filtering', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->get(route('cronograma.semanal'));
            
            $response->assertStatus(200);
            $response->assertViewIs('cronograma.semanal');
        });

        it('filters schedule by patient', function () {
            $otherPatient = Paciente::factory()->create();
            Administracion::factory()->create([
                'paciente_id' => $otherPatient->id,
                'fecha_hora_programada' => today()->setTime(8, 0),
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            $this->actingAs($this->cuidador);
            
            $response = $this->get(route('cronograma.diario', [
                'fecha' => today()->format('Y-m-d'),
                'paciente_id' => $this->paciente->id
            ]));
            
            $administraciones = $response->viewData('administraciones');
            expect($administraciones->every(fn($a) => $a->paciente_id === $this->paciente->id))->toBeTrue();
        });

        it('shows overdue administrations prominently', function () {
            // Create overdue administration
            Administracion::factory()->create([
                'paciente_id' => $this->paciente->id,
                'fecha_hora_programada' => now()->subHours(2),
                'estado' => Administracion::ESTADO_PENDIENTE
            ]);

            $this->actingAs($this->cuidador);
            
            $response = $this->get(route('cronograma.diario', ['fecha' => today()->format('Y-m-d')]));
            
            $response->assertStatus(200);
            $response->assertSee('overdue'); // CSS class or indicator
        });
    });

    describe('Administration History', function () {
        beforeEach(function () {
            // Create administration history
            Administracion::factory()->create([
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now()->subDays(1),
                'estado' => Administracion::ESTADO_ADMINISTRADA,
                'dosis_administrada' => 500
            ]);

            Administracion::factory()->create([
                'paciente_id' => $this->paciente->id,
                'fecha_hora_administrada' => now()->subDays(2),
                'estado' => Administracion::ESTADO_OMITIDA
            ]);
        });

        it('displays patient administration history', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->get(route('administraciones.historial', $this->paciente));
            
            $response->assertStatus(200);
            $response->assertViewIs('administraciones.historial');
            
            $historial = $response->viewData('historial');
            expect($historial)->toHaveCount(2);
        });

        it('filters history by date range', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->get(route('administraciones.historial', [
                'paciente' => $this->paciente,
                'fecha_inicio' => now()->subDays(1)->format('Y-m-d'),
                'fecha_fin' => now()->format('Y-m-d')
            ]));
            
            $historial = $response->viewData('historial');
            expect($historial)->toHaveCount(1);
        });

        it('filters history by medication', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->get(route('administraciones.historial', [
                'paciente' => $this->paciente,
                'medicamento_id' => $this->medicamento->id
            ]));
            
            $response->assertStatus(200);
        });
    });

    describe('Authorization and Security', function () {
        it('prevents unauthorized access to administration endpoints', function () {
            $unauthorizedUser = User::factory()->create();
            
            $this->actingAs($unauthorizedUser);
            
            $response = $this->post(route('administraciones.store'), []);
            
            $response->assertStatus(403);
        });

        it('prevents administration to patients not assigned to caregiver', function () {
            $otherPatient = Paciente::factory()->create();
            $otherAdmin = Administracion::factory()->create([
                'paciente_id' => $otherPatient->id
            ]);

            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $otherAdmin->id,
                'fecha_hora_administrada' => now()->format('Y-m-d H:i:s'),
                'dosis_administrada' => 500
            ]);

            $response->assertStatus(403);
        });

        it('logs all administration actions for audit', function () {
            $this->actingAs($this->cuidador);
            
            $response = $this->post(route('administraciones.store'), [
                'administracion_id' => $this->administracionPendiente->id,
                'fecha_hora_administrada' => now()->format('Y-m-d H:i:s'),
                'dosis_administrada' => 500
            ]);

            // Verify audit log entry was created
            $this->assertDatabaseHas('audit_logs', [
                'user_id' => $this->cuidador->id,
                'action' => 'medication_administered',
                'model_type' => 'App\Models\Administracion',
                'model_id' => $this->administracionPendiente->id
            ]);
        });
    });
}); 