<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicamentoController extends Controller
{
    public function index()
    {
        $medicamentos = Medicamento::activos()
            ->orderBy('nombre')
            ->paginate(15);

        return Inertia::render('Medicamentos/Index', [
            'medicamentos' => $medicamentos
        ]);
    }

    public function dataTable(Request $request)
    {
        $query = Medicamento::activos();

        // Aplicar búsqueda si existe
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('nombre', 'like', "%{$searchTerm}%")
                  ->orWhere('principio_activo', 'like', "%{$searchTerm}%")
                  ->orWhere('laboratorio', 'like', "%{$searchTerm}%");
            });
        }

        // Aplicar filtros
        if ($request->filled('estado') && $request->get('estado') !== 'todos') {
            if ($request->get('estado') === 'activo') {
                $query->where('activo', true);
            } elseif ($request->get('estado') === 'inactivo') {
                $query->where('activo', false);
            }
        }

        if ($request->filled('receta') && $request->get('receta') !== 'todos') {
            if ($request->get('receta') === 'si') {
                $query->where('requiere_receta', true);
            } elseif ($request->get('receta') === 'no') {
                $query->where('requiere_receta', false);
            }
        }

        if ($request->filled('categoria') && $request->get('categoria') !== 'todos') {
            $query->where('categoria_terapeutica', $request->get('categoria'));
        }

        // Aplicar ordenamiento
        $sortColumn = $request->get('sort', 'nombre');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validar columnas de ordenamiento
        $allowedSortColumns = [
            'nombre', 'principio_activo', 'categoria_terapeutica', 
            'laboratorio', 'requiere_receta', 'activo', 'created_at'
        ];
        
        if (in_array($sortColumn, $allowedSortColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('nombre', 'asc');
        }

        // Obtener número de elementos por página
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [5, 10, 20, 50]) ? $perPage : 20;

        $medicamentos = $query->paginate($perPage);

        // Añadir parámetros de consulta a los enlaces de paginación
        $medicamentos->appends($request->query());

        return Inertia::render('Medicamentos/DataTable', [
            'medicamentos' => $medicamentos,
            'filters' => [
                'search' => $request->get('search', ''),
                'estado' => $request->get('estado', 'todos'),
                'receta' => $request->get('receta', 'todos'),
                'categoria' => $request->get('categoria', 'todos'),
                'sort' => $sortColumn,
                'direction' => $sortDirection,
                'per_page' => $perPage,
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Medicamentos/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'principio_activo' => 'required|string|max:255',
            'concentracion' => 'required|string|max:50',
            'unidad_concentracion' => 'required|string|max:20',
            'forma_farmaceutica' => 'required|string|max:100',
            'via_administracion' => 'required|string|max:100',
            'presentacion' => 'required|string|max:100',
            'unidades_por_presentacion' => 'required|integer|min:1',
            'requiere_receta' => 'boolean',
            'contraindicaciones' => 'nullable|string',
            'efectos_secundarios' => 'nullable|string',
            'interacciones' => 'nullable|string',
            'categoria_terapeutica' => 'nullable|string|max:100',
            'laboratorio' => 'nullable|string|max:100',
            'codigo_barras' => 'nullable|string|max:50',
            'registro_sanitario' => 'nullable|string|max:50'
        ]);

        $medicamento = Medicamento::create($request->all());

        return redirect()->route('medicamentos.show', $medicamento)
            ->with('success', 'Medicamento creado exitosamente.');
    }

    public function show(Medicamento $medicamento)
    {
        $medicamento->load(['tratamientos.paciente', 'administraciones.cuidador']);

        return Inertia::render('Medicamentos/Show', [
            'medicamento' => $medicamento
        ]);
    }

    public function edit(Medicamento $medicamento)
    {
        return Inertia::render('Medicamentos/Edit', [
            'medicamento' => $medicamento
        ]);
    }

    public function update(Request $request, Medicamento $medicamento)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'principio_activo' => 'required|string|max:255',
            'concentracion' => 'required|string|max:50',
            'unidad_concentracion' => 'required|string|max:20',
            'forma_farmaceutica' => 'required|string|max:100',
            'via_administracion' => 'required|string|max:100',
            'presentacion' => 'required|string|max:100',
            'unidades_por_presentacion' => 'required|integer|min:1',
            'requiere_receta' => 'boolean',
            'contraindicaciones' => 'nullable|string',
            'efectos_secundarios' => 'nullable|string',
            'interacciones' => 'nullable|string',
            'categoria_terapeutica' => 'nullable|string|max:100',
            'laboratorio' => 'nullable|string|max:100',
            'codigo_barras' => 'nullable|string|max:50',
            'registro_sanitario' => 'nullable|string|max:50',
            'activo' => 'boolean'
        ]);

        $medicamento->update($request->all());

        return redirect()->route('medicamentos.show', $medicamento)
            ->with('success', 'Medicamento actualizado exitosamente.');
    }

    public function destroy(Medicamento $medicamento)
    {
        // Verificar que no tenga tratamientos activos
        if ($medicamento->tratamientos()->whereIn('tratamientos.estado', ['Activo', 'Pausado'])->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar un medicamento con tratamientos activos.']);
        }

        $medicamento->update(['activo' => false]);

        return redirect()->route('medicamentos.index')
            ->with('success', 'Medicamento desactivado exitosamente.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $medicamentos = Medicamento::activos()
            ->where(function($queryBuilder) use ($query) {
                $queryBuilder->where('nombre', 'like', "%{$query}%")
                    ->orWhere('principio_activo', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($medicamentos);
    }
}