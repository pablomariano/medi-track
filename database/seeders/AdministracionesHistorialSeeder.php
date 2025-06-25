<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administracion;
use App\Models\MedicamentoTratamiento;
use App\Models\HorarioProgramado;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdministracionesHistorialSeeder extends Seeder
{
    private $inicioHistorial;
    private $finHistorial;
    private $pacientes;
    private $cuidadores;
    
    public function run(): void
    {
        // Configurar período de un mes hacia atrás
        $this->finHistorial = Carbon::now();
        $this->inicioHistorial = Carbon::now()->subDays(30);
        
        // Obtener datos necesarios
        $this->pacientes = Paciente::with(['tratamientos.medicamentoTratamientos.horariosProgramados'])->get();
        $this->cuidadores = User::whereHas('role', function($query) {
            $query->where('nombre', 'cuidador');
        })->get();
        
        if ($this->pacientes->isEmpty() || $this->cuidadores->isEmpty()) {
            $this->command->info('No hay pacientes o cuidadores disponibles. Ejecutar seeders de usuarios primero.');
            return;
        }
        
        $this->command->info("Generando historial de administraciones del {$this->inicioHistorial->format('d/m/Y')} al {$this->finHistorial->format('d/m/Y')}");
        
        // Generar solo administraciones programadas
        $this->generarAdministracionesProgramadas();
        
        $this->command->info('✅ Historial de administraciones generado exitosamente');
    }
    
    private function generarAdministracionesProgramadas(): void
    {
        $totalAdministraciones = 0;
        
        foreach ($this->pacientes as $paciente) {
            foreach ($paciente->tratamientos as $tratamiento) {
                foreach ($tratamiento->medicamentoTratamientos as $medTrat) {
                    foreach ($medTrat->horariosProgramados as $horario) {
                        $administraciones = $this->generarAdministracionesParaHorario($paciente, $medTrat, $horario);
                        $totalAdministraciones += count($administraciones);
                    }
                }
            }
        }
        
        $this->command->info("📊 Generadas {$totalAdministraciones} administraciones programadas");
    }
    
    private function generarAdministracionesParaHorario(Paciente $paciente, MedicamentoTratamiento $medTrat, HorarioProgramado $horario): array
    {
        $administraciones = [];
        $fechaActual = max($this->inicioHistorial->copy(), Carbon::parse($horario->fecha_inicio));
        $fechaFin = min($this->finHistorial->copy(), $horario->fecha_fin ? Carbon::parse($horario->fecha_fin) : $this->finHistorial);
        
        while ($fechaActual <= $fechaFin) {
            if ($this->esValidoDiaSegun($fechaActual, $horario->dias_semana)) {
                $fechaHoraProgramada = $fechaActual->copy()->setTimeFromTimeString($horario->hora_programada);
                
                // Solo generar si está dentro del período y no es futuro
                if ($fechaHoraProgramada >= $this->inicioHistorial && $fechaHoraProgramada <= $this->finHistorial) {
                    $administracion = $this->crearAdministracionProgramada($paciente, $medTrat, $horario, $fechaHoraProgramada);
                    if ($administracion) {
                        $administraciones[] = $administracion;
                    }
                }
            }
            $fechaActual->addDay();
        }
        
        return $administraciones;
    }
    
    private function crearAdministracionProgramada(Paciente $paciente, MedicamentoTratamiento $medTrat, HorarioProgramado $horario, Carbon $fechaHoraProgramada): ?array
    {
        // Calcular adherencia realista (85-95% adherencia general)
        $adherenciaBase = $this->calcularAdherenciaPaciente($paciente);
        
        // Factores que afectan adherencia
        $factorDiasSemana = $this->obtenerFactorDiaSemana($fechaHoraProgramada);
        $factorHorario = $this->obtenerFactorHorario($fechaHoraProgramada);
        $factorMedicamento = $this->obtenerFactorMedicamento($medTrat);
        
        $probabilidadAdministracion = $adherenciaBase * $factorDiasSemana * $factorHorario * $factorMedicamento;
        
        // Decidir si se administra
        if (rand(1, 100) > ($probabilidadAdministracion * 100)) {
            // Medicamento omitido
            $administracion = [
                'medicamento_tratamiento_id' => $medTrat->id,
                'horario_programado_id' => $horario->id,
                'paciente_id' => $paciente->id,
                'cuidador_usuario_id' => null,
                'fecha_hora_programada' => $fechaHoraProgramada,
                'fecha_hora_administrada' => $fechaHoraProgramada,
                'dosis_administrada' => 0,
                'estado' => 'Omitida',
                'es_dentro_ventana_tolerancia' => false,
                'minutos_diferencia' => null,
                'observaciones' => $this->generarMotivoOmision(),
                'created_at' => $fechaHoraProgramada,
                'updated_at' => $fechaHoraProgramada,
            ];
            
            DB::table('administraciones')->insert($administracion);
            return $administracion;
        }
        
        // Medicamento administrado - calcular cuándo se administró realmente
        $tiempoReal = $this->calcularTiempoRealAdministracion($fechaHoraProgramada, $medTrat);
        $minutosDiferencia = $tiempoReal->diffInMinutes($fechaHoraProgramada, false);
        $toleranciaTotal = ($medTrat->tolerancia_antes_minutos ?? 30) + ($medTrat->tolerancia_despues_minutos ?? 60);
        $dentroVentana = abs($minutosDiferencia) <= $toleranciaTotal;
        
        $estado = $this->determinarEstadoAdministracion($minutosDiferencia, $dentroVentana);
        
        $administracion = [
            'medicamento_tratamiento_id' => $medTrat->id,
            'horario_programado_id' => $horario->id,
            'paciente_id' => $paciente->id,
            'cuidador_usuario_id' => $this->cuidadores->random()->id,
            'fecha_hora_programada' => $fechaHoraProgramada,
            'fecha_hora_administrada' => $tiempoReal,
            'dosis_administrada' => $this->calcularDosisAdministrada($medTrat),
            'estado' => $estado,
            'es_dentro_ventana_tolerancia' => $dentroVentana,
            'minutos_diferencia' => $minutosDiferencia,
            'observaciones' => $this->generarObservacionAleatoria(),
            'efectos_adversos' => $this->generarEfectoAdversoAleatorio(),
            'created_at' => $tiempoReal,
            'updated_at' => $tiempoReal,
        ];
        
        DB::table('administraciones')->insert($administracion);
        return $administracion;
    }

    private function calcularAdherenciaPaciente(Paciente $paciente): float
    {
        // Adherencia base entre 0.85 y 0.95 (85% - 95%)
        $adherenciaBase = 0.85 + (rand(0, 10) / 100);
        
        // Ajustar por edad
        $factorEdad = $this->calcularFactorEdad($paciente);
        
        return min(0.98, $adherenciaBase * $factorEdad);
    }
    
    private function calcularFactorEdad(Paciente $paciente): float
    {
        $edad = Carbon::parse($paciente->fecha_nacimiento)->age;
        
        if ($edad < 18) return 0.75; // Menores: menos adherencia
        if ($edad < 65) return 1.0;  // Adultos: adherencia normal
        if ($edad < 80) return 0.9;  // Adultos mayores: ligera reducción
        return 0.8; // Muy mayores: más dificultades
    }
    
    private function obtenerFactorDiaSemana(Carbon $fecha): float
    {
        $diaSemana = $fecha->dayOfWeek;
        
        // Fin de semana: menor adherencia
        if ($diaSemana == 0 || $diaSemana == 6) { // Domingo o Sábado
            return 0.85;
        }
        
        // Lunes: menor adherencia (después del fin de semana)
        if ($diaSemana == 1) {
            return 0.9;
        }
        
        return 1.0; // Días normales de semana
    }
    
    private function obtenerFactorHorario(Carbon $fecha): float
    {
        $hora = $fecha->hour;
        
        // Horarios nocturnos: menor adherencia
        if ($hora >= 22 || $hora <= 6) {
            return 0.7;
        }
        
        // Horas de comida: mejor adherencia
        if (in_array($hora, [8, 12, 19, 20])) {
            return 1.1;
        }
        
        // Horarios de trabajo: adherencia normal
        return 1.0;
    }
    
    private function obtenerFactorMedicamento(MedicamentoTratamiento $medTrat): float
    {
        $medicamento = $medTrat->medicamento;
        
        // Medicamentos críticos: mejor adherencia
        if (str_contains(strtolower($medicamento->nombre), 'insulin') ||
            str_contains(strtolower($medicamento->nombre), 'digoxin')) {
            return 1.2;
        }
        
        // Antibióticos: muy buena adherencia (temporales)
        if (str_contains(strtolower($medicamento->nombre), 'antibiotico') ||
            str_contains(strtolower($medicamento->nombre), 'amoxicilina')) {
            return 1.15;
        }
        
        return 1.0;
    }
    
    private function calcularTiempoRealAdministracion(Carbon $programada, MedicamentoTratamiento $medTrat): Carbon
    {
        $toleranciaAntes = $medTrat->tolerancia_antes_minutos ?? 30;
        $toleranciaDespues = $medTrat->tolerancia_despues_minutos ?? 60;
        
        // 70% dentro de ventana, 30% fuera
        if (rand(1, 100) <= 70) {
            // Dentro de ventana de tolerancia
            $minutos = rand(-$toleranciaAntes, $toleranciaDespues);
        } else {
            // Fuera de ventana
            $opciones = [
                rand(-120, -$toleranciaAntes), // Muy temprano
                rand($toleranciaDespues, 180)  // Muy tarde
            ];
            $minutos = $opciones[array_rand($opciones)];
        }
        
        return $programada->copy()->addMinutes($minutos);
    }
    
    private function determinarEstadoAdministracion(int $minutosDiferencia, bool $dentroVentana): string
    {
        if (!$dentroVentana) {
            return 'Tardía';
        }
        
        return 'Administrada';
    }
    
    private function calcularDosisAdministrada(MedicamentoTratamiento $medTrat): float
    {
        $dosisIndicada = $medTrat->dosis_cantidad;
        
        // 95% de las veces se da la dosis correcta
        if (rand(1, 100) <= 95) {
            return $dosisIndicada;
        }
        
        // 5% errores de dosificación (±10%)
        $factor = 1 + (rand(-10, 10) / 100);
        return round($dosisIndicada * $factor, 2);
    }
    
    private function esValidoDiaSegun(Carbon $fecha, string $diasSemana): bool
    {
        if ($diasSemana === 'Daily') return true;
        
        $diasSemanaArray = explode(',', $diasSemana);
        $diaActual = $fecha->locale('es')->dayName;
        
        $mapaDias = [
            'L' => 'lunes', 'M' => 'martes', 'X' => 'miércoles',
            'J' => 'jueves', 'V' => 'viernes', 'S' => 'sábado', 'D' => 'domingo'
        ];
        
        foreach ($diasSemanaArray as $dia) {
            if (isset($mapaDias[trim($dia)]) && $mapaDias[trim($dia)] === strtolower($diaActual)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function generarMotivoOmision(): ?string
    {
        $motivos = [
            'Paciente dormido',
            'Medicamento no disponible',
            'Paciente rechazó tomar medicamento',
            'Náuseas',
            'Olvido del cuidador',
            'Paciente fuera de casa',
            'Problema de deglución',
            null // Sin observaciones
        ];
        
        return $motivos[array_rand($motivos)];
    }
    
    private function generarObservacionAleatoria(): ?string
    {
        $observaciones = [
            'Medicamento tomado sin dificultad',
            'Paciente colaborativo',
            'Tomado con alimentos',
            'Tomado con agua',
            'Sin efectos adversos aparentes',
            null // Sin observaciones en mayoría de casos
        ];
        
        return $observaciones[array_rand($observaciones)];
    }
    
    private function generarEfectoAdversoAleatorio(): ?string
    {
        // Solo 5% de administraciones tienen efectos adversos reportados
        if (rand(1, 100) > 5) return null;
        
        $efectos = [
            'Leve mareo',
            'Náuseas leves',
            'Somnolencia',
            'Sequedad bucal',
            'Dolor de cabeza leve'
        ];
        
        return $efectos[array_rand($efectos)];
    }
} 