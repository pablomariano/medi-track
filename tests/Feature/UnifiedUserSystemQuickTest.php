<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Genero;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnifiedUserSystemQuickTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeder de roles
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        
        // Crear géneros
        Genero::create(['id' => 'M', 'nombre' => 'Masculino']);
        Genero::create(['id' => 'F', 'nombre' => 'Femenino']);
    }

    public function test_puede_crear_medico_exitosamente()
    {
        // Crear usuario admin para autenticación
        $adminRole = Role::where('nombre', 'admin')->first();
        $adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol_id' => $adminRole->id,
            'activo' => true,
            'email_verified_at' => now()
        ]);

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

        $response = $this->withoutMiddleware()
            ->actingAs($adminUser)
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
    }

    public function test_puede_crear_paciente_con_usuario()
    {
        // Crear usuario admin para autenticación
        $adminRole = Role::where('nombre', 'admin')->first();
        $adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol_id' => $adminRole->id,
            'activo' => true,
            'email_verified_at' => now()
        ]);

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

        $response = $this->withoutMiddleware()
            ->actingAs($adminUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'paciente',
                'user_data' => $userData,
                'specific_data' => $specificData
            ]);

        $response->assertRedirect(route('pacientes.index'));
        $response->assertSessionHas('success', 'Paciente creado exitosamente.');

        // Verificar que el paciente fue creado
        $paciente = \App\Models\Paciente::where('nombre', 'Ana Paciente Test')->first();
        $this->assertNotNull($paciente);
        
        // Verificar que el usuario fue creado y asociado
        $this->assertNotNull($paciente->usuario_id);
        $user = User::find($paciente->usuario_id);
        $this->assertNotNull($user);
        $this->assertEquals('paciente', $user->role->nombre);
    }

    public function test_puede_crear_paciente_sin_usuario()
    {
        // Crear usuario admin para autenticación
        $adminRole = Role::where('nombre', 'admin')->first();
        $adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol_id' => $adminRole->id,
            'activo' => true,
            'email_verified_at' => now()
        ]);

        $specificData = [
            'nombre' => 'Pedro Paciente Sin Usuario',
            'fecha_nacimiento' => '1995-08-20',
            'numero_documento' => '98.765.432-1',
            'tipo_documento' => 'rut',
            'activo' => true
        ];

        $response = $this->withoutMiddleware()
            ->actingAs($adminUser)
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
        $paciente = \App\Models\Paciente::where('nombre', 'Pedro Paciente Sin Usuario')->first();
        $this->assertNotNull($paciente);
        $this->assertNull($paciente->usuario_id);
    }

    public function test_validacion_coherencia_roles()
    {
        // Crear usuario admin para autenticación
        $adminRole = Role::where('nombre', 'admin')->first();
        $adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol_id' => $adminRole->id,
            'activo' => true,
            'email_verified_at' => now()
        ]);

        // Datos para crear diferentes tipos de usuarios
        $testData = [
            'medico' => [
                'user_data' => [
                    'name' => 'Dr. Coherencia',
                    'email' => 'medico@coherencia.test',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'activo' => true
                ],
                'specific_data' => [
                    'especialidad' => 'Test',
                    'numero_colegiatura' => 'COHERENCIA-1',
                    'institucion' => 'Hospital Test'
                ]
            ],
            'cuidador' => [
                'user_data' => [
                    'name' => 'Cuidador Coherencia',
                    'email' => 'cuidador@coherencia.test',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'activo' => true
                ],
                'specific_data' => [
                    'certificaciones' => 'Test',
                    'experiencia_anos' => 5,
                    'tarifa_hora' => 15000
                ]
            ]
        ];

        foreach ($testData as $tipo => $data) {
            $response = $this->withoutMiddleware()
                ->actingAs($adminUser)
                ->post(route('usuarios.store-by-type'), [
                    'tipo_usuario' => $tipo,
                    'user_data' => $data['user_data'],
                    'specific_data' => $data['specific_data']
                ]);

            $response->assertRedirect(); // Debería redirigir sin errores
        }

        // Verificar coherencia
        $medico = User::where('email', 'medico@coherencia.test')->first();
        $this->assertEquals('medico', $medico->role->nombre);
        $this->assertTrue(\App\Models\PersonalMedico::where('usuario_id', $medico->id)->exists());

        $cuidador = User::where('email', 'cuidador@coherencia.test')->first();
        $this->assertEquals('cuidador', $cuidador->role->nombre);
        $this->assertTrue(\App\Models\Cuidador::where('usuario_id', $cuidador->id)->exists());
    }
} 