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

    public function dataTable()
    {
        $medicamentos = Medicamento::activos()
            ->orderBy('nombre')
            ->paginate(20);

        return Inertia::render('Medicamentos/DataTable', [
            'medicamentos' => $medicamentos
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
        if ($medicamento->tratamientos()->whereIn('estado', ['activo', 'pausado'])->exists()) {
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