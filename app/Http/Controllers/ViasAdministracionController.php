<?php

namespace App\Http\Controllers;

use App\Models\ViaAdministracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ViasAdministracionController extends Controller
{
    /**
     * Display a listing of the administration routes.
     */
    public function index(Request $request)
    {
        try {
            $query = ViaAdministracion::query();

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
            $viasAdministracion = $query->paginate(15);

            return Inertia::render('Medicamentos/ViasAdministracion/index', [
                'viasAdministracion' => $viasAdministracion,
                'filters' => $request->only(['search', 'activo', 'sort_by', 'sort_direction'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar vías de administración: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las vías de administración.');
        }
    }

    /**
     * Show the form for creating a new administration route.
     */
    public function create()
    {
        try {
            return Inertia::render('Medicamentos/ViasAdministracion/create');
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Store a newly created administration route.
     */
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                'unique:vias_administracion,nombre'
            ],
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una vía de administración con este nombre.',
        ]);

        try {
            DB::beginTransaction();

            $viaAdministracion = ViaAdministracion::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

            DB::commit();

            Log::info('Vía de administración creada', [
                'id' => $viaAdministracion->id,
                'nombre' => $viaAdministracion->nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.vias-administracion.index')
                ->with('success', "Vía de administración '{$viaAdministracion->nombre}' creada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear vía de administración: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al crear la vía de administración. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified administration route.
     */
    public function show(ViaAdministracion $viasAdministracion)
    {
        try {
            return Inertia::render('Medicamentos/ViasAdministracion/show', [
                'viaAdministracion' => $viasAdministracion
            ]);
        } catch (\Exception $e) {
            Log::error('Error al mostrar vía de administración: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los detalles de la vía de administración.');
        }
    }

    /**
     * Show the form for editing the specified administration route.
     */
    public function edit(ViaAdministracion $viasAdministracion)
    {
        try {
            return Inertia::render('Medicamentos/ViasAdministracion/edit', [
                'viaAdministracion' => $viasAdministracion
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Update the specified administration route.
     */
    public function update(Request $request, ViaAdministracion $viasAdministracion)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('vias_administracion', 'nombre')->ignore($viasAdministracion->id)
            ],
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una vía de administración con este nombre.',
        ]);

        try {
            DB::beginTransaction();

            $viasAdministracion->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $validated['activo'] ?? true
            ]);

            DB::commit();

            Log::info('Vía de administración actualizada', [
                'id' => $viasAdministracion->id,
                'nombre' => $viasAdministracion->nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.vias-administracion.index')
                ->with('success', "Vía de administración '{$viasAdministracion->nombre}' actualizada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar vía de administración: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la vía de administración. Por favor, intente nuevamente.');
        }
    }

    /**
     * Remove the specified administration route.
     */
    public function destroy(ViaAdministracion $viasAdministracion)
    {
        try {
            // Verificar si la vía está siendo usada
            if ($viasAdministracion->medicamentos()->exists()) {
                return back()->with('error', 'No se puede eliminar la vía de administración porque está siendo utilizada por medicamentos.');
            }

            DB::beginTransaction();

            $nombre = $viasAdministracion->nombre;
            $viasAdministracion->delete();

            DB::commit();

            Log::info('Vía de administración eliminada', [
                'nombre' => $nombre,
                'usuario' => auth()->id()
            ]);

            return redirect()
                ->route('medicamentos.vias-administracion.index')
                ->with('success', "Vía de administración '{$nombre}' eliminada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar vía de administración: ' . $e->getMessage());
            
            return back()->with('error', 'Error al eliminar la vía de administración. Por favor, intente nuevamente.');
        }
    }

    /**
     * Toggle the status of the specified administration route.
     */
    public function toggleStatus(ViaAdministracion $viasAdministracion)
    {
        try {
            DB::beginTransaction();

            $viasAdministracion->update([
                'activo' => !$viasAdministracion->activo
            ]);

            DB::commit();

            $status = $viasAdministracion->activo ? 'activada' : 'desactivada';

            Log::info('Estado de vía de administración cambiado', [
                'id' => $viasAdministracion->id,
                'nombre' => $viasAdministracion->nombre,
                'nuevo_estado' => $viasAdministracion->activo,
                'usuario' => auth()->id()
            ]);

            return back()->with('success', "Vía de administración '{$viasAdministracion->nombre}' {$status} exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado de vía de administración: ' . $e->getMessage());
            
            return back()->with('error', 'Error al cambiar el estado de la vía de administración.');
        }
    }
}
