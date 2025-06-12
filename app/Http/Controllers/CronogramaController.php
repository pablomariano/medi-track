<?php

namespace App\Http\Controllers;

use App\Models\Administracion;
use App\Models\Paciente;
use App\Services\HorarioService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CronogramaController extends Controller
{
    private $horarioService;

    public function __construct(HorarioService $horarioService)
    {
        $this->horarioService = $horarioService;
    }

    /**
     * Mostrar cronograma diario del paciente
     */
    public function index(Request $request)
    {
        $pacienteId = $request->paciente_id ?? auth()->user()->paciente?->id;
        $fecha = $request->fecha ?? Carbon::today()->format('Y-m-d');

        if (!$pacienteId) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró el paciente asociado.');
        }

        $cronograma = $this->horarioService->obtenerCronogramaDiario($pacienteId, $fecha);
        $paciente = Paciente::findOrFail($pacienteId);

        // Estadísticas del día
        $estadisticas = [
            'total' => $cronograma->flatten()->count(),
            'administradas' => $cronograma->get(Administracion::ESTADO_ADMINISTRADO, collect())->count(),
            'pendientes' => $cronograma->get(Administracion::ESTADO_PENDIENTE, collect())->count(),
            'omitidas' => $cronograma->get(Administracion::ESTADO_OMITIDO, collect())->count(),
        ];

        $estadisticas['cumplimiento'] = $estadisticas['total'] > 0 
            ? round(($estadisticas['administradas'] / $estadisticas['total']) * 100, 1)
            : 0;

        return Inertia::render('Cronograma/Index', [
            'paciente' => $paciente,
            'cronograma' => $cronograma,
            'fecha' => $fecha,
            'estadisticas' => $estadisticas,
            'fechas_disponibles' => $this->obtenerFechasDisponibles($pacienteId)
        ]);
    }

    /**
     * Marcar medicamento como administrado
     */
    public function administrar(Request $request, Administracion $administracion)
    {
        $request->validate([
            'observaciones' => 'nullable|string|max:500',
            'efectos_observados' => 'nullable|string|max:500'
        ]);

        $administracion->update([
            'fecha_hora_administrada' => now(),
            'administrado_por_usuario_id' => auth()->id(),
            'estado' => Administracion::ESTADO_ADMINISTRADO,
            'observaciones' => $request->observaciones,
            'efectos_observados' => $request->efectos_observados
        ]);

        return back()->with('success', 'Medicamento marcado como administrado.');
    }

    /**
     * Marcar medicamento como omitido
     */
    public function omitir(Request $request, Administracion $administracion)
    {
        $request->validate([
            'motivo_no_administracion' => 'required|string|max:255',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $administracion->update([
            'estado' => Administracion::ESTADO_OMITIDO,
            'motivo_no_administracion' => $request->motivo_no_administracion,
            'observaciones' => $request->observaciones,
            'administrado_por_usuario_id' => auth()->id()
        ]);

        return back()->with('warning', 'Medicamento marcado como omitido.');
    }

    /**
     * Registrar medicamento PRN
     */
    public function registrarPrn(Request $request)
    {
        $request->validate([
            'tratamiento_id' => 'required|exists:tratamientos,id',
            'medicamento_id' => 'required|exists:medicamentos,id',
            'dosis_administrada' => 'required|numeric|min:0.1',
            'motivo' => 'required|string|max:255',
            'intensidad_sintoma' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:500'
        ]);

        // Verificar restricciones PRN (intervalo mínimo, dosis máxima)
        $puedeAdministrar = $this->verificarRestriccionesPrn(
            $request->tratamiento_id,
            $request->medicamento_id
        );

        if (!$puedeAdministrar['permitido']) {
            return back()->with('error', $puedeAdministrar['motivo']);
        }

        Administracion::create([
            'tratamiento_id' => $request->tratamiento_id,
            'medicamento_id' => $request->medicamento_id,
            'fecha_hora_administrada' => now(),
            'dosis_administrada' => $request->dosis_administrada,
            'estado' => Administracion::ESTADO_ADMINISTRADO,
            'criterio_cumplido' => $request->motivo,
            'intensidad_sintoma' => $request->intensidad_sintoma,
            'observaciones' => $request->observaciones,
            'administrado_por_usuario_id' => auth()->id()
        ]);

        return back()->with('success', 'Medicamento PRN registrado exitosamente.');
    }

    /**
     * Resumen semanal de cumplimiento
     */
    public function resumenSemanal(Request $request)
    {
        $pacienteId = $request->paciente_id ?? auth()->user()->paciente?->id;
        $fechaInicio = $request->fecha_inicio ?? Carbon::today()->startOfWeek()->format('Y-m-d');

        $resumen = $this->calcularResumenSemanal($pacienteId, $fechaInicio);

        return Inertia::render('Cronograma/ResumenSemanal', [
            'resumen' => $resumen,
            'fecha_inicio' => $fechaInicio
        ]);
    }

    /**
     * Métodos privados de apoyo
     */
    private function obtenerFechasDisponibles($pacienteId)
    {
        // Obtener fechas con administraciones programadas
        return Administracion::whereHas('tratamiento', function($query) use ($pacienteId) {
                $query->where('paciente_id', $pacienteId);
            })
            ->whereNotNull('fecha_hora_programada')
            ->selectRaw('DATE(fecha_hora_programada) as fecha')
            ->distinct()
            ->orderBy('fecha', 'desc')
            ->limit(30)
            ->pluck('fecha');
    }

    private function verificarRestriccionesPrn($tratamientoId, $medicamentoId)
    {
        // Obtener configuración PRN del medicamento
        $configuracion = \DB::table('medicamentos_tratamientos')
            ->where('tratamiento_id', $tratamientoId)
            ->where('medicamento_id', $medicamentoId)
            ->first();

        if (!$configuracion) {
            return ['permitido' => false, 'motivo' => 'Medicamento no encontrado en el tratamiento.'];
        }

        // Verificar última administración
        $ultimaAdministracion = Administracion::where('tratamiento_id', $tratamientoId)
            ->where('medicamento_id', $medicamentoId)
            ->where('estado', Administracion::ESTADO_ADMINISTRADO)
            ->latest('fecha_hora_administrada')
            ->first();

        if ($ultimaAdministracion && $configuracion->intervalo_minimo_horas) {
            $horasTranscurridas = $ultimaAdministracion->fecha_hora_administrada->diffInHours(now());
            if ($horasTranscurridas < $configuracion->intervalo_minimo_horas) {
                return [
                    'permitido' => false, 
                    'motivo' => "Debe esperar {$configuracion->intervalo_minimo_horas} horas desde la última dosis. Faltan " . 
                               ($configuracion->intervalo_minimo_horas - $horasTranscurridas) . " horas."
                ];
            }
        }

        // Verificar dosis máxima diaria
        if ($configuracion->dosis_maxima_consecutiva) {
            $dosisHoy = Administracion::where('tratamiento_id', $tratamientoId)
                ->where('medicamento_id', $medicamentoId)
                ->where('estado', Administracion::ESTADO_ADMINISTRADO)
                ->whereDate('fecha_hora_administrada', Carbon::today())
                ->count();

            if ($dosisHoy >= $configuracion->dosis_maxima_consecutiva) {
                return [
                    'permitido' => false,
                    'motivo' => "Ya se alcanzó la dosis máxima diaria de {$configuracion->dosis_maxima_consecutiva} dosis."
                ];
            }
        }

        return ['permitido' => true, 'motivo' => null];
    }

    private function calcularResumenSemanal($pacienteId, $fechaInicio)
    {
        $fechaInicio = Carbon::parse($fechaInicio);
        $fechaFin = $fechaInicio->copy()->endOfWeek();

        $administraciones = Administracion::whereHas('tratamiento', function($query) use ($pacienteId) {
                $query->where('paciente_id', $pacienteId);
            })
            ->whereBetween('fecha_hora_programada', [$fechaInicio, $fechaFin])
            ->with(['tratamiento', 'medicamento'])
            ->get();

        $resumenPorDia = [];
        for ($fecha = $fechaInicio->copy(); $fecha <= $fechaFin; $fecha->addDay()) {
            $administracionesDia = $administraciones->filter(function($admin) use ($fecha) {
                return $admin->fecha_hora_programada->format('Y-m-d') === $fecha->format('Y-m-d');
            });

            $resumenPorDia[$fecha->format('Y-m-d')] = [
                'fecha' => $fecha->format('Y-m-d'),
                'dia_semana' => $fecha->locale('es')->dayName,
                'total' => $administracionesDia->count(),
                'administradas' => $administracionesDia->where('estado', Administracion::ESTADO_ADMINISTRADO)->count(),
                'omitidas' => $administracionesDia->where('estado', Administracion::ESTADO_OMITIDO)->count(),
                'pendientes' => $administracionesDia->where('estado', Administracion::ESTADO_PENDIENTE)->count(),
            ];
        }

        return [
            'semana' => $resumenPorDia,
            'totales' => [
                'administradas' => $administraciones->where('estado', Administracion::ESTADO_ADMINISTRADO)->count(),
                'omitidas' => $administraciones->where('estado', Administracion::ESTADO_OMITIDO)->count(),
                'pendientes' => $administraciones->where('estado', Administracion::ESTADO_PENDIENTE)->count(),
                'total' => $administraciones->count()
            ]
        ];
    }
} 