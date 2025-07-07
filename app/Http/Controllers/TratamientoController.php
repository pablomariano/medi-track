<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\User;
use App\Services\HorarioService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TratamientoController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Tratamiento::class);
        
        $query = Tratamiento::with(['paciente', 'medico', 'medicamentos']);
        
        // Filtrar tratamientos según el rol del usuario
        if (auth()->user()->hasRole('medico')) {
            $query->where('medico_usuario_id', auth()->id());
        } elseif (auth()->user()->hasRole('cuidador')) {
            $query->whereHas('paciente.cuidadores', function($q) {
                $q->where('cuidador_usuario_id', auth()->id())
                  ->where('activo', true);
            });
        } elseif (auth()->user()->hasRole('apoderado')) {
            $query->whereHas('paciente.apoderados', function($q) {
                $q->where('apoderado_usuario_id', auth()->id());
            });
        } elseif (auth()->user()->hasRole('paciente')) {
            $query->whereHas('paciente', function($q) {
                $q->where('usuario_id', auth()->id());
            });
        }
        
        $tratamientos = $query->latest()->paginate(10);

        return Inertia::render('Tratamientos/Index', [
            'tratamientos' => $tratamientos
        ]);
    }

    public function create()
    {
        $this->authorize('create', Tratamiento::class);
        
        $pacientes = Paciente::where('activo', true)->get();
        $medicos = User::whereHas('role', function($query) {
            $query->where('nombre', 'medico');
        })->get();
        $medicamentos = \App\Models\Medicamento::where('activo', true)->get();

        return Inertia::render('Tratamientos/Create', [
            'pacientes' => $pacientes,
            'medicos' => $medicos,
            'medicamentos' => $medicamentos
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tratamiento::class);
        
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_usuario_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:Programado',
            'estado' => 'required|in:Activo,Pausado,Suspendido',
            'objetivo' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'observaciones' => 'nullable|string',
            
            // Validaciones para medicamentos
            'medicamentos' => 'nullable|array',
            'medicamentos.*.medicamento_id' => 'required|exists:medicamentos,id',
            'medicamentos.*.dosis_cantidad' => 'required|numeric|min:0.1',
            'medicamentos.*.unidad_dosis' => 'required|string|max:50',
            'medicamentos.*.frecuencia_horas' => 'required|integer|min:1',
            'medicamentos.*.tolerancia_antes_minutos' => 'nullable|integer|min:0',
            'medicamentos.*.tolerancia_despues_minutos' => 'nullable|integer|min:0',
            'medicamentos.*.instrucciones_especiales' => 'nullable|string',
            'medicamentos.*.orden' => 'nullable|integer|min:1',
        ]);

        $tratamiento = Tratamiento::create($request->except('medicamentos'));

        // Asociar medicamentos si se proporcionaron
        if ($request->has('medicamentos') && is_array($request->medicamentos)) {
            foreach ($request->medicamentos as $medicamentoData) {
                $pivotData = [
                    'dosis_cantidad' => $medicamentoData['dosis_cantidad'],
                    'unidad_dosis' => $medicamentoData['unidad_dosis'],
                    'frecuencia_horas' => $medicamentoData['frecuencia_horas'] ?? 24,
                    'tolerancia_antes_minutos' => $medicamentoData['tolerancia_antes_minutos'] ?? 30,
                    'tolerancia_despues_minutos' => $medicamentoData['tolerancia_despues_minutos'] ?? 60,
                    'instrucciones_especiales' => $medicamentoData['instrucciones_especiales'] ?? null,
                    'estado' => 'Activo',
                    'orden' => $medicamentoData['orden'] ?? 1,
                ];

                $tratamiento->medicamentos()->attach($medicamentoData['medicamento_id'], $pivotData);
            }
        }

        // Generar horarios automáticamente para tratamiento programado
        // Recargar medicamentos con pivot data antes de generar horarios
        $tratamiento->load('medicamentos');
        
        // Verificar que hay medicamentos asociados
        $medicamentosAsociados = $tratamiento->medicamentos()->count();
        \Log::info("Tratamiento ID {$tratamiento->id}: {$medicamentosAsociados} medicamento(s) asociado(s)");
        
        if ($medicamentosAsociados > 0) {
            $horarioService = new HorarioService();
            $horarioService->generarHorariosProgramados($tratamiento, $request->horario_principal ?? null);
            $horarioService->generarAdministracionesProgramadas($tratamiento, 30); // Extender a 30 días
            
            // Verificar que se generaron horarios
            $horariosGenerados = \App\Models\HorarioProgramado::where('paciente_id', $tratamiento->paciente_id)->count();
            $administracionesGeneradas = \App\Models\Administracion::where('paciente_id', $tratamiento->paciente_id)->count();
            \Log::info("Generados: {$horariosGenerados} horarios, {$administracionesGeneradas} administraciones para paciente ID {$tratamiento->paciente_id}");
        } else {
            \Log::warning("No se generaron horarios porque el tratamiento ID {$tratamiento->id} no tiene medicamentos asociados");
        }

        return redirect()->route('tratamientos.index')
            ->with('success', 'Tratamiento creado exitosamente con ' . count($request->medicamentos ?? []) . ' medicamento(s). Se generaron los horarios automáticamente.');
    }

    public function show(Tratamiento $tratamiento)
    {
        $this->authorize('view', $tratamiento);
        
        $tratamiento->load([
            'paciente',
            'medico',
            'medicamentos'
        ]);

        // Cargar horarios y administraciones para tratamientos programados
        $medicamentoTratamientoIds = $tratamiento->medicamentos()->pluck('medicamentos_tratamientos.id');
        
        $horarios = \App\Models\HorarioProgramado::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds)->get();
        $tratamiento->horarios_programados = $horarios;
        
        $administraciones = \App\Models\Administracion::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds)
            ->latest()
            ->limit(20)
            ->get();
        $tratamiento->administraciones_recientes = $administraciones;

        return Inertia::render('Tratamientos/Show', [
            'tratamiento' => $tratamiento
        ]);
    }

    public function edit(Tratamiento $tratamiento)
    {
        $this->authorize('update', $tratamiento);
        
        $pacientes = Paciente::where('activo', true)->get();
        $medicos = User::whereHas('role', function($query) {
            $query->where('nombre', 'medico');
        })->get();
        $medicamentos = \App\Models\Medicamento::where('activo', true)->get();

        // Cargar medicamentos actuales del tratamiento con datos del pivot
        $tratamiento->load('medicamentos');

        return Inertia::render('Tratamientos/Edit', [
            'tratamiento' => $tratamiento,
            'pacientes' => $pacientes,
            'medicos' => $medicos,
            'medicamentos' => $medicamentos
        ]);
    }

    public function update(Request $request, Tratamiento $tratamiento)
    {
        $this->authorize('update', $tratamiento);
        
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_usuario_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:Programado',
            'estado' => 'required|in:Activo,Pausado,Completado,Suspendido',
            'objetivo' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'observaciones' => 'nullable|string',
            
            // Validaciones para medicamentos
            'medicamentos' => 'nullable|array',
            'medicamentos.*.medicamento_id' => 'required|exists:medicamentos,id',
            'medicamentos.*.dosis_cantidad' => 'required|numeric|min:0.1',
            'medicamentos.*.unidad_dosis' => 'required|string|max:50',
            'medicamentos.*.frecuencia_horas' => 'required|integer|min:1',
            'medicamentos.*.tolerancia_antes_minutos' => 'nullable|integer|min:0',
            'medicamentos.*.tolerancia_despues_minutos' => 'nullable|integer|min:0',
            'medicamentos.*.instrucciones_especiales' => 'nullable|string',
            'medicamentos.*.orden' => 'nullable|integer|min:1',
        ]);

        // Actualizar el tratamiento
        $tratamiento->update($request->except('medicamentos'));

        // Actualizar medicamentos si se proporcionaron
        if ($request->has('medicamentos') && is_array($request->medicamentos)) {
            // Primero, detach todos los medicamentos existentes
            $tratamiento->medicamentos()->detach();
            
            // Luego, attach los nuevos medicamentos
            foreach ($request->medicamentos as $medicamentoData) {
                $pivotData = [
                    'dosis_cantidad' => $medicamentoData['dosis_cantidad'],
                    'unidad_dosis' => $medicamentoData['unidad_dosis'],
                    'frecuencia_horas' => $medicamentoData['frecuencia_horas'] ?? 24,
                    'tolerancia_antes_minutos' => $medicamentoData['tolerancia_antes_minutos'] ?? 30,
                    'tolerancia_despues_minutos' => $medicamentoData['tolerancia_despues_minutos'] ?? 60,
                    'instrucciones_especiales' => $medicamentoData['instrucciones_especiales'] ?? null,
                    'estado' => 'Activo',
                    'orden' => $medicamentoData['orden'] ?? 1,
                ];

                $tratamiento->medicamentos()->attach($medicamentoData['medicamento_id'], $pivotData);
            }

            // Regenerar horarios automáticamente
            $tratamiento->load('medicamentos');
            
            // Verificar que hay medicamentos asociados
            $medicamentosAsociados = $tratamiento->medicamentos()->count();
            \Log::info("Tratamiento actualizado ID {$tratamiento->id}: {$medicamentosAsociados} medicamento(s) asociado(s)");
            
            if ($medicamentosAsociados > 0) {
                $horarioService = new HorarioService();
                $horarioService->generarHorariosProgramados($tratamiento, $request->horario_principal ?? null);
                $horarioService->generarAdministracionesProgramadas($tratamiento, 30); // Extender a 30 días
                
                // Verificar que se generaron horarios
                $horariosGenerados = \App\Models\HorarioProgramado::where('paciente_id', $tratamiento->paciente_id)->count();
                $administracionesGeneradas = \App\Models\Administracion::where('paciente_id', $tratamiento->paciente_id)->count();
                \Log::info("Regenerados: {$horariosGenerados} horarios, {$administracionesGeneradas} administraciones para paciente ID {$tratamiento->paciente_id}");
            } else {
                \Log::warning("No se regeneraron horarios porque el tratamiento ID {$tratamiento->id} no tiene medicamentos asociados");
            }
        }

        return redirect()->route('tratamientos.index')
            ->with('success', 'Tratamiento actualizado exitosamente. Se regeneraron los horarios automáticamente.');
    }

    public function destroy(Tratamiento $tratamiento)
    {
        $this->authorize('delete', $tratamiento);
        
        $tratamiento->delete();

        return redirect()->route('tratamientos.index')
            ->with('success', 'Tratamiento eliminado exitosamente.');
    }

    public function activar(Tratamiento $tratamiento)
    {
        $this->authorize('changeStatus', $tratamiento);
        
        $tratamiento->update(['estado' => Tratamiento::ESTADO_ACTIVO]);

        return back()->with('success', 'Tratamiento activado exitosamente.');
    }

    public function pausar(Tratamiento $tratamiento)
    {
        $this->authorize('changeStatus', $tratamiento);
        
        $tratamiento->update(['estado' => Tratamiento::ESTADO_PAUSADO]);

        return back()->with('success', 'Tratamiento pausado exitosamente.');
    }

    public function finalizar(Tratamiento $tratamiento)
    {
        $this->authorize('changeStatus', $tratamiento);
        
        $tratamiento->update(['estado' => Tratamiento::ESTADO_FINALIZADO]);

        return back()->with('success', 'Tratamiento finalizado exitosamente.');
    }
} 