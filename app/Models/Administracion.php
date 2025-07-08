<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Administracion extends Model
{
    use HasFactory;

    protected $table = 'administraciones';

    protected $fillable = [
        'medicamento_tratamiento_id',
        'horario_programado_id',
        'paciente_id',
        'cuidador_usuario_id',
        'fecha_hora_programada',
        'fecha_hora_administrada',
        'dosis_administrada',
        'estado',
        'es_dentro_ventana_tolerancia',
        'minutos_diferencia',
        'minutos_adelanto',
        'minutos_retraso',
        'score_puntualidad',
        'categoria_temporal',
        'sintoma_reportado_id',
        'intensidad_sintoma',
        'criterio_cumplido',
        'observaciones',
        'efectos_adversos'
    ];

    protected $casts = [
        'fecha_hora_programada' => 'datetime',
        'fecha_hora_administrada' => 'datetime',
        'es_dentro_ventana_tolerancia' => 'boolean',
        'score_puntualidad' => 'decimal:2',
        'minutos_adelanto' => 'integer',
        'minutos_retraso' => 'integer',
        'minutos_diferencia' => 'integer'
    ];

    protected $appends = ['tratamiento', 'medicamento'];

    const ESTADO_PENDIENTE = 'Pendiente';
    const ESTADO_ADMINISTRADA = 'Administrada';
    const ESTADO_OMITIDA = 'Omitida';
    const ESTADO_TARDIA = 'Tardía';

    // Relación a través de la tabla pivot para obtener tratamiento y medicamento
    public function getTratamientoAttribute()
    {
        $pivot = DB::table('medicamentos_tratamientos')->where('id', $this->medicamento_tratamiento_id)->first();
        return $pivot ? Tratamiento::with('paciente')->find($pivot->tratamiento_id) : null;
    }

    public function getMedicamentoAttribute()
    {
        $pivot = DB::table('medicamentos_tratamientos')->where('id', $this->medicamento_tratamiento_id)->first();
        return $pivot ? Medicamento::find($pivot->medicamento_id) : null;
    }

    public function horarioProgramado()
    {
        return $this->belongsTo(HorarioProgramado::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function cuidador()
    {
        return $this->belongsTo(User::class, 'cuidador_usuario_id');
    }

    public function medicamentoTratamiento()
    {
        return $this->belongsTo(MedicamentoTratamiento::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeAdministradas($query)
    {
        return $query->where('estado', self::ESTADO_ADMINISTRADA);
    }

    public function scopeOmitidas($query)
    {
        return $query->where('estado', self::ESTADO_OMITIDA);
    }

    public function scopeTardias($query)
    {
        return $query->where('estado', self::ESTADO_TARDIA);
    }

    // Scopes para métricas temporales
    public function scopePuntuales($query)
    {
        return $query->where('categoria_temporal', 'puntual');
    }

    public function scopeTempranas($query)
    {
        return $query->whereIn('categoria_temporal', ['temprano', 'muy_temprano']);
    }

    public function scopeTardiasTemporales($query)
    {
        return $query->whereIn('categoria_temporal', ['tardio', 'muy_tardio']);
    }

    public function scopeConBuenaPuntualidad($query, $umbral = 80)
    {
        return $query->where('score_puntualidad', '>=', $umbral);
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_hora_programada', [$fechaInicio, $fechaFin]);
    }

    public function scopePorMedicamento($query, $medicamentoId)
    {
        return $query->whereHas('medicamentoTratamiento', function($q) use ($medicamentoId) {
            $q->where('medicamento_id', $medicamentoId);
        });
    }

    public function scopePorTratamiento($query, $tratamientoId)
    {
        return $query->whereHas('medicamentoTratamiento', function($q) use ($tratamientoId) {
            $q->where('tratamiento_id', $tratamientoId);
        });
    }

    public function scopeConMetricasTemporales($query)
    {
        return $query->whereNotNull('fecha_hora_administrada')
                    ->whereNotNull('score_puntualidad');
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria_temporal', $categoria);
    }

    // Métodos de ayuda para métricas temporales
    public function esPuntual(): bool
    {
        return $this->categoria_temporal === 'puntual';
    }

    public function esTemprana(): bool
    {
        return in_array($this->categoria_temporal, ['temprano', 'muy_temprano']);
    }

    public function esTardiaTemporalmente(): bool
    {
        return in_array($this->categoria_temporal, ['tardio', 'muy_tardio']);
    }

    public function tieneBuenaPuntualidad($umbral = 80): bool
    {
        return $this->score_puntualidad >= $umbral;
    }

    public function getColorCategoriaTemporal(): string
    {
        return match($this->categoria_temporal) {
            'muy_temprano' => '#dc2626', // red-600
            'temprano' => '#f59e0b',     // amber-500
            'puntual' => '#10b981',      // emerald-500
            'tardio' => '#f59e0b',       // amber-500
            'muy_tardio' => '#dc2626',   // red-600
            default => '#6b7280'         // gray-500
        };
    }

    public function getTextoCategoriaTemporal(): string
    {
        return match($this->categoria_temporal) {
            'muy_temprano' => 'Muy Temprano',
            'temprano' => 'Temprano',
            'puntual' => 'Puntual',
            'tardio' => 'Tardío',
            'muy_tardio' => 'Muy Tardío',
            default => 'No definido'
        };
    }

    public function getDescripcionTemporal(): string
    {
        if (!$this->fecha_hora_programada || !$this->fecha_hora_administrada) {
            return 'Sin información temporal';
        }

        if ($this->esPuntual()) {
            return 'Administrado a tiempo';
        }

        if ($this->esTemprana()) {
            $minutos = $this->minutos_adelanto;
            return "Administrado {$minutos} minuto" . ($minutos != 1 ? 's' : '') . " antes de tiempo";
        }

        if ($this->esTardiaTemporalmente()) {
            $minutos = $this->minutos_retraso;
            return "Administrado {$minutos} minuto" . ($minutos != 1 ? 's' : '') . " después de tiempo";
        }

        return 'Información temporal no disponible';
    }

    public function formatearDiferenciaTemporal(): string
    {
        if ($this->minutos_diferencia === 0) {
            return 'Exacto';
        }

        $abs = abs($this->minutos_diferencia);
        $signo = $this->minutos_diferencia > 0 ? '+' : '-';
        
        if ($abs >= 60) {
            $horas = intval($abs / 60);
            $minutos = $abs % 60;
            $texto = $horas . 'h';
            if ($minutos > 0) {
                $texto .= ' ' . $minutos . 'm';
            }
        } else {
            $texto = $abs . 'm';
        }

        return $signo . $texto;
    }

    /**
     * Retorna la diferencia temporal en minutos para el TemporalAdherenceService
     * Positivo = tarde, Negativo = temprano
     */
    public function getDiferenciaTemporal(): ?int
    {
        if (!$this->fecha_hora_programada || !$this->fecha_hora_administrada) {
            return null;
        }

        $programada = \Carbon\Carbon::parse($this->fecha_hora_programada);
        $administrada = \Carbon\Carbon::parse($this->fecha_hora_administrada);
        
        return $administrada->diffInMinutes($programada, false);
    }

    /**
     * Verifica si la administración está completa para análisis temporal
     */
    public function tieneMetricasTemporalesCompletas(): bool
    {
        return !is_null($this->fecha_hora_programada) &&
               !is_null($this->fecha_hora_administrada) &&
               !is_null($this->score_puntualidad) &&
               !is_null($this->categoria_temporal);
    }
} 