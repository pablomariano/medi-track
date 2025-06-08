<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\SolicitudCambio;
use App\Models\AlertaMedicamento;
use App\Models\Apoderado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AutorizacionesController extends Controller
{
    /**
     * Dashboard principal para apoderados
     */
    public function dashboardApoderado()
    {
        $user = Auth::user();
        $apoderado = Apoderado::where('usuario_id', $user->id)->first();
        
        if (!$apoderado) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de apoderado.');
        }

        // Obtener pacientes a cargo
        $pacientesACargo = $apoderado->pacientes()
            ->where('activo', true)
            ->with(['tratamientos' => function($query) {
                $query->where('activo', true)->count();
            }])
            ->get();

        // Solicitudes pendientes de autorización
        $solicitudesPendientes = SolicitudCambio::whereHas('tratamiento.paciente', function($query) use ($apoderado) {
                $query->whereHas('apoderados', function($q) use ($apoderado) {
                    $q->where('apoderado_id', $apoderado->id);
                });
            })
            ->where('estado', 'pendiente')
            ->with(['tratamiento.paciente', 'tratamiento.medicamentos', 'medico'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Alertas críticas
        $alertasCriticas = AlertaMedicamento::whereHas('administracion.tratamiento.paciente', function($query) use ($apoderado) {
                $query->whereHas('apoderados', function($q) use ($apoderado) {
                    $q->where('apoderado_id', $apoderado->id);
                });
            })
            ->where('tipo_alerta', 'critica')
            ->where('leida', false)
            ->with(['administracion.tratamiento.paciente'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Estadísticas
        $stats = [
            'pacientes_cargo' => $pacientesACargo->count(),
            'solicitudes_pendientes' => $solicitudesPendientes->count(),
            'alertas_criticas' => $alertasCriticas->count(),
            'tratamientos_activos' => $pacientesACargo->sum(function($p) {
                return $p->tratamientos->where('activo', true)->count();
            })
        ];

        return Inertia::render('Seguimiento/Apoderados/Dashboard', [
            'pacientesACargo' => $pacientesACargo,
            'solicitudesPendientes' => $solicitudesPendientes,
            'alertasCriticas' => $alertasCriticas,
            'stats' => $stats
        ]);
    }

    /**
     * Mostrar solicitud específica para autorización
     */
    public function mostrarSolicitud(SolicitudCambio $solicitud)
    {
        $user = Auth::user();
        $apoderado = Apoderado::where('usuario_id', $user->id)->first();
        
        // Verificar que el apoderado tiene permisos sobre este paciente
        $tienePermiso = $solicitud->tratamiento->paciente->apoderados()
            ->where('apoderado_id', $apoderado->id)
            ->exists();
            
        if (!$tienePermiso) {
            return redirect()->back()->with('error', 'No tienes permisos para ver esta solicitud.');
        }

        $solicitud->load([
            'tratamiento.paciente',
            'tratamiento.medicamentos.medicamento',
            'medico.user',
            'medicamentosNuevos.medicamento'
        ]);

        return Inertia::render('Seguimiento/Apoderados/SolicitudDetalle', [
            'solicitud' => $solicitud
        ]);
    }

    /**
     * Aprobar o rechazar solicitud de cambio
     */
    public function procesarSolicitud(Request $request, SolicitudCambio $solicitud)
    {
        $request->validate([
            'accion' => 'required|in:aprobar,rechazar',
            'comentarios' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();
        $apoderado = Apoderado::where('usuario_id', $user->id)->first();
        
        // Verificar permisos
        $tienePermiso = $solicitud->tratamiento->paciente->apoderados()
            ->where('apoderado_id', $apoderado->id)
            ->exists();
            
        if (!$tienePermiso) {
            return redirect()->back()->with('error', 'No tienes permisos para procesar esta solicitud.');
        }

        if ($solicitud->estado !== 'pendiente') {
            return redirect()->back()->with('error', 'Esta solicitud ya ha sido procesada.');
        }

        try {
            DB::beginTransaction();

            $accion = $request->input('accion');
            $comentarios = $request->input('comentarios');

            // Actualizar estado de la solicitud
            $solicitud->update([
                'estado' => $accion === 'aprobar' ? 'aprobada' : 'rechazada',
                'fecha_respuesta' => now(),
                'respondido_por' => $user->id,
                'comentarios_respuesta' => $comentarios
            ]);

            // Si se aprueba, aplicar los cambios al tratamiento
            if ($accion === 'aprobar') {
                $this->aplicarCambiosTratamiento($solicitud);
            }

            // Crear notificación para el médico
            $this->crearNotificacionMedico($solicitud, $accion);

            DB::commit();

            $mensaje = $accion === 'aprobar' 
                ? 'Solicitud aprobada exitosamente. Los cambios han sido aplicados.'
                : 'Solicitud rechazada. El médico ha sido notificado.';

            return redirect()->route('autorizaciones.dashboard')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Aplicar cambios aprobados al tratamiento
     */
    private function aplicarCambiosTratamiento(SolicitudCambio $solicitud)
    {
        $tratamiento = $solicitud->tratamiento;

        // Actualizar datos básicos del tratamiento si fueron modificados
        if ($solicitud->datos_cambios) {
            $cambios = json_decode($solicitud->datos_cambios, true);
            
            if (isset($cambios['tratamiento'])) {
                $tratamiento->update($cambios['tratamiento']);
            }

            // Actualizar medicamentos si hubo cambios
            if (isset($cambios['medicamentos'])) {
                foreach ($cambios['medicamentos'] as $cambio) {
                    if ($cambio['accion'] === 'eliminar') {
                        $tratamiento->medicamentos()
                            ->where('medicamento_id', $cambio['medicamento_id'])
                            ->update(['activo' => false, 'fecha_fin' => now()]);
                    } elseif ($cambio['accion'] === 'modificar') {
                        $tratamiento->medicamentos()
                            ->where('medicamento_id', $cambio['medicamento_id'])
                            ->update($cambio['datos']);
                    }
                }
            }

            // Agregar nuevos medicamentos
            if (isset($cambios['medicamentos_nuevos'])) {
                foreach ($cambios['medicamentos_nuevos'] as $nuevo) {
                    $tratamiento->medicamentos()->create($nuevo);
                }
            }
        }

        // Registrar en historial
        $tratamiento->historialCambios()->create([
            'tipo_cambio' => 'modificacion_aprobada',
            'datos_anteriores' => $tratamiento->getOriginal(),
            'datos_nuevos' => $tratamiento->getAttributes(),
            'autorizado_por' => auth()->id(),
            'observaciones' => 'Cambios aprobados por apoderado'
        ]);
    }

    /**
     * Crear notificación para el médico
     */
    private function crearNotificacionMedico(SolicitudCambio $solicitud, $accion)
    {
        // Implementar sistema de notificaciones
        // Por ahora, simplemente log
        \Log::info("Solicitud {$accion} por apoderado", [
            'solicitud_id' => $solicitud->id,
            'medico_id' => $solicitud->medico_solicitante_id,
            'apoderado_id' => auth()->id()
        ]);
    }

    /**
     * Historial de autorizaciones
     */
    public function historial(Request $request)
    {
        $user = Auth::user();
        $apoderado = Apoderado::where('usuario_id', $user->id)->first();
        
        $query = SolicitudCambio::whereHas('tratamiento.paciente', function($q) use ($apoderado) {
                $q->whereHas('apoderados', function($sq) use ($apoderado) {
                    $sq->where('apoderado_id', $apoderado->id);
                });
            })
            ->with(['tratamiento.paciente', 'medico.user'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        $solicitudes = $query->paginate(15);

        return Inertia::render('Seguimiento/Apoderados/Historial', [
            'solicitudes' => $solicitudes,
            'filtros' => $request->only(['estado', 'fecha_desde', 'fecha_hasta'])
        ]);
    }
} 