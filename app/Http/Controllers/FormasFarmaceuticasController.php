<?php

namespace App\Http\Controllers;

use App\Models\FormaFarmaceutica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FormasFarmaceuticasController extends Controller
{
    /**
     * Display a listing of the pharmaceutical forms.
     */
    public function index(Request $request)
    {
        try {
            $query = FormaFarmaceutica::query();

            // Filtro de búsqueda
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('nombre', 'like', "%{$search}%");
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
            $formasFarmaceuticas = $query->paginate(15);

            return Inertia::render('Medicamentos/FormasFarmaceuticas/index', [
                'formasFarmaceuticas' => $formasFarmaceuticas,
                'filters' => $request->only(['search', 'activo', 'sort_by', 'sort_direction'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar formas farmacéuticas: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las formas farmacéuticas.');
        }
    }

    /**
     * Show the form for creating a new pharmaceutical form.
     */
    public function create()
    {
        try {
            return Inertia::render('Medicamentos/FormasFarmaceuticas/create');
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Store a newly created pharmaceutical form.
     */
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                'unique:formas_farmaceuticas,nombre'
            ],
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una forma farmacéutica con este nombre.',
        ]);

        try {
            DB::beginTransaction();

            $formaFarmaceutica = FormaFarmaceutica::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

            DB::commit();

            Log::info('Forma farmacéutica creada', [
                'id' => $formaFarmaceutica->id,
                'nombre' => $formaFarmaceutica->nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.formas-farmaceuticas.index')
                ->with('success', "Forma farmacéutica '{$formaFarmaceutica->nombre}' creada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear forma farmacéutica: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al crear la forma farmacéutica. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified pharmaceutical form.
     */
    public function show(FormaFarmaceutica $formasFarmaceutica)
    {
        try {
            return Inertia::render('Medicamentos/FormasFarmaceuticas/show', [
                'formaFarmaceutica' => $formasFarmaceutica
            ]);
        } catch (\Exception $e) {
            Log::error('Error al mostrar forma farmacéutica: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los detalles de la forma farmacéutica.');
        }
    }

    /**
     * Show the form for editing the specified pharmaceutical form.
     */
    public function edit(FormaFarmaceutica $formasFarmaceutica)
    {
        try {
            return Inertia::render('Medicamentos/FormasFarmaceuticas/edit', [
                'formaFarmaceutica' => $formasFarmaceutica
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Update the specified pharmaceutical form.
     */
    public function update(Request $request, FormaFarmaceutica $formasFarmaceutica)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('formas_farmaceuticas', 'nombre')->ignore($formasFarmaceutica->id)
            ],
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una forma farmacéutica con este nombre.',
        ]);

        try {
            DB::beginTransaction();

            $formasFarmaceutica->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

            DB::commit();

            Log::info('Forma farmacéutica actualizada', [
                'id' => $formasFarmaceutica->id,
                'nombre' => $formasFarmaceutica->nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.formas-farmaceuticas.index')
                ->with('success', "Forma farmacéutica '{$formasFarmaceutica->nombre}' actualizada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar forma farmacéutica: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la forma farmacéutica. Por favor, intente nuevamente.');
        }
    }

    /**
     * Remove the specified pharmaceutical form.
     */
    public function destroy(FormaFarmaceutica $formasFarmaceutica)
    {
        try {
            // Verificar si la forma está siendo usada
            if ($formasFarmaceutica->medicamentos()->exists()) {
                return back()->with('error', 'No se puede eliminar la forma farmacéutica porque está siendo utilizada por medicamentos.');
            }

            DB::beginTransaction();

            $nombre = $formasFarmaceutica->nombre;
            $formasFarmaceutica->delete();

            DB::commit();

            Log::info('Forma farmacéutica eliminada', [
                'nombre' => $nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.formas-farmaceuticas.index')
                ->with('success', "Forma farmacéutica '{$nombre}' eliminada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar forma farmacéutica: ' . $e->getMessage());
            
            return back()->with('error', 'Error al eliminar la forma farmacéutica. Por favor, intente nuevamente.');
        }
    }

    /**
     * Toggle the status of the specified pharmaceutical form.
     */
    public function toggleStatus(FormaFarmaceutica $formasFarmaceutica)
    {
        try {
            DB::beginTransaction();

            $formasFarmaceutica->update([
                'activo' => !$formasFarmaceutica->activo
            ]);

            DB::commit();

            $status = $formasFarmaceutica->activo ? 'activada' : 'desactivada';

            Log::info('Estado de forma farmacéutica cambiado', [
                'id' => $formasFarmaceutica->id,
                'nombre' => $formasFarmaceutica->nombre,
                'nuevo_estado' => $formasFarmaceutica->activo,
                'usuario' => auth()->id()
            ]);

            return back()->with('success', "Forma farmacéutica '{$formasFarmaceutica->nombre}' {$status} exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado de forma farmacéutica: ' . $e->getMessage());
            
            return back()->with('error', 'Error al cambiar el estado de la forma farmacéutica.');
        }
    }
}
