<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\CreateUserByTypeRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Genero;

class CreateUserByTypeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear géneros para tests (usando char(1) como ID)
        Genero::create(['id' => 'M', 'nombre' => 'Masculino']);
        Genero::create(['id' => 'F', 'nombre' => 'Femenino']);
    }

    /** @test */
    public function valida_datos_base_de_usuario_correctamente()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'admin',
            'user_data' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'telefono' => '+56912345678',
                'activo' => true,
                'email_verificado' => false
            ],
            'specific_data' => []
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function falla_validacion_con_email_duplicado()
    {
        // Crear un usuario existente
        \App\Models\User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password')
        ]);

        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'admin',
            'user_data' => [
                'name' => 'Test User',
                'email' => 'existing@example.com', // Email duplicado
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_data.email', $validator->errors()->toArray());
    }

    /** @test */
    public function valida_campos_especificos_de_medico()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'medico',
            'user_data' => [
                'name' => 'Dr. Test',
                'email' => 'doctor@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            'specific_data' => [
                'especialidad' => 'Cardiología',
                'numero_colegiatura' => '12345',
                'institucion' => 'Hospital Test',
                'anos_experiencia' => 10
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function falla_validacion_con_colegiatura_duplicada()
    {
        // Crear un personal médico existente
        $user = \App\Models\User::create([
            'name' => 'Dr. Existing',
            'email' => 'existing.doctor@example.com',
            'password' => bcrypt('password')
        ]);
        
        \App\Models\PersonalMedico::create([
            'usuario_id' => $user->id,
            'numero_colegiatura' => '12345'
        ]);

        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'medico',
            'user_data' => [
                'name' => 'Dr. Test',
                'email' => 'doctor@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            'specific_data' => [
                'numero_colegiatura' => '12345' // Colegiatura duplicada
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('specific_data.numero_colegiatura', $validator->errors()->toArray());
    }

    /** @test */
    public function valida_campos_especificos_de_cuidador()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'cuidador',
            'user_data' => [
                'name' => 'María Cuidadora',
                'email' => 'cuidadora@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            'specific_data' => [
                'certificaciones' => 'Primeros auxilios',
                'experiencia_anos' => 5,
                'disponibilidad_horaria' => 'Tiempo completo',
                'tarifa_hora' => 15000
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function valida_campos_especificos_de_paciente()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'paciente',
            'user_data' => [
                'name' => '', // Puede estar vacío para pacientes
                'email' => '', // Puede estar vacío para pacientes
                'password' => '',
                'password_confirmation' => ''
            ],
            'specific_data' => [
                'nombre' => 'Paciente Test',
                'fecha_nacimiento' => '1990-01-01',
                'genero_id' => 'M', // Usar char ID
                'numero_documento' => '12.345.678-9',
                'tipo_documento' => 'rut',
                'tipo_sangre' => 'O+',
                'altura' => 175,
                'activo' => true
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function requiere_usuario_para_paciente_si_se_proporciona_email()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'paciente',
            'user_data' => [
                'name' => 'Paciente con Usuario',
                'email' => 'paciente@example.com', // Se proporciona email
                'password' => '', // Pero no contraseña
                'password_confirmation' => ''
            ],
            'specific_data' => [
                'nombre' => 'Paciente Test'
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_data.password', $validator->errors()->toArray());
    }

    /** @test */
    public function falla_validacion_con_tipo_usuario_invalido()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'tipo_invalido',
            'user_data' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tipo_usuario', $validator->errors()->toArray());
    }

    /** @test */
    public function valida_campos_especificos_de_apoderado()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'apoderado',
            'user_data' => [
                'name' => 'Carlos Apoderado',
                'email' => 'apoderado@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            'specific_data' => [
                'relacion_paciente' => 'Padre',
                'es_contacto_emergencia' => true
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function falla_validacion_con_contraseñas_no_coincidentes()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => 'admin',
            'user_data' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different_password' // No coincide
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_data.password', $validator->errors()->toArray());
    }

    /** @test */
    public function mensajes_de_error_personalizados_funcionan()
    {
        $request = new CreateUserByTypeRequest();
        
        $data = [
            'tipo_usuario' => '', // Vacío para activar el error
            'user_data' => [
                'name' => '',
                'email' => 'invalid-email', // Email inválido
                'password' => 'short' // Contraseña muy corta
            ]
        ];

        $request->merge($data);
        $rules = $request->rules();
        $messages = $request->messages();
        $validator = Validator::make($data, $rules, $messages);

        $this->assertTrue($validator->fails());
        
        $errors = $validator->errors();
        $this->assertStringContainsString('Debe seleccionar un tipo de usuario', $errors->first('tipo_usuario'));
        $this->assertStringContainsString('El nombre es obligatorio', $errors->first('user_data.name'));
    }
} 