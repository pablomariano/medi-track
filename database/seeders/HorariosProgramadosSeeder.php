<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MedicamentoTratamiento;
use App\Models\Paciente;
use Carbon\Carbon;

class HorariosProgramadosSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener tratamientos programados (no PRN)
        $medicamentosTratamientos = MedicamentoTratamiento::where('es_prn', false)
            ->where('activo', true)
            ->with(['tratamiento.paciente'])
            ->get();
            
        if ($medicamentosTratamientos->isEmpty()) {
            $this->command->info('No hay medicamentos programados disponibles.');
            return;
        }
        
        $totalHorarios = 0;
        
        foreach ($medicamentosTratamientos as $medTrat) {
            $horarios = $this->generarHorariosParaMedicamento($medTrat);
            $totalHorarios += count($horarios);
            
            foreach ($horarios as $horario) {
                DB::table('horarios_programados')->insertOrIgnore($horario);
            }
        }
        
        $this->command->info("✅ Generados {$totalHorarios} horarios programados");
    }
    
    private function generarHorariosParaMedicamento(MedicamentoTratamiento $medTrat): array
    {
        $paciente = $medTrat->tratamiento->paciente;
        $frecuenciaHoras = $medTrat->frecuencia_horas;
        
        // Calcular cuántas tomas por día
        $tomasPorDia = 24 / $frecuenciaHoras;
        
        // Generar horarios basados en frecuencia
        $horarios = $this->calcularHorarios($frecuenciaHoras, $tomasPorDia);
        
        // Fechas de inicio y fin
        $fechaInicio = Carbon::parse($medTrat->tratamiento->fecha_inicio ?? now()->subDays(45));
        $fechaFin = $medTrat->duracion_dias ? 
            $fechaInicio->copy()->addDays($medTrat->duracion_dias) : 
            Carbon::parse($medTrat->tratamiento->fecha_fin_estimada ?? now()->addDays(90));
        
        $horariosGenerados = [];
        
        foreach ($horarios as $hora) {
            $horariosGenerados[] = [
                'medicamento_tratamiento_id' => $medTrat->id,
                'paciente_id' => $paciente->id,
                'hora_programada' => $hora,
                'dias_semana' => $this->determinarDiasSemana($medTrat),
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $horariosGenerados;
    }
    
    private function calcularHorarios(int $frecuenciaHoras, float $tomasPorDia): array
    {
        $horarios = [];
        
        switch ($frecuenciaHoras) {
            case 24: // Una vez al día
                $horarios = ['08:00:00']; // Mañana
                break;
                
            case 12: // Cada 12 horas (2 veces al día)
                $horarios = ['08:00:00', '20:00:00']; // Mañana y noche
                break;
                
            case 8: // Cada 8 horas (3 veces al día)
                $horarios = ['08:00:00', '16:00:00', '00:00:00']; // Mañana, tarde, medianoche
                break;
                
            case 6: // Cada 6 horas (4 veces al día)
                $horarios = ['06:00:00', '12:00:00', '18:00:00', '00:00:00'];
                break;
                
            case 4: // Cada 4 horas (6 veces al día)
                $horarios = ['06:00:00', '10:00:00', '14:00:00', '18:00:00', '22:00:00', '02:00:00'];
                break;
                
            case 3: // Cada 3 horas (8 veces al día) - medicamentos críticos
                $horarios = ['06:00:00', '09:00:00', '12:00:00', '15:00:00', '18:00:00', '21:00:00', '00:00:00', '03:00:00'];
                break;
                
            case 2: // Cada 2 horas (12 veces al día) - cuidados intensivos
                for ($h = 0; $h < 24; $h += 2) {
                    $horarios[] = sprintf('%02d:00:00', $h);
                }
                break;
                
            case 1: // Cada hora (24 veces al día) - extremadamente raro
                for ($h = 0; $h < 24; $h++) {
                    $horarios[] = sprintf('%02d:00:00', $h);
                }
                break;
                
            default:
                // Para frecuencias no estándar, calcular dinámicamente
                $intervaloMinutos = $frecuenciaHoras * 60;
                $horaInicio = 8; // Comenzar a las 8 AM
                
                for ($minutos = $horaInicio * 60; $minutos < 24 * 60; $minutos += $intervaloMinutos) {
                    $horas = intval($minutos / 60) % 24;
                    $minutosRestantes = $minutos % 60;
                    $horarios[] = sprintf('%02d:%02d:00', $horas, $minutosRestantes);
                }
                break;
        }
        
        return $horarios;
    }
    
    private function determinarDiasSemana(MedicamentoTratamiento $medTrat): string
    {
        $medicamento = $medTrat->medicamento;
        
        // Algunos medicamentos tienen patrones específicos
        if (str_contains(strtolower($medicamento->nombre), 'antibiotico') || 
            str_contains(strtolower($medicamento->nombre), 'amoxicilina')) {
            return 'Daily'; // Antibióticos todos los días
        }
        
        if (str_contains(strtolower($medicamento->nombre), 'vitamina')) {
            return 'L,M,X,J,V'; // Vitaminas solo días de semana
        }
        
        // Medicamentos para condiciones crónicas
        if (str_contains(strtolower($medicamento->nombre), 'losartan') ||
            str_contains(strtolower($medicamento->nombre), 'metformina') ||
            str_contains(strtolower($medicamento->nombre), 'simvastatina')) {
            return 'Daily'; // Medicamentos crónicos todos los días
        }
        
        // Por defecto, todos los días
        return 'Daily';
    }
} 