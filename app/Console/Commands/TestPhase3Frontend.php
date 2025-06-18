<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestPhase3Frontend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:phase3-frontend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la integración frontend de autorización (Fase 3)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎨 INICIANDO PRUEBAS DE FRONTEND - FASE 3');
        $this->info('==========================================');

        // Obtener usuarios de prueba
        $admin = User::whereHas('role', function($q) { $q->where('nombre', 'admin'); })->first();
        $medico = User::whereHas('role', function($q) { $q->where('nombre', 'medico'); })->first();
        $cuidador = User::whereHas('role', function($q) { $q->where('nombre', 'cuidador'); })->first();
        $paciente = User::whereHas('role', function($q) { $q->where('nombre', 'paciente'); })->first();

        if (!$admin || !$medico || !$cuidador || !$paciente) {
            $this->error('No se encontraron usuarios de prueba. Ejecute primero el seeder.');
            return;
        }

        $this->info("\n📋 Probando data para frontend:");

        // Probar cada usuario
        $this->testUserData($admin, 'Administrador');
        $this->testUserData($medico, 'Médico');
        $this->testUserData($cuidador, 'Cuidador');
        $this->testUserData($paciente, 'Paciente');

        // Verificar estructura de datos
        $this->testDataStructure();

        // Verificar componentes de autorización
        $this->testAuthorizationComponents();

        $this->info("\n✅ PRUEBAS DE FRONTEND COMPLETADAS");
    }

    private function testUserData($user, $roleName)
    {
        $this->info("\n🧪 Probando datos para: $roleName ({$user->name})");
        
        // Simular request con usuario autenticado
        $request = Request::create('/test');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Usar el middleware para obtener datos
        $middleware = new HandleInertiaRequests();
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('getUserWithPermissions');
        $method->setAccessible(true);
        
        $userData = $method->invoke($middleware, $request);

        if (!$userData) {
            $this->error("  ❌ No se pudieron obtener datos del usuario");
            return;
        }

        // Verificar estructura básica
        $this->info("  ✅ Datos básicos: ID {$userData['id']}, Email: {$userData['email']}");
        
        // Verificar rol
        if (isset($userData['role'])) {
            $this->info("  ✅ Rol: {$userData['role']['nombre']}");
        } else {
            $this->error("  ❌ No se encontró información de rol");
        }

        // Verificar permisos
        if (isset($userData['can_permissions']) && is_array($userData['can_permissions'])) {
            $permissionsCount = count($userData['can_permissions']);
            $this->info("  ✅ Permisos: $permissionsCount permisos disponibles");
            
            if ($permissionsCount > 0) {
                $this->info("    📝 Ejemplos: " . implode(', ', array_slice($userData['can_permissions'], 0, 3)));
            }
        } else {
            $this->error("  ❌ No se encontraron permisos en el formato esperado");
        }
    }

    private function testDataStructure()
    {
        $this->info("\n🏗️ VERIFICANDO ESTRUCTURA DE DATOS");
        
        $admin = User::whereHas('role', function($q) { $q->where('nombre', 'admin'); })->first();
        
        $request = Request::create('/test');
        $request->setUserResolver(function () use ($admin) {
            return $admin;
        });

        $middleware = new HandleInertiaRequests();
        $shareData = $middleware->share($request);

        // Verificar estructura auth
        if (isset($shareData['auth']['user'])) {
            $this->info("  ✅ Estructura auth.user presente");
            
            $userData = $shareData['auth']['user'];
            
            // Verificar campos requeridos para frontend
            $requiredFields = ['id', 'name', 'email', 'role', 'can_permissions'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($userData[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (empty($missingFields)) {
                $this->info("  ✅ Todos los campos requeridos están presentes");
            } else {
                $this->error("  ❌ Campos faltantes: " . implode(', ', $missingFields));
            }
        } else {
            $this->error("  ❌ Estructura auth.user no encontrada");
        }
    }

    private function testAuthorizationComponents()
    {
        $this->info("\n🔧 VERIFICANDO COMPONENTES DE AUTORIZACIÓN");
        
        // Verificar archivos de componentes
        $frontendFiles = [
            'resources/js/hooks/use-auth.tsx' => 'Hook de autorización principal',
            'resources/js/hooks/use-permissions.tsx' => 'Hook de permisos específicos',
            'resources/js/components/auth/CanAccess.tsx' => 'Componente de acceso condicional',
            'resources/js/components/auth/ProtectedButton.tsx' => 'Botones protegidos',
            'resources/js/components/auth/ProtectedLink.tsx' => 'Enlaces protegidos',
            'resources/js/components/protected-sidebar.tsx' => 'Sidebar dinámico',
            'resources/js/pages/Pacientes/ProtectedIndex.tsx' => 'Página de ejemplo protegida',
        ];

        foreach ($frontendFiles as $file => $description) {
            if (file_exists(base_path($file))) {
                $this->info("  ✅ $description");
            } else {
                $this->error("  ❌ Falta: $description ($file)");
            }
        }

        // Verificar tipos TypeScript
        $typesFile = base_path('resources/js/types/index.d.ts');
        if (file_exists($typesFile)) {
            $content = file_get_contents($typesFile);
            
            if (strpos($content, 'AuthContextType') !== false) {
                $this->info("  ✅ Tipos de autorización definidos");
            } else {
                $this->error("  ❌ Tipos de autorización no encontrados");
            }
        }
    }
}
