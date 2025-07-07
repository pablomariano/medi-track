<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\Medicamento;
use App\Models\Paciente;
use App\Services\HorarioService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MisTratamientosController extends Controller
{
    /**
     * Mostrar formulario de crear tratamiento para paciente
     */
    public function create()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('paciente')) {
            abort(403, 'Acceso denegado. Esta sección es solo para pacientes.');
        }

        // Obtener medicamentos disponibles
        $medicamentos = Medicamento::where('activo', true)
            ->select('id', 'nombre', 'principio_activo', 'forma_farmaceutica')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('MisTratamientos/Crear', [
            'medicamentos' => $medicamentos
        ]);
    }

    /**
     * Almacenar nuevo tratamiento creado por paciente
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('paciente')) {
            abort(403, 'Acceso denegado.');
        }

        // Obtener o crear el registro de paciente
        $paciente = $user->pacientes()->first();
        
        if (!$paciente) {
            // Crear automáticamente el registro de paciente si no existe
            $paciente = \App\Models\Paciente::create([
                'usuario_id' => $user->id,
                'nombre' => $user->nombre_completo,
                'fecha_nacimiento' => null, // Se puede completar después
                'genero_id' => null, // Se puede completar después
                'numero_documento' => null,
                'tipo_documento' => 'rut',
                'tipo_sangre' => null,
                'altura' => null,
                'direccion' => null,
                'telefono_emergencia' => null,
                'observaciones_medicas' => 'Paciente creado automáticamente para autogestión de tratamientos',
                'activo' => true
            ]);
        }

        // Validación simplificada para pacientes
        $request->validate([
            'medicamento_id' => 'nullable|exists:medicamentos,id',
            'medicamento_personalizado' => 'nullable|string|max:255',
            'dosis' => 'required|numeric|min:0.1',
            'unidad_dosis' => 'required|string|max:50',
            'frecuencia' => 'required|integer|min:1|max:6',
            'tipo_frecuencia' => 'required|in:diario,semanal',
            'horario_principal' => 'required|date_format:H:i',
            'duracion' => 'required|integer|min:1',
            'tipo_duracion' => 'required|in:dias,semanas,meses,indefinido',
            'indicaciones' => 'nullable|string|max:1000',
            'es_prn' => 'boolean'
        ], [
            'medicamento_id.exists' => 'El medicamento seleccionado no es válido.',
            'medicamento_personalizado.required_without' => 'Debe seleccionar un medicamento o escribir el nombre de uno nuevo.',
            'dosis.required' => 'La dosis es obligatoria.',
            'dosis.numeric' => 'La dosis debe ser un número.',
            'dosis.min' => 'La dosis debe ser mayor a 0.',
            'frecuencia.required' => 'La frecuencia es obligatoria.',
            'frecuencia.min' => 'La frecuencia debe ser al menos 1 vez.',
            'frecuencia.max' => 'La frecuencia no puede ser mayor a 6 veces.',
            'horario_principal.required' => 'El horario principal es obligatorio.',
            'horario_principal.date_format' => 'El horario debe tener formato HH:MM.',
            'duracion.required' => 'La duración es obligatoria.',
            'duracion.min' => 'La duración debe ser al menos 1.',
        ]);

        // Validar que se proporcione al menos un medicamento
        if (empty($request->medicamento_id) && empty($request->medicamento_personalizado)) {
            return back()->withErrors([
                'medicamento_personalizado' => 'Debe seleccionar un medicamento o escribir el nombre de uno nuevo.'
            ]);
        }

        try {
            // Crear o encontrar el medicamento
            $medicamento = null;
            if ($request->medicamento_id) {
                $medicamento = Medicamento::find($request->medicamento_id);
            } else {
                // Crear medicamento personalizado simple
                $medicamento = Medicamento::create([
                    'nombre' => $request->medicamento_personalizado,
                    'principio_activo' => $request->medicamento_personalizado,
                    'medida' => (float) $request->dosis,
                    'unidad_medida' => $request->unidad_dosis,
                    'forma_farmaceutica' => 'No especificada',
                    'via_administracion' => 'Oral',
                    'activo' => true,
                    'descripcion' => 'Medicamento creado por paciente'
                ]);
            }

            if (!$medicamento) {
                return back()->withErrors(['error' => 'Error al procesar el medicamento.']);
            }

            // Calcular fechas
            $fechaInicio = Carbon::today();
            $fechaFin = null;
            
            if ($request->tipo_duracion !== 'indefinido') {
                $duracion = (int) $request->duracion; // Convertir a entero
                switch ($request->tipo_duracion) {
                    case 'dias':
                        $fechaFin = $fechaInicio->copy()->addDays($duracion);
                        break;
                    case 'semanas':
                        $fechaFin = $fechaInicio->copy()->addWeeks($duracion);
                        break;
                    case 'meses':
                        $fechaFin = $fechaInicio->copy()->addMonths($duracion);
                        break;
                }
            }

            // Buscar un médico disponible para asignar al tratamiento de autogestión
            $medicoDisponible = \App\Models\PersonalMedico::first();
            
            if (!$medicoDisponible) {
                return back()->withErrors(['error' => 'No hay médicos disponibles en el sistema. Contacte al administrador.']);
            }

            // Crear el tratamiento
            $tratamiento = Tratamiento::create([
                'paciente_id' => $paciente->id,
                'medico_usuario_id' => $medicoDisponible->usuario_id, // Asignar médico disponible
                'nombre' => "Tratamiento de {$medicamento->nombre}",
                'tipo' => 'Programado',
                'estado' => 'Activo',
                'objetivo' => 'Autogestión de medicamento',
                'diagnostico' => 'Registrado por paciente',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'observaciones' => ($request->indicaciones ?: 'Tratamiento creado por el paciente a través de la plataforma.') . " [Médico asignado: {$medicoDisponible->usuario_id}]",
            ]);

            // Calcular frecuencia en horas
            $frecuencia = (int) $request->frecuencia; // Convertir a entero
            $frecuenciaHoras = 24; // Por defecto diario
            if ($request->tipo_frecuencia === 'diario') {
                $frecuenciaHoras = (int) (24 / $frecuencia);
            } elseif ($request->tipo_frecuencia === 'semanal') {
                $frecuenciaHoras = (int) (168 / $frecuencia); // 168 horas en una semana
            }

            // Asociar el medicamento al tratamiento
            $pivotData = [
                'dosis_cantidad' => (float) $request->dosis,
                'unidad_dosis' => $request->unidad_dosis,
                'frecuencia_horas' => $frecuenciaHoras,
                'tolerancia_antes_minutos' => 30,
                'tolerancia_despues_minutos' => 60,
                'instrucciones_especiales' => $request->indicaciones,
                'estado' => 'Activo',
                'orden' => 1,
                'activo' => true,
            ];

            $tratamiento->medicamentos()->attach($medicamento->id, $pivotData);
            
            // Verificar que la asociación fue exitosa
            $medicamentosAsociados = $tratamiento->medicamentos()->count();
            \Log::info("Tratamiento ID {$tratamiento->id}: {$medicamentosAsociados} medicamento(s) asociado(s)");
            
            if ($medicamentosAsociados === 0) {
                throw new \Exception('Error: No se pudo asociar el medicamento al tratamiento');
            }

            // Generar horarios automáticamente
            $tratamiento->load('medicamentos');
            $horarioService = new HorarioService();
            
            // Generar horarios programados automáticamente basados en la frecuencia y horario principal
            $horarioService->generarHorariosProgramados($tratamiento, $request->horario_principal);
            $horarioService->generarAdministracionesProgramadas($tratamiento, 30); // Extender a 30 días
            
            // Verificar que se generaron horarios
            $horariosGenerados = \App\Models\HorarioProgramado::where('paciente_id', $paciente->id)->count();
            $administracionesGeneradas = \App\Models\Administracion::where('paciente_id', $paciente->id)->count();
            \Log::info("Generados: {$horariosGenerados} horarios, {$administracionesGeneradas} administraciones para paciente ID {$paciente->id}");

            return redirect()->route('mis-tratamientos.index')
                ->with('success', '¡Tratamiento creado exitosamente! Ya puedes ver tus medicamentos en tu cronograma.');

        } catch (\Exception $e) {
            \Log::error('Error al crear tratamiento de paciente: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear el tratamiento. Intenta nuevamente.']);
        }
    }
} 