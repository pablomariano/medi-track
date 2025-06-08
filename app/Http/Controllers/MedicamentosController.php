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
use Inertia\Inertia;
use Carbon\Carbon;

class MedicamentosController extends Controller
{
    /**
     * Display a listing of the medications.
     */
    public function index(Request $request)
    {
        try {
            $query = Medicamento::with([
                'principioActivo:id,nombre_generico,grupo_farmacologico',
                'formaFarmaceutica:id,nombre',
                'viaAdministracion:id,nombre',
                'unidadConcentracion:id,nombre,simbolo'
            ]);

            // Filtro de búsqueda
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nombre_comercial', 'like', "%{$search}%")
                      ->orWhere('codigo_barras', 'like', "%{$search}%")
                      ->orWhere('lote', 'like', "%{$search}%")
                      ->orWhereHas('principioActivo', function($sq) use ($search) {
                          $sq->where('nombre_generico', 'like', "%{$search}%");
                      });
                });
            }

            // Filtro por principio activo
            if ($request->filled('principio_activo_id')) {
                $query->where('principio_activo_id', $request->get('principio_activo_id'));
            }

            // Filtro por forma farmacéutica
            if ($request->filled('forma_farmaceutica_id')) {
                $query->where('forma_farmaceutica_id', $request->get('forma_farmaceutica_id'));
            }

            // Filtro por vía de administración
            if ($request->filled('via_administracion_id')) {
                $query->where('via_administracion_id', $request->get('via_administracion_id'));
            }

            // Filtro por stock bajo
            if ($request->filled('stock_bajo')) {
                if ($request->get('stock_bajo') === '1') {
                    $query->whereRaw('stock_actual <= stock_minimo');
                } elseif ($request->get('stock_bajo') === '0') {
                    $query->whereRaw('stock_actual > stock_minimo');
                }
            }

            // Filtro por vencimiento
            if ($request->filled('vencidos')) {
                $vencidos = $request->get('vencidos');
                if ($vencidos === 'vencidos') {
                    $query->where('fecha_vencimiento', '<', now());
                } elseif ($vencidos === 'proximo') {
                    // Próximos a vencer en 3 meses
                    $query->whereBetween('fecha_vencimiento', [
                        now(),
                        now()->addMonths(3)
                    ]);
                }
            }

            // Filtro por estado
            if ($request->filled('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'nombre_comercial');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $medicamentos = $query->paginate(15);

            // Obtener datos para filtros
            $principiosActivos = PrincipioActivo::activos()
                                               ->select('id', 'nombre_generico', 'grupo_farmacologico')
                                               ->orderBy('nombre_generico')
                                               ->get();

            $formasFarmaceuticas = FormaFarmaceutica::activos()
                                                   ->select('id', 'nombre')
                                                   ->orderBy('nombre')
                                                   ->get();

            $viasAdministracion = ViaAdministracion::activos()
                                                  ->select('id', 'nombre')
                                                  ->orderBy('nombre')
                                                  ->get();

            return Inertia::render('Medicamentos/Medicamentos/index', [
                'medicamentos' => $medicamentos,
                'principiosActivos' => $principiosActivos,
                'formasFarmaceuticas' => $formasFarmaceuticas,
                'viasAdministracion' => $viasAdministracion,
                'filters' => $request->only([
                    'search', 'principio_activo_id', 'forma_farmaceutica_id', 
                    'via_administracion_id', 'stock_bajo', 'vencidos', 'activo',
                    'sort_by', 'sort_direction'
                ])
            ]);

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
                                               ->select('id', 'nombre_generico', 'grupo_farmacologico')
                                               ->orderBy('nombre_generico')
                                               ->get();

            $formasFarmaceuticas = FormaFarmaceutica::activos()
                                                   ->select('id', 'nombre')
                                                   ->orderBy('nombre')
                                                   ->get();

            $viasAdministracion = ViaAdministracion::activos()
                                                  ->select('id', 'nombre')
                                                  ->orderBy('nombre')
                                                  ->get();

            $unidadesMedida = UnidadMedida::activos()
                                         ->select('id', 'nombre', 'simbolo', 'tipo')
                                         ->orderBy('nombre')
                                         ->get();

            return Inertia::render('Medicamentos/Medicamentos/create', [
                'principiosActivos' => $principiosActivos,
                'formasFarmaceuticas' => $formasFarmaceuticas,
                'viasAdministracion' => $viasAdministracion,
                'unidadesMedida' => $unidadesMedida
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Store a newly created medication.
     */
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'nombre_comercial' => [
                'required',
                'string',
                'max:150',
                'unique:medicamentos,nombre_comercial'
            ],
            'principio_activo_id' => 'required|exists:principios_activos,id',
            'forma_farmaceutica_id' => 'required|exists:formas_farmaceuticas,id',
            'via_administracion_id' => 'required|exists:vias_administracion,id',
            'unidad_concentracion_id' => 'required|exists:unidades_medida,id',
            'concentracion' => 'required|numeric|min:0',
            'codigo_barras' => 'nullable|string|max:50|unique:medicamentos,codigo_barras',
            'lote' => 'nullable|string|max:50',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'precio_unitario' => 'nullable|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ], [
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'nombre_comercial.unique' => 'Ya existe un medicamento con este nombre comercial.',
            'principio_activo_id.required' => 'Debe seleccionar un principio activo.',
            'forma_farmaceutica_id.required' => 'Debe seleccionar una forma farmacéutica.',
            'via_administracion_id.required' => 'Debe seleccionar una vía de administración.',
            'unidad_concentracion_id.required' => 'Debe seleccionar una unidad de concentración.',
            'concentracion.required' => 'La concentración es obligatoria.',
            'stock_actual.required' => 'El stock actual es obligatorio.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
        ]);

        try {
            DB::beginTransaction();

            $medicamento = Medicamento::create([
                'nombre_comercial' => $validated['nombre_comercial'],
                'principio_activo_id' => $validated['principio_activo_id'],
                'forma_farmaceutica_id' => $validated['forma_farmaceutica_id'],
                'via_administracion_id' => $validated['via_administracion_id'],
                'unidad_concentracion_id' => $validated['unidad_concentracion_id'],
                'concentracion' => $validated['concentracion'],
                'codigo_barras' => $validated['codigo_barras'] ?? null,
                'lote' => $validated['lote'] ?? null,
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                'precio_unitario' => $validated['precio_unitario'] ?? null,
                'stock_actual' => $validated['stock_actual'],
                'stock_minimo' => $validated['stock_minimo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
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
                'tratamientos' => function($query) {
                    $query->with(['paciente:id,nombres,apellidos'])
                          ->where('activo', true)
                          ->orderBy('created_at', 'desc')
                          ->limit(10);
                }
            ]);

            return Inertia::render('Medicamentos/Medicamentos/show', [
                'medicamento' => $medicamento
            ]);

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
            $principiosActivos = PrincipioActivo::activos()
                                               ->select('id', 'nombre_generico', 'grupo_farmacologico')
                                               ->orderBy('nombre_generico')
                                               ->get();

            $formasFarmaceuticas = FormaFarmaceutica::activos()
                                                   ->select('id', 'nombre')
                                                   ->orderBy('nombre')
                                                   ->get();

            $viasAdministracion = ViaAdministracion::activos()
                                                  ->select('id', 'nombre')
                                                  ->orderBy('nombre')
                                                  ->get();

            $unidadesMedida = UnidadMedida::activos()
                                         ->select('id', 'nombre', 'simbolo', 'tipo')
                                         ->orderBy('nombre')
                                         ->get();

            return Inertia::render('Medicamentos/Medicamentos/edit', [
                'medicamento' => $medicamento,
                'principiosActivos' => $principiosActivos,
                'formasFarmaceuticas' => $formasFarmaceuticas,
                'viasAdministracion' => $viasAdministracion,
                'unidadesMedida' => $unidadesMedida
            ]);

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
        // Validación
        $validated = $request->validate([
            'nombre_comercial' => [
                'required',
                'string',
                'max:150',
                Rule::unique('medicamentos', 'nombre_comercial')->ignore($medicamento->id)
            ],
            'principio_activo_id' => 'required|exists:principios_activos,id',
            'forma_farmaceutica_id' => 'required|exists:formas_farmaceuticas,id',
            'via_administracion_id' => 'required|exists:vias_administracion,id',
            'unidad_concentracion_id' => 'required|exists:unidades_medida,id',
            'concentracion' => 'required|numeric|min:0',
            'codigo_barras' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('medicamentos', 'codigo_barras')->ignore($medicamento->id)
            ],
            'lote' => 'nullable|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
            'precio_unitario' => 'nullable|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ], [
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'nombre_comercial.unique' => 'Ya existe otro medicamento con este nombre comercial.',
            'principio_activo_id.required' => 'Debe seleccionar un principio activo.',
            'forma_farmaceutica_id.required' => 'Debe seleccionar una forma farmacéutica.',
            'via_administracion_id.required' => 'Debe seleccionar una vía de administración.',
            'unidad_concentracion_id.required' => 'Debe seleccionar una unidad de concentración.',
            'concentracion.required' => 'La concentración es obligatoria.',
            'stock_actual.required' => 'El stock actual es obligatorio.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
        ]);

        try {
            DB::beginTransaction();

            $medicamento->update([
                'nombre_comercial' => $validated['nombre_comercial'],
                'principio_activo_id' => $validated['principio_activo_id'],
                'forma_farmaceutica_id' => $validated['forma_farmaceutica_id'],
                'via_administracion_id' => $validated['via_administracion_id'],
                'unidad_concentracion_id' => $validated['unidad_concentracion_id'],
                'concentracion' => $validated['concentracion'],
                'codigo_barras' => $validated['codigo_barras'] ?? null,
                'lote' => $validated['lote'] ?? null,
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                'precio_unitario' => $validated['precio_unitario'] ?? null,
                'stock_actual' => $validated['stock_actual'],
                'stock_minimo' => $validated['stock_minimo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

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
     * Remove the specified medication.
     */
    public function destroy(Medicamento $medicamento)
    {
        try {
            // Verificar si el medicamento está siendo usado en tratamientos
            if ($medicamento->tratamientos()->exists()) {
                return back()->with('error', 'No se puede eliminar el medicamento porque está siendo utilizado en tratamientos.');
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
     * Toggle the status of the specified medication.
     */
    public function toggleStatus(Medicamento $medicamento)
    {
        try {
            DB::beginTransaction();

            $medicamento->update([
                'activo' => !$medicamento->activo
            ]);

            DB::commit();

            $status = $medicamento->activo ? 'activado' : 'desactivado';

            Log::info('Estado de medicamento cambiado', [
                'id' => $medicamento->id,
                'nombre' => $medicamento->nombre_comercial,
                'nuevo_estado' => $medicamento->activo,
                'usuario' => auth()->id()
            ]);

            return back()->with('success', "Medicamento '{$medicamento->nombre_comercial}' {$status} exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado de medicamento: ' . $e->getMessage());
            
            return back()->with('error', 'Error al cambiar el estado del medicamento.');
        }
    }

    /**
     * Show inventory alerts (low stock, expired, expiring soon).
     */
    public function inventario()
    {
        try {
            // Medicamentos con stock bajo
            $stockBajo = Medicamento::with(['principioActivo', 'formaFarmaceutica'])
                                   ->whereRaw('stock_actual <= stock_minimo')
                                   ->where('activo', true)
                                   ->orderBy('stock_actual')
                                   ->get();

            // Medicamentos vencidos
            $vencidos = Medicamento::with(['principioActivo', 'formaFarmaceutica'])
                                  ->where('fecha_vencimiento', '<', now())
                                  ->where('activo', true)
                                  ->orderBy('fecha_vencimiento')
                                  ->get();

            // Medicamentos próximos a vencer (3 meses)
            $proximosVencer = Medicamento::with(['principioActivo', 'formaFarmaceutica'])
                                        ->whereBetween('fecha_vencimiento', [
                                            now(),
                                            now()->addMonths(3)
                                        ])
                                        ->where('activo', true)
                                        ->orderBy('fecha_vencimiento')
                                        ->get();

            return Inertia::render('Medicamentos/Medicamentos/inventario', [
                'stockBajo' => $stockBajo,
                'vencidos' => $vencidos,
                'proximosVencer' => $proximosVencer
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar alertas de inventario: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las alertas de inventario.');
        }
    }

    /**
     * Get active medications for AJAX requests.
     */
    public function getActivos(Request $request)
    {
        try {
            $query = Medicamento::with(['principioActivo', 'formaFarmaceutica', 'unidadConcentracion'])
                                ->where('activo', true);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('nombre_comercial', 'like', "%{$search}%");
            }

            $medicamentos = $query->select('id', 'nombre_comercial', 'concentracion', 'principio_activo_id', 'forma_farmaceutica_id', 'unidad_concentracion_id')
                                 ->orderBy('nombre_comercial')
                                 ->limit(20)
                                 ->get();

            return response()->json($medicamentos);

        } catch (\Exception $e) {
            Log::error('Error al obtener medicamentos activos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }
}
