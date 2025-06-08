<?php

namespace App\Models;

/**
 * Alias del modelo PersonalMedico para mantener compatibilidad
 * con el sistema de seguimiento de tratamientos.
 */
class Medico extends PersonalMedico
{
    // Usamos la misma tabla que PersonalMedico
    protected $table = 'personal_medico';
    
    // Configuraciones heredadas de PersonalMedico
    protected $primaryKey = 'usuario_id';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * Relación con solicitudes de cambio como médico solicitante
     */
    public function solicitudesCambio()
    {
        return $this->hasMany(SolicitudCambio::class, 'medico_solicitante_id', 'usuario_id');
    }

    /**
     * Relación con alertas de medicamentos
     */
    public function alertasMedicamentos()
    {
        return $this->hasManyThrough(
            AlertaMedicamento::class,
            Tratamiento::class,
            'medico_usuario_id',
            'tratamiento_id',
            'usuario_id',
            'id'
        );
    }

    /**
     * Scope para médicos activos
     */
    public function scopeActivos($query)
    {
        return $query->whereHas('user', function($q) {
            $q->where('activo', true);
        });
    }

    /**
     * Obtener estadísticas del médico
     */
    public function getEstadisticas()
    {
        return [
            'pacientes_activos' => $this->pacientesEnTratamiento()->count(),
            'tratamientos_activos' => $this->tratamientosActivos()->count(),
            'solicitudes_pendientes' => $this->solicitudesCambio()
                ->where('estado', SolicitudCambio::ESTADO_PENDIENTE)
                ->count()
        ];
    }
} 