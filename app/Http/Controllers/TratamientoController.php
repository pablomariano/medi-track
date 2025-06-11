<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TratamientoController extends Controller
{
    public function index()
    {
        $tratamientos = Tratamiento::with(['paciente', 'medico', 'medicamentos'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Tratamientos/Index', [
            'tratamientos' => $tratamientos
        ]);
    }

    public function create()
    {
        $pacientes = Paciente::where('activo', true)->get();
        $medicos = User::whereHas('role', function($query) {
            $query->where('nombre', 'medico');
        })->get();

        return Inertia::render('Tratamientos/Create', [
            'pacientes' => $pacientes,
            'medicos' => $medicos
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_usuario_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:programado,prn',
            'objetivo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'observaciones' => 'nullable|string'
        ]);

        $tratamiento = Tratamiento::create($request->all());

        return redirect()->route('tratamientos.show', $tratamiento)
            ->with('success', 'Tratamiento creado exitosamente.');
    }

    public function show(Tratamiento $tratamiento)
    {
        $tratamiento->load([
            'paciente',
            'medico',
            'medicamentos.pivot',
            'horarios',
            'indicacionesPrn.sintoma',
            'administraciones' => function($query) {
                $query->latest()->limit(20);
            }
        ]);

        return Inertia::render('Tratamientos/Show', [
            'tratamiento' => $tratamiento
        ]);
    }

    public function edit(Tratamiento $tratamiento)
    {
        $pacientes = Paciente::where('activo', true)->get();
        $medicos = User::whereHas('role', function($query) {
            $query->where('nombre', 'medico');
        })->get();

        return Inertia::render('Tratamientos/Edit', [
            'tratamiento' => $tratamiento,
            'pacientes' => $pacientes,
            'medicos' => $medicos
        ]);
    }

    public function update(Request $request, Tratamiento $tratamiento)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_usuario_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:programado,prn',
            'objetivo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'estado' => 'required|in:activo,pausado,finalizado,cancelado',
            'observaciones' => 'nullable|string'
        ]);

        $tratamiento->update($request->all());

        return redirect()->route('tratamientos.show', $tratamiento)
            ->with('success', 'Tratamiento actualizado exitosamente.');
    }

    public function destroy(Tratamiento $tratamiento)
    {
        $tratamiento->delete();

        return redirect()->route('tratamientos.index')
            ->with('success', 'Tratamiento eliminado exitosamente.');
    }

    public function activar(Tratamiento $tratamiento)
    {
        $tratamiento->update(['estado' => Tratamiento::ESTADO_ACTIVO]);

        return back()->with('success', 'Tratamiento activado exitosamente.');
    }

    public function pausar(Tratamiento $tratamiento)
    {
        $tratamiento->update(['estado' => Tratamiento::ESTADO_PAUSADO]);

        return back()->with('success', 'Tratamiento pausado exitosamente.');
    }

    public function finalizar(Tratamiento $tratamiento)
    {
        $tratamiento->update(['estado' => Tratamiento::ESTADO_FINALIZADO]);

        return back()->with('success', 'Tratamiento finalizado exitosamente.');
    }
} 