<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administracion;
use App\Models\MedicamentoTratamiento;
use App\Models\HorarioProgramado;
use App\Models\Paciente;
use App\Models\User;
use App\Models\SintomaPrn;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdministracionesHistorialSeeder extends Seeder
{
    private $inicioHistorial;
    private $finHistorial;
    private $pacientes;
    private $cuidadores;
    private $sintomas;
    
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
        $this->sintomas = SintomaPrn::all();
        
        if ($this->pacientes->isEmpty() || $this->cuidadores->isEmpty()) {
            $this->command->info('No hay pacientes o cuidadores disponibles. Ejecutar seeders de usuarios primero.');
            return;
        }
        
        $this->command->info("Generando historial de administraciones del {$this->inicioHistorial->format('d/m/Y')} al {$this->finHistorial->format('d/m/Y')}");
        
        // Generar administraciones programadas
        $this->generarAdministracionesProgramadas();
        
        // Generar administraciones PRN
        $this->generarAdministracionesPRN();
        
        $this->command->info('✅ Historial de administraciones generado exitosamente');
    }
    
    private function generarAdministracionesProgramadas(): void
    {
        $totalAdministraciones = 0;
        
        foreach ($this->pacientes as $paciente) {
            foreach ($paciente->tratamientos as $tratamiento) {
                foreach ($tratamiento->medicamentoTratamientos as $medTrat) {
                    if ($medTrat->es_prn) continue; // Solo medicamentos programados
                    
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
    
    private function generarAdministracionesPRN(): void
    {
        $totalPRN = 0;
        
        foreach ($this->pacientes as $paciente) {
            // Generar entre 15-45 administraciones PRN por paciente en el mes
            $numAdministracionesPRN = rand(15, 45);
            
            for ($i = 0; $i < $numAdministracionesPRN; $i++) {
                $administracion = $this->crearAdministracionPRN($paciente);
                if ($administracion) {
                    $totalPRN++;
                }
            }
        }
        
        $this->command->info("💊 Generadas {$totalPRN} administraciones PRN");
    }
    
    private function crearAdministracionPRN(Paciente $paciente): ?array
    {
        // Obtener medicamentos PRN del paciente
        $medicamentosPRN = collect();
        foreach ($paciente->tratamientos as $tratamiento) {
            $prnMeds = $tratamiento->medicamentoTratamientos->where('es_prn', true);
            $medicamentosPRN = $medicamentosPRN->merge($prnMeds);
        }
        
        if ($medicamentosPRN->isEmpty()) return null;
        
        $medTrat = $medicamentosPRN->random();
        $sintoma = $this->sintomas->random();
        
        // Generar fecha/hora aleatoria dentro del período
        $fechaHora = Carbon::createFromTimestamp(
            rand($this->inicioHistorial->timestamp, $this->finHistorial->timestamp)
        );
        
        // Intensidad del síntoma (1-10 o descriptiva)
        $intensidades = ['leve', 'moderado', 'severo'];
        $intensidadNumerica = rand(1, 10);
        $intensidad = $intensidadNumerica >= 7 ? 'severo' : ($intensidadNumerica >= 4 ? 'moderado' : 'leve');
        
        $administracion = [
            'medicamento_tratamiento_id' => $medTrat->id,
            'horario_programado_id' => null,
            'paciente_id' => $paciente->id,
            'cuidador_usuario_id' => $this->cuidadores->random()->id,
            'fecha_hora_programada' => null,
            'fecha_hora_administrada' => $fechaHora,
            'dosis_administrada' => $this->calcularDosisAdministrada($medTrat),
            'estado' => 'Administrada',
            'es_dentro_ventana_tolerancia' => true,
            'minutos_diferencia' => null,
            'sintoma_reportado_id' => $sintoma->id,
            'intensidad_sintoma' => $intensidad,
            'criterio_cumplido' => $this->generarCriterioPRN($sintoma, $intensidad),
            'observaciones' => $this->generarObservacionPRN($sintoma, $intensidad),
            'efectos_adversos' => $this->generarEfectoAdversoAleatorio(),
            'created_at' => $fechaHora,
            'updated_at' => $fechaHora,
        ];
        
        // Insertar en base de datos
        DB::table('administraciones')->insert($administracion);
        
        return $administracion;
    }
    
    // Métodos auxiliares
    private function calcularAdherenciaPaciente(Paciente $paciente): float
    {
        // Adherencia base entre 75-98% dependiendo del paciente
        $edadFactor = $this->calcularFactorEdad($paciente);
        $baseAdherencia = 0.75 + (0.23 * $edadFactor); // 75% - 98%
        
        return min(0.98, max(0.75, $baseAdherencia));
    }
    
    private function calcularFactorEdad(Paciente $paciente): float
    {
        if (!$paciente->fecha_nacimiento) return 0.8;
        
        $edad = Carbon::parse($paciente->fecha_nacimiento)->age;
        
        if ($edad < 18) return 0.7; // Niños - menor adherencia
        if ($edad >= 18 && $edad <= 65) return 1.0; // Adultos - mejor adherencia
        if ($edad > 65 && $edad <= 80) return 0.9; // Adultos mayores
        
        return 0.8; // Muy ancianos
    }
    
    private function obtenerFactorDiaSemana(Carbon $fecha): float
    {
        $diaSemana = $fecha->dayOfWeek;
        
        // Lunes a Viernes: mejor adherencia
        if ($diaSemana >= 1 && $diaSemana <= 5) return 1.0;
        
        // Sábado: ligeramente menor
        if ($diaSemana == 6) return 0.95;
        
        // Domingo: menor adherencia
        return 0.90;
    }
    
    private function obtenerFactorHorario(Carbon $fecha): float
    {
        $hora = $fecha->hour;
        
        // Mañana (6-12): excelente adherencia
        if ($hora >= 6 && $hora <= 12) return 1.0;
        
        // Tarde (13-18): buena adherencia
        if ($hora >= 13 && $hora <= 18) return 0.95;
        
        // Noche (19-22): adherencia regular
        if ($hora >= 19 && $hora <= 22) return 0.90;
        
        // Madrugada: pobre adherencia
        return 0.75;
    }
    
    private function obtenerFactorMedicamento(MedicamentoTratamiento $medTrat): float
    {
        // Medicamentos complejos o controlados tienen menor adherencia
        $medicamento = $medTrat->medicamento;
        
        if ($medicamento->controlado) return 0.85;
        if ($medicamento->via_administracion === 'Inyectable') return 0.90;
        if (($medTrat->frecuencia_horas ?? 24) <= 6) return 0.85; // Frecuencia alta
        
        return 1.0;
    }
    
    private function calcularTiempoRealAdministracion(Carbon $programada, MedicamentoTratamiento $medTrat): Carbon
    {
        $tolerancia = ($medTrat->tolerancia_antes_minutos ?? 30) + ($medTrat->tolerancia_despues_minutos ?? 60);
        
        // 70% dentro de ventana, 20% tardío, 10% temprano
        $probabilidad = rand(1, 100);
        
        if ($probabilidad <= 70) {
            // Dentro de ventana de tolerancia
            $minutos = rand(-$tolerancia, $tolerancia);
        } elseif ($probabilidad <= 90) {
            // Tardío (fuera de ventana)
            $minutos = rand($tolerancia + 1, $tolerancia * 3);
        } else {
            // Muy temprano
            $minutos = rand(-($tolerancia * 2), -($tolerancia + 1));
        }
        
        return $programada->copy()->addMinutes($minutos);
    }
    
    private function determinarEstadoAdministracion(int $minutosDiferencia, bool $dentroVentana): string
    {
        if ($dentroVentana) {
            return 'Administrada';
        }
        
        return 'Tardía';
    }
    
    private function calcularDosisAdministrada(MedicamentoTratamiento $medTrat): float
    {
        $dosisBase = floatval($medTrat->dosis_cantidad);
        
        // 95% de las veces dosis exacta, 5% variaciones menores
        if (rand(1, 100) <= 95) {
            return $dosisBase;
        }
        
        // Variación del ±10%
        $variacion = $dosisBase * 0.1;
        return $dosisBase + (rand(-$variacion * 100, $variacion * 100) / 100);
    }
    
    private function esValidoDiaSegun(Carbon $fecha, string $diasSemana): bool
    {
        if ($diasSemana === 'Daily') return true;
        
        $diasMap = [
            1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 
            5 => 'V', 6 => 'S', 0 => 'D'
        ];
        
        $letraDia = $diasMap[$fecha->dayOfWeek];
        return strpos($diasSemana, $letraDia) !== false;
    }
    
    private function generarMotivoOmision(): ?string
    {
        $motivos = [
            'Paciente dormido',
            'Paciente ausente',
            'Náuseas persistentes',
            'Rechazo del paciente',
            'Medicamento no disponible',
            'Olvido del cuidador',
            'Paciente en procedimiento médico',
            null, null, null // 30% sin motivo específico
        ];
        
        return $motivos[array_rand($motivos)];
    }
    
    private function generarObservacionAleatoria(): ?string
    {
        $observaciones = [
            'Administración sin novedades',
            'Paciente cooperativo',
            'Tomado con abundante agua',
            'Administrado con alimentos',
            'Paciente preguntó sobre el medicamento',
            'Sin efectos adversos reportados',
            null, null, null, null, null // 50% sin observaciones
        ];
        
        return $observaciones[array_rand($observaciones)];
    }
    
    private function generarEfectoAdversoAleatorio(): ?string
    {
        // 90% sin efectos adversos
        if (rand(1, 100) <= 90) return null;
        
        $efectos = [
            'Náuseas leves',
            'Somnolencia',
            'Dolor de cabeza leve',
            'Mareos ligeros',
            'Molestias estomacales',
            'Sequedad de boca'
        ];
        
        return $efectos[array_rand($efectos)];
    }
    
    private function generarCriterioPRN(SintomaPrn $sintoma, string $intensidad): string
    {
        $criterios = [
            'Dolor de cabeza' => [
                'leve' => 'Dolor tipo tensional, escala 3-4/10',
                'moderado' => 'Dolor pulsátil, escala 5-6/10',
                'severo' => 'Dolor intenso que interfiere con actividades, 7-10/10'
            ],
            'Fiebre' => [
                'leve' => 'Temperatura 38.0-38.5°C',
                'moderado' => 'Temperatura 38.6-39.0°C',
                'severo' => 'Temperatura >39.0°C'
            ],
            'Náuseas' => [
                'leve' => 'Náuseas ocasionales sin vómito',
                'moderado' => 'Náuseas persistentes con arcadas',
                'severo' => 'Náuseas con vómito activo'
            ],
            'Ansiedad' => [
                'leve' => 'Inquietud leve, puede realizar actividades',
                'moderado' => 'Ansiedad que interfiere con concentración',
                'severo' => 'Crisis de pánico, incapacidad para funcionar'
            ],
            'default' => [
                'leve' => "Síntoma {$sintoma->nombre} de intensidad leve",
                'moderado' => "Síntoma {$sintoma->nombre} de intensidad moderada",
                'severo' => "Síntoma {$sintoma->nombre} de intensidad severa"
            ]
        ];
        
        $nombreSintoma = $sintoma->nombre;
        $criteriosSintoma = $criterios[$nombreSintoma] ?? $criterios['default'];
        
        return $criteriosSintoma[$intensidad];
    }
    
    private function generarObservacionPRN(SintomaPrn $sintoma, string $intensidad): string
    {
        $observaciones = [
            "Paciente reporta {$sintoma->nombre} {$intensidad}",
            "Administración según protocolo PRN para {$sintoma->nombre}",
            "Evaluado síntoma antes de administración",
            "Paciente solicitó medicamento para alivio",
            "Síntoma evaluado y medicación indicada"
        ];
        
        return $observaciones[array_rand($observaciones)];
    }
} 