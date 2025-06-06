<?php

namespace App\Http\Controllers;

use App\Models\PrincipioActivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PrincipiosActivosController extends Controller
{
    /**
     * Display a listing of the active pharmaceutical ingredients.
     */
    public function index(Request $request)
    {
        try {
            $query = PrincipioActivo::query();

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nombre_generico', 'like', "%{$search}%")
                      ->orWhere('nombre_comercial', 'like', "%{$search}%")
                      ->orWhere('grupo_farmacologico', 'like', "%{$search}%");
                });
            }

            // Filtro por grupo farmacológico
            if ($request->filled('grupo_farmacologico')) {
                $query->where('grupo_farmacologico', $request->get('grupo_farmacologico'));
            }

            // Filtro por estado
            if ($request->filled('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'nombre_generico');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $principiosActivos = $query->withCount('medicamentos')->paginate(15);

            // Obtener grupos farmacológicos únicos para el filtro
            $grupos = PrincipioActivo::distinct()
                        ->pluck('grupo_farmacologico')
                        ->filter()
                        ->sort()
                        ->values();

            return Inertia::render('Medicamentos/PrincipiosActivos/index', [
                'principiosActivos' => $principiosActivos,
                'grupos' => $grupos,
                'filters' => $request->only(['search', 'grupo_farmacologico', 'activo', 'sort_by', 'sort_direction'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar principios activos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los principios activos.');
        }
    }

    /**
     * Show the form for creating a new active pharmaceutical ingredient.
     */
    public function create()
    {
        try {
            // Obtener grupos farmacológicos existentes para el select
            $grupos = PrincipioActivo::distinct()
                        ->pluck('grupo_farmacologico')
                        ->filter()
                        ->sort()
                        ->values();

            return Inertia::render('Medicamentos/PrincipiosActivos/create', [
                'grupos' => $grupos
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Store a newly created active pharmaceutical ingredient.
     */
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'nombre_generico' => [
                'required',
                'string',
                'max:100',
                'unique:principios_activos,nombre_generico'
            ],
            'nombre_comercial' => 'nullable|string|max:100',
            'clasificacion_atc' => 'nullable|string|max:10',
            'grupo_farmacologico' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ], [
            'nombre_generico.required' => 'El nombre genérico es obligatorio.',
            'nombre_generico.unique' => 'Ya existe un principio activo con este nombre genérico.',
            'grupo_farmacologico.required' => 'El grupo farmacológico es obligatorio.',
        ]);

        try {
            DB::beginTransaction();

            $principioActivo = PrincipioActivo::create([
                'nombre_generico' => $validated['nombre_generico'],
                'nombre_comercial' => $validated['nombre_comercial'] ?? null,
                'clasificacion_atc' => $validated['clasificacion_atc'] ?? null,
                'grupo_farmacologico' => $validated['grupo_farmacologico'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

            DB::commit();

            Log::info('Principio activo creado', [
                'id' => $principioActivo->id,
                'nombre' => $principioActivo->nombre_generico,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('principios-activos.index')
                ->with('success', "Principio activo '{$principioActivo->nombre_generico}' creado exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear principio activo: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al crear el principio activo. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified active pharmaceutical ingredient.
     */
    public function show(PrincipioActivo $principiosActivo)
    {
        try {
            // Cargar medicamentos relacionados
            $principiosActivo->load(['medicamentos' => function($query) {
                $query->with(['formaFarmaceutica', 'viaAdministracion', 'unidadConcentracion'])
                      ->where('activo', true)
                      ->orderBy('nombre_comercial');
            }]);

            // Cargar interacciones medicamentosas
            $interacciones = $principiosActivo->todasLasInteracciones()
                                             ->load(['principioActivo1', 'principioActivo2']);

            // Estadísticas
            $stats = [
                'medicamentos_activos' => $principiosActivo->medicamentos()->where('activo', true)->count(),
                'medicamentos_total' => $principiosActivo->medicamentos()->count(),
                'interacciones_conocidas' => $interacciones->count(),
                'medicamentos_vencidos' => $principiosActivo->medicamentos()
                                                           ->where('fecha_vencimiento', '<', now())
                                                           ->count()
            ];

            return view('medicamentos.principios-activos.show', compact(
                'principiosActivo', 
                'interacciones', 
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Error al mostrar principio activo: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los detalles del principio activo.');
        }
    }

    /**
     * Show the form for editing the specified active pharmaceutical ingredient.
     */
    public function edit(PrincipioActivo $principiosActivo)
    {
        try {
            // Obtener grupos farmacológicos existentes
            $grupos = PrincipioActivo::distinct()
                        ->pluck('grupo_farmacologico')
                        ->filter()
                        ->sort()
                        ->values();

            return view('medicamentos.principios-activos.edit', compact(
                'principiosActivo', 
                'grupos'
            ));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Update the specified active pharmaceutical ingredient.
     */
    public function update(Request $request, PrincipioActivo $principiosActivo)
    {
        // Validación
        $validated = $request->validate([
            'nombre_generico' => [
                'required',
                'string',
                'max:100',
                Rule::unique('principios_activos')->ignore($principiosActivo->id)
            ],
            'nombre_comercial' => 'nullable|string|max:100',
            'clasificacion_atc' => 'nullable|string|max:10',
            'grupo_farmacologico' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ], [
            'nombre_generico.required' => 'El nombre genérico es obligatorio.',
            'nombre_generico.unique' => 'Ya existe otro principio activo con este nombre genérico.',
            'grupo_farmacologico.required' => 'El grupo farmacológico es obligatorio.',
        ]);

        try {
            DB::beginTransaction();

            $principiosActivo->update([
                'nombre_generico' => $validated['nombre_generico'],
                'nombre_comercial' => $validated['nombre_comercial'] ?? null,
                'clasificacion_atc' => $validated['clasificacion_atc'] ?? null,
                'grupo_farmacologico' => $validated['grupo_farmacologico'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

            DB::commit();

            Log::info('Principio activo actualizado', [
                'id' => $principiosActivo->id,
                'nombre' => $principiosActivo->nombre_generico,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('principios-activos.show', $principiosActivo)
                ->with('success', "Principio activo '{$principiosActivo->nombre_generico}' actualizado exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar principio activo: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el principio activo. Por favor, intente nuevamente.');
        }
    }

    /**
     * Remove the specified active pharmaceutical ingredient from storage.
     */
    public function destroy(PrincipioActivo $principiosActivo)
    {
        try {
            // Verificar si tiene medicamentos asociados
            $medicamentosCount = $principiosActivo->medicamentos()->count();
            
            if ($medicamentosCount > 0) {
                return back()->with('error', 
                    "No se puede eliminar el principio activo '{$principiosActivo->nombre_generico}' " .
                    "porque tiene {$medicamentosCount} medicamento(s) asociado(s)."
                );
            }

            DB::beginTransaction();

            $nombre = $principiosActivo->nombre_generico;
            $principiosActivo->delete();

            DB::commit();

            Log::info('Principio activo eliminado', [
                'nombre' => $nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('principios-activos.index')
                ->with('success', "Principio activo '{$nombre}' eliminado exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar principio activo: ' . $e->getMessage());
            
            return back()->with('error', 'Error al eliminar el principio activo. Por favor, intente nuevamente.');
        }
    }

    /**
     * Toggle the active status of the specified active pharmaceutical ingredient.
     */
    public function toggleStatus(PrincipioActivo $principiosActivo)
    {
        try {
            DB::beginTransaction();

            $nuevoEstado = !$principiosActivo->activo;
            $principiosActivo->update(['activo' => $nuevoEstado]);

            DB::commit();

            $estado = $nuevoEstado ? 'activado' : 'desactivado';
            
            Log::info('Principio activo ' . $estado, [
                'id' => $principiosActivo->id,
                'nombre' => $principiosActivo->nombre_generico,
                'usuario' => auth()->id()
            ]);

            return back()->with('success', 
                "Principio activo '{$principiosActivo->nombre_generico}' {$estado} exitosamente."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado del principio activo: ' . $e->getMessage());
            
            return back()->with('error', 'Error al cambiar el estado del principio activo.');
        }
    }

    /**
     * Get active pharmaceutical ingredients for AJAX requests.
     */
    public function getActivos(Request $request)
    {
        try {
            $query = PrincipioActivo::activos();

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('nombre_generico', 'like', "%{$search}%");
            }

            $principios = $query->select('id', 'nombre_generico', 'grupo_farmacologico')
                               ->orderBy('nombre_generico')
                               ->limit(20)
                               ->get();

            return response()->json($principios);

        } catch (\Exception $e) {
            Log::error('Error al obtener principios activos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }
}
