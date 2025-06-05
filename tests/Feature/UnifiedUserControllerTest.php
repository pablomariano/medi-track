<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Genero;
use App\Models\PersonalMedico;
use App\Models\Cuidador;
use App\Models\Apoderado;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

class UnifiedUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles necesarios
        Role::create(['nombre' => 'admin', 'descripcion' => 'Administrador', 'activo' => true]);
        Role::create(['nombre' => 'medico', 'descripcion' => 'Personal médico', 'activo' => true]);
        Role::create(['nombre' => 'cuidador', 'descripcion' => 'Cuidador', 'activo' => true]);
        Role::create(['nombre' => 'apoderado', 'descripcion' => 'Apoderado', 'activo' => true]);
        Role::create(['nombre' => 'paciente', 'descripcion' => 'Paciente', 'activo' => true]);
        
        // Crear géneros
        Genero::create(['id' => 'M', 'nombre' => 'Masculino']);
        Genero::create(['id' => 'F', 'nombre' => 'Femenino']);
        
        // Crear usuario admin para autenticación
        $adminRole = Role::where('nombre', 'admin')->first();
        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol_id' => $adminRole->id,
            'activo' => true,
            'email_verified_at' => now()
        ]);
    }

    /** @test */
    public function puede_acceder_a_la_pagina_de_seleccion_de_tipo()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('usuarios.select-type'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/SelectType')
                ->has('userTypes')
                ->where('userTypes.medico.label', 'Personal Médico')
                ->where('userTypes.cuidador.label', 'Cuidador')
                ->where('userTypes.apoderado.label', 'Apoderado')
                ->where('userTypes.paciente.label', 'Paciente')
                ->where('userTypes.admin.label', 'Administrador')
        );
    }

    /** @test */
    public function puede_acceder_a_la_pagina_de_creacion_por_tipo()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('usuarios.create-by-type', ['tipo' => 'medico']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/CreateByType')
                ->where('tipo', 'medico')
                ->has('tipoInfo')
                ->where('tipoInfo.label', 'Personal Médico')
        );
    }

    /** @test */
    public function redirige_si_tipo_es_invalido()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('usuarios.create-by-type', ['tipo' => 'tipo_invalido']));

        $response->assertRedirect(route('usuarios.select-type'));
        $response->assertSessionHas('error', 'Debe seleccionar un tipo de usuario válido.');
    }

    /** @test */
    public function puede_crear_medico_exitosamente()
    {
        $userData = [
            'name' => 'Dr. Test Médico',
            'email' => 'medico.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'telefono' => '+56912345678',
            'activo' => true,
            'email_verificado' => false
        ];

        $specificData = [
            'especialidad' => 'Cardiología',
            'numero_colegiatura' => '12345',
            'institucion' => 'Hospital Test',
            'anos_experiencia' => 10
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'medico',
                'user_data' => $userData,
                'specific_data' => $specificData
            ]);

        $response->assertRedirect(route('personal-medico.index'));
        $response->assertSessionHas('success', 'Personal médico creado exitosamente.');

        // Verificar que el usuario fue creado
        $user = User::where('email', 'medico.test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('medico', $user->role->nombre);

        // Verificar que el personal médico fue creado
        $personalMedico = PersonalMedico::where('usuario_id', $user->id)->first();
        $this->assertNotNull($personalMedico);
        $this->assertEquals('Cardiología', $personalMedico->especialidad);
    }

    /** @test */
    public function puede_crear_cuidador_exitosamente()
    {
        $userData = [
            'name' => 'María Cuidadora',
            'email' => 'cuidadora.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activo' => true
        ];

        $specificData = [
            'certificaciones' => 'Primeros auxilios',
            'experiencia_anos' => 5,
            'disponibilidad_horaria' => 'Tiempo completo',
            'tarifa_hora' => 15000
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'cuidador',
                'user_data' => $userData,
                'specific_data' => $specificData
            ]);

        $response->assertRedirect(route('cuidadores.index'));
        $response->assertSessionHas('success', 'Cuidador creado exitosamente.');

        // Verificar que el usuario fue creado
        $user = User::where('email', 'cuidadora.test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('cuidador', $user->role->nombre);

        // Verificar que el cuidador fue creado
        $cuidador = Cuidador::where('usuario_id', $user->id)->first();
        $this->assertNotNull($cuidador);
        $this->assertEquals(15000, $cuidador->tarifa_hora);
    }

    /** @test */
    public function puede_crear_apoderado_exitosamente()
    {
        $userData = [
            'name' => 'Carlos Apoderado',
            'email' => 'apoderado.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activo' => true
        ];

        $specificData = [
            'relacion_paciente' => 'Padre',
            'es_contacto_emergencia' => true
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'apoderado',
                'user_data' => $userData,
                'specific_data' => $specificData
            ]);

        $response->assertRedirect(route('apoderados.index'));
        $response->assertSessionHas('success', 'Apoderado creado exitosamente.');

        // Verificar que el usuario fue creado
        $user = User::where('email', 'apoderado.test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('apoderado', $user->role->nombre);

        // Verificar que el apoderado fue creado
        $apoderado = Apoderado::where('usuario_id', $user->id)->first();
        $this->assertNotNull($apoderado);
        $this->assertEquals('Padre', $apoderado->relacion_paciente);
        $this->assertTrue($apoderado->es_contacto_emergencia);
    }

    /** @test */
    public function puede_crear_paciente_con_usuario()
    {
        $userData = [
            'name' => 'Ana Paciente',
            'email' => 'paciente.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activo' => true
        ];

        $specificData = [
            'nombre' => 'Ana Paciente Test',
            'fecha_nacimiento' => '1990-05-15',
            'genero_id' => 'F',
            'numero_documento' => '12.345.678-9',
            'tipo_documento' => 'rut',
            'tipo_sangre' => 'O+',
            'altura' => 165,
            'activo' => true
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'paciente',
                'user_data' => $userData,
                'specific_data' => $specificData
            ]);

        $response->assertRedirect(route('pacientes.index'));
        $response->assertSessionHas('success', 'Paciente creado exitosamente.');

        // Verificar que el paciente fue creado
        $paciente = Paciente::where('nombre', 'Ana Paciente Test')->first();
        $this->assertNotNull($paciente);
        
        // Verificar que el usuario fue creado y asociado
        $this->assertNotNull($paciente->usuario_id);
        $user = User::find($paciente->usuario_id);
        $this->assertNotNull($user);
        $this->assertEquals('paciente', $user->role->nombre);
    }

    /** @test */
    public function puede_crear_paciente_sin_usuario()
    {
        $specificData = [
            'nombre' => 'Pedro Paciente Sin Usuario',
            'fecha_nacimiento' => '1995-08-20',
            'numero_documento' => '98.765.432-1',
            'tipo_documento' => 'rut',
            'activo' => true
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'paciente',
                'user_data' => [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'password_confirmation' => ''
                ],
                'specific_data' => $specificData
            ]);

        $response->assertRedirect(route('pacientes.index'));
        $response->assertSessionHas('success', 'Paciente creado exitosamente.');

        // Verificar que el paciente fue creado sin usuario
        $paciente = Paciente::where('nombre', 'Pedro Paciente Sin Usuario')->first();
        $this->assertNotNull($paciente);
        $this->assertNull($paciente->usuario_id);
    }

    /** @test */
    public function puede_crear_administrador()
    {
        $userData = [
            'name' => 'Nuevo Admin',
            'email' => 'nuevo.admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activo' => true
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'admin',
                'user_data' => $userData,
                'specific_data' => []
            ]);

        $response->assertRedirect(route('usuarios.index'));
        $response->assertSessionHas('success', 'Administrador creado exitosamente.');

        // Verificar que el usuario fue creado
        $user = User::where('email', 'nuevo.admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->role->nombre);
    }

    /** @test */
    public function falla_validacion_con_datos_invalidos()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'medico',
                'user_data' => [
                    'name' => '', // Requerido
                    'email' => 'invalid-email', // Email inválido
                    'password' => 'short', // Muy corta
                    'password_confirmation' => 'different' // No coincide
                ],
                'specific_data' => []
            ]);

        $response->assertSessionHasErrors([
            'user_data.name',
            'user_data.email', 
            'user_data.password'
        ]);
    }

    /** @test */
    public function falla_creacion_si_no_existe_rol()
    {
        // Eliminar el rol de médico para simular el error
        Role::where('nombre', 'medico')->delete();

        $userData = [
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'medico',
                'user_data' => $userData,
                'specific_data' => []
            ]);

        $response->assertSessionHas('error');
        
        // Verificar que no se creó el usuario
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNull($user);
    }

    /** @test */
    public function requiere_autenticacion()
    {
        $response = $this->get(route('usuarios.select-type'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('usuarios.create-by-type', ['tipo' => 'medico']));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('usuarios.store-by-type'), []);
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function incluye_generos_para_pacientes()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('usuarios.create-by-type', ['tipo' => 'paciente']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/CreateByType')
                ->where('tipo', 'paciente')
                ->has('generos')
                ->has('generos.0')
                ->where('generos.0.nombre', 'Masculino')
        );
    }

    /** @test */
    public function api_form_data_devuelve_datos_correctos_para_paciente()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('usuarios.form-data', ['tipo' => 'paciente']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'generos',
            'tiposDocumento',
            'tiposSangre'
        ]);

        $data = $response->json();
        $this->assertCount(2, $data['generos']); // Masculino y Femenino
        $this->assertCount(4, $data['tiposDocumento']); // RUT, CI, Passport, Otro
        $this->assertCount(8, $data['tiposSangre']); // A+, A-, B+, B-, AB+, AB-, O+, O-
    }
} 