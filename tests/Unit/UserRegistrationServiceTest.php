<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserRegistrationService;
use App\Models\User;
use App\Models\Role;
use App\Models\PersonalMedico;
use App\Models\Cuidador;
use App\Models\Apoderado;
use App\Models\Paciente;
use App\Models\Genero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

class UserRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserRegistrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserRegistrationService();
        
        // Crear roles necesarios para los tests
        Role::create(['nombre' => 'admin', 'descripcion' => 'Administrador', 'activo' => true]);
        Role::create(['nombre' => 'medico', 'descripcion' => 'Personal médico', 'activo' => true]);
        Role::create(['nombre' => 'cuidador', 'descripcion' => 'Cuidador', 'activo' => true]);
        Role::create(['nombre' => 'apoderado', 'descripcion' => 'Apoderado', 'activo' => true]);
        Role::create(['nombre' => 'paciente', 'descripcion' => 'Paciente', 'activo' => true]);
        
        // Crear géneros para tests de pacientes (usando char(1) como ID)
        Genero::create(['id' => 'M', 'nombre' => 'Masculino']);
        Genero::create(['id' => 'F', 'nombre' => 'Femenino']);
    }

    /** @test */
    public function puede_crear_medico_con_usuario_y_datos_especificos()
    {
        $userData = [
            'name' => 'Dr. Juan Pérez',
            'email' => 'juan.perez@example.com',
            'password' => 'password123',
            'telefono' => '+56912345678',
            'activo' => true,
            'email_verified_at' => now()
        ];

        $medicoData = [
            'especialidad' => 'Cardiología',
            'numero_colegiatura' => '12345',
            'institucion' => 'Hospital General',
            'anos_experiencia' => 10
        ];

        $user = $this->service->createMedico($userData, $medicoData);

        // Verificar que el usuario fue creado
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Dr. Juan Pérez', $user->name);
        $this->assertEquals('juan.perez@example.com', $user->email);
        
        // Verificar que tiene el rol correcto
        $this->assertEquals('medico', $user->role->nombre);
        
        // Verificar que el registro de personal médico fue creado
        $personalMedico = PersonalMedico::where('usuario_id', $user->id)->first();
        $this->assertNotNull($personalMedico);
        $this->assertEquals('Cardiología', $personalMedico->especialidad);
        $this->assertEquals('12345', $personalMedico->numero_colegiatura);
        $this->assertEquals('Hospital General', $personalMedico->institucion);
        $this->assertEquals(10, $personalMedico->anos_experiencia);
    }

    /** @test */
    public function puede_crear_cuidador_con_usuario_y_datos_especificos()
    {
        $userData = [
            'name' => 'María González',
            'email' => 'maria.gonzalez@example.com',
            'password' => 'password123',
            'telefono' => '+56987654321',
            'activo' => true
        ];

        $cuidadorData = [
            'certificaciones' => 'Primeros auxilios, Cuidado de adultos mayores',
            'experiencia_anos' => 5,
            'disponibilidad_horaria' => 'Lunes a Viernes 08:00-18:00',
            'tarifa_hora' => 15000
        ];

        $user = $this->service->createCuidador($userData, $cuidadorData);

        // Verificar que el usuario fue creado
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('María González', $user->name);
        
        // Verificar que tiene el rol correcto
        $this->assertEquals('cuidador', $user->role->nombre);
        
        // Verificar que el registro de cuidador fue creado
        $cuidador = Cuidador::where('usuario_id', $user->id)->first();
        $this->assertNotNull($cuidador);
        $this->assertEquals('Primeros auxilios, Cuidado de adultos mayores', $cuidador->certificaciones);
        $this->assertEquals(5, $cuidador->experiencia_anos);
        $this->assertEquals(15000, $cuidador->tarifa_hora);
    }

    /** @test */
    public function puede_crear_apoderado_con_usuario_y_datos_especificos()
    {
        $userData = [
            'name' => 'Carlos Rodríguez',
            'email' => 'carlos.rodriguez@example.com',
            'password' => 'password123',
            'activo' => true
        ];

        $apoderadoData = [
            'relacion_paciente' => 'Padre',
            'es_contacto_emergencia' => true
        ];

        $user = $this->service->createApoderado($userData, $apoderadoData);

        // Verificar que el usuario fue creado
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Carlos Rodríguez', $user->name);
        
        // Verificar que tiene el rol correcto
        $this->assertEquals('apoderado', $user->role->nombre);
        
        // Verificar que el registro de apoderado fue creado
        $apoderado = Apoderado::where('usuario_id', $user->id)->first();
        $this->assertNotNull($apoderado);
        $this->assertEquals('Padre', $apoderado->relacion_paciente);
        $this->assertTrue($apoderado->es_contacto_emergencia);
    }

    /** @test */
    public function puede_crear_paciente_con_usuario()
    {
        $userData = [
            'name' => 'Ana López',
            'email' => 'ana.lopez@example.com',
            'password' => 'password123',
            'activo' => true
        ];

        $pacienteData = [
            'nombre' => 'Ana López',
            'fecha_nacimiento' => '1990-05-15',
            'genero_id' => 'F', // Usar char ID
            'numero_documento' => '12.345.678-9',
            'tipo_documento' => 'rut',
            'tipo_sangre' => 'O+',
            'altura' => 165,
            'direccion' => 'Calle Principal 123',
            'telefono_emergencia' => '+56911111111',
            'activo' => true
        ];

        $paciente = $this->service->createPaciente($pacienteData, $userData);

        // Verificar que el paciente fue creado
        $this->assertInstanceOf(Paciente::class, $paciente);
        $this->assertEquals('Ana López', $paciente->nombre);
        
        // Verificar que el usuario fue creado y asociado
        $this->assertNotNull($paciente->usuario_id);
        $user = User::find($paciente->usuario_id);
        $this->assertNotNull($user);
        $this->assertEquals('paciente', $user->role->nombre);
    }

    /** @test */
    public function puede_crear_paciente_sin_usuario()
    {
        $pacienteData = [
            'nombre' => 'Pedro Martínez',
            'fecha_nacimiento' => '1995-08-20',
            'numero_documento' => '98.765.432-1',
            'tipo_documento' => 'rut',
            'activo' => true
        ];

        $paciente = $this->service->createPaciente($pacienteData);

        // Verificar que el paciente fue creado sin usuario
        $this->assertInstanceOf(Paciente::class, $paciente);
        $this->assertEquals('Pedro Martínez', $paciente->nombre);
        $this->assertNull($paciente->usuario_id);
    }

    /** @test */
    public function puede_crear_administrador()
    {
        $userData = [
            'name' => 'Admin Sistema',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'activo' => true
        ];

        $user = $this->service->createAdmin($userData);

        // Verificar que el usuario fue creado
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Admin Sistema', $user->name);
        
        // Verificar que tiene el rol correcto
        $this->assertEquals('admin', $user->role->nombre);
    }

    /** @test */
    public function falla_al_crear_medico_si_no_existe_rol()
    {
        // Eliminar el rol de médico
        Role::where('nombre', 'medico')->delete();

        $userData = [
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $medicoData = [
            'especialidad' => 'Test'
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El rol de médico no existe en el sistema');

        $this->service->createMedico($userData, $medicoData);
    }

    /** @test */
    public function devuelve_tipos_de_usuario_disponibles()
    {
        $types = $this->service->getUserTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey('medico', $types);
        $this->assertArrayHasKey('cuidador', $types);
        $this->assertArrayHasKey('apoderado', $types);
        $this->assertArrayHasKey('paciente', $types);
        $this->assertArrayHasKey('admin', $types);

        // Verificar estructura de cada tipo
        foreach ($types as $type) {
            $this->assertArrayHasKey('label', $type);
            $this->assertArrayHasKey('description', $type);
            $this->assertArrayHasKey('icon', $type);
        }
    }

    /** @test */
    public function encripta_contraseñas_correctamente()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintext-password',
            'activo' => true
        ];

        $user = $this->service->createAdmin($userData);

        // Verificar que la contraseña fue encriptada
        $this->assertNotEquals('plaintext-password', $user->password);
        $this->assertTrue(\Hash::check('plaintext-password', $user->password));
    }

    /** @test */
    public function maneja_transacciones_correctamente_en_caso_de_error()
    {
        // Crear un escenario donde falle la creación del registro específico
        $userData = [
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $medicoData = [
            'numero_colegiatura' => null, // Esto podría causar un error de validación
            'especialidad' => str_repeat('x', 200) // Texto muy largo para causar error
        ];

        try {
            $this->service->createMedico($userData, $medicoData);
        } catch (\Exception $e) {
            // Verificar que el usuario no fue creado debido al rollback
            $user = User::where('email', 'test@example.com')->first();
            $this->assertNull($user);
        }
    }
} 