<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\PersonalMedico;
use App\Models\Medicamento;
use App\Models\UnidadMedida;
use App\Models\HistorialTratamiento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TratamientosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Tratamiento::with([
                'paciente:id,nombre',
                'medico.user:id,name',
                'medicamentos' => function($q) {
                    $q->select('medicamentos.id', 'medicamentos.nombre_comercial')
                      ->with('principioActivo:id,nombre_generico');
                }
            ]);

            // Filtros
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('diagnostico', 'like', "%{$search}%")
                      ->orWhereHas('paciente', function($subQ) use ($search) {
                          $subQ->where('nombre', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('paciente_id')) {
                $query->where('paciente_id', $request->paciente_id);
            }

            if ($request->filled('medico_id')) {
                $query->where('medico_usuario_id', $request->medico_id);
            }

            if ($request->filled('fecha_desde')) {
                $query->where('fecha_inicio', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->where('fecha_inicio', '<=', $request->fecha_hasta);
            }

            // Ordenamiento
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            $allowedSorts = ['nombre', 'diagnostico', 'estado', 'fecha_inicio', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }

            $tratamientos = $query->paginate(15)->withQueryString();

            // Datos para filtros
            $pacientes = Paciente::select('id', 'nombre')
                                ->where('activo', true)
                                ->orderBy('nombre')
                                ->get();

            $medicos = PersonalMedico::with('user:id,name')
                                   ->get()
                                   ->map(function($medico) {
                                       return [
                                           'id' => $medico->usuario_id,
                                           'nombre' => $medico->user->name ?? 'Sin nombre'
                                       ];
                                   });

            // Estadísticas para el dashboard
            $stats = [
                'total' => Tratamiento::count(),
                'activos' => Tratamiento::where('estado', Tratamiento::ESTADO_ACTIVO)->count(),
                'pausados' => Tratamiento::where('estado', Tratamiento::ESTADO_PAUSADO)->count(),
                'completados' => Tratamiento::where('estado', Tratamiento::ESTADO_COMPLETADO)->count(),
            ];

            return Inertia::render('Medicamentos/Tratamientos/index', [
                'tratamientos' => $tratamientos,
                'pacientes' => $pacientes,
                'medicos' => $medicos,
                'stats' => $stats,
                'filters' => $request->only(['search', 'estado', 'paciente_id', 'medico_id', 'fecha_desde', 'fecha_hasta']),
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TratamientosController@index: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al cargar los tratamientos']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Obtener datos para el formulario
            $pacientes = Paciente::select('id', 'nombre', 'fecha_nacimiento')
                                ->where('activo', true)
                                ->orderBy('nombre')
                                ->get()
                                ->map(function($paciente) {
                                    return [
                                        'id' => $paciente->id,
                                        'nombre' => $paciente->nombre,
                                        'edad' => $paciente->edad ?? 'N/A'
                                    ];
                                });

            $medicos = PersonalMedico::with(['user:id,name', 'user.profile'])
                                   ->get()
                                   ->map(function($medico) {
                                       return [
                                           'id' => $medico->usuario_id,
                                           'nombre' => $medico->user->name ?? 'Sin nombre',
                                           'especialidad' => $medico->especialidad,
                                           'institucion' => $medico->institucion
                                       ];
                                   });

            $medicamentos = Medicamento::with([
                                'principioActivo:id,nombre_generico,grupo_farmacologico',
                                'formaFarmaceutica:id,nombre',
                                'viaAdministracion:id,nombre',
                                'unidadConcentracion:id,nombre,simbolo'
                            ])
                            ->where('activo', true)
                            ->where('stock_actual', '>', 0)
                            ->get()
                            ->map(function($medicamento) {
                                return [
                                    'id' => $medicamento->id,
                                    'nombre_comercial' => $medicamento->nombre_comercial,
                                    'principio_activo' => $medicamento->principioActivo->nombre_generico,
                                    'grupo_farmacologico' => $medicamento->principioActivo->grupo_farmacologico,
                                    'concentracion' => $medicamento->concentracion,
                                    'unidad_concentracion' => $medicamento->unidadConcentracion->simbolo,
                                    'forma_farmaceutica' => $medicamento->formaFarmaceutica->nombre,
                                    'via_administracion' => $medicamento->viaAdministracion->nombre,
                                    'stock_actual' => $medicamento->stock_actual
                                ];
                            });

            $unidadesDosis = UnidadMedida::select('id', 'nombre', 'simbolo', 'tipo')
                                       ->where('activo', true)
                                       ->orderBy('nombre')
                                       ->get();

            return Inertia::render('Medicamentos/Tratamientos/create', [
                'pacientes' => $pacientes,
                'medicos' => $medicos,
                'medicamentos' => $medicamentos,
                'unidadesDosis' => $unidadesDosis,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TratamientosController@create: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al cargar el formulario de creación']);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'medico_usuario_id' => 'nullable|exists:personal_medico,usuario_id',
                'nombre' => 'required|string|max:100',
                'diagnostico' => 'nullable|string|max:200',
                'objetivo_terapeutico' => 'nullable|string|max:1000',
                'fecha_inicio' => 'required|date|after_or_equal:today',
                'fecha_fin_estimada' => 'nullable|date|after:fecha_inicio',
                'medico_prescriptor' => 'nullable|string|max:100',
                'institucion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string|max:1000',
                'medicamentos' => 'required|array|min:1',
                'medicamentos.*.medicamento_id' => 'required|exists:medicamentos,id',
                'medicamentos.*.tipo_esquema' => 'required|in:Fijo,Variable,PRN,Escalonamiento,Reduccion,Alterno',
                'medicamentos.*.dosis_cantidad' => 'required|numeric|min:0.001',
                'medicamentos.*.unidad_dosis_id' => 'required|exists:unidades_medida,id',
                'medicamentos.*.frecuencia_horas' => 'nullable|integer|min:1|max:168',
                'medicamentos.*.duracion_dias' => 'nullable|integer|min:1',
                'medicamentos.*.indicaciones_uso' => 'nullable|string|max:500',
                'medicamentos.*.orden_prescripcion' => 'nullable|integer|min:1',
            ], [
                'paciente_id.required' => 'Debe seleccionar un paciente',
                'nombre.required' => 'El nombre del tratamiento es obligatorio',
                'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
                'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy',
                'medicamentos.required' => 'Debe agregar al menos un medicamento al tratamiento',
                'medicamentos.*.medicamento_id.required' => 'Debe seleccionar un medicamento válido',
                'medicamentos.*.dosis_cantidad.required' => 'La dosis es obligatoria',
                'medicamentos.*.dosis_cantidad.min' => 'La dosis debe ser mayor a 0',
            ]);

            DB::beginTransaction();

            // Crear el tratamiento
            $tratamiento = Tratamiento::create([
                'paciente_id' => $validated['paciente_id'],
                'medico_usuario_id' => $validated['medico_usuario_id'],
                'nombre' => $validated['nombre'],
                'diagnostico' => $validated['diagnostico'],
                'objetivo_terapeutico' => $validated['objetivo_terapeutico'],
                'estado' => Tratamiento::ESTADO_ACTIVO,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
                'medico_prescriptor' => $validated['medico_prescriptor'],
                'institucion' => $validated['institucion'],
                'observaciones' => $validated['observaciones'],
            ]);

            // Agregar medicamentos al tratamiento
            foreach ($validated['medicamentos'] as $index => $medicamentoData) {
                $tratamiento->medicamentoTratamientos()->create([
                    'medicamento_id' => $medicamentoData['medicamento_id'],
                    'tipo_esquema' => $medicamentoData['tipo_esquema'],
                    'dosis_cantidad' => $medicamentoData['dosis_cantidad'],
                    'unidad_dosis_id' => $medicamentoData['unidad_dosis_id'],
                    'frecuencia_horas' => $medicamentoData['frecuencia_horas'],
                    'duracion_dias' => $medicamentoData['duracion_dias'],
                    'fecha_inicio' => $validated['fecha_inicio'],
                    'fecha_fin' => $validated['fecha_fin_estimada'],
                    'indicaciones_uso' => $medicamentoData['indicaciones_uso'],
                    'activo' => true,
                    'orden_prescripcion' => $medicamentoData['orden_prescripcion'] ?? ($index + 1),
                ]);
            }

            // Registrar en el historial
            HistorialTratamiento::registrarCreacion(
                $tratamiento->id,
                auth()->id(),
                'Tratamiento creado desde el sistema web'
            );

            DB::commit();

            Log::info("Tratamiento creado exitosamente", [
                'tratamiento_id' => $tratamiento->id,
                'usuario_id' => auth()->id(),
                'paciente_id' => $validated['paciente_id']
            ]);

            return redirect()
                ->route('tratamientos.show', $tratamiento)
                ->with('success', 'Tratamiento creado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear tratamiento: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear el tratamiento'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tratamiento $tratamiento)
    {
        try {
            $tratamiento->load([
                'paciente',
                'medico.user',
                'medicamentoTratamientos' => function($q) {
                    $q->with([
                        'medicamento.principioActivo',
                        'medicamento.formaFarmaceutica',
                        'medicamento.viaAdministracion',
                        'unidadDosis'
                    ])->orderBy('orden_prescripcion');
                },
                'historial' => function($q) {
                    $q->with('usuario:id,name')->latest('creado_en')->limit(10);
                }
            ]);

            // Estadísticas del tratamiento
            $stats = [
                'duracion_dias' => $tratamiento->duracion_dias,
                'porcentaje_completado' => $tratamiento->porcentaje_completado,
                'medicamentos_activos' => $tratamiento->medicamentoTratamientos()->where('activo', true)->count(),
                'administraciones_programadas' => $tratamiento->administraciones()->where('estado', 'programado')->count(),
                'administraciones_completadas' => $tratamiento->administraciones()->where('estado', 'administrado')->count(),
            ];

            return Inertia::render('Medicamentos/Tratamientos/show', [
                'tratamiento' => $tratamiento,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TratamientosController@show: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al cargar el tratamiento']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tratamiento $tratamiento)
    {
        try {
            // Verificar que el tratamiento puede ser modificado
            if (!$tratamiento->puedeSerModificado()) {
                return back()->withErrors(['error' => 'Este tratamiento no puede ser modificado en su estado actual']);
            }

            $tratamiento->load([
                'paciente',
                'medico.user',
                'medicamentoTratamientos' => function($q) {
                    $q->with([
                        'medicamento.principioActivo',
                        'medicamento.formaFarmaceutica',
                        'medicamento.viaAdministracion',
                        'unidadDosis'
                    ])->orderBy('orden_prescripcion');
                }
            ]);

            // Datos para el formulario (mismo que create)
            $pacientes = Paciente::select('id', 'nombre', 'fecha_nacimiento')
                                ->where('activo', true)
                                ->orderBy('nombre')
                                ->get()
                                ->map(function($paciente) {
                                    return [
                                        'id' => $paciente->id,
                                        'nombre' => $paciente->nombre,
                                        'edad' => $paciente->edad ?? 'N/A'
                                    ];
                                });

            $medicos = PersonalMedico::with('user:id,name')
                                   ->get()
                                   ->map(function($medico) {
                                       return [
                                           'id' => $medico->usuario_id,
                                           'nombre' => $medico->user->name ?? 'Sin nombre',
                                           'especialidad' => $medico->especialidad,
                                           'institucion' => $medico->institucion
                                       ];
                                   });

            $medicamentos = Medicamento::with([
                                'principioActivo:id,nombre_generico,grupo_farmacologico',
                                'formaFarmaceutica:id,nombre',
                                'viaAdministracion:id,nombre',
                                'unidadConcentracion:id,nombre,simbolo'
                            ])
                            ->where('activo', true)
                            ->get()
                            ->map(function($medicamento) {
                                return [
                                    'id' => $medicamento->id,
                                    'nombre_comercial' => $medicamento->nombre_comercial,
                                    'principio_activo' => $medicamento->principioActivo->nombre_generico,
                                    'grupo_farmacologico' => $medicamento->principioActivo->grupo_farmacologico,
                                    'concentracion' => $medicamento->concentracion,
                                    'unidad_concentracion' => $medicamento->unidadConcentracion->simbolo,
                                    'forma_farmaceutica' => $medicamento->formaFarmaceutica->nombre,
                                    'via_administracion' => $medicamento->viaAdministracion->nombre,
                                    'stock_actual' => $medicamento->stock_actual
                                ];
                            });

            $unidadesDosis = UnidadMedida::select('id', 'nombre', 'simbolo', 'tipo')
                                       ->where('activo', true)
                                       ->orderBy('nombre')
                                       ->get();

            return Inertia::render('Medicamentos/Tratamientos/edit', [
                'tratamiento' => $tratamiento,
                'pacientes' => $pacientes,
                'medicos' => $medicos,
                'medicamentos' => $medicamentos,
                'unidadesDosis' => $unidadesDosis,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TratamientosController@edit: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al cargar el formulario de edición']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tratamiento $tratamiento)
    {
        try {
            // Verificar que el tratamiento puede ser modificado
            if (!$tratamiento->puedeSerModificado()) {
                return back()->withErrors(['error' => 'Este tratamiento no puede ser modificado en su estado actual']);
            }

            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'diagnostico' => 'nullable|string|max:200',
                'objetivo_terapeutico' => 'nullable|string|max:1000',
                'fecha_fin_estimada' => 'nullable|date|after:fecha_inicio',
                'medico_prescriptor' => 'nullable|string|max:100',
                'institucion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            // Registrar cambios en el historial
            $cambios = [];
            foreach ($validated as $campo => $valor) {
                if ($tratamiento->$campo !== $valor) {
                    $cambios[$campo] = [
                        'anterior' => $tratamiento->$campo,
                        'nuevo' => $valor
                    ];
                }
            }

            $tratamiento->update($validated);

            // Registrar cambios en el historial
            foreach ($cambios as $campo => $cambio) {
                HistorialTratamiento::registrarModificacion(
                    $tratamiento->id,
                    $campo,
                    $cambio['anterior'],
                    $cambio['nuevo'],
                    auth()->id(),
                    'Modificación desde el sistema web'
                );
            }

            DB::commit();

            Log::info("Tratamiento actualizado exitosamente", [
                'tratamiento_id' => $tratamiento->id,
                'usuario_id' => auth()->id(),
                'cambios' => array_keys($cambios)
            ]);

            return redirect()
                ->route('tratamientos.show', $tratamiento)
                ->with('success', 'Tratamiento actualizado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar tratamiento: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el tratamiento'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tratamiento $tratamiento)
    {
        try {
            // Verificar que el tratamiento puede ser eliminado
            if (!in_array($tratamiento->estado, [Tratamiento::ESTADO_ACTIVO, Tratamiento::ESTADO_PAUSADO])) {
                return back()->withErrors(['error' => 'No se puede eliminar un tratamiento en estado ' . $tratamiento->estado]);
            }

            // Verificar si tiene administraciones programadas
            $administracionesPendientes = $tratamiento->administraciones()
                                                   ->where('estado', 'programado')
                                                   ->count();

            if ($administracionesPendientes > 0) {
                return back()->withErrors(['error' => 'No se puede eliminar un tratamiento con administraciones programadas']);
            }

            DB::beginTransaction();

            // Registrar en el historial antes de eliminar
            HistorialTratamiento::create([
                'tratamiento_id' => $tratamiento->id,
                'usuario_id' => auth()->id(),
                'accion' => 'Tratamiento eliminado',
                'motivo' => 'Eliminación desde el sistema web',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $tratamientoId = $tratamiento->id;
            $tratamiento->delete();

            DB::commit();

            Log::info("Tratamiento eliminado exitosamente", [
                'tratamiento_id' => $tratamientoId,
                'usuario_id' => auth()->id()
            ]);

            return redirect()
                ->route('tratamientos.index')
                ->with('success', 'Tratamiento eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar tratamiento: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el tratamiento']);
        }
    }

    // Métodos especiales para cambios de estado
    public function toggleStatus(Tratamiento $tratamiento)
    {
        try {
            $nuevoEstado = $tratamiento->estaActivo() ? 
                          Tratamiento::ESTADO_PAUSADO : 
                          Tratamiento::ESTADO_ACTIVO;

            DB::beginTransaction();

            $estadoAnterior = $tratamiento->estado;
            $tratamiento->update(['estado' => $nuevoEstado]);

            // Registrar en el historial
            HistorialTratamiento::registrarModificacion(
                $tratamiento->id,
                'estado',
                $estadoAnterior,
                $nuevoEstado,
                auth()->id(),
                'Cambio de estado desde el sistema web'
            );

            DB::commit();

            $mensaje = $nuevoEstado === Tratamiento::ESTADO_ACTIVO ? 
                      'Tratamiento reactivado exitosamente' : 
                      'Tratamiento pausado exitosamente';

            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado del tratamiento: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al cambiar el estado del tratamiento']);
        }
    }

    public function completar(Tratamiento $tratamiento)
    {
        try {
            if (!$tratamiento->puedeSerModificado()) {
                return back()->withErrors(['error' => 'Este tratamiento no puede ser completado en su estado actual']);
            }

            DB::beginTransaction();

            $tratamiento->completar(auth()->id());

            DB::commit();

            Log::info("Tratamiento completado exitosamente", [
                'tratamiento_id' => $tratamiento->id,
                'usuario_id' => auth()->id()
            ]);

            return back()->with('success', 'Tratamiento completado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al completar tratamiento: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al completar el tratamiento']);
        }
    }
}
