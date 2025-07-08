<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administracion;
use App\Models\MedicamentoTratamiento;
use App\Models\HorarioProgramado;
use App\Models\Paciente;
use App\Models\User;
use App\Services\TemporalAdherenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TemporalAdherenceSeeder extends Seeder
{
    private $temporalService;
    private $inicioHistorial;
    private $finHistorial;
    private $pacientes;
    private $cuidadores;
    
    public function __construct(TemporalAdherenceService $temporalService)
    {
        $this->temporalService = $temporalService;
    }
    
    public function run(): void
    {
        // Configurar período desde hace 3 semanas hasta ahora (incluyendo datos hasta hace una semana)
        $this->finHistorial = Carbon::now();
        $this->inicioHistorial = Carbon::now()->subWeeks(3);
        
        // Obtener datos necesarios
        $this->pacientes = Paciente::with(['tratamientos.medicamentoTratamientos.horariosProgramados'])->get();
        $this->cuidadores = User::whereHas('role', function($query) {
            $query->where('nombre', 'cuidador');
        })->get();
        
        if ($this->pacientes->isEmpty() || $this->cuidadores->isEmpty()) {
            $this->command->info('❌ No hay pacientes o cuidadores disponibles. Ejecutar seeders de usuarios primero.');
            return;
        }
        
        $this->command->info("🔄 Generando datos de adherencia temporal del {$this->inicioHistorial->format('d/m/Y')} al {$this->finHistorial->format('d/m/Y')}");
        
        // Limpiar administraciones existentes del período
        $this->limpiarDatosExistentes();
        
        // Generar administraciones con variaciones temporales realistas
        $this->generarAdministracionesConMetricasTemporales();
        
        // Calcular métricas temporales para todas las administraciones
        $this->calcularMetricasTemporales();
        
        $this->command->info('✅ Datos de adherencia temporal generados exitosamente');
    }
    
    private function limpiarDatosExistentes(): void
    {
        $count = Administracion::whereBetween('fecha_hora_programada', [
            $this->inicioHistorial,
            $this->finHistorial
        ])->count();
        
        if ($count > 0) {
            Administracion::whereBetween('fecha_hora_programada', [
                $this->inicioHistorial,
                $this->finHistorial
            ])->delete();
            
            $this->command->info("🗑️  Eliminadas {$count} administraciones existentes del período");
        }
    }
    
    private function generarAdministracionesConMetricasTemporales(): void
    {
        $totalAdministraciones = 0;
        $administracionesPorPaciente = [];
        
        foreach ($this->pacientes as $paciente) {
            $administracionesPaciente = 0;
            $perfilTemporal = $this->generarPerfilTemporalPaciente($paciente);
            
            foreach ($paciente->tratamientos as $tratamiento) {
                if ($tratamiento->estado !== 'Activo') continue;
                
                foreach ($tratamiento->medicamentoTratamientos as $medTrat) {
                    foreach ($medTrat->horariosProgramados as $horario) {
                        $administraciones = $this->generarAdministracionesParaHorarioConMetricas(
                            $paciente, 
                            $medTrat, 
                            $horario, 
                            $perfilTemporal
                        );
                        $administracionesPaciente += count($administraciones);
                    }
                }
            }
            
            $administracionesPorPaciente[$paciente->nombre] = $administracionesPaciente;
            $totalAdministraciones += $administracionesPaciente;
        }
        
        $this->command->info("📊 Generadas {$totalAdministraciones} administraciones con métricas temporales");
        
        // Mostrar resumen por paciente
        foreach ($administracionesPorPaciente as $nombre => $count) {
            $this->command->info("   👤 {$nombre}: {$count} administraciones");
        }
    }
    
    private function generarPerfilTemporalPaciente(Paciente $paciente): array
    {
        // Crear perfiles de comportamiento temporal diversos
        $perfiles = [
            'muy_puntual' => [
                'tendencia_adelanto' => 0.1,  // 10% probabilidad de adelantarse
                'tendencia_retraso' => 0.15,  // 15% probabilidad de retrasarse
                'variabilidad_max' => 10,     // Máximo 10 minutos de variación
                'adherencia_base' => 0.95,    // 95% adherencia
            ],
            'puntual' => [
                'tendencia_adelanto' => 0.15,
                'tendencia_retraso' => 0.25,
                'variabilidad_max' => 20,
                'adherencia_base' => 0.90,
            ],
            'irregular' => [
                'tendencia_adelanto' => 0.20,
                'tendencia_retraso' => 0.40,
                'variabilidad_max' => 45,
                'adherencia_base' => 0.80,
            ],
            'problematico' => [
                'tendencia_adelanto' => 0.10,
                'tendencia_retraso' => 0.60,
                'variabilidad_max' => 90,
                'adherencia_base' => 0.70,
            ]
        ];
        
        // Asignar perfil basado en edad y otros factores
        $edad = Carbon::parse($paciente->fecha_nacimiento)->age;
        
        if ($edad < 30) {
            $perfilKey = collect(['puntual', 'irregular'])->random();
        } elseif ($edad < 60) {
            $perfilKey = collect(['muy_puntual', 'puntual'])->random();
        } else {
            $perfilKey = collect(['puntual', 'irregular', 'problematico'])->random();
        }
        
        return array_merge($perfiles[$perfilKey], ['tipo' => $perfilKey]);
    }
    
    private function generarAdministracionesParaHorarioConMetricas(
        Paciente $paciente, 
        MedicamentoTratamiento $medTrat, 
        HorarioProgramado $horario, 
        array $perfilTemporal
    ): array {
        $administraciones = [];
        $fechaActual = max($this->inicioHistorial->copy(), Carbon::parse($horario->fecha_inicio));
        $fechaFin = min($this->finHistorial->copy(), $horario->fecha_fin ? Carbon::parse($horario->fecha_fin) : $this->finHistorial);
        
        while ($fechaActual <= $fechaFin) {
            if ($this->esValidoDiaSegun($fechaActual, $horario->dias_semana)) {
                $fechaHoraProgramada = $fechaActual->copy()->setTimeFromTimeString($horario->hora_programada);
                
                if ($fechaHoraProgramada >= $this->inicioHistorial && $fechaHoraProgramada <= $this->finHistorial) {
                    $administracion = $this->crearAdministracionConMetricasTemporales(
                        $paciente, 
                        $medTrat, 
                        $horario, 
                        $fechaHoraProgramada, 
                        $perfilTemporal
                    );
                    
                    if ($administracion) {
                        $administraciones[] = $administracion;
                    }
                }
            }
            $fechaActual->addDay();
        }
        
        return $administraciones;
    }
    
    private function crearAdministracionConMetricasTemporales(
        Paciente $paciente, 
        MedicamentoTratamiento $medTrat, 
        HorarioProgramado $horario, 
        Carbon $fechaHoraProgramada,
        array $perfilTemporal
    ): ?array {
        // Decidir si se administra basado en adherencia
        if (rand(1, 100) > ($perfilTemporal['adherencia_base'] * 100)) {
            return $this->crearAdministracionOmitida($paciente, $medTrat, $horario, $fechaHoraProgramada);
        }
        
        // Calcular variación temporal basada en el perfil
        $variacionMinutos = $this->calcularVariacionTemporal($perfilTemporal, $fechaHoraProgramada);
        $fechaHoraReal = $fechaHoraProgramada->copy()->addMinutes($variacionMinutos);
        
        // Calcular métricas temporales
        $minutosDiferencia = $variacionMinutos;
        $minutosAdelanto = $variacionMinutos < 0 ? abs($variacionMinutos) : 0;
        $minutosRetraso = $variacionMinutos > 0 ? $variacionMinutos : 0;
        
        // Calcular score de puntualidad usando la misma fórmula del servicio
        $scorePuntualidad = $this->calcularScorePuntualidad($minutosDiferencia);
        
        // Determinar categoría temporal
        $categoriaTemporal = $this->determinarCategoriaTemporal($minutosDiferencia);
        
        // Calcular ventana de tolerancia
        $toleranciaAntes = $medTrat->tolerancia_antes_minutos ?? 30;
        $toleranciaDespues = $medTrat->tolerancia_despues_minutos ?? 60;
        $dentroVentana = ($variacionMinutos >= -$toleranciaAntes) && ($variacionMinutos <= $toleranciaDespues);
        
        $estado = $this->determinarEstadoAdministracion($minutosDiferencia, $dentroVentana);
        
        $administracion = [
            'medicamento_tratamiento_id' => $medTrat->id,
            'horario_programado_id' => $horario->id,
            'paciente_id' => $paciente->id,
            'cuidador_usuario_id' => $this->cuidadores->random()->id,
            'fecha_hora_programada' => $fechaHoraProgramada,
            'fecha_hora_administrada' => $fechaHoraReal,
            'dosis_administrada' => $medTrat->dosis_cantidad,
            'estado' => $estado,
            'es_dentro_ventana_tolerancia' => $dentroVentana,
            'minutos_diferencia' => $minutosDiferencia,
            // Nuevas columnas de métricas temporales
            'minutos_adelanto' => $minutosAdelanto,
            'minutos_retraso' => $minutosRetraso,
            'score_puntualidad' => $scorePuntualidad,
            'categoria_temporal' => $categoriaTemporal,
            'observaciones' => $this->generarObservacionPorCategoria($categoriaTemporal, $perfilTemporal['tipo']),
            'created_at' => $fechaHoraReal,
            'updated_at' => $fechaHoraReal,
        ];
        
        DB::table('administraciones')->insert($administracion);
        return $administracion;
    }
    
    private function calcularVariacionTemporal(array $perfilTemporal, Carbon $fechaProgramada): int
    {
        // Factores que afectan la variación temporal
        $factorDia = $this->obtenerFactorDiaSemana($fechaProgramada);
        $factorHora = $this->obtenerFactorHorario($fechaProgramada);
        
        // Decidir dirección (adelanto vs retraso)
        $rand = rand(1, 100) / 100;
        
        if ($rand < $perfilTemporal['tendencia_adelanto'] * $factorDia * $factorHora) {
            // Adelanto (negativo)
            return -rand(1, min(60, $perfilTemporal['variabilidad_max']));
        } elseif ($rand < ($perfilTemporal['tendencia_adelanto'] + $perfilTemporal['tendencia_retraso']) * $factorDia * $factorHora) {
            // Retraso (positivo)
            return rand(1, min(120, $perfilTemporal['variabilidad_max'] * 2));
        } else {
            // Puntual (variación mínima)
            return rand(-5, 5);
        }
    }
    
    private function calcularScorePuntualidad(int $minutosDiferencia): float
    {
        $diferencia = abs($minutosDiferencia);
        
        if ($diferencia <= 15) {
            return 100.0; // Perfecta puntualidad
        }
        
        // Fórmula exponencial: 100 * e^(-0.05 * (diferencia - 15))
        $score = 100 * exp(-0.05 * ($diferencia - 15));
        
        return round(max(0, min(100, $score)), 1);
    }
    
    private function determinarCategoriaTemporal(int $minutosDiferencia): string
    {
        if ($minutosDiferencia < -60) return 'muy_temprano';
        if ($minutosDiferencia < -15) return 'temprano';
        if ($minutosDiferencia >= -15 && $minutosDiferencia <= 15) return 'puntual';
        if ($minutosDiferencia <= 60) return 'tardio';
        return 'muy_tardio';
    }
    
    private function crearAdministracionOmitida(
        Paciente $paciente, 
        MedicamentoTratamiento $medTrat, 
        HorarioProgramado $horario, 
        Carbon $fechaHoraProgramada
    ): array {
        $administracion = [
            'medicamento_tratamiento_id' => $medTrat->id,
            'horario_programado_id' => $horario->id,
            'paciente_id' => $paciente->id,
            'cuidador_usuario_id' => null,
            'fecha_hora_programada' => $fechaHoraProgramada,
            'fecha_hora_administrada' => null,
            'dosis_administrada' => 0,
            'estado' => 'Omitida',
            'es_dentro_ventana_tolerancia' => false,
            'minutos_diferencia' => null,
            'minutos_adelanto' => 0,
            'minutos_retraso' => 0,
            'score_puntualidad' => 0,
            'categoria_temporal' => null,
            'observaciones' => $this->generarMotivoOmision(),
            'created_at' => $fechaHoraProgramada,
            'updated_at' => $fechaHoraProgramada,
        ];
        
        DB::table('administraciones')->insert($administracion);
        return $administracion;
    }
    
    private function calcularMetricasTemporales(): void
    {
        $this->command->info("🔄 Calculando métricas temporales adicionales...");
        
        $administraciones = Administracion::whereNotNull('score_puntualidad')
            ->whereBetween('fecha_hora_programada', [$this->inicioHistorial, $this->finHistorial])
            ->get();
        
        $this->command->info("📊 Procesando {$administraciones->count()} administraciones con métricas");
        
        // Aquí podrías agregar cálculos adicionales de métricas agregadas si es necesario
        // Por ejemplo, métricas por paciente, por medicamento, etc.
    }
    
    // Métodos auxiliares
    private function esValidoDiaSegun(Carbon $fecha, string $diasSemana): bool
    {
        // Si es "Daily", válido todos los días
        if ($diasSemana === 'Daily') {
            return true;
        }
        
        // Si está en formato de array separado por comas
        if (str_contains($diasSemana, ',')) {
            $diasArray = explode(',', $diasSemana);
            $diaActual = $fecha->dayOfWeek; // 0=domingo, 1=lunes, etc.
            return in_array($diaActual, $diasArray);
        }
        
        // Por defecto, asumir que es válido
        return true;
    }
    
    private function obtenerFactorDiaSemana(Carbon $fecha): float
    {
        $diaSemana = $fecha->dayOfWeek;
        
        if ($diaSemana == 0 || $diaSemana == 6) return 0.7; // Fin de semana
        if ($diaSemana == 1) return 0.8; // Lunes
        return 1.0; // Días normales
    }
    
    private function obtenerFactorHorario(Carbon $fecha): float
    {
        $hora = $fecha->hour;
        
        if ($hora >= 22 || $hora <= 6) return 0.6; // Noche
        if (in_array($hora, [8, 12, 19, 20])) return 1.1; // Horas de comida
        return 1.0; // Horario normal
    }
    
    private function determinarEstadoAdministracion(int $minutosDiferencia, bool $dentroVentana): string
    {
        if (!$dentroVentana && abs($minutosDiferencia) > 60) {
            return 'Tardía';
        }
        
        return 'Administrada';
    }
    
    private function generarObservacionPorCategoria(string $categoria, string $perfil): ?string
    {
        $observaciones = [
            'muy_temprano' => [
                'Paciente muy ansioso por tomar medicamento',
                'Administrado antes de tiempo por rutina matutina',
                'Confusión con el horario programado'
            ],
            'temprano' => [
                'Administrado ligeramente antes por conveniencia',
                'Ajuste por horario de comida',
                'Prevención antes de salir de casa'
            ],
            'puntual' => [
                'Administrado en horario exacto',
                'Excelente adherencia al tratamiento',
                'Rutina bien establecida'
            ],
            'tardio' => [
                'Pequeño retraso por actividades diarias',
                'Olvido temporal, recordado posteriormente',
                'Retraso por reunión familiar'
            ],
            'muy_tardio' => [
                'Retraso significativo por olvido',
                'Problemas de movilidad',
                'Administrado al recordar antes de dormir'
            ]
        ];
        
        if (!isset($observaciones[$categoria])) return null;
        
        return collect($observaciones[$categoria])->random();
    }
    
    private function generarMotivoOmision(): string
    {
        $motivos = [
            'Paciente dormido',
            'Náuseas matutinas',
            'Salida imprevista de casa',
            'Olvido del medicamento',
            'Efectos adversos previos',
            'Consulta médica programada',
            'Malestar general',
            'Problema con el suministro del medicamento'
        ];
        
        return collect($motivos)->random();
    }
} 