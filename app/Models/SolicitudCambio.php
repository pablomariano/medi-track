<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCambio extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_cambio';

    protected $fillable = [
        'tratamiento_id',
        'medico_solicitante_id',
        'tipo_cambio',
        'descripcion_cambio',
        'justificacion',
        'datos_cambios',
        'estado',
        'fecha_respuesta',
        'respondido_por',
        'comentarios_respuesta',
        'prioridad'
    ];

    protected $casts = [
        'datos_cambios' => 'array',
        'fecha_respuesta' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Estados posibles
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_RECHAZADA = 'rechazada';
    const ESTADO_CANCELADA = 'cancelada';

    // Tipos de cambio
    const TIPO_MODIFICACION_DOSIS = 'modificacion_dosis';
    const TIPO_CAMBIO_MEDICAMENTO = 'cambio_medicamento';
    const TIPO_CAMBIO_FRECUENCIA = 'cambio_frecuencia';
    const TIPO_SUSPENSION = 'suspension';
    const TIPO_REINICIO = 'reinicio';

    // Prioridades
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_URGENTE = 'urgente';

    /**
     * Relación con el tratamiento
     */
    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class);
    }

    /**
     * Relación con el médico solicitante
     */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'medico_solicitante_id', 'usuario_id');
    }

    /**
     * Relación con el usuario que respondió
     */
    public function respondidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondido_por');
    }

    /**
     * Scope para solicitudes pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    /**
     * Scope para solicitudes por prioridad
     */
    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Scope para solicitudes urgentes
     */
    public function scopeUrgentes($query)
    {
        return $query->where('prioridad', self::PRIORIDAD_URGENTE);
    }

    /**
     * Verificar si la solicitud está pendiente
     */
    public function esPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    /**
     * Verificar si la solicitud fue aprobada
     */
    public function fueAprobada(): bool
    {
        return $this->estado === self::ESTADO_APROBADA;
    }

    /**
     * Verificar si la solicitud fue rechazada
     */
    public function fueRechazada(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADA;
    }

    /**
     * Verificar si la solicitud es urgente
     */
    public function esUrgente(): bool
    {
        return $this->prioridad === self::PRIORIDAD_URGENTE;
    }

    /**
     * Obtener el color asociado a la prioridad
     */
    public function getColorPrioridadAttribute(): string
    {
        return match($this->prioridad) {
            self::PRIORIDAD_BAJA => 'green',
            self::PRIORIDAD_MEDIA => 'yellow',
            self::PRIORIDAD_ALTA => 'orange',
            self::PRIORIDAD_URGENTE => 'red',
            default => 'gray'
        };
    }

    /**
     * Obtener el color asociado al estado
     */
    public function getColorEstadoAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'yellow',
            self::ESTADO_APROBADA => 'green',
            self::ESTADO_RECHAZADA => 'red',
            self::ESTADO_CANCELADA => 'gray',
            default => 'gray'
        };
    }

    /**
     * Obtener descripción legible del tipo de cambio
     */
    public function getDescripcionTipoAttribute(): string
    {
        return match($this->tipo_cambio) {
            self::TIPO_MODIFICACION_DOSIS => 'Modificación de dosis',
            self::TIPO_CAMBIO_MEDICAMENTO => 'Cambio de medicamento',
            self::TIPO_CAMBIO_FRECUENCIA => 'Cambio de frecuencia',
            self::TIPO_SUSPENSION => 'Suspensión de tratamiento',
            self::TIPO_REINICIO => 'Reinicio de tratamiento',
            default => 'Otro tipo de cambio'
        };
    }

    /**
     * Verificar si la solicitud puede ser procesada
     */
    public function puedeSerProcesada(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    /**
     * Obtener días transcurridos desde la creación
     */
    public function getDiasTranscurridosAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Verificar si la solicitud está vencida (más de 7 días sin respuesta)
     */
    public function estaVencida(): bool
    {
        return $this->esPendiente() && $this->dias_transcurridos > 7;
    }
} 