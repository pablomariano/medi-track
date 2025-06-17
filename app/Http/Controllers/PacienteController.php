<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\User;
use App\Models\Genero;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacienteController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::with(['user', 'genero'])
            ->latest('created_at')
            ->paginate(10);
        
        return Inertia::render('Pacientes/Index', [
            'pacientes' => $pacientes
        ]);
    }

    public function create()
    {
        // Obtener usuarios que no son pacientes aún
        $usuariosDisponibles = User::whereNotIn('id', function($query) {
            $query->select('usuario_id')
                  ->from('pacientes')
                  ->whereNotNull('usuario_id');
        })->get();
        
        $generos = Genero::all();
        
        return Inertia::render('Pacientes/Create', [
            'usuarios' => $usuariosDisponibles,
            'generos' => $generos
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'nullable|exists:users,id|unique:pacientes',
            'nombre' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero_id' => 'nullable|exists:generos,id',
            'numero_documento' => 'nullable|string|max:20|unique:pacientes',
            'tipo_documento' => 'nullable|string|max:10',
            'tipo_sangre' => 'nullable|string|max:10',
            'altura' => 'nullable|numeric|min:0|max:300',
            'direccion' => 'nullable|string',
            'telefono_emergencia' => 'nullable|string|max:20',
            'observaciones_medicas' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        Paciente::create($validated);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente creado exitosamente.');
    }

    public function show(Paciente $paciente)
    {
        $paciente->load(['user', 'genero', 'cuidadoresVigentes.user']);
        
        return Inertia::render('Pacientes/Show', [
            'paciente' => $paciente
        ]);
    }

    public function edit(Paciente $paciente)
    {
        $paciente->load(['user', 'genero']);
        
        // Obtener usuarios disponibles (incluir el actual si existe)
        $usuariosDisponibles = User::whereNotIn('id', function($query) use ($paciente) {
            $query->select('usuario_id')
                  ->from('pacientes')
                  ->whereNotNull('usuario_id')
                  ->where('id', '!=', $paciente->id);
        })->get();
        
        $generos = Genero::all();
        
        return Inertia::render('Pacientes/Edit', [
            'paciente' => $paciente,
            'usuarios' => $usuariosDisponibles,
            'generos' => $generos
        ]);
    }

    public function update(Request $request, Paciente $paciente)
    {
        $validated = $request->validate([
            'usuario_id' => 'nullable|exists:users,id|unique:pacientes,usuario_id,' . $paciente->id,
            'nombre' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero_id' => 'nullable|exists:generos,id',
            'numero_documento' => 'nullable|string|max:20|unique:pacientes,numero_documento,' . $paciente->id,
            'tipo_documento' => 'nullable|string|max:10',
            'tipo_sangre' => 'nullable|string|max:10',
            'altura' => 'nullable|numeric|min:0|max:300',
            'direccion' => 'nullable|string',
            'telefono_emergencia' => 'nullable|string|max:20',
            'observaciones_medicas' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $paciente->update($validated);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado exitosamente.');
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente eliminado exitosamente.');
    }
} 