<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaMedicamento extends Model
{
    use HasFactory;

    protected $table = 'alertas_medicamentos';

    protected $fillable = [
        'tratamiento_id',
        'tipo_alerta',
        'nivel_prioridad',
        'titulo',
        'descripcion',
        'estado',
        'fecha_activacion',
        'fecha_resolucion',
        'usuario_resolucion_id',
        'acciones_requeridas'
    ];

    protected $casts = [
        'fecha_activacion' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'creado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    // Tipos de alerta
    const TIPO_INTERACCION = 'interaccion_medicamentosa';
    const TIPO_VENCIMIENTO = 'medicamento_vencido';
    const TIPO_DOSIS_OMITIDA = 'dosis_omitida';
    const TIPO_EFECTO_ADVERSO = 'efecto_adverso';
    const TIPO_DUPLICACION = 'duplicacion_terapeutica';
    const TIPO_CONTRAINDICACION = 'contraindicacion';

    // Niveles de prioridad
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_CRITICA = 'critica';

    // Estados
    const ESTADO_ACTIVA = 'activa';
    const ESTADO_RESUELTA = 'resuelta';
    const ESTADO_IGNORADA = 'ignorada';

    // Relaciones
    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function usuarioResolucion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_resolucion_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVA);
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('nivel_prioridad', $prioridad);
    }

    public function scopeCriticas($query)
    {
        return $query->where('nivel_prioridad', self::PRIORIDAD_CRITICA);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_alerta', $tipo);
    }

    // Métodos útiles
    public function esCritica(): bool
    {
        return $this->nivel_prioridad === self::PRIORIDAD_CRITICA;
    }

    public function resolver($usuarioId, $observaciones = null)
    {
        $this->update([
            'estado' => self::ESTADO_RESUELTA,
            'fecha_resolucion' => now(),
            'usuario_resolucion_id' => $usuarioId,
            'acciones_requeridas' => $observaciones
        ]);

        return $this;
    }

    public function ignorar($usuarioId, $motivo = null)
    {
        $this->update([
            'estado' => self::ESTADO_IGNORADA,
            'fecha_resolucion' => now(),
            'usuario_resolucion_id' => $usuarioId,
            'acciones_requeridas' => $motivo
        ]);

        return $this;
    }

    public function getColorPrioridadAttribute(): string
    {
        return match($this->nivel_prioridad) {
            self::PRIORIDAD_BAJA => 'text-green-600',
            self::PRIORIDAD_MEDIA => 'text-yellow-600',
            self::PRIORIDAD_ALTA => 'text-orange-600',
            self::PRIORIDAD_CRITICA => 'text-red-600',
            default => 'text-gray-600'
        };
    }
}
