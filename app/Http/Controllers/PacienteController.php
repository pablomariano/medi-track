<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\User;
use App\Models\Genero;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacienteController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Paciente::class);
        
        $query = Paciente::with(['user', 'genero']);
        
        // Filtrar pacientes según el rol del usuario
        if (auth()->user()->hasRole('cuidador')) {
            $query->whereHas('cuidadores', function($q) {
                $q->where('cuidador_usuario_id', auth()->id())
                  ->where('activo', true);
            });
        } elseif (auth()->user()->hasRole('apoderado')) {
            $query->whereHas('apoderados', function($q) {
                $q->where('apoderado_usuario_id', auth()->id());
            });
        } elseif (auth()->user()->hasRole('paciente')) {
            $query->where('usuario_id', auth()->id());
        }
        
        $pacientes = $query->latest('created_at')->paginate(10);
        
        return Inertia::render('Pacientes/Index', [
            'pacientes' => $pacientes
        ]);
    }

    public function create()
    {
        $this->authorize('create', Paciente::class);
        
        // Obtener usuarios que no son pacientes aún
        $usuariosDisponibles = User::whereNotIn('id', function($query) {
            $query->select('usuario_id')
                  ->from('pacientes')
                  ->whereNotNull('usuario_id');
        })->get();
        
        $generos = Genero::all();
        
        return Inertia::render('Pacientes/Create', [
            'usuarios' => $usuariosDisponibles,
            'generos' => $generos
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Paciente::class);
        
        $validated = $request->validate([
            'usuario_id' => 'nullable|exists:users,id|unique:pacientes',
            'nombre' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero_id' => 'nullable|exists:generos,id',
            'numero_documento' => 'nullable|string|max:20|unique:pacientes',
            'tipo_documento' => 'nullable|string|max:10',
            'tipo_sangre' => 'nullable|string|max:10',
            'altura' => 'nullable|numeric|min:0|max:300',
            'direccion' => 'nullable|string',
            'telefono_emergencia' => 'nullable|string|max:20',
            'observaciones_medicas' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        Paciente::create($validated);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente creado exitosamente.');
    }

    public function show(Paciente $paciente)
    {
        $this->authorize('view', $paciente);
        
        $paciente->load(['user', 'genero', 'cuidadoresVigentes.user']);
        
        return Inertia::render('Pacientes/Show', [
            'paciente' => $paciente
        ]);
    }

    public function edit(Paciente $paciente)
    {
        $this->authorize('update', $paciente);
        
        $paciente->load(['user', 'genero']);
        
        // Obtener usuarios disponibles (incluir el actual si existe)
        $usuariosDisponibles = User::whereNotIn('id', function($query) use ($paciente) {
            $query->select('usuario_id')
                  ->from('pacientes')
                  ->whereNotNull('usuario_id')
                  ->where('id', '!=', $paciente->id);
        })->get();
        
        $generos = Genero::all();
        
        return Inertia::render('Pacientes/Edit', [
            'paciente' => $paciente,
            'usuarios' => $usuariosDisponibles,
            'generos' => $generos
        ]);
    }

    public function update(Request $request, Paciente $paciente)
    {
        $this->authorize('update', $paciente);
        
        $validated = $request->validate([
            'usuario_id' => 'nullable|exists:users,id|unique:pacientes,usuario_id,' . $paciente->id,
            'nombre' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero_id' => 'nullable|exists:generos,id',
            'numero_documento' => 'nullable|string|max:20|unique:pacientes,numero_documento,' . $paciente->id,
            'tipo_documento' => 'nullable|string|max:10',
            'tipo_sangre' => 'nullable|string|max:10',
            'altura' => 'nullable|numeric|min:0|max:300',
            'direccion' => 'nullable|string',
            'telefono_emergencia' => 'nullable|string|max:20',
            'observaciones_medicas' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $paciente->update($validated);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado exitosamente.');
    }

    public function destroy(Paciente $paciente)
    {
        $this->authorize('delete', $paciente);
        
        $paciente->delete();

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente eliminado exitosamente.');
    }

    /**
     * Dashboard personalizado para el paciente autenticado
     */
    public function miDashboard()
    {
        $user = auth()->user();
        
        // Verificar que el usuario es un paciente
        if (!$user->hasRole('paciente')) {
            abort(403, 'Acceso denegado. Esta sección es solo para pacientes.');
        }

        // Obtener el registro de paciente
        $paciente = $user->pacientes()->first();
        
        if (!$paciente) {
            return Inertia::render('MiPerfil/Index', [
                'error' => 'No se encontró información de paciente. Contacte al administrador.'
            ]);
        }

        // Obtener tratamientos activos
        $tratamientosActivos = $paciente->tratamientos()
            ->where('tratamientos.estado', 'Activo')
            ->with(['medicamentos', 'medico.user'])
            ->get();

        // Obtener próximas administraciones (próximas 24 horas)
        $proximasAdministraciones = \App\Models\Administracion::where('paciente_id', $paciente->id)
            ->where('administraciones.estado', 'Pendiente')
            ->whereBetween('fecha_hora_programada', [now(), now()->addHours(24)])
            ->with(['medicamentoTratamiento.medicamento'])
            ->orderBy('fecha_hora_programada')
            ->limit(5)
            ->get();

        // Calcular estadísticas de adherencia
        $totalAdministraciones = \App\Models\Administracion::where('paciente_id', $paciente->id)
            ->whereDate('fecha_hora_programada', '>=', now()->subDays(7))
            ->count();

        $administracionesCompletadas = \App\Models\Administracion::where('paciente_id', $paciente->id)
            ->where('administraciones.estado', 'Administrada')
            ->whereDate('fecha_hora_programada', '>=', now()->subDays(7))
            ->count();

        $adherencia = $totalAdministraciones > 0 
            ? round(($administracionesCompletadas / $totalAdministraciones) * 100, 1)
            : 0;

        return Inertia::render('DashboardPaciente', [
            'paciente' => $paciente->load('genero'),
            'tratamientos_activos' => $tratamientosActivos,
            'proximas_administraciones' => $proximasAdministraciones,
            'estadisticas' => [
                'tratamientos_activos' => $tratamientosActivos->count(),
                'adherencia_7_dias' => $adherencia,
                'dosis_pendientes_hoy' => \App\Models\Administracion::where('paciente_id', $paciente->id)
                    ->where('administraciones.estado', 'Pendiente')
                    ->whereDate('fecha_hora_programada', today())
                    ->count(),
                'proxima_dosis' => $proximasAdministraciones->first()
            ]
        ]);
    }

    /**
     * Mis medicamentos - página específica del paciente
     */
    public function misMedicamentos()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('paciente')) {
            abort(403, 'Acceso denegado.');
        }

        $paciente = $user->pacientes()->first();
        
        if (!$paciente) {
            return redirect()->route('mi-perfil.index')
                ->with('error', 'No se encontró información de paciente.');
        }

        // Obtener medicamentos asignados a través de tratamientos activos
        $medicamentosIds = $paciente->tratamientos()
            ->where('tratamientos.estado', 'Activo')
            ->with('medicamentos')
            ->get()
            ->pluck('medicamentos')
            ->flatten()
            ->pluck('id')
            ->unique();

        $medicamentos = \App\Models\Medicamento::whereIn('id', $medicamentosIds)
            ->with(['tratamientos' => function($query) use ($paciente) {
                $query->where('paciente_id', $paciente->id)
                      ->where('tratamientos.estado', 'Activo');
            }])
            ->get()
            ->map(function($medicamento) use ($paciente) {
                // Añadir información específica del paciente
                $tratamiento = $medicamento->tratamientos->first();
                $medicamento->tratamiento_actual = $tratamiento;
                
                if ($tratamiento) {
                    $pivotData = $tratamiento->medicamentos()
                        ->where('medicamento_id', $medicamento->id)
                        ->first();
                    
                    if ($pivotData && $pivotData->pivot) {
                        $medicamento->dosis_actual = [
                            'cantidad' => $pivotData->pivot->dosis_cantidad,
                            'unidad' => $pivotData->pivot->unidad_dosis,
                            'frecuencia_horas' => $pivotData->pivot->frecuencia_horas,
                            'instrucciones' => $pivotData->pivot->instrucciones_especiales,
                            'tolerancia_antes' => $pivotData->pivot->tolerancia_antes_minutos,
                            'tolerancia_despues' => $pivotData->pivot->tolerancia_despues_minutos,
                            'duracion_dias' => $pivotData->pivot->duracion_dias,
                            'estado' => $pivotData->pivot->estado
                        ];
                    } else {
                        $medicamento->dosis_actual = null;
                    }
                }

                return $medicamento;
            });

        $categorias = $medicamentos->pluck('categoria_terapeutica')
            ->filter()
            ->unique()
            ->values();

        return Inertia::render('MisMedicamentos/Index', [
            'medicamentos' => $medicamentos,
            'categorias' => $categorias,
            'estadisticas' => [
                'total_medicamentos' => $medicamentos->count(),
                'en_tratamiento' => $medicamentos->count(), // Todos están en tratamiento
                'disponibles' => $medicamentos->where('activo', true)->count()
            ]
        ]);
    }

    /**
     * Mi cronograma - página específica del paciente
     */
    public function miCronograma(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('paciente')) {
            abort(403, 'Acceso denegado.');
        }

        $paciente = $user->pacientes()->first();
        
        if (!$paciente) {
            return redirect()->route('mi-perfil.index')
                ->with('error', 'No se encontró información de paciente.');
        }

        $fecha = $request->get('fecha', today()->toDateString());
        $fechaObj = \Carbon\Carbon::parse($fecha);

        // Obtener administraciones del día seleccionado
        $administraciones = \App\Models\Administracion::where('paciente_id', $paciente->id)
            ->whereDate('fecha_hora_programada', $fechaObj)
            ->with([
                'medicamentoTratamiento.medicamento',
                'medicamentoTratamiento.tratamiento'
            ])
            ->orderBy('fecha_hora_programada')
            ->get()
            ->map(function($admin) {
                return [
                    'id' => $admin->id,
                    'fecha_hora_programada' => $admin->fecha_hora_programada,
                    'fecha_hora_administrada' => $admin->fecha_hora_administrada,
                    'dosis_administrada' => $admin->dosis_administrada,
                    'unidad_dosis' => $admin->medicamentoTratamiento->unidad_dosis ?? 'unidad',
                    'estado' => strtolower($admin->estado),
                    'observaciones' => $admin->observaciones,
                    'medicamento' => [
                        'id' => $admin->medicamentoTratamiento->medicamento->id,
                        'nombre' => $admin->medicamentoTratamiento->medicamento->nombre,
                        'principio_activo' => $admin->medicamentoTratamiento->medicamento->principio_activo
                    ],
                    'tratamiento' => [
                        'id' => $admin->medicamentoTratamiento->tratamiento->id,
                        'nombre' => $admin->medicamentoTratamiento->tratamiento->nombre
                    ]
                ];
            });

        // Calcular estadísticas del día
        $total = $administraciones->count();
        $administradas = $administraciones->where('estado', 'administrada')->count();
        $pendientes = $administraciones->where('estado', 'pendiente')->count();
        $omitidas = $administraciones->where('estado', 'omitida')->count();
        $cumplimiento = $total > 0 ? round(($administradas / $total) * 100, 1) : 0;

        // Generar fechas disponibles (últimos 7 días y próximos 3)
        $fechasDisponibles = collect();
        for ($i = -7; $i <= 3; $i++) {
            $fechasDisponibles->push([
                'fecha' => today()->addDays($i)->toDateString(),
                'label' => today()->addDays($i)->format('d/m/Y'),
                'es_hoy' => $i === 0
            ]);
        }

        return Inertia::render('MiCronograma/Index', [
            'cronograma' => $administraciones,
            'fecha' => $fecha,
            'estadisticas' => [
                'total' => $total,
                'administradas' => $administradas,
                'pendientes' => $pendientes,
                'omitidas' => $omitidas,
                'cumplimiento' => $cumplimiento
            ],
            'fechas_disponibles' => $fechasDisponibles
        ]);
    }

    /**
     * Mis tratamientos - página específica del paciente
     */
    public function misTratamientos()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('paciente')) {
            abort(403, 'Acceso denegado.');
        }

        $paciente = $user->pacientes()->first();
        
        if (!$paciente) {
            return redirect()->route('mi-perfil.index')
                ->with('error', 'No se encontró información de paciente.');
        }

        $tratamientos = $paciente->tratamientos()
            ->with(['medicamentos', 'medico'])
            ->get()
            ->map(function($tratamiento) use ($paciente) {
                // Calcular adherencia del tratamiento
                $totalAdministraciones = \App\Models\Administracion::where('paciente_id', $paciente->id)
                    ->whereHas('medicamentoTratamiento', function($query) use ($tratamiento) {
                        $query->where('tratamiento_id', $tratamiento->id);
                    })
                    ->count();

                $administracionesCompletadas = \App\Models\Administracion::where('paciente_id', $paciente->id)
                    ->where('administraciones.estado', 'Administrada')
                    ->whereHas('medicamentoTratamiento', function($query) use ($tratamiento) {
                        $query->where('tratamiento_id', $tratamiento->id);
                    })
                    ->count();

                $adherencia = $totalAdministraciones > 0 
                    ? round(($administracionesCompletadas / $totalAdministraciones) * 100, 1)
                    : 0;

                // Formatear medicamentos con dosificación
                $medicamentosFormateados = $tratamiento->medicamentos->map(function($medicamento) {
                    $pivot = $medicamento->pivot;
                    return [
                        'id' => $medicamento->id,
                        'nombre' => $medicamento->nombre,
                        'principio_activo' => $medicamento->principio_activo,
                        'presentacion' => $medicamento->presentacion,
                        'dosis' => $pivot->dosis_cantidad ?? 0,
                        'unidad_dosis' => $pivot->unidad_dosis ?? 'unidad',
                        'frecuencia' => $pivot->frecuencia_horas ?? 0,
                        'tipo_frecuencia' => 'horas',
                        'instrucciones' => $pivot->instrucciones_especiales,
                        'tolerancia_antes' => $pivot->tolerancia_antes_minutos ?? 30,
                        'tolerancia_despues' => $pivot->tolerancia_despues_minutos ?? 60,
                        'duracion_dias' => $pivot->duracion_dias,
                        'estado_medicamento' => $pivot->estado ?? 'Activo'
                    ];
                });

                return [
                    'id' => $tratamiento->id,
                    'nombre' => $tratamiento->nombre,
                    'fecha_inicio' => $tratamiento->fecha_inicio,
                    'fecha_fin' => $tratamiento->fecha_fin,
                    'estado' => strtolower($tratamiento->estado),
                    'indicaciones' => $tratamiento->observaciones,
                    'medicamentos' => $medicamentosFormateados,
                    'adherencia' => [
                        'porcentaje' => $adherencia,
                        'administraciones_completadas' => $administracionesCompletadas,
                        'administraciones_totales' => $totalAdministraciones
                    ]
                ];
            });

        // Calcular estadísticas generales
        $tratamientosActivos = $tratamientos->where('estado', 'activo');
        $adherenciaPromedio = $tratamientosActivos->isNotEmpty() 
            ? $tratamientosActivos->avg('adherencia.porcentaje')
            : 0;

        // Próxima administración
        $proximaAdministracion = \App\Models\Administracion::where('paciente_id', $paciente->id)
            ->where('administraciones.estado', 'Pendiente')
            ->where('fecha_hora_programada', '>', now())
            ->with('medicamentoTratamiento.medicamento')
            ->orderBy('fecha_hora_programada')
            ->first();

        return Inertia::render('MisTratamientos/Index', [
            'tratamientos' => $tratamientos,
            'estadisticas' => [
                'total_activos' => $tratamientosActivos->count(),
                'adherencia_promedio' => round($adherenciaPromedio, 1),
                'proxima_administracion' => $proximaAdministracion ? [
                    'fecha_hora' => $proximaAdministracion->fecha_hora_programada,
                    'medicamento' => $proximaAdministracion->medicamentoTratamiento->medicamento->nombre
                ] : null
            ]
        ]);
    }

    /**
     * Confirmar administración de medicamento
     */
    public function confirmarAdministracion(Request $request, $administracionId)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('paciente')) {
            abort(403, 'Acceso denegado.');
        }

        $paciente = $user->pacientes()->first();
        
        if (!$paciente) {
            return back()->withErrors(['error' => 'Paciente no encontrado']);
        }

        $administracion = \App\Models\Administracion::where('id', $administracionId)
            ->where('paciente_id', $paciente->id)
            ->where('administraciones.estado', 'Pendiente')
            ->first();

        if (!$administracion) {
            return back()->withErrors(['error' => 'Administración no encontrada']);
        }

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
            'efectos_observados' => 'nullable|string|max:500'
        ]);

        $administracion->update([
            'estado' => 'Administrada',
            'fecha_hora_administrada' => now(),
            'observaciones' => $request->observaciones,
            'efectos_adversos' => $request->efectos_observados
        ]);

        return back()->with('success', 'Administración confirmada correctamente');
    }

    /**
     * Omitir administración de medicamento
     */
    public function omitirAdministracion(Request $request, $administracionId)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('paciente')) {
            abort(403, 'Acceso denegado.');
        }

        $paciente = $user->pacientes()->first();
        
        if (!$paciente) {
            return back()->withErrors(['error' => 'Paciente no encontrado']);
        }

        $administracion = \App\Models\Administracion::where('id', $administracionId)
            ->where('paciente_id', $paciente->id)
            ->where('administraciones.estado', 'Pendiente')
            ->first();

        if (!$administracion) {
            return back()->withErrors(['error' => 'Administración no encontrada']);
        }

        $request->validate([
            'motivo' => 'required|string|max:500'
        ]);

        $administracion->update([
            'estado' => 'Omitida',
            'observaciones' => $request->motivo
        ]);

        return back()->with('success', 'Administración omitida correctamente');
    }
} 