<?php

namespace App\Services;

use App\Models\Tratamiento;
use App\Models\HorarioProgramado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HorarioService
{
    /**
     * Generar horarios automáticamente para un tratamiento programado
     */
    public function generarHorariosProgramados(Tratamiento $tratamiento, $horarioPrincipal = null)
    {
        if ($tratamiento->tipo !== 'Programado') {
            return; // Solo generar horarios para tratamientos programados
        }

        // Limpiar horarios existentes
        $this->limpiarHorariosExistentes($tratamiento);

        // Recargar la relación para asegurar que tenemos los IDs de pivot
        $tratamiento->load('medicamentos');

        foreach ($tratamiento->medicamentos as $medicamento) {
            $pivot = $medicamento->pivot;
            
            if (!$pivot || !$pivot->frecuencia_horas) {
                continue; // Saltar si no tiene pivot data o frecuencia definida
            }

            // Obtener el ID del registro pivot desde la base de datos
            $medicamentoTratamientoId = DB::table('medicamentos_tratamientos')
                ->where('tratamiento_id', $tratamiento->id)
                ->where('medicamento_id', $medicamento->id)
                ->value('id');

            if ($medicamentoTratamientoId) {
                $this->generarHorariosPorMedicamento($tratamiento, $medicamento, $pivot, $medicamentoTratamientoId, $horarioPrincipal);
            }
        }
    }

    /**
     * Generar horarios específicos para un medicamento
     */
    private function generarHorariosPorMedicamento($tratamiento, $medicamento, $pivot, $medicamentoTratamientoId, $horarioPrincipal = null)
    {
        $frecuencia = $pivot->frecuencia_horas;
        $fechaInicio = Carbon::parse($tratamiento->fecha_inicio);
        $fechaFin = $tratamiento->fecha_fin ? Carbon::parse($tratamiento->fecha_fin) : $fechaInicio->copy()->addDays(30);

        // Calcular cuántas tomas por día
        $tomasPorDia = 24 / $frecuencia;
        
        // Horarios base según la frecuencia y el horario principal del usuario
        $horariosBase = $this->obtenerHorariosBase($frecuencia, $horarioPrincipal);

        foreach ($horariosBase as $hora) {
            HorarioProgramado::create([
                'medicamento_tratamiento_id' => $medicamentoTratamientoId,
                'paciente_id' => $tratamiento->paciente_id,
                'hora_programada' => $hora,
                'dias_semana' => 'Daily', // Diario por defecto
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'activo' => true
            ]);
        }
    }

    /**
     * Obtener horarios base según la frecuencia y horario principal del usuario
     */
    private function obtenerHorariosBase($frecuenciaHoras, $horarioPrincipal = null)
    {
        $horarios = [];
        
        // Si se proporciona horario principal, usarlo como base
        $horaBase = 8; // Default: 8 AM
        if ($horarioPrincipal) {
            try {
                $horaBase = Carbon::parse($horarioPrincipal)->hour;
            } catch (\Exception $e) {
                \Log::warning("Error parsing horario principal: {$horarioPrincipal}. Using default 8 AM");
            }
        }

        // Calcular número de tomas por día
        $tomasPorDia = 24 / $frecuenciaHoras;

        switch ($frecuenciaHoras) {
            case 24: // Una vez al día
                $horarios = [sprintf('%02d:00:00', $horaBase)];
                break;
                
            case 12: // Dos veces al día
                $horarios = [
                    sprintf('%02d:00:00', $horaBase),
                    sprintf('%02d:00:00', ($horaBase + 12) % 24)
                ];
                break;
                
            case 8: // Tres veces al día
                $horarios = [
                    sprintf('%02d:00:00', $horaBase),
                    sprintf('%02d:00:00', ($horaBase + 8) % 24),
                    sprintf('%02d:00:00', ($horaBase + 16) % 24)
                ];
                break;
                
            case 6: // Cuatro veces al día
                $horarios = [
                    sprintf('%02d:00:00', $horaBase),
                    sprintf('%02d:00:00', ($horaBase + 6) % 24),
                    sprintf('%02d:00:00', ($horaBase + 12) % 24),
                    sprintf('%02d:00:00', ($horaBase + 18) % 24)
                ];
                break;
                
            case 4: // Seis veces al día
                $horarios = [
                    sprintf('%02d:00:00', $horaBase),
                    sprintf('%02d:00:00', ($horaBase + 4) % 24),
                    sprintf('%02d:00:00', ($horaBase + 8) % 24),
                    sprintf('%02d:00:00', ($horaBase + 12) % 24),
                    sprintf('%02d:00:00', ($horaBase + 16) % 24),
                    sprintf('%02d:00:00', ($horaBase + 20) % 24)
                ];
                break;
                
            default:
                // Para frecuencias personalizadas, calcular dinámicamente
                for ($i = 0; $i < $tomasPorDia; $i++) {
                    $hora = ($horaBase + ($i * $frecuenciaHoras)) % 24;
                    $horarios[] = sprintf('%02d:00:00', $hora);
                }
                break;
        }

        return $horarios;
    }

    /**
     * Limpiar horarios existentes de un tratamiento
     */
    private function limpiarHorariosExistentes(Tratamiento $tratamiento)
    {
        // Buscar horarios por medicamento-tratamiento
        $medicamentoTratamientoIds = $tratamiento->medicamentos()->pluck('medicamentos_tratamientos.id');
        
        HorarioProgramado::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds)->delete();
    }

    /**
     * Generar administraciones programadas para los próximos días
     */
    public function generarAdministracionesProgramadas(Tratamiento $tratamiento, $diasAdelante = 7)
    {
        if ($tratamiento->tipo !== 'Programado') {
            return;
        }

        $fechaInicio = Carbon::today();
        
        // Usar la fecha de fin del tratamiento si existe y es posterior a diasAdelante
        $fechaFinTratamiento = $tratamiento->fecha_fin ? Carbon::parse($tratamiento->fecha_fin) : null;
        $fechaFinCalculada = $fechaInicio->copy()->addDays($diasAdelante);
        
        // Usar la fecha más lejana entre las dos
        $fechaFin = $fechaFinTratamiento && $fechaFinTratamiento->gt($fechaFinCalculada) 
            ? $fechaFinTratamiento 
            : $fechaFinCalculada;

        foreach ($tratamiento->medicamentos as $medicamento) {
            $pivot = $medicamento->pivot;
            
            if (!$pivot) {
                continue; // Saltar si no hay pivot data
            }
            
            // Obtener el ID del registro pivot desde la base de datos
            $medicamentoTratamientoId = DB::table('medicamentos_tratamientos')
                ->where('tratamiento_id', $tratamiento->id)
                ->where('medicamento_id', $medicamento->id)
                ->value('id');
            
            if (!$medicamentoTratamientoId) {
                continue;
            }
            
            // Obtener horarios programados para este medicamento
            $horarios = HorarioProgramado::where('medicamento_tratamiento_id', $medicamentoTratamientoId)
                ->where('activo', true)
                ->get();

            foreach ($horarios as $horario) {
                $this->crearAdministracionesPorHorario($horario, $fechaInicio, $fechaFin, $medicamento, $pivot);
            }
        }
    }

    /**
     * Crear administraciones específicas por horario
     */
    private function crearAdministracionesPorHorario($horario, $fechaInicio, $fechaFin, $medicamento, $pivot)
    {
        $fechaActual = $fechaInicio->copy();

        // Obtener el ID del medicamento_tratamiento
        $medicamentoTratamientoId = DB::table('medicamentos_tratamientos')
            ->where('tratamiento_id', $pivot->tratamiento_id)
            ->where('medicamento_id', $medicamento->id)
            ->value('id');

        if (!$medicamentoTratamientoId) {
            return; // No se puede crear administración sin el ID pivot
        }

        // Verificar las fechas de vigencia del horario
        $fechaInicioHorario = Carbon::parse($horario->fecha_inicio);
        $fechaFinHorario = Carbon::parse($horario->fecha_fin);

        while ($fechaActual <= $fechaFin) {
            // Solo crear administraciones dentro del rango de vigencia del horario
            if ($fechaActual->gte($fechaInicioHorario) && $fechaActual->lte($fechaFinHorario)) {
                // Extraer solo la hora de la hora programada (puede venir como fecha completa)
                $soloHora = Carbon::parse($horario->hora_programada)->format('H:i:s');
                // Verificar si ya existe administración para este día y horario
                $fechaHoraProgramada = $fechaActual->copy()->setTimeFromTimeString($soloHora);
                
                $existeAdministracion = \App\Models\Administracion::where('medicamento_tratamiento_id', $medicamentoTratamientoId)
                    ->where('fecha_hora_programada', $fechaHoraProgramada)
                    ->exists();

                if (!$existeAdministracion) {
                    \App\Models\Administracion::create([
                        'medicamento_tratamiento_id' => $medicamentoTratamientoId,
                        'horario_programado_id' => $horario->id,
                        'paciente_id' => $horario->paciente_id,
                        'fecha_hora_programada' => $fechaHoraProgramada,
                        'fecha_hora_administrada' => $fechaHoraProgramada, // Se actualizará cuando se administre
                        'dosis_administrada' => $pivot->dosis_cantidad,
                        'estado' => \App\Models\Administracion::ESTADO_PENDIENTE,
                    ]);
                }
            }

            $fechaActual->addDay();
        }
    }

    /**
     * Obtener cronograma del día para un paciente
     */
    public function obtenerCronogramaDiario($pacienteId, $fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::today();

        return \App\Models\Administracion::with(['horarioProgramado', 'paciente'])
            ->where('paciente_id', $pacienteId)
            ->whereDate('fecha_hora_programada', $fecha)
            ->orderBy('fecha_hora_programada')
            ->get()
            ->groupBy('estado');
    }
} 