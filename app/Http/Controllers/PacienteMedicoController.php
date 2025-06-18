<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PersonalMedico;
use App\Models\PacienteMedico;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PacienteMedicoController extends Controller
{
    /**
     * Mostrar todas las asignaciones médico-paciente
     */
    public function index()
    {
        $asignaciones = PacienteMedico::with(['paciente', 'medico.user'])
            ->orderBy('fecha_asignacion', 'desc')
            ->paginate(15);

        // Calcular estadísticas
        $estadisticas = [
            'total' => PacienteMedico::count(),
            'vigentes' => PacienteMedico::vigentes()->count(),
            'principales' => PacienteMedico::principales()->vigentes()->count(),
            'finalizadas' => PacienteMedico::whereNotNull('fecha_fin')->where('fecha_fin', '<=', now())->count(),
        ];

        return Inertia::render('AsignacionesMedicos/Index', [
            'asignaciones' => $asignaciones,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Mostrar formulario para crear nueva asignación médico-paciente
     */
    public function create()
    {
        $pacientes = Paciente::activos()
            ->orderBy('nombre')
            ->get()
            ->map(function ($paciente) {
                return [
                    'id' => $paciente->id,
                    'nombre' => $paciente->nombre,
                    'documento' => $paciente->numero_documento,
                    'medicos_actuales' => $paciente->medicosVigentes()->count(),
                    'tiene_medico_principal' => $paciente->medicoPrincipal() ? true : false
                ];
            });

        $medicos = PersonalMedico::with('user')
            ->get()
            ->map(function ($medico) {
                return [
                    'usuario_id' => $medico->usuario_id,
                    'nombre' => $medico->nombre,
                    'email' => $medico->email,
                    'especialidad' => $medico->especialidad,
                    'anos_experiencia' => $medico->anos_experiencia,
                    'pacientes_actuales' => $medico->pacientesVigentes()->count(),
                    'pacientes_principales' => $medico->pacientesPrincipales()->count()
                ];
            });

        return Inertia::render('AsignacionesMedicos/Create', [
            'pacientes' => $pacientes,
            'medicos' => $medicos
        ]);
    }

    /**
     * Crear nueva asignación médico-paciente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_usuario_id' => 'required|exists:personal_medico,usuario_id',
            'es_medico_principal' => 'boolean',
            'fecha_asignacion' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_asignacion',
            'especialidad_tratamiento' => 'nullable|string|max:100'
        ]);

        // Verificar que no exista una asignación vigente entre el mismo paciente y médico
        $asignacionExistente = PacienteMedico::where([
            'paciente_id' => $validated['paciente_id'],
            'medico_usuario_id' => $validated['medico_usuario_id']
        ])->vigentes()->first();

        if ($asignacionExistente) {
            return back()->withErrors([
                'error' => 'Ya existe una asignación vigente entre este paciente y médico.'
            ]);
        }

        // Si se marca como médico principal, verificar que no haya otro médico principal activo
        if ($validated['es_medico_principal'] ?? false) {
            $medicoPrincipalExistente = PacienteMedico::where('paciente_id', $validated['paciente_id'])
                ->principales()
                ->vigentes()
                ->first();

            if ($medicoPrincipalExistente) {
                return back()->withErrors([
                    'error' => 'Este paciente ya tiene un médico principal asignado. Debe finalizar esa asignación primero.'
                ]);
            }
        }

        $validated['es_medico_principal'] = $validated['es_medico_principal'] ?? false;

        PacienteMedico::create($validated);

        return redirect()->route('asignaciones-medicos.index')
            ->with('success', 'Asignación médico-paciente creada exitosamente.');
    }

    /**
     * Mostrar asignación específica
     */
    public function show($pacienteId, $medicoId)
    {
        $asignacion = PacienteMedico::with(['paciente', 'medico.user'])
            ->where('paciente_id', $pacienteId)
            ->where('medico_usuario_id', $medicoId)
            ->firstOrFail();

        // Obtener tratamientos relacionados
        $tratamientos = $asignacion->paciente->tratamientos()
            ->where('medico_usuario_id', $medicoId)
            ->with('medicamentos')
            ->get();

        return Inertia::render('AsignacionesMedicos/Show', [
            'asignacion' => $asignacion,
            'tratamientos' => $tratamientos
        ]);
    }

    /**
     * Mostrar formulario para editar asignación
     */
    public function edit($pacienteId, $medicoId)
    {
        $asignacion = PacienteMedico::with(['paciente', 'medico.user'])
            ->where('paciente_id', $pacienteId)
            ->where('medico_usuario_id', $medicoId)
            ->firstOrFail();

        return Inertia::render('AsignacionesMedicos/Edit', [
            'asignacion' => $asignacion
        ]);
    }

    /**
     * Actualizar asignación médico-paciente
     */
    public function update(Request $request, $pacienteId, $medicoId)
    {
        $asignacion = PacienteMedico::where('paciente_id', $pacienteId)
            ->where('medico_usuario_id', $medicoId)
            ->firstOrFail();

        $validated = $request->validate([
            'es_medico_principal' => 'boolean',
            'fecha_asignacion' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_asignacion',
            'especialidad_tratamiento' => 'nullable|string|max:100'
        ]);

        // Si se cambia a médico principal, verificar que no haya otro médico principal activo
        if (($validated['es_medico_principal'] ?? false) && !$asignacion->es_medico_principal) {
            $medicoPrincipalExistente = PacienteMedico::where('paciente_id', $pacienteId)
                ->where('medico_usuario_id', '!=', $medicoId)
                ->principales()
                ->vigentes()
                ->first();

            if ($medicoPrincipalExistente) {
                return back()->withErrors([
                    'error' => 'Este paciente ya tiene un médico principal asignado. Debe finalizar esa asignación primero.'
                ]);
            }
        }

        $asignacion->update($validated);

        return redirect()->route('asignaciones-medicos.index')
            ->with('success', 'Asignación médico-paciente actualizada exitosamente.');
    }

    /**
     * Finalizar asignación médico-paciente
     */
    public function destroy($pacienteId, $medicoId)
    {
        $asignacion = PacienteMedico::where('paciente_id', $pacienteId)
            ->where('medico_usuario_id', $medicoId)
            ->firstOrFail();

        $asignacion->finalizar();

        return redirect()->route('asignaciones-medicos.index')
            ->with('success', 'Asignación médico-paciente finalizada exitosamente.');
    }

    /**
     * Asignar médico desde la vista del paciente
     */
    public function asignarDesdeView(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_usuario_id' => 'required|exists:personal_medico,usuario_id',
            'es_medico_principal' => 'boolean',
            'fecha_asignacion' => 'required|date',
            'especialidad_tratamiento' => 'nullable|string|max:100'
        ]);

        // Verificar asignación existente
        $asignacionExistente = PacienteMedico::where([
            'paciente_id' => $validated['paciente_id'],
            'medico_usuario_id' => $validated['medico_usuario_id']
        ])->vigentes()->first();

        if ($asignacionExistente) {
            return response()->json([
                'error' => 'Ya existe una asignación vigente entre este paciente y médico.'
            ], 422);
        }

        // Verificar médico principal si es necesario
        if ($validated['es_medico_principal'] ?? false) {
            $medicoPrincipalExistente = PacienteMedico::where('paciente_id', $validated['paciente_id'])
                ->principales()
                ->vigentes()
                ->first();

            if ($medicoPrincipalExistente) {
                return response()->json([
                    'error' => 'Este paciente ya tiene un médico principal asignado.'
                ], 422);
            }
        }

        $asignacion = PacienteMedico::create([
            'paciente_id' => $validated['paciente_id'],
            'medico_usuario_id' => $validated['medico_usuario_id'],
            'es_medico_principal' => $validated['es_medico_principal'] ?? false,
            'fecha_asignacion' => $validated['fecha_asignacion'],
            'especialidad_tratamiento' => $validated['especialidad_tratamiento']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Médico asignado exitosamente.',
            'asignacion' => $asignacion->load(['medico.user'])
        ]);
    }

    /**
     * Obtener médicos disponibles para un paciente
     */
    public function medicosDisponibles($pacienteId)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        
        // Obtener IDs de médicos ya asignados al paciente (asignaciones vigentes)
        $medicosAsignados = $paciente->medicosVigentes()
            ->pluck('personal_medico.usuario_id');

        // Obtener médicos no asignados
        $medicosDisponibles = PersonalMedico::with('user')
            ->whereNotIn('usuario_id', $medicosAsignados)
            ->get()
            ->map(function ($medico) {
                return [
                    'usuario_id' => $medico->usuario_id,
                    'nombre' => $medico->nombre,
                    'email' => $medico->email,
                    'especialidad' => $medico->especialidad,
                    'anos_experiencia' => $medico->anos_experiencia,
                    'pacientes_actuales' => $medico->pacientesVigentes()->count(),
                    'pacientes_principales' => $medico->pacientesPrincipales()->count()
                ];
            });

        return response()->json($medicosDisponibles);
    }

    /**
     * Mostrar historial completo de asignaciones con filtros avanzados
     */
    public function historial(Request $request)
    {
        $query = PacienteMedico::with([
            'paciente' => function($q) {
                $q->select('id', 'nombre', 'numero_documento', 'telefono_emergencia', 'activo');
            },
            'medico.user' => function($q) {
                $q->select('id', 'name', 'email');
            },
            'medico' => function($q) {
                $q->select('usuario_id', 'especialidad', 'anos_experiencia');
            }
        ]);

        // Filtros
        if ($request->filled('paciente_nombre')) {
            $query->whereHas('paciente', function($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->paciente_nombre}%")
                  ->orWhere('numero_documento', 'like', "%{$request->paciente_nombre}%");
            });
        }

        if ($request->filled('medico_nombre')) {
            $query->whereHas('medico.user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->medico_nombre}%")
                  ->orWhere('email', 'like', "%{$request->medico_nombre}%");
            });
        }

        if ($request->filled('especialidad')) {
            $query->whereHas('medico', function($q) use ($request) {
                $q->where('especialidad', 'like', "%{$request->especialidad}%");
            });
        }

        if ($request->filled('tipo_medico')) {
            if ($request->tipo_medico === 'principal') {
                $query->principales();
            } elseif ($request->tipo_medico === 'secundario') {
                $query->where('es_medico_principal', false);
            }
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'vigente') {
                $query->vigentes();
            } elseif ($request->estado === 'finalizada') {
                $query->whereNotNull('fecha_fin')->where('fecha_fin', '<=', now());
            }
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_asignacion', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_asignacion', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortColumn = $request->get('sort', 'fecha_asignacion');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortColumn === 'paciente') {
            $query->join('pacientes', 'paciente_medicos.paciente_id', '=', 'pacientes.id')
                  ->orderBy('pacientes.nombre', $sortDirection)
                  ->select('paciente_medicos.*');
        } elseif ($sortColumn === 'medico') {
            $query->join('personal_medico', 'paciente_medicos.medico_usuario_id', '=', 'personal_medico.usuario_id')
                  ->join('users', 'personal_medico.usuario_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDirection)
                  ->select('paciente_medicos.*');
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $perPage = $request->get('per_page', 20);
        $asignaciones = $query->paginate($perPage);

        // Agregar datos calculados
        $asignaciones->getCollection()->transform(function ($asignacion) {
            $asignacion->estado_calculado = $asignacion->estado;
            $asignacion->dias_restantes = $asignacion->dias_restantes;
            $asignacion->duracion_dias = $asignacion->duracion;
            return $asignacion;
        });

        // Estadísticas para filtros
        $estadisticas = [
            'total' => PacienteMedico::count(),
            'vigentes' => PacienteMedico::vigentes()->count(),
            'principales' => PacienteMedico::principales()->vigentes()->count(),
            'finalizadas' => PacienteMedico::whereNotNull('fecha_fin')->where('fecha_fin', '<=', now())->count(),
        ];

        // Listas para filtros
        $pacientes = Paciente::activos()
            ->select('id', 'nombre', 'numero_documento')
            ->orderBy('nombre')
            ->get();

        $medicos = PersonalMedico::with('user:id,name,email')
            ->get()
            ->map(function ($medico) {
                return [
                    'usuario_id' => $medico->usuario_id,
                    'nombre' => $medico->user->name,
                    'email' => $medico->user->email,
                    'especialidad' => $medico->especialidad,
                ];
            });

        $especialidades = PersonalMedico::distinct()
            ->pluck('especialidad')
            ->filter()
            ->sort()
            ->values();

        return Inertia::render('AsignacionesMedicos/Historial', [
            'asignaciones' => $asignaciones,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'pacientes' => $pacientes,
                'medicos' => $medicos,
                'especialidades' => $especialidades,
            ],
            'params' => $request->only([
                'paciente_nombre', 'medico_nombre', 'especialidad', 'tipo_medico', 
                'estado', 'fecha_desde', 'fecha_hasta', 'sort', 'direction', 'per_page'
            ])
        ]);
    }

    /**
     * Cambiar médico principal para un paciente
     */
    public function cambiarMedicoPrincipal(Request $request, $pacienteId)
    {
        $validated = $request->validate([
            'nuevo_medico_usuario_id' => 'required|exists:personal_medico,usuario_id',
        ]);

        $paciente = Paciente::findOrFail($pacienteId);

        // Finalizar asignación del médico principal actual si existe
        $medicoPrincipalActual = PacienteMedico::where('paciente_id', $pacienteId)
            ->principales()
            ->vigentes()
            ->first();

        if ($medicoPrincipalActual) {
            $medicoPrincipalActual->update(['es_medico_principal' => false]);
        }

        // Verificar si el nuevo médico ya está asignado
        $asignacionExistente = PacienteMedico::where([
            'paciente_id' => $pacienteId,
            'medico_usuario_id' => $validated['nuevo_medico_usuario_id']
        ])->vigentes()->first();

        if ($asignacionExistente) {
            // Marcar como principal
            $asignacionExistente->update(['es_medico_principal' => true]);
        } else {
            // Crear nueva asignación como principal
            PacienteMedico::create([
                'paciente_id' => $pacienteId,
                'medico_usuario_id' => $validated['nuevo_medico_usuario_id'],
                'es_medico_principal' => true,
                'fecha_asignacion' => now()->format('Y-m-d')
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Médico principal cambiado exitosamente.'
        ]);
    }
} 