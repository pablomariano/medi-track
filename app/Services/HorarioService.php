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
    public function generarHorariosProgramados(Tratamiento $tratamiento)
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
                $this->generarHorariosPorMedicamento($tratamiento, $medicamento, $pivot, $medicamentoTratamientoId);
            }
        }
    }

    /**
     * Generar horarios específicos para un medicamento
     */
    private function generarHorariosPorMedicamento($tratamiento, $medicamento, $pivot, $medicamentoTratamientoId)
    {
        $frecuencia = $pivot->frecuencia_horas;
        $fechaInicio = Carbon::parse($tratamiento->fecha_inicio);
        $fechaFin = $tratamiento->fecha_fin ? Carbon::parse($tratamiento->fecha_fin) : $fechaInicio->copy()->addDays(30);

        // Calcular cuántas tomas por día
        $tomasPorDia = 24 / $frecuencia;
        
        // Horarios base según la frecuencia
        $horariosBase = $this->obtenerHorariosBase($frecuencia);

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
     * Obtener horarios base según la frecuencia
     */
    private function obtenerHorariosBase($frecuenciaHoras)
    {
        $horarios = [];

        switch ($frecuenciaHoras) {
            case 24: // Una vez al día
                $horarios = ['08:00:00'];
                break;
            case 12: // Dos veces al día
                $horarios = ['08:00:00', '20:00:00'];
                break;
            case 8: // Tres veces al día
                $horarios = ['08:00:00', '16:00:00', '00:00:00'];
                break;
            case 6: // Cuatro veces al día
                $horarios = ['06:00:00', '12:00:00', '18:00:00', '00:00:00'];
                break;
            case 4: // Seis veces al día
                $horarios = ['04:00:00', '08:00:00', '12:00:00', '16:00:00', '20:00:00', '00:00:00'];
                break;
            default:
                // Para frecuencias personalizadas, empezar a las 8 AM
                $horaActual = 8;
                $tomasAlDia = 24 / $frecuenciaHoras;
                for ($i = 0; $i < $tomasAlDia; $i++) {
                    $horarios[] = sprintf('%02d:00:00', $horaActual % 24);
                    $horaActual += $frecuenciaHoras;
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
        $fechaFin = $fechaInicio->copy()->addDays($diasAdelante);

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

        while ($fechaActual <= $fechaFin) {
            // Verificar si ya existe administración para este día y horario
            $fechaHoraProgramada = $fechaActual->copy()->setTimeFromTimeString($horario->hora_programada);
            
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