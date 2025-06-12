<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicamentoTratamiento extends Model
{
    use HasFactory;

    protected $table = 'medicamentos_tratamientos';

    protected $fillable = [
        'medicamento_id',
        'tratamiento_id',
        'dosis_cantidad',
        'unidad_dosis',
        'frecuencia_horas',
        'tolerancia_antes_minutos',
        'tolerancia_despues_minutos',
        'intervalo_minimo_horas',
        'dosis_maxima_dia',
        'dosis_maxima_consecutiva',
        'instrucciones_especiales',
        'estado',
        'motivo_suspension',
        'orden'
    ];

    protected $casts = [
        'dosis_cantidad' => 'decimal:3',
        'frecuencia_horas' => 'integer',
        'tolerancia_antes_minutos' => 'integer',
        'tolerancia_despues_minutos' => 'integer',
        'intervalo_minimo_horas' => 'integer',
        'dosis_maxima_dia' => 'decimal:3',
        'dosis_maxima_consecutiva' => 'integer',
        'orden' => 'integer'
    ];

    // Relación con medicamento
    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    // Relación con tratamiento
    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    // Relación con horarios programados
    public function horariosProgramados()
    {
        return $this->hasMany(HorarioProgramado::class);
    }

    // Relación con administraciones
    public function administraciones()
    {
        return $this->hasMany(Administracion::class);
    }

    // Relación con indicaciones PRN
    public function indicacionesPrn()
    {
        return $this->hasMany(IndicacionPrn::class);
    }

    // Scope para medicamentos activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    // Scope para medicamentos de tratamientos programados
    public function scopeProgramados($query)
    {
        return $query->whereHas('tratamiento', function($q) {
            $q->where('tipo', 'Programado');
        });
    }

    // Scope para medicamentos de tratamientos PRN
    public function scopePrn($query)
    {
        return $query->whereHas('tratamiento', function($q) {
            $q->where('tipo', 'PRN');
        });
    }
} 