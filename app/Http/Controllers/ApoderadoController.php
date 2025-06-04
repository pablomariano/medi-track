<?php

namespace App\Http\Controllers;

use App\Models\Apoderado;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApoderadoController extends Controller
{
    public function index()
    {
        $apoderados = Apoderado::with('user')->latest('usuario_id')->paginate(10);
        
        return Inertia::render('Apoderados/Index', [
            'apoderados' => $apoderados
        ]);
    }

    public function create()
    {
        // Obtener usuarios que no son apoderados aún
        $usuariosDisponibles = User::whereNotIn('id', Apoderado::pluck('usuario_id'))->get();
        
        return Inertia::render('Apoderados/Create', [
            'usuarios' => $usuariosDisponibles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id|unique:apoderados',
            'relacion_paciente' => 'nullable|string|max:50',
            'es_contacto_emergencia' => 'boolean'
        ]);

        Apoderado::create($validated);

        return redirect()->route('apoderados.index')
            ->with('success', 'Apoderado creado exitosamente.');
    }

    public function edit(Apoderado $apoderado)
    {
        $apoderado->load('user');
        
        return Inertia::render('Apoderados/Edit', [
            'apoderado' => $apoderado
        ]);
    }

    public function update(Request $request, Apoderado $apoderado)
    {
        $validated = $request->validate([
            'relacion_paciente' => 'nullable|string|max:50',
            'es_contacto_emergencia' => 'boolean'
        ]);

        $apoderado->update($validated);

        return redirect()->route('apoderados.index')
            ->with('success', 'Apoderado actualizado exitosamente.');
    }

    public function destroy(Apoderado $apoderado)
    {
        $apoderado->delete();

        return redirect()->route('apoderados.index')
            ->with('success', 'Apoderado eliminado exitosamente.');
    }
} 