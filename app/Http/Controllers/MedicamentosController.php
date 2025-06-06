<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use App\Models\PrincipioActivo;
use App\Models\FormaFarmaceutica;
use App\Models\ViaAdministracion;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MedicamentosController extends Controller
{
    /**
     * Display a listing of medications.
     */
    public function index(Request $request)
    {
        try {
            $query = Medicamento::with([
                'principioActivo',
                'formaFarmaceutica', 
                'viaAdministracion',
                'unidadConcentracion'
            ]);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nombre_comercial', 'like', "%{$search}%")
                      ->orWhere('laboratorio', 'like', "%{$search}%")
                      ->orWhere('registro_sanitario', 'like', "%{$search}%")
                      ->orWhereHas('principioActivo', function($subQ) use ($search) {
                          $subQ->where('nombre_generico', 'like', "%{$search}%");
                      });
                });
            }

            // Filtro por principio activo
            if ($request->filled('principio_activo_id')) {
                $query->where('principio_activo_id', $request->get('principio_activo_id'));
            }

            // Filtro por laboratorio
            if ($request->filled('laboratorio')) {
                $query->where('laboratorio', $request->get('laboratorio'));
            }

            // Filtro por estado
            if ($request->filled('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            // Filtro por medicamentos controlados
            if ($request->filled('controlado')) {
                $query->where('controlado', $request->boolean('controlado'));
            }

            // Filtro por vencimiento
            if ($request->filled('vencimiento')) {
                $vencimiento = $request->get('vencimiento');
                switch ($vencimiento) {
                    case 'vencidos':
                        $query->where('fecha_vencimiento', '<', now());
                        break;
                    case 'proximo_30':
                        $query->proximosAVencer(30);
                        break;
                    case 'proximo_90':
                        $query->proximosAVencer(90);
                        break;
                }
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'nombre_comercial');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $medicamentos = $query->paginate(20);

            // Datos para filtros
            $principiosActivos = PrincipioActivo::activos()
                                              ->orderBy('nombre_generico')
                                              ->pluck('nombre_generico', 'id');

            $laboratorios = Medicamento::distinct()
                                      ->pluck('laboratorio')
                                      ->filter()
                                      ->sort()
                                      ->values();

            // Estadísticas
            $stats = [
                'total' => Medicamento::count(),
                'activos' => Medicamento::activos()->count(),
                'vencidos' => Medicamento::where('fecha_vencimiento', '<', now())->count(),
                'proximo_vencer' => Medicamento::proximosAVencer(30)->count(),
                'controlados' => Medicamento::controlados()->count()
            ];

            return view('medicamentos.index', compact(
                'medicamentos',
                'principiosActivos',
                'laboratorios',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Error al cargar medicamentos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los medicamentos.');
        }
    }

    /**
     * Show the form for creating a new medication.
     */
    public function create()
    {
        try {
            $principiosActivos = PrincipioActivo::activos()
                                              ->orderBy('nombre_generico')
                                              ->pluck('nombre_generico', 'id');

            $formasFarmaceuticas = FormaFarmaceutica::activos()
                                                   ->orderBy('nombre')
                                                   ->pluck('nombre', 'id');

            $viasAdministracion = ViaAdministracion::activos()
                                                  ->orderBy('nombre')
                                                  ->pluck('nombre', 'id');

            $unidadesMedida = UnidadMedida::activos()
                                         ->whereIn('tipo_unidad', ['peso', 'volumen', 'concentracion'])
                                         ->orderBy('nombre')
                                         ->pluck('nombre', 'id');

            $laboratorios = Medicamento::distinct()
                                      ->pluck('laboratorio')
                                      ->filter()
                                      ->sort()
                                      ->values();

            return view('medicamentos.create', compact(
                'principiosActivos',
                'formasFarmaceuticas',
                'viasAdministracion',
                'unidadesMedida',
                'laboratorios'
            ));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de medicamento: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Store a newly created medication.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'principio_activo_id' => 'required|exists:principios_activos,id',
            'nombre_comercial' => 'required|string|max:100',
            'forma_farmaceutica_id' => 'required|exists:formas_farmaceuticas,id',
            'concentracion' => 'required|numeric|min:0|max:9999.999',
            'unidad_concentracion_id' => 'required|exists:unidades_medida,id',
            'via_administracion_id' => 'required|exists:vias_administracion,id',
            'laboratorio' => 'required|string|max:100',
            'registro_sanitario' => 'nullable|string|max:50',
            'lote' => 'nullable|string|max:50',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'precio_unitario' => 'nullable|numeric|min:0|max:999999.99',
            'requiere_receta' => 'boolean',
            'controlado' => 'boolean',
            'activo' => 'boolean',
            'observaciones' => 'nullable|string|max:1000'
        ], [
            'principio_activo_id.required' => 'El principio activo es obligatorio.',
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'forma_farmaceutica_id.required' => 'La forma farmacéutica es obligatoria.',
            'concentracion.required' => 'La concentración es obligatoria.',
            'unidad_concentracion_id.required' => 'La unidad de concentración es obligatoria.',
            'via_administracion_id.required' => 'La vía de administración es obligatoria.',
            'laboratorio.required' => 'El laboratorio es obligatorio.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
        ]);

        try {
            DB::beginTransaction();

            $medicamento = Medicamento::create([
                'principio_activo_id' => $validated['principio_activo_id'],
                'nombre_comercial' => $validated['nombre_comercial'],
                'forma_farmaceutica_id' => $validated['forma_farmaceutica_id'],
                'concentracion' => $validated['concentracion'],
                'unidad_concentracion_id' => $validated['unidad_concentracion_id'],
                'via_administracion_id' => $validated['via_administracion_id'],
                'laboratorio' => $validated['laboratorio'],
                'registro_sanitario' => $validated['registro_sanitario'],
                'lote' => $validated['lote'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'precio_unitario' => $validated['precio_unitario'],
                'requiere_receta' => $validated['requiere_receta'] ?? false,
                'controlado' => $validated['controlado'] ?? false,
                'activo' => $validated['activo'] ?? true,
                'observaciones' => $validated['observaciones']
            ]);

            DB::commit();

            Log::info('Medicamento creado', [
                'id' => $medicamento->id,
                'nombre' => $medicamento->nombre_comercial,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.index')
                ->with('success', "Medicamento '{$medicamento->nombre_comercial}' creado exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear medicamento: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al crear el medicamento. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified medication.
     */
    public function show(Medicamento $medicamento)
    {
        try {
            $medicamento->load([
                'principioActivo',
                'formaFarmaceutica',
                'viaAdministracion', 
                'unidadConcentracion',
                'medicamentoTratamientos.tratamiento.paciente',
                'medicamentoTratamientos.administraciones' => function($query) {
                    $query->orderBy('fecha_hora_programada', 'desc')->limit(10);
                }
            ]);

            // Estadísticas de uso
            $stats = [
                'tratamientos_activos' => $medicamento->medicamentoTratamientos()
                                                    ->whereHas('tratamiento', function($q) {
                                                        $q->where('estado', 'Activo');
                                                    })->count(),
                'administraciones_mes' => $medicamento->medicamentoTratamientos()
                                                    ->whereHas('administraciones', function($q) {
                                                        $q->where('fecha_hora_programada', '>=', now()->subMonth())
                                                          ->where('estado', 'administrado');
                                                    })->count(),
                'pacientes_usando' => $medicamento->medicamentoTratamientos()
                                                 ->whereHas('tratamiento', function($q) {
                                                     $q->where('estado', 'Activo');
                                                 })
                                                 ->distinct('tratamiento_id')
                                                 ->count(),
                'dias_para_vencer' => $medicamento->diasParaVencer(),
                'esta_vencido' => $medicamento->estaVencido()
            ];

            return view('medicamentos.show', compact('medicamento', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar medicamento: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los detalles del medicamento.');
        }
    }

    /**
     * Show the form for editing the specified medication.
     */
    public function edit(Medicamento $medicamento)
    {
        try {
            $medicamento->load([
                'principioActivo',
                'formaFarmaceutica',
                'viaAdministracion',
                'unidadConcentracion'
            ]);

            $principiosActivos = PrincipioActivo::activos()
                                              ->orderBy('nombre_generico')
                                              ->pluck('nombre_generico', 'id');

            $formasFarmaceuticas = FormaFarmaceutica::activos()
                                                   ->orderBy('nombre')
                                                   ->pluck('nombre', 'id');

            $viasAdministracion = ViaAdministracion::activos()
                                                  ->orderBy('nombre')
                                                  ->pluck('nombre', 'id');

            $unidadesMedida = UnidadMedida::activos()
                                         ->whereIn('tipo_unidad', ['peso', 'volumen', 'concentracion'])
                                         ->orderBy('nombre')
                                         ->pluck('nombre', 'id');

            $laboratorios = Medicamento::distinct()
                                      ->pluck('laboratorio')
                                      ->filter()
                                      ->sort()
                                      ->values();

            return view('medicamentos.edit', compact(
                'medicamento',
                'principiosActivos',
                'formasFarmaceuticas',
                'viasAdministracion',
                'unidadesMedida',
                'laboratorios'
            ));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Update the specified medication.
     */
    public function update(Request $request, Medicamento $medicamento)
    {
        $validated = $request->validate([
            'principio_activo_id' => 'required|exists:principios_activos,id',
            'nombre_comercial' => 'required|string|max:100',
            'forma_farmaceutica_id' => 'required|exists:formas_farmaceuticas,id',
            'concentracion' => 'required|numeric|min:0|max:9999.999',
            'unidad_concentracion_id' => 'required|exists:unidades_medida,id',
            'via_administracion_id' => 'required|exists:vias_administracion,id',
            'laboratorio' => 'required|string|max:100',
            'registro_sanitario' => 'nullable|string|max:50',
            'lote' => 'nullable|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
            'precio_unitario' => 'nullable|numeric|min:0|max:999999.99',
            'requiere_receta' => 'boolean',
            'controlado' => 'boolean',
            'activo' => 'boolean',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $medicamento->update($validated);

            DB::commit();

            Log::info('Medicamento actualizado', [
                'id' => $medicamento->id,
                'nombre' => $medicamento->nombre_comercial,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.show', $medicamento)
                ->with('success', "Medicamento '{$medicamento->nombre_comercial}' actualizado exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar medicamento: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el medicamento. Por favor, intente nuevamente.');
        }
    }

    /**
     * Remove the specified medication from storage.
     */
    public function destroy(Medicamento $medicamento)
    {
        try {
            // Verificar si tiene tratamientos asociados
            $tratamientosCount = $medicamento->medicamentoTratamientos()->count();
            
            if ($tratamientosCount > 0) {
                return back()->with('error', 
                    "No se puede eliminar el medicamento '{$medicamento->nombre_comercial}' " .
                    "porque está siendo usado en {$tratamientosCount} tratamiento(s)."
                );
            }

            DB::beginTransaction();

            $nombre = $medicamento->nombre_comercial;
            $medicamento->delete();

            DB::commit();

            Log::info('Medicamento eliminado', [
                'nombre' => $nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.index')
                ->with('success', "Medicamento '{$nombre}' eliminado exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar medicamento: ' . $e->getMessage());
            
            return back()->with('error', 'Error al eliminar el medicamento. Por favor, intente nuevamente.');
        }
    }

    /**
     * Get medications for AJAX requests (for treatment prescriptions).
     */
    public function getActivos(Request $request)
    {
        try {
            $query = Medicamento::activos()->with(['principioActivo', 'formaFarmaceutica', 'unidadConcentracion']);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nombre_comercial', 'like', "%{$search}%")
                      ->orWhereHas('principioActivo', function($subQ) use ($search) {
                          $subQ->where('nombre_generico', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('principio_activo_id')) {
                $query->where('principio_activo_id', $request->get('principio_activo_id'));
            }

            $medicamentos = $query->select('id', 'nombre_comercial', 'principio_activo_id', 'concentracion', 'unidad_concentracion_id')
                                 ->orderBy('nombre_comercial')
                                 ->limit(20)
                                 ->get()
                                 ->map(function($medicamento) {
                                     return [
                                         'id' => $medicamento->id,
                                         'nombre_completo' => $medicamento->nombre_completo,
                                         'nombre_comercial' => $medicamento->nombre_comercial,
                                         'principio_activo' => $medicamento->principioActivo->nombre_generico,
                                         'concentracion' => $medicamento->concentracion,
                                         'unidad' => $medicamento->unidadConcentracion->simbolo
                                     ];
                                 });

            return response()->json($medicamentos);

        } catch (\Exception $e) {
            Log::error('Error al obtener medicamentos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }

    /**
     * Show inventory alerts dashboard.
     */
    public function inventario()
    {
        try {
            $vencidos = Medicamento::where('fecha_vencimiento', '<', now())
                                  ->with(['principioActivo', 'formaFarmaceutica'])
                                  ->orderBy('fecha_vencimiento')
                                  ->get();

            $proximosVencer30 = Medicamento::proximosAVencer(30)
                                          ->with(['principioActivo', 'formaFarmaceutica'])
                                          ->orderBy('fecha_vencimiento')
                                          ->get();

            $proximosVencer90 = Medicamento::proximosAVencer(90)
                                          ->with(['principioActivo', 'formaFarmaceutica'])
                                          ->orderBy('fecha_vencimiento')
                                          ->get();

            $controlados = Medicamento::controlados()
                                     ->activos()
                                     ->with(['principioActivo', 'formaFarmaceutica'])
                                     ->orderBy('nombre_comercial')
                                     ->get();

            $stats = [
                'total_medicamentos' => Medicamento::count(),
                'medicamentos_activos' => Medicamento::activos()->count(),
                'vencidos' => $vencidos->count(),
                'proximo_vencer_30' => $proximosVencer30->count(),
                'proximo_vencer_90' => $proximosVencer90->count(),
                'controlados' => $controlados->count()
            ];

            return view('medicamentos.inventario', compact(
                'vencidos',
                'proximosVencer30', 
                'proximosVencer90',
                'controlados',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Error al cargar inventario: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el inventario de medicamentos.');
        }
    }
}
