<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialTratamiento extends Model
{
    use HasFactory;

    protected $table = 'historial_tratamientos';

    protected $fillable = [
        'tratamiento_id',
        'usuario_id',
        'accion',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'motivo',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'creado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    // Tipos de acciones
    const ACCION_CREADO = 'Tratamiento creado';
    const ACCION_MODIFICADO = 'Tratamiento modificado';
    const ACCION_PAUSADO = 'Tratamiento pausado';
    const ACCION_REANUDADO = 'Tratamiento reanudado';
    const ACCION_FINALIZADO = 'Tratamiento finalizado';
    const ACCION_MEDICAMENTO_AGREGADO = 'Medicamento agregado';
    const ACCION_MEDICAMENTO_MODIFICADO = 'Medicamento modificado';
    const ACCION_MEDICAMENTO_SUSPENDIDO = 'Medicamento suspendido';
    const ACCION_DOSIS_ADMINISTRADA = 'Dosis administrada';
    const ACCION_DOSIS_OMITIDA = 'Dosis omitida';

    // Relaciones
    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePorTratamiento($query, $tratamientoId)
    {
        return $query->where('tratamiento_id', $tratamientoId);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('creado_en', '>=', now()->subDays($dias));
    }

    // Métodos estáticos para crear registros comunes
    public static function registrarCreacion($tratamientoId, $usuarioId = null, $motivo = null)
    {
        return self::create([
            'tratamiento_id' => $tratamientoId,
            'usuario_id' => $usuarioId ?: auth()->id(),
            'accion' => self::ACCION_CREADO,
            'motivo' => $motivo,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public static function registrarModificacion($tratamientoId, $campo, $valorAnterior, $valorNuevo, $usuarioId = null, $motivo = null)
    {
        return self::create([
            'tratamiento_id' => $tratamientoId,
            'usuario_id' => $usuarioId ?: auth()->id(),
            'accion' => self::ACCION_MODIFICADO,
            'campo_modificado' => $campo,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo,
            'motivo' => $motivo,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public static function registrarAdministracion($tratamientoId, $medicamentoNombre, $usuarioId = null)
    {
        return self::create([
            'tratamiento_id' => $tratamientoId,
            'usuario_id' => $usuarioId ?: auth()->id(),
            'accion' => self::ACCION_DOSIS_ADMINISTRADA,
            'campo_modificado' => 'medicamento',
            'valor_nuevo' => $medicamentoNombre,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    // Métodos útiles
    public function getDetalleCompletoAttribute(): string
    {
        $detalle = $this->accion;
        
        if ($this->campo_modificado) {
            $detalle .= " - Campo: {$this->campo_modificado}";
        }
        
        if ($this->valor_anterior && $this->valor_nuevo) {
            $detalle .= " (de '{$this->valor_anterior}' a '{$this->valor_nuevo}')";
        }
        
        if ($this->motivo) {
            $detalle .= " - Motivo: {$this->motivo}";
        }
        
        return $detalle;
    }

    public function getIconoAccionAttribute(): string
    {
        return match($this->accion) {
            self::ACCION_CREADO => '🆕',
            self::ACCION_MODIFICADO => '✏️',
            self::ACCION_PAUSADO => '⏸️',
            self::ACCION_REANUDADO => '▶️',
            self::ACCION_FINALIZADO => '✅',
            self::ACCION_MEDICAMENTO_AGREGADO => '💊',
            self::ACCION_MEDICAMENTO_SUSPENDIDO => '🚫',
            self::ACCION_DOSIS_ADMINISTRADA => '✅',
            self::ACCION_DOSIS_OMITIDA => '❌',
            default => 'ℹ️'
        };
    }
}
