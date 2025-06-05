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
use Illuminate\Support\Facades\DB;

class UnifiedUserSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeder de roles
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        
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
    public function flujo_completo_creacion_medico()
    {
        // 1. Acceder a la página de selección de tipo
        $response = $this->actingAs($this->adminUser)
            ->get(route('usuarios.select-type'));
        $response->assertStatus(200);

        // 2. Navegar a la creación de médico
        $response = $this->actingAs($this->adminUser)
            ->get(route('usuarios.create-by-type', ['tipo' => 'medico']));
        $response->assertStatus(200);

        // 3. Crear médico con datos completos
        $medicoData = [
            'tipo_usuario' => 'medico',
            'user_data' => [
                'name' => 'Dr. Juan Integración',
                'email' => 'juan.integracion@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'telefono' => '+56912345678',
                'activo' => true,
                'email_verificado' => false
            ],
            'specific_data' => [
                'especialidad' => 'Cardiología Intervencionista',
                'numero_colegiatura' => 'INT-12345',
                'institucion' => 'Hospital de Integración',
                'anos_experiencia' => 15
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), $medicoData);

        // 4. Verificar redirección exitosa
        $response->assertRedirect(route('personal-medico.index'));
        $response->assertSessionHas('success', 'Personal médico creado exitosamente.');

        // 5. Verificar integridad en base de datos
        $user = User::where('email', 'juan.integracion@test.com')->first();
        $this->assertNotNull($user);
        
        // Verificar datos del usuario
        $this->assertEquals('Dr. Juan Integración', $user->name);
        $this->assertEquals('+56912345678', $user->telefono);
        $this->assertTrue($user->activo);
        $this->assertNull($user->email_verified_at); // No verificado inicialmente
        $this->assertEquals('medico', $user->role->nombre);
        
        // Verificar password encriptado
        $this->assertTrue(\Hash::check('password123', $user->password));
        
        // Verificar datos específicos del médico
        $personalMedico = PersonalMedico::where('usuario_id', $user->id)->first();
        $this->assertNotNull($personalMedico);
        $this->assertEquals('Cardiología Intervencionista', $personalMedico->especialidad);
        $this->assertEquals('INT-12345', $personalMedico->numero_colegiatura);
        $this->assertEquals('Hospital de Integración', $personalMedico->institucion);
        $this->assertEquals(15, $personalMedico->anos_experiencia);

        // 6. Verificar que aparece en el listado de personal médico
        $response = $this->actingAs($this->adminUser)
            ->get(route('personal-medico.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function flujo_completo_creacion_paciente_con_y_sin_usuario()
    {
        // Crear paciente CON usuario
        $pacienteConUsuario = [
            'tipo_usuario' => 'paciente',
            'user_data' => [
                'name' => 'Ana Paciente Completa',
                'email' => 'ana.completa@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'telefono' => '+56987654321',
                'activo' => true,
                'email_verificado' => true
            ],
            'specific_data' => [
                'nombre' => 'Ana Paciente Completa',
                'fecha_nacimiento' => '1985-03-15',
                'genero_id' => 'F',
                'numero_documento' => '12.345.678-9',
                'tipo_documento' => 'rut',
                'tipo_sangre' => 'A+',
                'altura' => 162,
                'direccion' => 'Av. Principal 123, Santiago',
                'telefono_emergencia' => '+56911111111',
                'observaciones_medicas' => 'Alergia a la penicilina',
                'activo' => true
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), $pacienteConUsuario);

        $response->assertRedirect(route('pacientes.index'));
        
        // Verificar paciente con usuario
        $paciente1 = Paciente::where('numero_documento', '12.345.678-9')->first();
        $this->assertNotNull($paciente1);
        $this->assertNotNull($paciente1->usuario_id);
        
        $usuario1 = User::find($paciente1->usuario_id);
        $this->assertNotNull($usuario1);
        $this->assertEquals('paciente', $usuario1->role->nombre);
        $this->assertNotNull($usuario1->email_verified_at); // Verificado según el form

        // Crear paciente SIN usuario
        $pacienteSinUsuario = [
            'tipo_usuario' => 'paciente',
            'user_data' => [
                'name' => '',
                'email' => '',
                'password' => '',
                'password_confirmation' => ''
            ],
            'specific_data' => [
                'nombre' => 'Pedro Paciente Sin Usuario',
                'fecha_nacimiento' => '1990-08-20',
                'genero_id' => 'M',
                'numero_documento' => '98.765.432-1',
                'tipo_documento' => 'rut',
                'activo' => true
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), $pacienteSinUsuario);

        $response->assertRedirect(route('pacientes.index'));
        
        // Verificar paciente sin usuario
        $paciente2 = Paciente::where('numero_documento', '98.765.432-1')->first();
        $this->assertNotNull($paciente2);
        $this->assertNull($paciente2->usuario_id);
    }

    /** @test */
    public function verifica_unicidad_de_campos_criticos()
    {
        // Crear primer médico
        $user1 = User::create([
            'name' => 'Dr. Primero',
            'email' => 'primero@test.com',
            'password' => bcrypt('password'),
            'rol_id' => Role::where('nombre', 'medico')->first()->id
        ]);
        
        PersonalMedico::create([
            'usuario_id' => $user1->id,
            'numero_colegiatura' => 'UNICO-123'
        ]);

        // Intentar crear segundo médico con misma colegiatura
        $medicoData = [
            'tipo_usuario' => 'medico',
            'user_data' => [
                'name' => 'Dr. Segundo',
                'email' => 'segundo@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            'specific_data' => [
                'numero_colegiatura' => 'UNICO-123' // Duplicado
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store-by-type'), $medicoData);

        $response->assertSessionHasErrors(['specific_data.numero_colegiatura']);
        
        // Verificar que no se creó el segundo usuario
        $this->assertNull(User::where('email', 'segundo@test.com')->first());
    }

    /** @test */
    public function manejo_de_errores_con_rollback_de_transacciones()
    {
        // Simular error eliminando temporalmente el rol después de la validación
        $originalMedicoRole = Role::where('nombre', 'medico')->first();
        
        $medicoData = [
            'tipo_usuario' => 'medico',
            'user_data' => [
                'name' => 'Dr. Error Test',
                'email' => 'error.test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            'specific_data' => [
                'especialidad' => 'Test'
            ]
        ];

        // Simular error en el proceso
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Simulated database error'));

        try {
            $response = $this->actingAs($this->adminUser)
                ->post(route('usuarios.store-by-type'), $medicoData);
        } catch (\Exception $e) {
            // Error esperado
        }

        // Verificar que no se creó ningún registro parcial
        $this->assertNull(User::where('email', 'error.test@example.com')->first());
        $this->assertEquals(0, PersonalMedico::where('especialidad', 'Test')->count());
    }

    /** @test */
    public function validacion_coherencia_roles_y_registros()
    {
        // Crear usuarios de diferentes tipos
        $tipos = [
            ['tipo' => 'medico', 'email' => 'medico@coherencia.test'],
            ['tipo' => 'cuidador', 'email' => 'cuidador@coherencia.test'],
            ['tipo' => 'apoderado', 'email' => 'apoderado@coherencia.test'],
            ['tipo' => 'admin', 'email' => 'admin@coherencia.test']
        ];

        foreach ($tipos as $tipoData) {
            $userData = [
                'tipo_usuario' => $tipoData['tipo'],
                'user_data' => [
                    'name' => ucfirst($tipoData['tipo']) . ' Coherencia',
                    'email' => $tipoData['email'],
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'activo' => true
                ],
                'specific_data' => $this->getSpecificDataForType($tipoData['tipo'])
            ];

            $response = $this->actingAs($this->adminUser)
                ->post(route('usuarios.store-by-type'), $userData);

            $response->assertRedirect(); // Debería redirigir sin errores
        }

        // Verificar coherencia final
        $medico = User::where('email', 'medico@coherencia.test')->first();
        $this->assertEquals('medico', $medico->role->nombre);
        $this->assertTrue(PersonalMedico::where('usuario_id', $medico->id)->exists());

        $cuidador = User::where('email', 'cuidador@coherencia.test')->first();
        $this->assertEquals('cuidador', $cuidador->role->nombre);
        $this->assertTrue(Cuidador::where('usuario_id', $cuidador->id)->exists());

        $apoderado = User::where('email', 'apoderado@coherencia.test')->first();
        $this->assertEquals('apoderado', $apoderado->role->nombre);
        $this->assertTrue(Apoderado::where('usuario_id', $apoderado->id)->exists());

        $admin = User::where('email', 'admin@coherencia.test')->first();
        $this->assertEquals('admin', $admin->role->nombre);
        // Los admins no tienen tabla específica
        $this->assertFalse(PersonalMedico::where('usuario_id', $admin->id)->exists());
        $this->assertFalse(Cuidador::where('usuario_id', $admin->id)->exists());
        $this->assertFalse(Apoderado::where('usuario_id', $admin->id)->exists());
    }

    /** @test */
    public function rendimiento_creacion_multiples_usuarios()
    {
        $startTime = microtime(true);
        
        // Crear 10 usuarios de diferentes tipos
        for ($i = 1; $i <= 10; $i++) {
            $tipo = ['medico', 'cuidador', 'apoderado', 'admin'][($i - 1) % 4];
            
            $userData = [
                'tipo_usuario' => $tipo,
                'user_data' => [
                    'name' => "{$tipo} Rendimiento {$i}",
                    'email' => "{$tipo}.rendimiento.{$i}@test.com",
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'activo' => true
                ],
                'specific_data' => $this->getSpecificDataForType($tipo, $i)
            ];

            $response = $this->actingAs($this->adminUser)
                ->post(route('usuarios.store-by-type'), $userData);

            $response->assertRedirect();
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Verificar que se completó en tiempo razonable (menos de 5 segundos)
        $this->assertLessThan(5.0, $executionTime, 'La creación de 10 usuarios tomó demasiado tiempo');
        
        // Verificar que todos los usuarios fueron creados
        $this->assertEquals(11, User::count()); // 10 creados + 1 admin de setup
    }

    private function getSpecificDataForType(string $tipo, int $index = 1): array
    {
        switch ($tipo) {
            case 'medico':
                return [
                    'especialidad' => 'Especialidad ' . $index,
                    'numero_colegiatura' => 'COL-' . $index,
                    'institucion' => 'Hospital ' . $index,
                    'anos_experiencia' => $index + 5
                ];
            case 'cuidador':
                return [
                    'certificaciones' => 'Certificación ' . $index,
                    'experiencia_anos' => $index + 2,
                    'disponibilidad_horaria' => 'Horario ' . $index,
                    'tarifa_hora' => 10000 + ($index * 1000)
                ];
            case 'apoderado':
                return [
                    'relacion_paciente' => 'Relación ' . $index,
                    'es_contacto_emergencia' => $index % 2 === 0
                ];
            default:
                return [];
        }
    }
} 