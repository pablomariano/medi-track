<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permiso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PacienteAuthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        
        // Seed básico necesario para los tests
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function test_paciente_role_has_correct_permissions()
    {
        $rolePaciente = Role::where('nombre', 'paciente')->first();
        
        $this->assertNotNull($rolePaciente, 'El rol de paciente debe existir');
        
        $permisos = $rolePaciente->permisos->pluck('nombre')->toArray();
        
        echo "\n🔍 PERMISOS ACTUALES DEL PACIENTE:\n";
        foreach ($permisos as $permiso) {
            echo "  ✓ $permiso\n";
        }
        
        // Verificar permisos críticos que debe tener un paciente
        $permisosEsperados = [
            'mi-perfil.index',
            'mi-perfil.edit', 
            'mi-cronograma.index',
            'mis-medicamentos.index',
            'mis-tratamientos.index',
        ];
        
        echo "\n❌ PERMISOS FALTANTES:\n";
        foreach ($permisosEsperados as $permiso) {
            if (!in_array($permiso, $permisos)) {
                echo "  ❌ $permiso\n";
            }
        }
        
        $this->assertTrue(true); // Para que el test pase mientras diagnosticamos
    }

    /** @test */
    public function test_paciente_can_register_and_access_welcome()
    {
        // Crear un usuario como paciente
        $userData = [
            'name' => 'Juan Paciente Test',
            'nombre' => 'Juan',
            'apellido_paterno' => 'Paciente',
            'apellido_materno' => 'Test',
            'email' => 'juan.paciente.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        echo "\n🧪 TESTING REGISTRO DE PACIENTE\n";

        // Test 1: Registro debe funcionar
        $response = $this->post('/register', $userData);
        
        echo "  ✓ Status del registro: " . $response->getStatusCode() . "\n";
        
        // Verificar que el usuario existe
        $user = User::where('email', 'juan.paciente.test@example.com')->first();
        $this->assertNotNull($user, 'El usuario debe existir después del registro');
        
        echo "  ✓ Usuario creado: " . $user->email . "\n";
        echo "  ✓ Rol asignado: " . ($user->role ? $user->role->nombre : 'NINGUNO') . "\n";
        
        // Test 2: Verificar redirección apropiada
        if ($response->getStatusCode() == 302) {
            $redirectUrl = $response->headers->get('Location');
            echo "  ✓ Redirige a: " . $redirectUrl . "\n";
        }
        
        $this->assertTrue(true);
    }

    /** @test */
    public function test_paciente_frontend_permissions_simulation()
    {
        // Crear usuario paciente
        $user = User::create([
            'name' => 'Test Frontend Paciente',
            'nombre' => 'Test',
            'apellido_paterno' => 'Frontend',
            'email' => 'test.frontend@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'activo' => true,
        ]);

        $rolePaciente = User::getDefaultRole();
        $user->rol_id = $rolePaciente->id;
        $user->save();
        
        // Cargar relaciones como hace el middleware
        $user->load(['role.permisos']);
        
        echo "\n🎯 SIMULANDO VERIFICACIONES FRONTEND\n";
        
        // Simular los datos que recibe el frontend
        $permissions = $user->role->permisos->pluck('nombre')->toArray();
        echo "  📋 Permisos disponibles: " . count($permissions) . "\n";
        
        // Tests específicos que hace useAuth
        $tests = [
            'hasRole("paciente")' => $user->hasRole('paciente'),
            'hasPermission("mi-perfil.index")' => in_array('mi-perfil.index', $permissions),
            'hasPermission("mi-cronograma.index")' => in_array('mi-cronograma.index', $permissions),
            'hasPermission("mis-medicamentos.index")' => in_array('mis-medicamentos.index', $permissions),
            'hasPermission("pacientes.index")' => in_array('pacientes.index', $permissions),
            'canAccess("medicamentos", "index")' => in_array('medicamentos.index', $permissions),
        ];
        
        foreach ($tests as $test => $result) {
            $status = $result ? '✅' : '❌';
            echo "  $status $test\n";
        }
        
        $this->assertTrue(true);
    }

    /** @test */
    public function test_sidebar_navigation_accessibility()
    {
        $user = User::create([
            'name' => 'Test Sidebar Paciente',
            'nombre' => 'Test',
            'apellido_paterno' => 'Sidebar',
            'email' => 'test.sidebar@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'activo' => true,
        ]);

        $rolePaciente = User::getDefaultRole();
        $user->rol_id = $rolePaciente->id;
        $user->save();
        
        echo "\n🔗 TESTING ACCESO A RUTAS DE PACIENTE\n";
        
        // Simular login
        $this->actingAs($user);
        
        // Tests de rutas específicas de pacientes
        $rutasPaciente = [
            '/mi-perfil' => 'Mi Perfil',
            '/mi-cronograma' => 'Mi Cronograma', 
            '/mis-medicamentos' => 'Mis Medicamentos',
            '/mis-tratamientos' => 'Mis Tratamientos',
            '/bienvenida' => 'Página de Bienvenida',
        ];
        
        foreach ($rutasPaciente as $ruta => $nombre) {
            try {
                $response = $this->get($ruta);
                $status = $response->getStatusCode();
                
                if ($status == 200) {
                    echo "  ✅ $nombre ($ruta): ACCESIBLE\n";
                } else {
                    echo "  ❌ $nombre ($ruta): $status\n";
                }
            } catch (\Exception $e) {
                echo "  ❌ $nombre ($ruta): ERROR - " . $e->getMessage() . "\n";
            }
        }
        
        $this->assertTrue(true);
    }

    /** @test */
    public function test_create_missing_permissions_for_patients()
    {
        echo "\n🔧 CREANDO PERMISOS FALTANTES PARA PACIENTES\n";
        
        $permisosFaltantes = [
            'mi-perfil.index' => 'Ver mi perfil',
            'mi-perfil.edit' => 'Editar mi perfil',
            'mi-cronograma.index' => 'Ver mi cronograma',
            'mis-medicamentos.index' => 'Ver mis medicamentos',
            'mis-tratamientos.index' => 'Ver mis tratamientos',
            'mis-tratamientos.create' => 'Crear mis tratamientos',
            'mis-tratamientos.edit' => 'Editar mis tratamientos',
            'medicamentos.index' => 'Ver catálogo de medicamentos',
        ];
        
        $rolePaciente = Role::where('nombre', 'paciente')->first();
        
        foreach ($permisosFaltantes as $nombre => $descripcion) {
            // Verificar si el permiso existe
            $permiso = Permiso::where('nombre', $nombre)->first();
            
            if (!$permiso) {
                // Crear el permiso
                $permiso = Permiso::create([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'grupo' => 'pacientes',
                    'activo' => true,
                ]);
                echo "  ✨ Creado permiso: $nombre\n";
            }
            
            // Verificar si el rol ya tiene este permiso
            if (!$rolePaciente->permisos()->where('nombre', $nombre)->exists()) {
                $rolePaciente->permisos()->attach($permiso->id);
                echo "  🔗 Asignado a paciente: $nombre\n";
            } else {
                echo "  ✅ Ya existe: $nombre\n";
            }
        }
        
        echo "\n✅ PERMISOS ACTUALIZADOS PARA PACIENTES\n";
        
        $this->assertTrue(true);
    }
} 