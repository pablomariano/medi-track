<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Role;

class FixPacienteRegistros extends Command
{
    protected $signature = 'meditrack:fix-paciente-registros';
    protected $description = 'Crear registros faltantes en tabla pacientes para usuarios con rol paciente';

    public function handle()
    {
        $this->info('🔧 Arreglando registros de pacientes...');

        // Obtener usuarios con rol paciente
        $rolePaciente = Role::where('nombre', 'paciente')->first();
        
        if (!$rolePaciente) {
            $this->error('❌ Rol de paciente no encontrado');
            return Command::FAILURE;
        }

        $usuariosPacientes = User::where('rol_id', $rolePaciente->id)->get();
        
        $this->info("✅ Encontrados {$usuariosPacientes->count()} usuarios con rol paciente");

        $created = 0;
        $existing = 0;

        foreach ($usuariosPacientes as $user) {
            // Verificar si ya tiene registro en pacientes
            $existingPaciente = $user->pacientes()->first();
            
            if ($existingPaciente) {
                $this->line("  ✅ Ya existe: {$user->email}");
                $existing++;
                continue;
            }

            // Crear registro de paciente
            $paciente = Paciente::create([
                'usuario_id' => $user->id,
                'nombre' => $user->display_name,
                'fecha_nacimiento' => null,
                'genero_id' => null,
                'numero_documento' => null,
                'tipo_documento' => null,
                'tipo_sangre' => null,
                'altura' => null,
                'direccion' => null,
                'telefono_emergencia' => null,
                'observaciones_medicas' => 'Registro creado automáticamente durante migración',
                'activo' => true,
            ]);

            $this->line("  ✨ Creado registro para: {$user->email} (Paciente ID: {$paciente->id})");
            $created++;
        }

        // Mostrar resumen
        $this->newLine();
        $this->info('📊 RESUMEN:');
        $this->line("  Registros creados: {$created}");
        $this->line("  Registros existentes: {$existing}");
        $this->line("  Total usuarios pacientes: {$usuariosPacientes->count()}");
        
        $this->newLine();
        $this->info('✅ ¡Registros de pacientes arreglados exitosamente!');
        
        return Command::SUCCESS;
    }
} 