<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UnidadesMedidaController extends Controller
{
    /**
     * Display a listing of the units of measurement.
     */
    public function index(Request $request)
    {
        try {
            $query = UnidadMedida::query();

            // Filtro de búsqueda
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('simbolo', 'like', "%{$search}%");
                });
            }

            // Filtro por tipo
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->get('tipo'));
            }

            // Filtro por estado
            if ($request->filled('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'nombre');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $unidadesMedida = $query->paginate(15);

            return Inertia::render('Medicamentos/UnidadesMedida/index', [
                'unidadesMedida' => $unidadesMedida,
                'filters' => $request->only(['search', 'tipo', 'activo', 'sort_by', 'sort_direction'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar unidades de medida: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las unidades de medida.');
        }
    }

    /**
     * Show the form for creating a new unit of measurement.
     */
    public function create()
    {
        try {
            return Inertia::render('Medicamentos/UnidadesMedida/create');
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Store a newly created unit of measurement.
     */
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                'unique:unidades_medida,nombre'
            ],
            'simbolo' => [
                'required',
                'string',
                'max:10',
                'unique:unidades_medida,simbolo'
            ],
            'tipo' => [
                'required',
                'string',
                'in:peso,volumen,concentracion,unidad,tiempo'
            ],
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una unidad de medida con este nombre.',
            'simbolo.required' => 'El símbolo es obligatorio.',
            'simbolo.unique' => 'Ya existe una unidad de medida con este símbolo.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
        ]);

        try {
            DB::beginTransaction();

            $unidadMedida = UnidadMedida::create([
                'nombre' => $validated['nombre'],
                'simbolo' => $validated['simbolo'],
                'tipo' => $validated['tipo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

            DB::commit();

            Log::info('Unidad de medida creada', [
                'id' => $unidadMedida->id,
                'nombre' => $unidadMedida->nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.unidades-medida.index')
                ->with('success', "Unidad de medida '{$unidadMedida->nombre}' creada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear unidad de medida: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al crear la unidad de medida. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified unit of measurement.
     */
    public function show(UnidadMedida $unidadesMedida)
    {
        try {
            return Inertia::render('Medicamentos/UnidadesMedida/show', [
                'unidadMedida' => $unidadesMedida
            ]);
        } catch (\Exception $e) {
            Log::error('Error al mostrar unidad de medida: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los detalles de la unidad de medida.');
        }
    }

    /**
     * Show the form for editing the specified unit of measurement.
     */
    public function edit(UnidadMedida $unidadesMedida)
    {
        try {
            return Inertia::render('Medicamentos/UnidadesMedida/edit', [
                'unidadMedida' => $unidadesMedida
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Update the specified unit of measurement.
     */
    public function update(Request $request, UnidadMedida $unidadesMedida)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('unidades_medida', 'nombre')->ignore($unidadesMedida->id)
            ],
            'simbolo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('unidades_medida', 'simbolo')->ignore($unidadesMedida->id)
            ],
            'tipo' => [
                'required',
                'string',
                'in:peso,volumen,concentracion,unidad,tiempo'
            ],
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una unidad de medida con este nombre.',
            'simbolo.required' => 'El símbolo es obligatorio.',
            'simbolo.unique' => 'Ya existe una unidad de medida con este símbolo.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
        ]);

        try {
            DB::beginTransaction();

            $unidadesMedida->update([
                'nombre' => $validated['nombre'],
                'simbolo' => $validated['simbolo'],
                'tipo' => $validated['tipo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

            DB::commit();

            Log::info('Unidad de medida actualizada', [
                'id' => $unidadesMedida->id,
                'nombre' => $unidadesMedida->nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.unidades-medida.index')
                ->with('success', "Unidad de medida '{$unidadesMedida->nombre}' actualizada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar unidad de medida: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la unidad de medida. Por favor, intente nuevamente.');
        }
    }

    /**
     * Remove the specified unit of measurement.
     */
    public function destroy(UnidadMedida $unidadesMedida)
    {
        try {
            // Verificar si la unidad está siendo usada
            if ($unidadesMedida->medicamentos()->exists()) {
                return back()->with('error', 'No se puede eliminar la unidad de medida porque está siendo utilizada por medicamentos.');
            }

            DB::beginTransaction();

            $nombre = $unidadesMedida->nombre;
            $unidadesMedida->delete();

            DB::commit();

            Log::info('Unidad de medida eliminada', [
                'nombre' => $nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.unidades-medida.index')
                ->with('success', "Unidad de medida '{$nombre}' eliminada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar unidad de medida: ' . $e->getMessage());
            
            return back()->with('error', 'Error al eliminar la unidad de medida. Por favor, intente nuevamente.');
        }
    }

    /**
     * Toggle the status of the specified unit of measurement.
     */
    public function toggleStatus(UnidadMedida $unidadesMedida)
    {
        try {
            DB::beginTransaction();

            $unidadesMedida->update([
                'activo' => !$unidadesMedida->activo
            ]);

            DB::commit();

            $status = $unidadesMedida->activo ? 'activada' : 'desactivada';

            Log::info('Estado de unidad de medida cambiado', [
                'id' => $unidadesMedida->id,
                'nombre' => $unidadesMedida->nombre,
                'nuevo_estado' => $unidadesMedida->activo,
                'usuario' => auth()->id()
            ]);

            return back()->with('success', "Unidad de medida '{$unidadesMedida->nombre}' {$status} exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado de unidad de medida: ' . $e->getMessage());
            
            return back()->with('error', 'Error al cambiar el estado de la unidad de medida.');
        }
    }
}
