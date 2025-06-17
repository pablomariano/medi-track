<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Permiso;

class TestPermissionsSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:permissions {--user-id= : ID del usuario a testear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testear el sistema de roles y permisos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Testeando el sistema de roles y permisos...');
        $this->newLine();

        // Test 1: Verificar relaciones entre modelos
        $this->testModelRelations();

        // Test 2: Verificar métodos de autorización
        $this->testAuthorizationMethods();

        // Test 3: Testear con usuario específico
        if ($userId = $this->option('user-id')) {
            $this->testSpecificUser($userId);
        }

        $this->newLine();
        $this->info('✅ Todos los tests completados!');
    }

    private function testModelRelations()
    {
        $this->info('📊 Test 1: Verificando relaciones entre modelos...');

        try {
            // Test relaciones Role -> Permisos
            $adminRole = Role::where('nombre', 'admin')->first();
            if ($adminRole) {
                $permisos = $adminRole->permisos;
                $this->line("   ✓ Role 'admin' tiene {$permisos->count()} permisos");
            }

            // Test relaciones Role -> Users
            $totalUsers = User::count();
            $this->line("   ✓ Total usuarios en sistema: {$totalUsers}");

            // Test usuarios con roles
            $usersWithRoles = User::whereNotNull('rol_id')->with('role')->get();
            $this->line("   ✓ Usuarios con roles asignados: {$usersWithRoles->count()}");

            foreach ($usersWithRoles->take(3) as $user) {
                $this->line("     - {$user->name} ({$user->email}) -> {$user->role->nombre}");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error en relaciones: " . $e->getMessage());
            return false;
        }

        $this->info('   ✅ Relaciones funcionando correctamente');
        $this->newLine();
        return true;
    }

    private function testAuthorizationMethods()
    {
        $this->info('🔑 Test 2: Verificando métodos de autorización...');

        try {
            $user = User::whereNotNull('rol_id')->with('role.permisos')->first();
            
            if (!$user) {
                $this->error('   ❌ No hay usuarios con roles para testear');
                return false;
            }

            $this->line("   Testeando usuario: {$user->name} ({$user->role->nombre})");
            
            // Test hasRole
            $hasRole = $user->hasRole($user->role->nombre);
            $this->line("   ✓ hasRole('{$user->role->nombre}'): " . ($hasRole ? 'true' : 'false'));

            // Test isAdmin
            $isAdmin = $user->isAdmin();
            $this->line("   ✓ isAdmin(): " . ($isAdmin ? 'true' : 'false'));

            // Test hasPermission con permisos existentes
            $firstPermission = $user->role->permisos->first();
            if ($firstPermission) {
                $hasPermission = $user->hasPermission($firstPermission->nombre);
                $this->line("   ✓ hasPermission('{$firstPermission->nombre}'): " . ($hasPermission ? 'true' : 'false'));
            }

            // Test hasAnyPermission
            $somePermissions = ['usuarios.index', 'pacientes.index'];
            $hasAnyPermission = $user->hasAnyPermission($somePermissions);
            $this->line("   ✓ hasAnyPermission(['usuarios.index', 'pacientes.index']): " . ($hasAnyPermission ? 'true' : 'false'));

            // Test getAllPermissions
            $allPerms = $user->getAllPermissions();
            $this->line("   ✓ getAllPermissions(): {$allPerms->count()} permisos");

        } catch (\Exception $e) {
            $this->error("   ❌ Error en métodos de autorización: " . $e->getMessage());
            return false;
        }

        $this->info('   ✅ Métodos de autorización funcionando correctamente');
        $this->newLine();
        return true;
    }

    private function testSpecificUser($userId)
    {
        $this->info("👤 Test 3: Verificando usuario específico (ID: {$userId})...");

        try {
            $user = User::with('role.permisos')->find($userId);
            
            if (!$user) {
                $this->error("   ❌ Usuario con ID {$userId} no encontrado");
                return false;
            }

            $this->table(
                ['Propiedad', 'Valor'],
                [
                    ['ID', $user->id],
                    ['Nombre', $user->name],
                    ['Email', $user->email],
                    ['Activo', $user->activo ? 'Sí' : 'No'],
                    ['Rol', $user->role?->nombre ?? 'Sin rol'],
                    ['Permisos', $user->getAllPermissions()->count()],
                    ['Es Admin', $user->isAdmin() ? 'Sí' : 'No'],
                ]
            );

            if ($user->role) {
                $this->info('   Permisos del usuario:');
                foreach ($user->getAllPermissions()->take(10) as $permiso) {
                    $this->line("     - {$permiso->nombre} ({$permiso->modulo})");
                }
                
                if ($user->getAllPermissions()->count() > 10) {
                    $this->line("     ... y " . ($user->getAllPermissions()->count() - 10) . " más");
                }
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error testeando usuario específico: " . $e->getMessage());
            return false;
        }

        $this->info('   ✅ Test de usuario específico completado');
        return true;
    }
}
