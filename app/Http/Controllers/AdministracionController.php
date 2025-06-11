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
        $query = Administracion::with(['tratamiento.paciente', 'medicamento', 'administradoPor']);

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
            ->with(['tratamiento.paciente', 'medicamento'])
            ->where('fecha_hora_programada', '<=', now()->addHours(2))
            ->orderBy('fecha_hora_programada')
            ->get();

        return Inertia::render('Administraciones/Pendientes', [
            'administraciones' => $administraciones
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tratamiento_id' => 'required|exists:tratamientos,id',
            'medicamento_id' => 'required|exists:medicamentos,id',
            'fecha_hora_programada' => 'required|date',
            'dosis_administrada' => 'required|numeric|min:0',
            'unidad_dosis' => 'required|string|max:20'
        ]);

        $administracion = Administracion::create(array_merge(
            $request->all(),
            ['estado' => Administracion::ESTADO_PENDIENTE]
        ));

        return response()->json($administracion->load(['tratamiento.paciente', 'medicamento']));
    }

    public function administrar(Request $request, Administracion $administracion)
    {
        $request->validate([
            'dosis_administrada' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
            'efectos_observados' => 'nullable|string'
        ]);

        $administracion->update([
            'fecha_hora_administrada' => now(),
            'dosis_administrada' => $request->dosis_administrada,
            'administrado_por_usuario_id' => auth()->id(),
            'estado' => Administracion::ESTADO_ADMINISTRADO,
            'observaciones' => $request->observaciones,
            'efectos_observados' => $request->efectos_observados
        ]);

        return back()->with('success', 'Medicamento administrado exitosamente.');
    }

    public function omitir(Request $request, Administracion $administracion)
    {
        $request->validate([
            'motivo_no_administracion' => 'required|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $administracion->update([
            'estado' => Administracion::ESTADO_OMITIDO,
            'motivo_no_administracion' => $request->motivo_no_administracion,
            'observaciones' => $request->observaciones,
            'administrado_por_usuario_id' => auth()->id()
        ]);

        // Crear alerta por dosis omitida
        Alerta::create([
            'paciente_id' => $administracion->tratamiento->paciente_id,
            'tratamiento_id' => $administracion->tratamiento_id,
            'medicamento_id' => $administracion->medicamento_id,
            'tipo' => Alerta::TIPO_DOSIS_OMITIDA,
            'prioridad' => Alerta::PRIORIDAD_MEDIA,
            'titulo' => 'Dosis omitida',
            'mensaje' => "Se omitió la dosis de {$administracion->medicamento->nombre}. Motivo: {$request->motivo_no_administracion}",
            'fecha_hora' => now(),
            'leida' => false
        ]);

        return back()->with('warning', 'Dosis marcada como omitida.');
    }

    public function rechazar(Request $request, Administracion $administracion)
    {
        $request->validate([
            'motivo_no_administracion' => 'required|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $administracion->update([
            'estado' => Administracion::ESTADO_RECHAZADO,
            'motivo_no_administracion' => $request->motivo_no_administracion,
            'observaciones' => $request->observaciones,
            'administrado_por_usuario_id' => auth()->id()
        ]);

        return back()->with('info', 'Administración rechazada.');
    }

    public function historial(Request $request)
    {
        $query = Administracion::with(['tratamiento.paciente', 'medicamento', 'administradoPor'])
            ->administradas();

        if ($request->filled('paciente_id')) {
            $query->whereHas('tratamiento', function($q) use ($request) {
                $q->where('paciente_id', $request->paciente_id);
            });
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