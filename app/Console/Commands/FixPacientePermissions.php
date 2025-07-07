<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permiso;

class FixPacientePermissions extends Command
{
    protected $signature = 'meditrack:fix-paciente-permissions';
    protected $description = 'Crear y asignar permisos faltantes para el rol de paciente';

    public function handle()
    {
        $this->info('🔧 Arreglando permisos para pacientes...');

        // Permisos que necesitan los pacientes
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

        // Obtener el rol de paciente
        $rolePaciente = Role::where('nombre', 'paciente')->first();
        
        if (!$rolePaciente) {
            $this->error('❌ Rol de paciente no encontrado');
            return Command::FAILURE;
        }

        $this->info("✅ Rol de paciente encontrado: {$rolePaciente->nombre}");

        $created = 0;
        $assigned = 0;
        $existing = 0;

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
                $this->line("  ✨ Creado permiso: {$nombre}");
                $created++;
            }
            
            // Verificar si el rol ya tiene este permiso
            if (!$rolePaciente->permisos()->where('nombre', $nombre)->exists()) {
                $rolePaciente->permisos()->attach($permiso->id);
                $this->line("  🔗 Asignado a paciente: {$nombre}");
                $assigned++;
            } else {
                $this->line("  ✅ Ya existe: {$nombre}");
                $existing++;
            }
        }

        // Mostrar resumen
        $this->newLine();
        $this->info('📊 RESUMEN:');
        $this->line("  Permisos creados: {$created}");
        $this->line("  Permisos asignados: {$assigned}");
        $this->line("  Permisos existentes: {$existing}");
        
        // Verificar permisos finales
        $rolePaciente->load('permisos');
        $totalPermisos = $rolePaciente->permisos->count();
        $this->newLine();
        $this->info("🎯 Total de permisos del paciente: {$totalPermisos}");
        
        $this->newLine();
        $this->info('✅ ¡Permisos de paciente arreglados exitosamente!');
        
        return Command::SUCCESS;
    }
} 