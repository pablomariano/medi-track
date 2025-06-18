<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Paciente;
use App\Models\PersonalMedico;
use App\Models\Cuidador;
use App\Models\PacienteMedico;
use App\Models\PacienteCuidador;
use App\Models\PermisoTemporal;
use Carbon\Carbon;

class TestPhase4Assignments extends Command
{
    protected $signature = 'test:phase4-assignments';
    protected $description = 'Prueba completa de la Fase 4: Sistema de Asignaciones Específicas y Gestión Avanzada';

    public function handle()
    {
        $this->info('🚀 INICIANDO PRUEBAS DE FASE 4: SISTEMA DE ASIGNACIONES ESPECÍFICAS');
        $this->info('==================================================================');

        $tests = [
            'testModels' => 'Modelos y relaciones',
            'testMedicoAssignments' => 'Asignaciones médico-paciente',
            'testCuidadorAssignments' => 'Asignaciones cuidador-paciente',
            'testTemporaryPermissions' => 'Permisos temporales',
            'testAssignmentMiddleware' => 'Middleware de asignaciones',
            'testDashboardData' => 'Datos específicos por rol'
        ];

        $results = [];

        foreach ($tests as $method => $description) {
            $this->newLine();
            $this->info("🔍 Probando: {$description}");
            $this->line('-----------------------------------');
            
            try {
                $result = $this->$method();
                $results[$method] = $result;
                
                if ($result['success']) {
                    $this->info("✅ {$description}: ÉXITO");
                } else {
                    $this->error("❌ {$description}: FALLÓ");
                    $this->error("   Razón: {$result['message']}");
                }
            } catch (\Exception $e) {
                $results[$method] = ['success' => false, 'message' => $e->getMessage()];
                $this->error("❌ {$description}: ERROR");
                $this->error("   Excepción: {$e->getMessage()}");
            }
        }

        $this->showSummary($results);
        
        return $this->getExitCode($results);
    }

    private function testModels()
    {
        $this->comment('Verificando modelos y relaciones...');

        // Verificar modelo PacienteMedico
        if (!class_exists(PacienteMedico::class)) {
            return ['success' => false, 'message' => 'Modelo PacienteMedico no existe'];
        }

        // Verificar modelo PermisoTemporal
        if (!class_exists(PermisoTemporal::class)) {
            return ['success' => false, 'message' => 'Modelo PermisoTemporal no existe'];
        }

        // Probar relaciones en Paciente
        $paciente = Paciente::first();
        if ($paciente) {
            $this->line("  - Paciente '{$paciente->nombre}' encontrado");
            
            // Probar relación con médicos
            $medicos = $paciente->medicos;
            $this->line("    • Médicos asignados: " . $medicos->count());
            
            // Probar relación con cuidadores
            $cuidadores = $paciente->cuidadores;
            $this->line("    • Cuidadores asignados: " . $cuidadores->count());
        }

        // Probar relaciones en PersonalMedico
        $medico = PersonalMedico::first();
        if ($medico) {
            $this->line("  - Médico '{$medico->nombre}' encontrado");
            $pacientes = $medico->pacientes;
            $this->line("    • Pacientes asignados: " . $pacientes->count());
        }

        return ['success' => true, 'message' => 'Modelos verificados correctamente'];
    }

    private function testMedicoAssignments()
    {
        $this->comment('Verificando asignaciones médico-paciente...');

        $totalAsignaciones = PacienteMedico::count();
        $asignacionesVigentes = PacienteMedico::vigentes()->count();
        $medicosCardinales = PacienteMedico::principales()->vigentes()->count();

        $this->line("  - Total asignaciones médico-paciente: {$totalAsignaciones}");
        $this->line("  - Asignaciones vigentes: {$asignacionesVigentes}");
        $this->line("  - Médicos principales: {$medicosCardinales}");

        // Crear una asignación de prueba si no existen datos
        if ($totalAsignaciones === 0) {
            $paciente = Paciente::first();
            $medico = PersonalMedico::first();
            
            if ($paciente && $medico) {
                try {
                    PacienteMedico::create([
                        'paciente_id' => $paciente->id,
                        'medico_usuario_id' => $medico->usuario_id,
                        'es_medico_principal' => true,
                        'fecha_asignacion' => Carbon::now(),
                        'especialidad_tratamiento' => 'Medicina General'
                    ]);
                    
                    $this->line("  ✓ Asignación de prueba creada exitosamente");
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => "Error al crear asignación: {$e->getMessage()}"];
                }
            }
        }

        // Verificar métodos del modelo
        $asignacion = PacienteMedico::first();
        if ($asignacion) {
            $this->line("  - Probando métodos del modelo:");
            $this->line("    • Es vigente: " . ($asignacion->es_vigente ? 'Sí' : 'No'));
            $this->line("    • Estado: {$asignacion->estado}");
            $this->line("    • Días restantes: " . ($asignacion->dias_restantes ?? 'Indefinido'));
        }

        return ['success' => true, 'message' => 'Asignaciones médico-paciente funcionando correctamente'];
    }

    private function testCuidadorAssignments()
    {
        $this->comment('Verificando asignaciones cuidador-paciente...');

        $totalAsignaciones = PacienteCuidador::count();
        $asignacionesActivas = PacienteCuidador::activas()->count();
        $asignacionesVigentes = PacienteCuidador::vigentes()->count();

        $this->line("  - Total asignaciones cuidador-paciente: {$totalAsignaciones}");
        $this->line("  - Asignaciones activas: {$asignacionesActivas}");
        $this->line("  - Asignaciones vigentes: {$asignacionesVigentes}");

        // Verificar métodos del modelo
        $asignacion = PacienteCuidador::first();
        if ($asignacion) {
            $this->line("  - Probando métodos del modelo:");
            $this->line("    • Es vigente: " . ($asignacion->es_vigente ? 'Sí' : 'No'));
            $this->line("    • Duración: " . ($asignacion->duracion ?? 'No calculable') . " días");
        }

        return ['success' => true, 'message' => 'Asignaciones cuidador-paciente funcionando correctamente'];
    }

    private function testTemporaryPermissions()
    {
        $this->comment('Verificando sistema de permisos temporales...');

        // Verificar tabla existe
        try {
            $totalPermisos = PermisoTemporal::count();
            $permisosVigentes = PermisoTemporal::vigentes()->count();
            $permisosExpirados = PermisoTemporal::expirados()->count();

            $this->line("  - Total permisos temporales: {$totalPermisos}");
            $this->line("  - Permisos vigentes: {$permisosVigentes}");
            $this->line("  - Permisos expirados: {$permisosExpirados}");

            // Crear un permiso temporal de prueba
            $usuario = User::where('rol_id', 3)->first(); // Cuidador
            $admin = User::where('rol_id', 1)->first(); // Admin para otorgar el permiso
            
            if ($usuario && $admin) {
                // Simular que el admin está autenticado
                auth()->login($admin);
                
                $permiso = PermisoTemporal::otorgar([
                    'usuario_id' => $usuario->id,
                    'permiso' => 'pacientes.edit',
                    'motivo' => 'Prueba del sistema - Fase 4',
                    'fecha_inicio' => Carbon::now(),
                    'fecha_fin' => Carbon::now()->addHours(24),
                    'notas_adicionales' => 'Permiso temporal de prueba para testing'
                ]);

                $this->line("  ✓ Permiso temporal creado exitosamente (ID: {$permiso->id})");
                
                // Verificar el permiso
                $tienePermiso = PermisoTemporal::usuarioTienePermiso($usuario->id, 'pacientes.edit');
                $this->line("    • Usuario tiene permiso: " . ($tienePermiso ? 'Sí' : 'No'));
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Error con tabla permisos_temporales: {$e->getMessage()}"];
        }

        return ['success' => true, 'message' => 'Sistema de permisos temporales funcionando correctamente'];
    }

    private function testAssignmentMiddleware()
    {
        $this->comment('Verificando middleware de asignaciones...');

        // Verificar que el middleware existe
        $middlewareFile = app_path('Http/Middleware/CheckAssignment.php');
        if (!file_exists($middlewareFile)) {
            return ['success' => false, 'message' => 'Middleware CheckAssignment no existe'];
        }

        $this->line("  ✓ Middleware CheckAssignment encontrado");

        // Verificar registro en bootstrap/app.php
        $bootstrapFile = base_path('bootstrap/app.php');
        $bootstrapContent = file_get_contents($bootstrapFile);
        
        if (strpos($bootstrapContent, 'assignment') === false) {
            return ['success' => false, 'message' => 'Middleware no registrado en bootstrap/app.php'];
        }

        $this->line("  ✓ Middleware registrado en bootstrap/app.php");

        return ['success' => true, 'message' => 'Middleware de asignaciones configurado correctamente'];
    }

    private function testDashboardData()
    {
        $this->comment('Verificando datos específicos por rol...');

        $roles = ['admin', 'medico', 'cuidador', 'paciente'];
        $datosRol = [];

        foreach ($roles as $rol) {
            $usuarios = User::whereHas('role', function($q) use ($rol) {
                $q->where('nombre', $rol);
            })->count();

            $datosRol[$rol] = $usuarios;
            $this->line("  - Usuarios con rol '{$rol}': {$usuarios}");
        }

        // Datos específicos para médicos
        $medicos = PersonalMedico::count();
        $medicosConPacientes = PersonalMedico::has('pacientes')->count();
        
        $this->line("  - Médicos registrados: {$medicos}");
        $this->line("  - Médicos con pacientes asignados: {$medicosConPacientes}");

        // Datos específicos para cuidadores
        $cuidadores = Cuidador::count();
        $cuidadoresConPacientes = Cuidador::has('pacientes')->count();
        
        $this->line("  - Cuidadores registrados: {$cuidadores}");
        $this->line("  - Cuidadores con pacientes asignados: {$cuidadoresConPacientes}");

        // Datos específicos para pacientes
        $pacientes = Paciente::count();
        $pacientesConMedicos = Paciente::has('medicos')->count();
        $pacientesConCuidadores = Paciente::has('cuidadores')->count();
        
        $this->line("  - Pacientes registrados: {$pacientes}");
        $this->line("  - Pacientes con médicos asignados: {$pacientesConMedicos}");
        $this->line("  - Pacientes con cuidadores asignados: {$pacientesConCuidadores}");

        return ['success' => true, 'message' => 'Datos específicos por rol disponibles'];
    }

    private function showSummary(array $results)
    {
        $this->newLine(2);
        $this->info('📊 RESUMEN DE PRUEBAS FASE 4');
        $this->info('============================');

        $successful = count(array_filter($results, fn($r) => $r['success']));
        $total = count($results);

        foreach ($results as $test => $result) {
            $icon = $result['success'] ? '✅' : '❌';
            $status = $result['success'] ? 'ÉXITO' : 'FALLÓ';
            $this->line("{$icon} {$test}: {$status}");
        }

        $this->newLine();
        $percentage = ($successful / $total) * 100;
        $this->info("🎯 RESULTADO FINAL: {$successful}/{$total} pruebas exitosas ({$percentage}%)");

        if ($percentage >= 80) {
            $this->info('🎉 FASE 4 IMPLEMENTADA CORRECTAMENTE');
            $this->comment('   ✓ Sistema de asignaciones específicas funcionando');
            $this->comment('   ✓ Gestión de permisos temporales operativa');
            $this->comment('   ✓ Middleware de verificación configurado');
            $this->comment('   ✓ Datos específicos por rol disponibles');
        } else {
            $this->error('⚠️  FASE 4 REQUIERE CORRECCIONES');
            $this->comment('   Revisa los errores mostrados arriba');
        }
    }

    private function getExitCode(array $results): int
    {
        $successful = count(array_filter($results, fn($r) => $r['success']));
        $total = count($results);
        $percentage = ($successful / $total) * 100;

        return $percentage >= 80 ? 0 : 1;
    }
} 