<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicineController extends Controller
{
    public function index()
    {
        $medicines = Medicine::latest()->paginate(10);
        
        return Inertia::render('Medicines/Index', [
            'medicines' => $medicines
        ]);
    }

    public function create()
    {
        return Inertia::render('Medicines/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'medida' => 'required|numeric',
            'unidad_medida' => 'required|string|max:50',
            'descripcion' => 'nullable|string'
        ]);

        Medicine::create($validated);

        return redirect()->route('medicines.index')
            ->with('success', 'Medicamento creado exitosamente.');
    }

    public function edit(Medicine $medicine)
    {
        return Inertia::render('Medicines/Edit', [
            'medicine' => $medicine
        ]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'medida' => 'required|numeric',
            'unidad_medida' => 'required|string|max:50',
            'descripcion' => 'nullable|string'
        ]);

        $medicine->update($validated);

        return redirect()->route('medicines.index')
            ->with('success', 'Medicamento actualizado exitosamente.');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return redirect()->route('medicines.index')
            ->with('success', 'Medicamento eliminado exitosamente.');
    }
} 