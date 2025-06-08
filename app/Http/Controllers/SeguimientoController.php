<?php

namespace App\Http\Controllers;

use App\Models\AdministracionMedicamento;
use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\AlertaMedicamento;
use App\Models\Cuidador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class SeguimientoController extends Controller
{
    /**
     * Panel principal de seguimiento para cuidadores
     */
    public function dashboardCuidador()
    {
        $user = Auth::user();
        $cuidador = Cuidador::where('usuario_id', $user->id)->first();
        
        if (!$cuidador) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de cuidador');
        }

        $hoy = Carbon::today();
        
        // Administraciones pendientes de hoy
        $administracionesPendientes = AdministracionMedicamento::with([
            'medicamentoTratamiento.medicamento.principioActivo',
            'medicamentoTratamiento.tratamiento.paciente',
            'unidadDosis'
        ])
        ->where('estado', AdministracionMedicamento::ESTADO_PROGRAMADO)
        ->whereBetween('fecha_hora_programada', [$hoy, $hoy->copy()->endOfDay()])
        ->whereHas('medicamentoTratamiento.tratamiento', function($query) use ($cuidador) {
            $query->whereHas('paciente.cuidadores', function($subQuery) use ($cuidador) {
                $subQuery->where('cuidador_usuario_id', $cuidador->usuario_id);
            });
        })
        ->orderBy('fecha_hora_programada')
        ->get();

        // Administraciones vencidas (más de 30 minutos de retraso)
        $administracionesVencidas = AdministracionMedicamento::with([
            'medicamentoTratamiento.medicamento.principioActivo',
            'medicamentoTratamiento.tratamiento.paciente'
        ])
        ->where('estado', AdministracionMedicamento::ESTADO_PROGRAMADO)
        ->where('fecha_hora_programada', '<', now()->subMinutes(30))
        ->whereHas('medicamentoTratamiento.tratamiento', function($query) use ($cuidador) {
            $query->whereHas('paciente.cuidadores', function($subQuery) use ($cuidador) {
                $subQuery->where('cuidador_usuario_id', $cuidador->usuario_id);
            });
        })
        ->get();

        // Administraciones completadas hoy
        $administracionesCompletadas = AdministracionMedicamento::with([
            'medicamentoTratamiento.medicamento.principioActivo',
            'medicamentoTratamiento.tratamiento.paciente'
        ])
        ->where('estado', AdministracionMedicamento::ESTADO_ADMINISTRADO)
        ->where('cuidador_usuario_id', $cuidador->usuario_id)
        ->whereBetween('fecha_hora_real', [$hoy, $hoy->copy()->endOfDay()])
        ->orderBy('fecha_hora_real', 'desc')
        ->get();

        // Pacientes asignados
        $pacientesAsignados = Paciente::whereHas('cuidadores', function($query) use ($cuidador) {
            $query->where('cuidador_usuario_id', $cuidador->usuario_id);
        })
        ->with(['tratamientos' => function($query) {
            $query->where('estado', Tratamiento::ESTADO_ACTIVO);
        }])
        ->get();

        // Alertas críticas
        $alertasCriticas = AlertaMedicamento::with(['tratamiento.paciente'])
        ->where('estado', 'activa')
        ->where('nivel_prioridad', AlertaMedicamento::PRIORIDAD_CRITICA)
        ->whereHas('tratamiento.paciente.cuidadores', function($query) use ($cuidador) {
            $query->where('cuidador_usuario_id', $cuidador->usuario_id);
        })
        ->get();

        // Estadísticas del día
        $estadisticas = [
            'pendientes' => $administracionesPendientes->count(),
            'vencidas' => $administracionesVencidas->count(),
            'completadas' => $administracionesCompletadas->count(),
            'pacientes_activos' => $pacientesAsignados->count(),
            'alertas_criticas' => $alertasCriticas->count()
        ];

        return Inertia::render('Seguimiento/CuidadorDashboard', [
            'administracionesPendientes' => $administracionesPendientes,
            'administracionesVencidas' => $administracionesVencidas,
            'administracionesCompletadas' => $administracionesCompletadas,
            'pacientesAsignados' => $pacientesAsignados,
            'alertasCriticas' => $alertasCriticas,
            'estadisticas' => $estadisticas,
            'cuidador' => $cuidador
        ]);
    }

    /**
     * Confirmar administración de medicamento
     */
    public function confirmarAdministracion(Request $request, AdministracionMedicamento $administracion)
    {
        $validated = $request->validate([
            'dosis_administrada' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
            'efectos_adversos' => 'nullable|string|max:500',
            'metodo_confirmacion' => 'required|in:' . implode(',', [
                AdministracionMedicamento::CONFIRMACION_VISUAL,
                AdministracionMedicamento::CONFIRMACION_CODIGO_QR,
                AdministracionMedicamento::CONFIRMACION_FIRMA_DIGITAL
            ])
        ]);

        DB::transaction(function() use ($administracion, $validated) {
            $administracion->update([
                'estado' => AdministracionMedicamento::ESTADO_ADMINISTRADO,
                'fecha_hora_real' => now(),
                'cuidador_usuario_id' => Auth::id(),
                'dosis_administrada' => $validated['dosis_administrada'],
                'observaciones' => $validated['observaciones'],
                'efectos_adversos' => $validated['efectos_adversos'],
                'metodo_confirmacion' => $validated['metodo_confirmacion']
            ]);

            // Registrar en el historial
            $administracion->medicamentoTratamiento->tratamiento->historial()->create([
                'usuario_id' => Auth::id(),
                'accion' => 'Medicamento Administrado',
                'campo_modificado' => 'administracion_medicamento',
                'valor_nuevo' => "Dosis: {$validated['dosis_administrada']} - Método: {$validated['metodo_confirmacion']}",
                'motivo' => $validated['observaciones']
            ]);

            // Crear alerta si hay efectos adversos
            if (!empty($validated['efectos_adversos'])) {
                AlertaMedicamento::create([
                    'tratamiento_id' => $administracion->medicamentoTratamiento->tratamiento_id,
                    'tipo_alerta' => AlertaMedicamento::TIPO_EFECTO_ADVERSO,
                    'nivel_prioridad' => AlertaMedicamento::PRIORIDAD_ALTA,
                    'titulo' => 'Efectos Adversos Reportados',
                    'descripcion' => $validated['efectos_adversos'],
                    'estado' => 'activa',
                    'fecha_activacion' => now()
                ]);
            }
        });

        return back()->with('success', 'Administración registrada exitosamente');
    }

    /**
     * Marcar administración como omitida
     */
    public function omitirAdministracion(Request $request, AdministracionMedicamento $administracion)
    {
        $validated = $request->validate([
            'motivo' => 'required|string|max:500'
        ]);

        DB::transaction(function() use ($administracion, $validated) {
            $administracion->update([
                'estado' => AdministracionMedicamento::ESTADO_OMITIDO,
                'cuidador_usuario_id' => Auth::id(),
                'motivo_no_administracion' => $validated['motivo']
            ]);

            // Crear alerta por dosis omitida
            AlertaMedicamento::create([
                'tratamiento_id' => $administracion->medicamentoTratamiento->tratamiento_id,
                'tipo_alerta' => AlertaMedicamento::TIPO_DOSIS_OMITIDA,
                'nivel_prioridad' => AlertaMedicamento::PRIORIDAD_MEDIA,
                'titulo' => 'Dosis Omitida',
                'descripcion' => "Motivo: {$validated['motivo']}",
                'estado' => 'activa',
                'fecha_activacion' => now()
            ]);

            // Registrar en historial
            $administracion->medicamentoTratamiento->tratamiento->historial()->create([
                'usuario_id' => Auth::id(),
                'accion' => 'Dosis Omitida',
                'campo_modificado' => 'administracion_medicamento',
                'valor_nuevo' => $validated['motivo'],
                'motivo' => 'Dosis no administrada'
            ]);
        });

        return back()->with('success', 'Administración marcada como omitida');
    }

    /**
     * Vista de paciente específico para el cuidador
     */
    public function verPaciente(Paciente $paciente)
    {
        $user = Auth::user();
        $cuidador = Cuidador::where('usuario_id', $user->id)->first();
        
        // Verificar que el cuidador está asignado a este paciente
        if (!$paciente->cuidadores->contains('usuario_id', $cuidador->usuario_id)) {
            return redirect()->route('seguimiento.dashboard')
                ->with('error', 'No tienes permisos para ver este paciente');
        }

        $tratamientosActivos = $paciente->tratamientos()
            ->where('estado', Tratamiento::ESTADO_ACTIVO)
            ->with([
                'medicamentos.principioActivo',
                'medico.user'
            ])
            ->get();

        $administracionesHoy = AdministracionMedicamento::with([
            'medicamentoTratamiento.medicamento.principioActivo'
        ])
        ->whereHas('medicamentoTratamiento.tratamiento', function($query) use ($paciente) {
            $query->where('paciente_id', $paciente->id);
        })
        ->whereBetween('fecha_hora_programada', [
            Carbon::today(),
            Carbon::today()->endOfDay()
        ])
        ->orderBy('fecha_hora_programada')
        ->get();

        return Inertia::render('Seguimiento/PacienteDetalle', [
            'paciente' => $paciente,
            'tratamientosActivos' => $tratamientosActivos,
            'administracionesHoy' => $administracionesHoy
        ]);
    }

    /**
     * Historial de administraciones del cuidador
     */
    public function historialAdministraciones(Request $request)
    {
        $user = Auth::user();
        
        $query = AdministracionMedicamento::with([
            'medicamentoTratamiento.medicamento.principioActivo',
            'medicamentoTratamiento.tratamiento.paciente'
        ])
        ->where('cuidador_usuario_id', $user->id)
        ->whereNotNull('fecha_hora_real');

        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_hora_real', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_hora_real', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        if ($request->filled('paciente_id')) {
            $query->whereHas('medicamentoTratamiento.tratamiento', function($subQuery) use ($request) {
                $subQuery->where('paciente_id', $request->paciente_id);
            });
        }

        $administraciones = $query->orderBy('fecha_hora_real', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Seguimiento/HistorialAdministraciones', [
            'administraciones' => $administraciones,
            'filters' => $request->only(['fecha_desde', 'fecha_hasta', 'paciente_id'])
        ]);
    }
} 