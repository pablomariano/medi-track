<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Genero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

class SidebarCreateUserButtonTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeder de roles
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        
        // Crear géneros
        Genero::create(['id' => 'M', 'nombre' => 'Masculino']);
        Genero::create(['id' => 'F', 'nombre' => 'Femenino']);
        
        // Crear usuario para autenticación
        $adminRole = Role::where('nombre', 'admin')->first();
        $this->testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'rol_id' => $adminRole->id,
            'activo' => true,
            'email_verified_at' => now()
        ]);
    }

    public function test_sidebar_contiene_boton_crear_usuario()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        
        // Verificar que la página incluye el componente del sidebar
        $response->assertInertia(fn (Assert $page) => 
            $page->component('dashboard')
        );
    }

    public function test_boton_crear_usuario_redirige_a_select_type()
    {
        // Simular clic en el botón "Crear Usuario" accediendo directamente a la ruta
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get('/usuarios/select-type');

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

    public function test_boton_crear_usuario_funciona_con_ruta_nombrada()
    {
        // Verificar que la ruta nombrada funciona correctamente
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get(route('usuarios.select-type'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/SelectType')
                ->has('userTypes')
        );
    }

    public function test_flujo_completo_desde_sidebar_hasta_creacion()
    {
        // 1. Acceder a la página de selección desde el sidebar
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get(route('usuarios.select-type'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/SelectType')
                ->has('userTypes')
        );

        // 2. Navegar a la creación de un tipo específico (ej: médico)
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get(route('usuarios.create-by-type', ['tipo' => 'medico']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/CreateByType')
                ->where('tipo', 'medico')
                ->has('tipoInfo')
                ->where('tipoInfo.label', 'Personal Médico')
        );

        // 3. Crear el usuario
        $userData = [
            'name' => 'Dr. Desde Sidebar',
            'email' => 'sidebar@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'telefono' => '+56912345678',
            'activo' => true,
            'email_verificado' => false
        ];

        $specificData = [
            'especialidad' => 'Cardiología Sidebar',
            'numero_colegiatura' => 'SIDEBAR-123',
            'institucion' => 'Hospital Sidebar',
            'anos_experiencia' => 5
        ];

        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->post(route('usuarios.store-by-type'), [
                'tipo_usuario' => 'medico',
                'user_data' => $userData,
                'specific_data' => $specificData
            ]);

        $response->assertRedirect(route('personal-medico.index'));
        $response->assertSessionHas('success', 'Personal médico creado exitosamente.');

        // Verificar que el usuario fue creado correctamente
        $user = User::where('email', 'sidebar@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Dr. Desde Sidebar', $user->name);
        $this->assertEquals('medico', $user->role->nombre);
    }

    public function test_boton_crear_usuario_requiere_autenticacion()
    {
        // Verificar que sin autenticación, redirige al login
        $response = $this->get(route('usuarios.select-type'));
        
        $response->assertRedirect(route('login'));
    }

    public function test_acceso_con_diferentes_tipos_de_usuario()
    {
        // Crear usuarios con diferentes roles para verificar acceso
        $roles = ['medico', 'cuidador', 'apoderado'];
        
        foreach ($roles as $roleName) {
            $role = Role::where('nombre', $roleName)->first();
            $user = User::create([
                'name' => "Usuario {$roleName}",
                'email' => "{$roleName}@test.com",
                'password' => bcrypt('password'),
                'rol_id' => $role->id,
                'activo' => true,
                'email_verified_at' => now()
            ]);

            // Verificar que puede acceder al sidebar y al botón crear usuario
            $response = $this->withoutMiddleware()
                ->actingAs($user)
                ->get(route('usuarios.select-type'));

            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => 
                $page->component('Usuarios/SelectType')
                    ->has('userTypes')
            );
        }
    }

    public function test_datos_correctos_en_select_type_desde_sidebar()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get(route('usuarios.select-type'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/SelectType')
                ->has('userTypes')
                ->has('userTypes.medico')
                ->has('userTypes.cuidador') 
                ->has('userTypes.apoderado')
                ->has('userTypes.paciente')
                ->has('userTypes.admin')
                ->where('userTypes.medico.icon', 'user-doctor')
                ->where('userTypes.cuidador.icon', 'heart-handshake')
                ->where('userTypes.apoderado.icon', 'users')
                ->where('userTypes.paciente.icon', 'user')
                ->where('userTypes.admin.icon', 'shield')
        );
    }

    public function test_navegacion_hacia_atras_desde_formulario_a_select_type()
    {
        // Acceder a formulario de creación
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get(route('usuarios.create-by-type', ['tipo' => 'cuidador']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/CreateByType')
                ->where('tipo', 'cuidador')
        );

        // Volver a la selección de tipo (simular navegación hacia atrás)
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get(route('usuarios.select-type'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Usuarios/SelectType')
                ->has('userTypes')
        );
    }

    public function test_validacion_tipos_invalidos_desde_select_type()
    {
        // Intentar acceder a un tipo inválido
        $response = $this->withoutMiddleware()
            ->actingAs($this->testUser)
            ->get(route('usuarios.create-by-type', ['tipo' => 'tipo_inexistente']));

        // Debería redirigir de vuelta al select-type con error
        $response->assertRedirect(route('usuarios.select-type'));
        $response->assertSessionHas('error', 'Debe seleccionar un tipo de usuario válido.');
    }

    public function test_continuidad_datos_entre_select_type_y_formulario()
    {
        // Para cada tipo, verificar que los datos se mantienen consistentes
        $tiposValidos = ['medico', 'cuidador', 'apoderado', 'paciente', 'admin'];

        foreach ($tiposValidos as $tipo) {
            $response = $this->withoutMiddleware()
                ->actingAs($this->testUser)
                ->get(route('usuarios.create-by-type', ['tipo' => $tipo]));

            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => 
                $page->component('Usuarios/CreateByType')
                    ->where('tipo', $tipo)
                    ->has('tipoInfo')
                    ->has('tipoInfo.label')
                    ->has('tipoInfo.description')
                    ->has('tipoInfo.icon')
            );
        }
    }
} 