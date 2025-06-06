<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutorizacionTratamiento extends Model
{
    use HasFactory;

    protected $table = 'autorizaciones_tratamiento';

    protected $fillable = [
        'tratamiento_id',
        'apoderado_usuario_id',
        'tipo_autorizacion',
        'estado',
        'fecha_solicitud',
        'fecha_respuesta',
        'motivo_solicitud',
        'observaciones_apoderado',
        'metodo_autorizacion'
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_respuesta' => 'datetime',
        'creado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    // Tipos de autorización
    const TIPO_INICIO_TRATAMIENTO = 'inicio_tratamiento';
    const TIPO_MODIFICACION_DOSIS = 'modificacion_dosis';
    const TIPO_CAMBIO_MEDICAMENTO = 'cambio_medicamento';
    const TIPO_SUSPENSION_TEMPORAL = 'suspension_temporal';
    const TIPO_FINALIZACION = 'finalizacion';

    // Estados
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_AUTORIZADA = 'autorizada';
    const ESTADO_DENEGADA = 'denegada';
    const ESTADO_VENCIDA = 'vencida';

    // Métodos de autorización
    const METODO_FIRMA_DIGITAL = 'firma_digital';
    const METODO_SMS = 'sms';
    const METODO_EMAIL = 'email';
    const METODO_PRESENCIAL = 'presencial';

    // Relaciones
    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function apoderado(): BelongsTo
    {
        return $this->belongsTo(Apoderado::class, 'apoderado_usuario_id', 'usuario_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeAutorizadas($query)
    {
        return $query->where('estado', self::ESTADO_AUTORIZADA);
    }

    public function scopePorApoderado($query, $apoderadoId)
    {
        return $query->where('apoderado_usuario_id', $apoderadoId);
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE)
                    ->where('fecha_solicitud', '<', now()->subDays(7));
    }

    // Métodos útiles
    public function estaVencida(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE &&
               $this->fecha_solicitud < now()->subDays(7);
    }

    public function autorizar($observaciones = null, $metodo = null)
    {
        $this->update([
            'estado' => self::ESTADO_AUTORIZADA,
            'fecha_respuesta' => now(),
            'observaciones_apoderado' => $observaciones,
            'metodo_autorizacion' => $metodo ?? self::METODO_FIRMA_DIGITAL
        ]);

        return $this;
    }

    public function denegar($observaciones = null)
    {
        $this->update([
            'estado' => self::ESTADO_DENEGADA,
            'fecha_respuesta' => now(),
            'observaciones_apoderado' => $observaciones
        ]);

        return $this;
    }
}
