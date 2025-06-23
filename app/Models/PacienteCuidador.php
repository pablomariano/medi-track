<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PacienteCuidador extends Model
{
    use HasFactory;

    protected $table = 'paciente_cuidadores';

    protected $fillable = [
        'paciente_id',
        'cuidador_usuario_id',
        'fecha_asignacion',
        'fecha_fin',
        'activo'
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean'
    ];

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    // Relación con el paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Relación con el cuidador
    public function cuidador()
    {
        return $this->belongsTo(Cuidador::class, 'cuidador_usuario_id', 'usuario_id');
    }

    // Scope para asignaciones activas
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    // Scope para asignaciones vigentes (sin fecha fin o fecha fin futura)
    public function scopeVigentes($query)
    {
        return $query->where('activo', true)
            ->where(function($q) {
                $q->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>', now());
            });
    }

    // Método para verificar si la asignación está vigente
    public function getEsVigenteAttribute()
    {
        if (!$this->activo) {
            return false;
        }

        if ($this->fecha_fin && $this->fecha_fin->isPast()) {
            return false;
        }

        return true;
    }

    // Método para obtener la duración de la asignación
    public function getDuracionAttribute()
    {
        $inicio = $this->fecha_asignacion;
        $fin = $this->fecha_fin ?: now();
        
        return $inicio->diffForHumans($fin, true);
    }

    // Método para finalizar asignación
    public function finalizar($fecha_fin = null)
    {
        $this->update([
            'fecha_fin' => $fecha_fin ?: now(),
            'activo' => false
        ]);
    }

    /**
     * Buscar una asignación específica por paciente y cuidador
     */
    public static function findByKeys($paciente_id, $cuidador_usuario_id)
    {
        return static::where('paciente_id', $paciente_id)
                     ->where('cuidador_usuario_id', $cuidador_usuario_id)
                     ->first();
    }

    /**
     * Actualizar una asignación específica
     */
    public function updateByKeys($data)
    {
        return $this->where('paciente_id', $this->paciente_id)
                    ->where('cuidador_usuario_id', $this->cuidador_usuario_id)
                    ->update($data);
    }
} 