<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixUsersWithoutRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-roles {--dry-run : Mostrar qué usuarios serían afectados sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar rol por defecto (paciente) a usuarios que no tengan rol asignado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        // Buscar usuarios sin rol
        $usersWithoutRole = User::whereNull('rol_id')->get();
        
        if ($usersWithoutRole->isEmpty()) {
            $this->info('✅ Todos los usuarios tienen rol asignado.');
            return 0;
        }

        $defaultRole = User::getDefaultRole();
        
        if (!$defaultRole) {
            $this->error('❌ No se encontró el rol por defecto "paciente". Ejecuta las migraciones y seeders primero.');
            return 1;
        }

        $this->info("🔍 Encontrados {$usersWithoutRole->count()} usuarios sin rol asignado:");
        $this->newLine();

        $this->table(
            ['ID', 'Nombre', 'Email', 'Activo'],
            $usersWithoutRole->map(function ($user) {
                return [
                    $user->id,
                    $user->display_name ?? $user->name,
                    $user->email,
                    $user->activo ? 'Sí' : 'No'
                ];
            })
        );

        if ($isDryRun) {
            $this->warn("🔍 MODO DRY-RUN: No se realizaron cambios.");
            $this->info("💡 A estos usuarios se les asignaría el rol: {$defaultRole->nombre} ({$defaultRole->descripcion})");
            return 0;
        }

        if (!$this->confirm('¿Deseas asignar el rol por defecto "paciente" a estos usuarios?')) {
            $this->info('❌ Operación cancelada.');
            return 0;
        }

        $updated = 0;
        foreach ($usersWithoutRole as $user) {
            try {
                $user->update(['rol_id' => $defaultRole->id]);
                $updated++;
                $this->info("✅ Usuario {$user->email} actualizado con rol 'paciente'");
            } catch (\Exception $e) {
                $this->error("❌ Error actualizando usuario {$user->email}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("🎉 Proceso completado. {$updated} usuarios actualizados.");
        
        return 0;
    }
}
