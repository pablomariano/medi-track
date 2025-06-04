<?php

namespace App\Http\Controllers;

use App\Models\Cuidador;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CuidadorController extends Controller
{
    public function index()
    {
        $cuidadores = Cuidador::with('user')->latest('usuario_id')->paginate(10);
        
        return Inertia::render('Cuidadores/Index', [
            'cuidadores' => $cuidadores
        ]);
    }

    public function create()
    {
        // Obtener usuarios que no son cuidadores aún
        $usuariosDisponibles = User::whereNotIn('id', Cuidador::pluck('usuario_id'))->get();
        
        return Inertia::render('Cuidadores/Create', [
            'usuarios' => $usuariosDisponibles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id|unique:cuidadores',
            'certificaciones' => 'nullable|string',
            'experiencia_anos' => 'nullable|integer|min:0',
            'disponibilidad_horaria' => 'nullable|string|max:100',
            'tarifa_hora' => 'nullable|integer|min:0'
        ]);

        Cuidador::create($validated);

        return redirect()->route('cuidadores.index')
            ->with('success', 'Cuidador creado exitosamente.');
    }

    public function edit(Cuidador $cuidador)
    {
        $cuidador->load('user');
        
        return Inertia::render('Cuidadores/Edit', [
            'cuidador' => $cuidador
        ]);
    }

    public function update(Request $request, Cuidador $cuidador)
    {
        $validated = $request->validate([
            'certificaciones' => 'nullable|string',
            'experiencia_anos' => 'nullable|integer|min:0',
            'disponibilidad_horaria' => 'nullable|string|max:100',
            'tarifa_hora' => 'nullable|integer|min:0'
        ]);

        $cuidador->update($validated);

        return redirect()->route('cuidadores.index')
            ->with('success', 'Cuidador actualizado exitosamente.');
    }

    public function destroy(Cuidador $cuidador)
    {
        $cuidador->delete();

        return redirect()->route('cuidadores.index')
            ->with('success', 'Cuidador eliminado exitosamente.');
    }
} 