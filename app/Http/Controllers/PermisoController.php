<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermisoController extends Controller
{
    public function index()
    {
        $permisos = Permiso::orderBy('id', 'desc')->paginate(10);
        
        return Inertia::render('Permisos/Index', [
            'permisos' => $permisos
        ]);
    }

    public function create()
    {
        return Inertia::render('Permisos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:permisos',
            'descripcion' => 'nullable|string',
            'modulo' => 'nullable|string|max:50'
        ]);

        Permiso::create($validated);

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso creado exitosamente.');
    }

    public function edit(Permiso $permiso)
    {
        return Inertia::render('Permisos/Edit', [
            'permiso' => $permiso
        ]);
    }

    public function update(Request $request, Permiso $permiso)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:permisos,nombre,' . $permiso->id,
            'descripcion' => 'nullable|string',
            'modulo' => 'nullable|string|max:50'
        ]);

        $permiso->update($validated);

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso actualizado exitosamente.');
    }

    public function destroy(Permiso $permiso)
    {
        $permiso->delete();

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso eliminado exitosamente.');
    }
} 