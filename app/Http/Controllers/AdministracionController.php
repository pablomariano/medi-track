<?php

namespace App\Http\Controllers;

use App\Models\Administracion;
use App\Models\Tratamiento;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AdministracionController extends Controller
{
    public function index(Request $request)
    {
        $query = Administracion::with(['horarioProgramado', 'paciente', 'cuidador']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_hora_programada', $request->fecha);
        }

        $administraciones = $query->latest('fecha_hora_programada')->paginate(15);

        return Inertia::render('Administraciones/Index', [
            'administraciones' => $administraciones,
            'filtros' => $request->only(['estado', 'fecha'])
        ]);
    }

    public function pendientes()
    {
        $administraciones = Administracion::pendientes()
            ->with(['horarioProgramado', 'paciente'])
            ->where('fecha_hora_programada', '<=', now()->addHours(2))
            ->orderBy('fecha_hora_programada')
            ->get();

        // Asegurar que los accessors se cargan correctamente
        $administraciones->each(function ($administracion) {
            // Esto fuerza la carga de los accessors tratamiento y medicamento
            $administracion->tratamiento;
            $administracion->medicamento;
        });

        return Inertia::render('Administraciones/Pendientes', [
            'administraciones' => $administraciones
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicamento_tratamiento_id' => 'required|exists:medicamentos_tratamientos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_hora_programada' => 'required|date',
            'dosis_administrada' => 'required|numeric|min:0'
        ]);

        $administracion = Administracion::create(array_merge(
            $request->all(),
            [
                'fecha_hora_administrada' => $request->fecha_hora_programada,
                'estado' => Administracion::ESTADO_PENDIENTE
            ]
        ));

        return response()->json($administracion->load(['horarioProgramado', 'paciente']));
    }

    public function administrar(Request $request, Administracion $administracion)
    {
        $request->validate([
            'dosis_administrada' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
            'efectos_adversos' => 'nullable|string'
        ]);

        $administracion->update([
            'fecha_hora_administrada' => now(),
            'dosis_administrada' => $request->dosis_administrada,
            'cuidador_usuario_id' => auth()->id(),
            'estado' => Administracion::ESTADO_ADMINISTRADA,
            'observaciones' => $request->observaciones,
            'efectos_adversos' => $request->efectos_adversos
        ]);

        return back()->with('success', 'Medicamento administrado exitosamente.');
    }

    public function omitir(Request $request, Administracion $administracion)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $administracion->update([
            'estado' => Administracion::ESTADO_OMITIDA,
            'observaciones' => $request->observaciones . (isset($request->motivo) ? " Motivo: {$request->motivo}" : ""),
            'cuidador_usuario_id' => auth()->id()
        ]);

        // Crear alerta por dosis omitida
        $tratamiento = $administracion->tratamiento;
        $medicamento = $administracion->medicamento;
        
        if ($tratamiento && $medicamento) {
            Alerta::create([
                'paciente_id' => $administracion->paciente_id,
                'tratamiento_id' => $tratamiento->id,
                'medicamento_id' => $medicamento->id,
                'tipo' => Alerta::TIPO_DOSIS_OMITIDA,
                'prioridad' => Alerta::PRIORIDAD_MEDIA,
                'titulo' => 'Dosis omitida',
                'mensaje' => "Se omitió la dosis de {$medicamento->nombre}. Motivo: {$request->motivo}",
                'fecha_hora' => now(),
                'leida' => false
            ]);
        }

        return back()->with('warning', 'Dosis marcada como omitida.');
    }

    public function rechazar(Request $request, Administracion $administracion)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $administracion->update([
            'estado' => Administracion::ESTADO_OMITIDA, // Usamos omitida como rechazo
            'observaciones' => $request->observaciones . " Rechazada: {$request->motivo}",
            'cuidador_usuario_id' => auth()->id()
        ]);

        return back()->with('info', 'Administración rechazada.');
    }

    public function historial(Request $request)
    {
        $query = Administracion::with(['horarioProgramado', 'paciente', 'cuidador'])
            ->administradas();

        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_hora_administrada', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_hora_administrada', '<=', $request->fecha_hasta);
        }

        $administraciones = $query->latest('fecha_hora_administrada')->paginate(20);

        return Inertia::render('Administraciones/Historial', [
            'administraciones' => $administraciones,
            'filtros' => $request->only(['paciente_id', 'fecha_desde', 'fecha_hasta'])
        ]);
    }
} 