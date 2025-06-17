<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Cuidador;
use App\Models\PacienteCuidador;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PacienteCuidadorController extends Controller
{
    /**
     * Mostrar todas las asignaciones
     */
    public function index()
    {
        $asignaciones = PacienteCuidador::with(['paciente', 'cuidador.user'])
            ->orderBy('fecha_asignacion', 'desc')
            ->paginate(15);

        return Inertia::render('AsignacionesCuidadores/Index', [
            'asignaciones' => $asignaciones
        ]);
    }

    /**
     * Mostrar formulario para crear nueva asignación
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
                    'cuidadores_actuales' => $paciente->cuidadoresVigentes()->count()
                ];
            });

        $cuidadores = Cuidador::with('user')
            ->get()
            ->map(function ($cuidador) {
                return [
                    'usuario_id' => $cuidador->usuario_id,
                    'nombre' => $cuidador->nombre,
                    'email' => $cuidador->email,
                    'experiencia_anos' => $cuidador->experiencia_anos,
                    'tarifa_hora' => $cuidador->tarifa_hora,
                    'pacientes_actuales' => $cuidador->pacientesVigentes()->count()
                ];
            });

        return Inertia::render('AsignacionesCuidadores/Create', [
            'pacientes' => $pacientes,
            'cuidadores' => $cuidadores
        ]);
    }

    /**
     * Crear nueva asignación
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'cuidador_usuario_id' => 'required|exists:cuidadores,usuario_id',
            'fecha_asignacion' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_asignacion',
            'activo' => 'boolean'
        ]);

        // Verificar que no exista una asignación activa entre el mismo paciente y cuidador
        $asignacionExistente = PacienteCuidador::where([
            'paciente_id' => $validated['paciente_id'],
            'cuidador_usuario_id' => $validated['cuidador_usuario_id'],
            'activo' => true
        ])->first();

        if ($asignacionExistente) {
            return back()->withErrors([
                'error' => 'Ya existe una asignación activa entre este paciente y cuidador.'
            ]);
        }

        $validated['activo'] = $validated['activo'] ?? true;

        PacienteCuidador::create($validated);

        return redirect()->route('asignaciones-cuidadores.index')
            ->with('success', 'Asignación creada exitosamente.');
    }

    /**
     * Mostrar asignación específica
     */
    public function show($pacienteId, $cuidadorId)
    {
        $asignacion = PacienteCuidador::with(['paciente', 'cuidador.user'])
            ->where('paciente_id', $pacienteId)
            ->where('cuidador_usuario_id', $cuidadorId)
            ->firstOrFail();

        return Inertia::render('AsignacionesCuidadores/Show', [
            'asignacion' => $asignacion
        ]);
    }

    /**
     * Mostrar formulario para editar asignación
     */
    public function edit($pacienteId, $cuidadorId)
    {
        $asignacion = PacienteCuidador::with(['paciente', 'cuidador.user'])
            ->where('paciente_id', $pacienteId)
            ->where('cuidador_usuario_id', $cuidadorId)
            ->firstOrFail();

        return Inertia::render('AsignacionesCuidadores/Edit', [
            'asignacion' => $asignacion
        ]);
    }

    /**
     * Actualizar asignación
     */
    public function update(Request $request, $pacienteId, $cuidadorId)
    {
        $asignacion = PacienteCuidador::where('paciente_id', $pacienteId)
            ->where('cuidador_usuario_id', $cuidadorId)
            ->firstOrFail();

        $validated = $request->validate([
            'fecha_asignacion' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_asignacion',
            'activo' => 'boolean'
        ]);

        $asignacion->update($validated);

        return redirect()->route('asignaciones-cuidadores.index')
            ->with('success', 'Asignación actualizada exitosamente.');
    }

    /**
     * Finalizar asignación (soft delete - marcar como inactiva)
     */
    public function destroy($pacienteId, $cuidadorId)
    {
        $asignacion = PacienteCuidador::where('paciente_id', $pacienteId)
            ->where('cuidador_usuario_id', $cuidadorId)
            ->firstOrFail();

        $asignacion->finalizar();

        return redirect()->route('asignaciones-cuidadores.index')
            ->with('success', 'Asignación finalizada exitosamente.');
    }

    /**
     * Asignar cuidador desde la vista del paciente
     */
    public function asignarDesdeView(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'cuidador_usuario_id' => 'required|exists:cuidadores,usuario_id',
            'fecha_asignacion' => 'required|date'
        ]);

        // Verificar asignación existente
        $asignacionExistente = PacienteCuidador::where([
            'paciente_id' => $validated['paciente_id'],
            'cuidador_usuario_id' => $validated['cuidador_usuario_id'],
            'activo' => true
        ])->first();

        if ($asignacionExistente) {
            return response()->json([
                'error' => 'Ya existe una asignación activa entre este paciente y cuidador.'
            ], 422);
        }

        $asignacion = PacienteCuidador::create([
            'paciente_id' => $validated['paciente_id'],
            'cuidador_usuario_id' => $validated['cuidador_usuario_id'],
            'fecha_asignacion' => $validated['fecha_asignacion'],
            'activo' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuidador asignado exitosamente.',
            'asignacion' => $asignacion->load(['cuidador.user'])
        ]);
    }

    /**
     * Obtener cuidadores disponibles para un paciente
     */
    public function cuidadoresDisponibles($pacienteId)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        
        // Obtener IDs de cuidadores ya asignados al paciente (asignaciones activas)
        $cuidadoresAsignados = $paciente->cuidadoresActivos()
            ->pluck('cuidadores.usuario_id');

        // Obtener cuidadores no asignados
        $cuidadoresDisponibles = Cuidador::with('user')
            ->whereNotIn('usuario_id', $cuidadoresAsignados)
            ->get()
            ->map(function ($cuidador) {
                return [
                    'usuario_id' => $cuidador->usuario_id,
                    'nombre' => $cuidador->nombre,
                    'email' => $cuidador->email,
                    'experiencia_anos' => $cuidador->experiencia_anos,
                    'tarifa_formateada' => $cuidador->tarifa_formateada,
                    'pacientes_actuales' => $cuidador->pacientesVigentes()->count()
                ];
            });

        return response()->json($cuidadoresDisponibles);
    }

    /**
     * Mostrar historial completo de asignaciones con filtros avanzados
     */
    public function historial(Request $request)
    {
        $query = PacienteCuidador::with([
            'paciente' => function($q) {
                $q->select('id', 'nombre', 'numero_documento', 'telefono_emergencia', 'activo');
            },
            'cuidador.user' => function($q) {
                $q->select('id', 'name', 'email');
            }
        ]);

        // Filtros
        if ($request->filled('paciente_nombre')) {
            $query->whereHas('paciente', function($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->paciente_nombre}%")
                  ->orWhere('numero_documento', 'like', "%{$request->paciente_nombre}%");
            });
        }

        if ($request->filled('cuidador_nombre')) {
            $query->whereHas('cuidador.user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->cuidador_nombre}%")
                  ->orWhere('email', 'like', "%{$request->cuidador_nombre}%");
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $query->activas();
            } elseif ($request->estado === 'inactivo') {
                $query->where('activo', false);
            } elseif ($request->estado === 'vigente') {
                $query->vigentes();
            }
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_asignacion', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_asignacion', '<=', $request->fecha_hasta);
        }

        if ($request->filled('experiencia_min')) {
            $query->whereHas('cuidador', function($q) use ($request) {
                $q->where('experiencia_anos', '>=', $request->experiencia_min);
            });
        }

        // Ordenamiento
        $sortColumn = $request->get('sort', 'fecha_asignacion');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortColumn === 'paciente') {
            $query->join('pacientes', 'paciente_cuidadores.paciente_id', '=', 'pacientes.id')
                  ->orderBy('pacientes.nombre', $sortDirection)
                  ->select('paciente_cuidadores.*');
        } elseif ($sortColumn === 'cuidador') {
            $query->join('cuidadores', 'paciente_cuidadores.cuidador_usuario_id', '=', 'cuidadores.usuario_id')
                  ->join('users', 'cuidadores.usuario_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDirection)
                  ->select('paciente_cuidadores.*');
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $perPage = $request->get('per_page', 20);
        $asignaciones = $query->paginate($perPage);

        // Agregar datos calculados
        $asignaciones->getCollection()->transform(function ($asignacion) {
            $asignacion->estado_calculado = $this->calcularEstado($asignacion);
            $asignacion->duracion_dias = $this->calcularDuracionDias($asignacion);
            return $asignacion;
        });

        // Estadísticas para filtros
        $estadisticas = [
            'total' => PacienteCuidador::count(),
            'activas' => PacienteCuidador::activas()->count(),
            'vigentes' => PacienteCuidador::vigentes()->count(),
            'finalizadas' => PacienteCuidador::where('activo', false)->count(),
        ];

        // Listas para filtros
        $pacientes = Paciente::activos()
            ->select('id', 'nombre', 'numero_documento')
            ->orderBy('nombre')
            ->get();

        $cuidadores = Cuidador::with('user:id,name,email')
            ->get()
            ->map(function ($cuidador) {
                return [
                    'usuario_id' => $cuidador->usuario_id,
                    'nombre' => $cuidador->user->name,
                    'email' => $cuidador->user->email,
                ];
            });

        return Inertia::render('AsignacionesCuidadores/Historial', [
            'asignaciones' => $asignaciones,
            'filtros' => $request->only([
                'paciente_nombre', 'cuidador_nombre', 'estado', 
                'fecha_desde', 'fecha_hasta', 'experiencia_min'
            ]),
            'estadisticas' => $estadisticas,
            'pacientes' => $pacientes,
            'cuidadores' => $cuidadores,
            'sort' => [
                'column' => $sortColumn,
                'direction' => $sortDirection
            ]
        ]);
    }

    /**
     * Calcular el estado actual de una asignación
     */
    private function calcularEstado($asignacion)
    {
        if (!$asignacion->activo) {
            return 'finalizada';
        }

        if ($asignacion->fecha_fin && $asignacion->fecha_fin < now()) {
            return 'vencida';
        }

        return 'vigente';
    }

    /**
     * Calcular duración en días
     */
    private function calcularDuracionDias($asignacion)
    {
        $inicio = $asignacion->fecha_asignacion;
        $fin = $asignacion->fecha_fin ?: now();
        
        return $inicio->diffInDays($fin);
    }
} 