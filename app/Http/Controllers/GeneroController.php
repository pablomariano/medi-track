<?php

namespace App\Http\Controllers;

use App\Models\Genero;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneroController extends Controller
{
    public function index()
    {
        $generos = Genero::all();
        
        return Inertia::render('Generos/Index', [
            'generos' => $generos
        ]);
    }

    public function create()
    {
        return Inertia::render('Generos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|size:1|unique:generos',
            'nombre' => 'required|string|max:20|unique:generos'
        ]);

        Genero::create($validated);

        return redirect()->route('generos.index')
            ->with('success', 'Género creado exitosamente.');
    }

    public function edit(Genero $genero)
    {
        return Inertia::render('Generos/Edit', [
            'genero' => $genero
        ]);
    }

    public function update(Request $request, Genero $genero)
    {
        $validated = $request->validate([
            'id' => 'required|string|size:1|unique:generos,id,' . $genero->id,
            'nombre' => 'required|string|max:20|unique:generos,nombre,' . $genero->id
        ]);

        $genero->update($validated);

        return redirect()->route('generos.index')
            ->with('success', 'Género actualizado exitosamente.');
    }

    public function destroy(Genero $genero)
    {
        $genero->delete();

        return redirect()->route('generos.index')
            ->with('success', 'Género eliminado exitosamente.');
    }
} 