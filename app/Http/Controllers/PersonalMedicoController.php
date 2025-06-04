<?php

namespace App\Http\Controllers;

use App\Models\PersonalMedico;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PersonalMedicoController extends Controller
{
    public function index()
    {
        $personalMedico = PersonalMedico::with('user')->latest('usuario_id')->paginate(10);
        
        return Inertia::render('PersonalMedico/Index', [
            'personalMedico' => $personalMedico
        ]);
    }

    public function create()
    {
        // Obtener usuarios que no son personal médico aún
        $usuariosDisponibles = User::whereNotIn('id', PersonalMedico::pluck('usuario_id'))->get();
        
        return Inertia::render('PersonalMedico/Create', [
            'usuarios' => $usuariosDisponibles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id|unique:personal_medico',
            'especialidad' => 'nullable|string|max:100',
            'numero_colegiatura' => 'nullable|string|max:50|unique:personal_medico',
            'institucion' => 'nullable|string|max:100',
            'anos_experiencia' => 'nullable|integer|min:0'
        ]);

        PersonalMedico::create($validated);

        return redirect()->route('personal-medico.index')
            ->with('success', 'Personal médico creado exitosamente.');
    }

    public function edit(PersonalMedico $personalMedico)
    {
        $personalMedico->load('user');
        
        return Inertia::render('PersonalMedico/Edit', [
            'personalMedico' => $personalMedico
        ]);
    }

    public function update(Request $request, PersonalMedico $personalMedico)
    {
        $validated = $request->validate([
            'especialidad' => 'nullable|string|max:100',
            'numero_colegiatura' => 'nullable|string|max:50|unique:personal_medico,numero_colegiatura,' . $personalMedico->usuario_id . ',usuario_id',
            'institucion' => 'nullable|string|max:100',
            'anos_experiencia' => 'nullable|integer|min:0'
        ]);

        $personalMedico->update($validated);

        return redirect()->route('personal-medico.index')
            ->with('success', 'Personal médico actualizado exitosamente.');
    }

    public function destroy(PersonalMedico $personalMedico)
    {
        $personalMedico->delete();

        return redirect()->route('personal-medico.index')
            ->with('success', 'Personal médico eliminado exitosamente.');
    }
} 