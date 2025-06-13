<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\MedicamentoTratamiento;
use App\Models\HorarioProgramado;
use App\Models\SintomaPrn;
use App\Models\Medicamento;

class DashboardDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎯 Generando datos específicos para dashboard...');
        
        // Limpiar datos anteriores
        $this->limpiarDatosAnteriores();
        
        // Generar administraciones para los últimos 7 días
        $this->generarAdministracionesSimple();
        
        // Generar estadísticas
        $this->generarEstadisticasConsumo();
        
        // Generar alertas
        $this->generarAlertas();
        
        $this->command->info('✅ Datos de dashboard generados exitosamente');
    }
    
    private function limpiarDatosAnteriores()
    {
        $fechaInicio = Carbon::now()->subDays(7);
        
        // Eliminar administraciones de los últimos 7 días
        DB::table('administraciones')
            ->where('fecha_hora_programada', '>=', $fechaInicio)
            ->orWhere('fecha_hora_administrada', '>=', $fechaInicio)
            ->delete();
            
        // Limpiar estadísticas
        DB::table('estadisticas_consumo')
            ->where('periodo_inicio', '>=', $fechaInicio->format('Y-m-d'))
            ->delete();
            
        $this->command->info('🧹 Datos anteriores limpiados');
    }
    
    private function generarAdministracionesSimple()
    {
        // Obtener algunos pacientes y medicamentos_tratamiento
        $pacientes = Paciente::take(3)->get();
        $medicamentoTratamientos = MedicamentoTratamiento::take(5)->get();
        
        if ($pacientes->isEmpty() || $medicamentoTratamientos->isEmpty()) {
            $this->command->info('⚠️ No hay pacientes o medicamentos tratamiento disponibles');
            return;
        }
        
        $administracionesGeneradas = 0;
        
        // Generar para cada día de los últimos 7 días
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $this->command->info("📅 Generando para: " . $fecha->format('Y-m-d (l)'));
            
            // Generar administraciones para este día
            $esFinde = $fecha->isWeekend();
            $numeroAdministraciones = $esFinde ? rand(8, 12) : rand(12, 18);
            
            for ($j = 0; $j < $numeroAdministraciones; $j++) {
                $paciente = $pacientes->random();
                $medicamentoTratamiento = $medicamentoTratamientos->random();
                
                // Crear horario de administración
                $horaAdministracion = $fecha->copy()
                    ->setHour(rand(6, 22))
                    ->setMinute(rand(0, 59));
                
                // Determinar si fue administrada o no (adherencia del 75-90%)
                $adherencia = $esFinde ? rand(70, 85) : rand(80, 95);
                $fueAdministrada = rand(1, 100) <= $adherencia;
                
                if ($fueAdministrada) {
                    // Calcular si fue tardía
                    $retrasoMinutos = rand(-30, 120); // Entre 30 min antes y 2h después
                    $horaReal = $horaAdministracion->copy()->addMinutes($retrasoMinutos);
                    $estado = abs($retrasoMinutos) <= 60 ? 'Administrada' : 'Tardía';
                    
                    DB::table('administraciones')->insert([
                        'medicamento_tratamiento_id' => $medicamentoTratamiento->id,
                        'horario_programado_id' => null,
                        'paciente_id' => $paciente->id,
                        'cuidador_usuario_id' => null,
                        'fecha_hora_programada' => $horaAdministracion,
                        'fecha_hora_administrada' => $horaReal,
                        'dosis_administrada' => $medicamentoTratamiento->dosis_cantidad,
                        'estado' => $estado,
                        'es_dentro_ventana_tolerancia' => abs($retrasoMinutos) <= 60,
                        'minutos_diferencia' => $retrasoMinutos,
                        'observaciones' => $this->generarObservacion($estado),
                        'created_at' => $horaReal,
                        'updated_at' => $horaReal,
                    ]);
                } else {
                    // Medicamento omitido
                    DB::table('administraciones')->insert([
                        'medicamento_tratamiento_id' => $medicamentoTratamiento->id,
                        'horario_programado_id' => null,
                        'paciente_id' => $paciente->id,
                        'cuidador_usuario_id' => null,
                        'fecha_hora_programada' => $horaAdministracion,
                        'fecha_hora_administrada' => $horaAdministracion,
                        'dosis_administrada' => 0,
                        'estado' => 'Omitida',
                        'es_dentro_ventana_tolerancia' => false,
                        'observaciones' => $this->generarMotivoOmision(),
                        'created_at' => $horaAdministracion,
                        'updated_at' => $horaAdministracion,
                    ]);
                }
                
                $administracionesGeneradas++;
            }
        }
        
        $this->command->info("💊 Generadas {$administracionesGeneradas} administraciones");
    }
    
    private function generarObservacion($estado)
    {
        $observaciones = [
            'Administrada' => [
                'Medicamento tomado correctamente',
                'Sin efectos adversos reportados',
                'Administración completada',
                'Paciente colaborativo'
            ],
            'Tardía' => [
                'Medicamento tomado con retraso',
                'Paciente se olvidó inicialmente',
                'Administración tardía por horario de trabajo',
                'Retraso por actividades familiares'
            ]
        ];
        
        return $observaciones[$estado][array_rand($observaciones[$estado])];
    }
    
    private function generarMotivoOmision()
    {
        $motivos = [
            'Paciente olvidó tomar medicamento',
            'Medicamento no disponible',
            'Paciente rechazó medicamento',
            'Efectos adversos previos',
            'Paciente durmiendo',
            'Paciente fuera de casa'
        ];
        
        return $motivos[array_rand($motivos)];
    }
    
    private function generarEstadisticasConsumo()
    {
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            
            $administraciones = DB::table('administraciones')
                ->whereDate('fecha_hora_programada', $fecha)
                ->get();
                
            $total = $administraciones->count();
            $administradas = $administraciones->whereIn('estado', ['Administrada', 'Tardía'])->count();
            $adherencia = $total > 0 ? ($administradas / $total) * 100 : 0;
            
            DB::table('estadisticas_consumo')->insert([
                'paciente_id' => Paciente::first()->id ?? 1,
                'medicamento_id' => Medicamento::first()->id ?? 1,
                'periodo_inicio' => $fecha->format('Y-m-d'),
                'periodo_fin' => $fecha->format('Y-m-d'),
                'adherencia_porcentaje' => round($adherencia, 2),
                'dosis_programadas' => $total,
                'dosis_administradas' => $administradas,
                'dosis_omitidas' => $total - $administradas,
                'calculated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('📊 Estadísticas de consumo actualizadas');
    }
    
    private function generarAlertas()
    {
        // Generar algunas alertas aleatorias
        $pacientes = Paciente::take(3)->get();
        
        foreach ($pacientes as $paciente) {
            if (rand(1, 100) <= 30) { // 30% probabilidad de alerta
                DB::table('alertas')->insert([
                    'paciente_id' => $paciente->id,
                    'tipo' => $this->obtenerTipoAlerta(),
                    'nivel' => $this->obtenerNivelAlerta(),
                    'mensaje' => $this->generarMensajeAlerta(),
                    'fecha_generada' => now()->subHours(rand(1, 24)),
                    'revisada' => rand(1, 100) <= 40, // 40% ya revisadas
                    'fecha_revision' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $this->command->info('🚨 Alertas generadas');
    }
    
    private function obtenerTipoAlerta()
    {
        $tipos = ['Dosis_Omitida', 'Fuera_Ventana', 'Efecto_Adverso'];
        return $tipos[array_rand($tipos)];
    }
    
    private function obtenerNivelAlerta()
    {
        $niveles = ['Info', 'Advertencia', 'Critica'];
        return $niveles[array_rand($niveles)];
    }
    
    private function generarMensajeAlerta()
    {
        $mensajes = [
            'Adherencia por debajo del 80% en los últimos 3 días',
            'Medicamento próximo a vencer',
            'Múltiples dosis omitidas detectadas',
            'Patrón irregular en administración'
        ];
        
        return $mensajes[array_rand($mensajes)];
    }
} 