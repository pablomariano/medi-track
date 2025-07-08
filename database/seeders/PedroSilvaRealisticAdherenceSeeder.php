<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\Medicamento;
use App\Models\Tratamiento;
use App\Models\MedicamentoTratamiento;
use App\Models\HorarioProgramado;
use App\Models\Administracion;
use Carbon\Carbon;

class PedroSilvaRealisticAdherenceSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar el paciente Pedro Silva Menor
        $paciente = Paciente::where('nombre', 'Pedro Silva Menor')->first();

        if (!$paciente) {
            $this->command->error('Paciente Pedro Silva Menor no encontrado');
            return;
        }

        $this->command->info("Generando datos de adherencia temporal para: {$paciente->nombre}");

        // Limpiar datos existentes de Pedro Silva
        // Primero eliminar administraciones
        Administracion::where('paciente_id', $paciente->id)->delete();
        
        // Luego horarios programados
        HorarioProgramado::where('paciente_id', $paciente->id)->delete();
        
        // Después medicamentos-tratamientos
        $tratamientosIds = Tratamiento::where('paciente_id', $paciente->id)->pluck('id');
        MedicamentoTratamiento::whereIn('tratamiento_id', $tratamientosIds)->delete();
        
        // Finalmente tratamientos
        Tratamiento::where('paciente_id', $paciente->id)->delete();

        // Crear múltiples medicamentos
        $medicamentos = [
            [
                'nombre' => 'Ibuprofeno',
                'medida' => '400',
                'unidad_medida' => 'mg',
                'descripcion' => 'Antiinflamatorio no esteroideo',
                'tipo' => 'analgesico',
                'horarios' => ['09:00', '21:00'], // 2 veces al día
                'variabilidad' => 'media'
            ],
            [
                'nombre' => 'Omeprazol',
                'medida' => '20',
                'unidad_medida' => 'mg',
                'descripcion' => 'Inhibidor de la bomba de protones',
                'tipo' => 'gastroprotector',
                'horarios' => ['08:00'], // 1 vez al día
                'variabilidad' => 'baja'
            ],
            [
                'nombre' => 'Metformina',
                'medida' => '850',
                'unidad_medida' => 'mg',
                'descripcion' => 'Antidiabético oral',
                'tipo' => 'antidiabetico',
                'horarios' => ['08:30', '14:30', '20:30'], // 3 veces al día
                'variabilidad' => 'baja'
            ],
            [
                'nombre' => 'Losartán',
                'medida' => '50',
                'unidad_medida' => 'mg',
                'descripcion' => 'Antagonista de los receptores de angiotensina II',
                'tipo' => 'antihipertensivo',
                'horarios' => ['07:00'], // 1 vez al día
                'variabilidad' => 'baja'
            ]
        ];

        $fechaInicio = Carbon::now()->subDays(14)->startOfDay();
        $fechaFin = Carbon::now()->endOfDay();

        foreach ($medicamentos as $medData) {
            // Crear medicamento
            $medicamento = Medicamento::create([
                'nombre' => $medData['nombre'],
                'descripcion' => $medData['descripcion'],
                'medida' => $medData['medida'],
                'unidad_medida' => $medData['unidad_medida'],
                'created_at' => $fechaInicio,
                'updated_at' => $fechaInicio,
            ]);

            // Crear tratamiento
            $tratamiento = Tratamiento::create([
                'paciente_id' => $paciente->id,
                'medico_usuario_id' => 2, // ID del médico existente
                'nombre' => "Tratamiento {$medData['nombre']}",
                'diagnostico' => "Tratamiento con {$medData['nombre']}",
                'observaciones' => $medData['descripcion'],
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin->copy()->addMonths(3),
                'estado' => 'Activo',
                'created_at' => $fechaInicio,
                'updated_at' => $fechaInicio,
            ]);

            // Crear relación medicamento-tratamiento
            $medicamentoTratamiento = MedicamentoTratamiento::create([
                'tratamiento_id' => $tratamiento->id,
                'medicamento_id' => $medicamento->id,
                'dosis_cantidad' => $medData['medida'],
                'unidad_dosis' => $medData['unidad_medida'],
                'frecuencia_horas' => 24 / count($medData['horarios']),
                'duracion_dias' => 90,
                'activo' => true,
                'created_at' => $fechaInicio,
                'updated_at' => $fechaInicio,
            ]);

            // Crear horarios programados y administraciones
            foreach ($medData['horarios'] as $hora) {
                $horarioProgramado = HorarioProgramado::create([
                    'medicamento_tratamiento_id' => $medicamentoTratamiento->id,
                    'paciente_id' => $paciente->id,
                    'hora_programada' => $hora,
                    'dias_semana' => 'Daily', // Todos los días
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin->copy()->addMonths(3),
                    'activo' => true,
                    'created_at' => $fechaInicio,
                    'updated_at' => $fechaInicio,
                ]);

                // Generar administraciones para los últimos 14 días
                $fechaActual = $fechaInicio->copy();
                
                while ($fechaActual->lte($fechaFin)) {
                    $horaProgamada = Carbon::createFromFormat('Y-m-d H:i', $fechaActual->format('Y-m-d') . ' ' . $hora);
                    
                    // Aplicar variabilidad según el patrón de Pedro Silva
                    $variacionMinutos = $this->calcularVariabilidad($fechaActual, $hora, $medData['variabilidad']);
                    $horaReal = $horaProgamada->copy()->addMinutes($variacionMinutos);

                    // Calcular métricas temporales
                    $minutosAdelanto = $variacionMinutos < 0 ? abs($variacionMinutos) : 0;
                    $minutosRetraso = $variacionMinutos > 0 ? $variacionMinutos : 0;
                    
                    // Score de puntualidad (100 = perfecto, decrece con la desviación)
                    $scorePuntualidad = max(0, 100 - (abs($variacionMinutos) * 2));
                    
                    // Categoría temporal
                    $categoriaTemp = 'puntual';
                    if (abs($variacionMinutos) > 15) {
                        $categoriaTemp = 'muy_tarde';
                    } elseif ($variacionMinutos > 5) {
                        $categoriaTemp = 'tarde';
                    } elseif ($variacionMinutos < -5) {
                        $categoriaTemp = 'muy_temprano';
                    } elseif ($variacionMinutos < 0) {
                        $categoriaTemp = 'temprano';
                    }

                    Administracion::create([
                        'medicamento_tratamiento_id' => $medicamentoTratamiento->id,
                        'horario_programado_id' => $horarioProgramado->id,
                        'paciente_id' => $paciente->id,
                        'fecha_hora_programada' => $horaProgamada,
                        'fecha_hora_administrada' => $horaReal,
                        'dosis_administrada' => $medData['medida'],
                        'estado' => 'Administrada',
                        'es_dentro_ventana_tolerancia' => abs($variacionMinutos) <= 30,
                        'minutos_diferencia' => $variacionMinutos,
                        'observaciones' => $this->generarObservacion($variacionMinutos),
                        'minutos_adelanto' => $minutosAdelanto,
                        'minutos_retraso' => $minutosRetraso,
                        'score_puntualidad' => $scorePuntualidad,
                        'categoria_temporal' => $categoriaTemp,
                        'created_at' => $horaReal,
                        'updated_at' => $horaReal,
                    ]);

                    $fechaActual->addDay();
                }
            }

            $this->command->info("✓ Creado tratamiento: {$medData['nombre']} con " . count($medData['horarios']) . " dosis diarias");
        }

        $totalAdministraciones = Administracion::where('paciente_id', $paciente->id)->count();

        $this->command->info("✓ Generadas {$totalAdministraciones} administraciones totales para Pedro Silva Menor");
        $this->command->info("✓ Período: {$fechaInicio->format('d/m/Y')} - {$fechaFin->format('d/m/Y')}");
    }

    /**
     * Calcula la variabilidad según el patrón sofisticado de Pedro Silva
     */
    private function calcularVariabilidad(Carbon $fecha, string $hora, string $tipoVariabilidad): int
    {
        $esFinDeSemana = $fecha->isWeekend();
        $esManana = (int)explode(':', $hora)[0] < 12;

        // Patrón de fin de semana: muy puntual
        if ($esFinDeSemana) {
            return rand(-15, 30); // retorna un valor entre -3 y 3 minutos de variabilidad
        }

        // Patrones de días de semana
        if ($esManana) {
            // Mañanas: baja variabilidad
            return rand(-3, 3);
        } else {
            // Tardes/noches: variabilidad según el tipo
            switch ($tipoVariabilidad) {
                case 'baja':
                    return rand(-15, 15);
                case 'media':
                    return rand(-27, 27);
                case 'alta':
                    return rand(-42, 38);
                default:
                    return rand(-15, 15);
            }
        }
    }

    /**
     * Genera observaciones basadas en la variación de tiempo
     */
    private function generarObservacion(int $variacionMinutos): string
    {
        if ($variacionMinutos == 0) {
            return 'Administrado puntualmente';
        } elseif ($variacionMinutos > 0) {
            if ($variacionMinutos <= 5) {
                return 'Ligero retraso';
            } elseif ($variacionMinutos <= 15) {
                return 'Retraso moderado';
            } else {
                return 'Retraso significativo';
            }
        } else {
            $adelanto = abs($variacionMinutos);
            if ($adelanto <= 5) {
                return 'Ligeramente adelantado';
            } elseif ($adelanto <= 15) {
                return 'Moderadamente adelantado';
            } else {
                return 'Significativamente adelantado';
            }
        }
    }
} 