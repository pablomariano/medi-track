<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Paciente;
use App\Models\Medicamento;
use Carbon\Carbon;

class EstadisticasYAlertasSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Generando estadísticas y alertas basadas en administraciones...');
        
        // Generar estadísticas de consumo
        $this->generarEstadisticasConsumo();
        
        // Generar alertas basadas en patrones de administración
        $this->generarAlertas();
        
        $this->command->info('✅ Estadísticas y alertas generadas exitosamente');
    }
    
    private function generarEstadisticasConsumo(): void
    {
        $pacientes = Paciente::all();
        $medicamentos = Medicamento::all();
        
        $estadisticasGeneradas = 0;
        
        foreach ($pacientes as $paciente) {
            foreach ($medicamentos as $medicamento) {
                // Verificar si el paciente tiene administraciones de este medicamento
                $tieneAdministraciones = DB::table('administraciones')
                    ->join('medicamentos_tratamientos', 'administraciones.medicamento_tratamiento_id', '=', 'medicamentos_tratamientos.id')
                    ->where('administraciones.paciente_id', $paciente->id)
                    ->where('medicamentos_tratamientos.medicamento_id', $medicamento->id)
                    ->exists();
                
                if (!$tieneAdministraciones) continue;
                
                // Generar estadísticas mensuales
                $estadisticasGeneradas += $this->generarEstadisticasPorPeriodo($paciente, $medicamento, 'Mensual');
            }
        }
        
        $this->command->info("📊 Generadas {$estadisticasGeneradas} estadísticas de consumo");
    }
    
    private function generarEstadisticasPorPeriodo(Paciente $paciente, Medicamento $medicamento, string $tipoPeriodo): int
    {
        $fechaInicio = Carbon::now()->subDays(30);
        $fechaFin = Carbon::now();
        
        $datos = $this->calcularDatosPeriodo($paciente, $medicamento, $fechaInicio, $fechaFin);
        
        if ($datos['dosis_programadas'] > 0 || $datos['dosis_prn'] > 0) {
            $estadistica = [
                'paciente_id' => $paciente->id,
                'medicamento_id' => $medicamento->id,
                'periodo_inicio' => $fechaInicio->format('Y-m-d'),
                'periodo_fin' => $fechaFin->format('Y-m-d'),
                'tipo_periodo' => $tipoPeriodo,
                'dosis_programadas' => $datos['dosis_programadas'],
                'dosis_administradas' => $datos['dosis_administradas'],
                'dosis_omitidas' => $datos['dosis_omitidas'],
                'dosis_tardias' => $datos['dosis_tardias'],
                'dosis_prn' => $datos['dosis_prn'],
                'administraciones_prn_por_sintoma' => json_encode($datos['prn_por_sintoma']),
                'promedio_intensidad_sintomas' => $datos['promedio_intensidad'],
                'adherencia_porcentaje' => $datos['adherencia_porcentaje'],
                'puntualidad_porcentaje' => $datos['puntualidad_porcentaje'],
                'cantidad_total_administrada' => $datos['cantidad_total'],
                'unidad' => $datos['unidad'],
                'calculated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            DB::table('estadisticas_consumo')->insertOrIgnore($estadistica);
            return 1;
        }
        
        return 0;
    }
    
    private function calcularDatosPeriodo(Paciente $paciente, Medicamento $medicamento, Carbon $inicio, Carbon $fin): array
    {
        $administraciones = DB::table('administraciones')
            ->join('medicamentos_tratamientos', 'administraciones.medicamento_tratamiento_id', '=', 'medicamentos_tratamientos.id')
            ->where('administraciones.paciente_id', $paciente->id)
            ->where('medicamentos_tratamientos.medicamento_id', $medicamento->id)
            ->whereBetween('administraciones.created_at', [$inicio, $fin])
            ->get();
        
        // Separar programadas de PRN
        $programadas = $administraciones->whereNotNull('fecha_hora_programada');
        $prn = $administraciones->whereNull('fecha_hora_programada');
        
        $dosisAdministradas = $programadas->where('estado', 'Administrada')->count();
        $dosisOmitidas = $programadas->where('estado', 'Omitida')->count();
        $dosisTardias = $programadas->where('estado', 'Tardía')->count();
        $totalProgramadas = $dosisAdministradas + $dosisOmitidas + $dosisTardias;
        
        // Estadísticas PRN por síntoma
        $prnPorSintoma = [];
        $intensidades = [];
        
        foreach ($prn as $admin) {
            if ($admin->sintoma_reportado_id) {
                $sintoma = DB::table('sintomas_prn')->find($admin->sintoma_reportado_id);
                $nombreSintoma = $sintoma ? $sintoma->nombre : 'Desconocido';
                
                if (!isset($prnPorSintoma[$nombreSintoma])) {
                    $prnPorSintoma[$nombreSintoma] = 0;
                }
                $prnPorSintoma[$nombreSintoma]++;
                
                if ($admin->intensidad_sintoma) {
                    $intensidad = $this->convertirIntensidadANumero($admin->intensidad_sintoma);
                    if ($intensidad > 0) {
                        $intensidades[] = $intensidad;
                    }
                }
            }
        }
        
        $promedioIntensidad = !empty($intensidades) ? array_sum($intensidades) / count($intensidades) : null;
        
        // Cálculos de adherencia y puntualidad
        $adherenciaPorcentaje = $totalProgramadas > 0 ? 
            (($dosisAdministradas + $dosisTardias) / $totalProgramadas) * 100 : 0;
        
        $totalAdministradasProgramadas = $dosisAdministradas + $dosisTardias;
        $puntualidadPorcentaje = $totalAdministradasProgramadas > 0 ? 
            ($dosisAdministradas / $totalAdministradasProgramadas) * 100 : 0;
        
        // Cantidad total administrada
        $cantidadTotal = $administraciones->where('estado', '!=', 'Omitida')->sum('dosis_administrada');
        
        return [
            'dosis_programadas' => $totalProgramadas,
            'dosis_administradas' => $dosisAdministradas,
            'dosis_omitidas' => $dosisOmitidas,
            'dosis_tardias' => $dosisTardias,
            'dosis_prn' => $prn->count(),
            'prn_por_sintoma' => $prnPorSintoma,
            'promedio_intensidad' => $promedioIntensidad,
            'adherencia_porcentaje' => round($adherenciaPorcentaje, 2),
            'puntualidad_porcentaje' => round($puntualidadPorcentaje, 2),
            'cantidad_total' => $cantidadTotal,
            'unidad' => $medicamento->unidad_medida ?? 'mg',
        ];
    }
    
    private function generarAlertas(): void
    {
        $alertasGeneradas = 0;
        
        // Alertas por dosis omitidas
        $alertasGeneradas += $this->generarAlertasDosisOmitidas();
        
        // Alertas por administraciones fuera de ventana
        $alertasGeneradas += $this->generarAlertasFueraVentana();
        
        // Alertas por efectos adversos
        $alertasGeneradas += $this->generarAlertasEfectosAdversos();
        
        $this->command->info("🚨 Generadas {$alertasGeneradas} alertas");
    }
    
    private function generarAlertasDosisOmitidas(): int
    {
        $alertas = 0;
        
        $omisiones = DB::table('administraciones')
            ->where('estado', 'Omitida')
            ->where('fecha_hora_programada', '>=', Carbon::now()->subDays(7))
            ->get()
            ->groupBy('paciente_id');
        
        foreach ($omisiones as $pacienteId => $omisionesPaciente) {
            if ($omisionesPaciente->count() >= 3) {
                $nivel = $omisionesPaciente->count() >= 5 ? 'Critica' : 'Advertencia';
                
                $alerta = [
                    'paciente_id' => $pacienteId,
                    'tratamiento_id' => null,
                    'administracion_id' => null,
                    'tipo' => 'Dosis_Omitida',
                    'nivel' => $nivel,
                    'mensaje' => "Paciente ha omitido {$omisionesPaciente->count()} dosis en los últimos 7 días",
                    'fecha_generada' => now(),
                    'revisada' => rand(0, 100) < 25, // 25% ya revisadas
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                DB::table('alertas')->insert($alerta);
                $alertas++;
            }
        }
        
        return $alertas;
    }
    
    private function generarAlertasFueraVentana(): int
    {
        $alertas = 0;
        
        $administracionesFueraVentana = DB::table('administraciones')
            ->where('es_dentro_ventana_tolerancia', false)
            ->where('estado', 'Tardía')
            ->where('fecha_hora_administrada', '>=', Carbon::now()->subDays(3))
            ->where('minutos_diferencia', '>', 120) // Más de 2 horas tarde
            ->get();
        
        foreach ($administracionesFueraVentana as $admin) {
            $nivel = abs($admin->minutos_diferencia) > 360 ? 'Critica' : 'Advertencia';
            
            $alerta = [
                'paciente_id' => $admin->paciente_id,
                'tratamiento_id' => null,
                'administracion_id' => $admin->id,
                'tipo' => 'Fuera_Ventana',
                'nivel' => $nivel,
                'mensaje' => "Medicamento administrado con " . abs($admin->minutos_diferencia) . 
                           " minutos de retraso",
                'fecha_generada' => Carbon::parse($admin->fecha_hora_administrada),
                'revisada' => rand(0, 100) < 30, // 30% ya revisadas
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            DB::table('alertas')->insert($alerta);
            $alertas++;
        }
        
        return $alertas;
    }
    
    private function generarAlertasEfectosAdversos(): int
    {
        $alertas = 0;
        
        $efectosAdversos = DB::table('administraciones')
            ->whereNotNull('efectos_adversos')
            ->where('efectos_adversos', '!=', '')
            ->where('fecha_hora_administrada', '>=', Carbon::now()->subDays(7))
            ->get();
        
        foreach ($efectosAdversos as $admin) {
            $esSevero = str_contains(strtolower($admin->efectos_adversos), 'severo') ||
                       str_contains(strtolower($admin->efectos_adversos), 'grave');
            
            if ($esSevero || rand(0, 100) < 20) { // 20% de efectos leves generan alerta
                $nivel = $esSevero ? 'Critica' : 'Info';
                
                $alerta = [
                    'paciente_id' => $admin->paciente_id,
                    'tratamiento_id' => null,
                    'administracion_id' => $admin->id,
                    'tipo' => 'Efecto_Adverso',
                    'nivel' => $nivel,
                    'mensaje' => "Efecto adverso reportado: {$admin->efectos_adversos}",
                    'fecha_generada' => Carbon::parse($admin->fecha_hora_administrada),
                    'revisada' => rand(0, 100) < 60, // 60% ya revisadas
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                DB::table('alertas')->insert($alerta);
                $alertas++;
            }
        }
        
        return $alertas;
    }
    
    private function convertirIntensidadANumero(string $intensidad): int
    {
        $intensidad = strtolower($intensidad);
        
        if (is_numeric($intensidad)) return intval($intensidad);
        if (str_contains($intensidad, 'leve')) return 3;
        if (str_contains($intensidad, 'moderado')) return 6;
        if (str_contains($intensidad, 'severo')) return 9;
        
        return 0;
    }
} 