<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadisticaConsumo extends Model
{
    use HasFactory;

    protected $table = 'estadisticas_consumo';

    protected $fillable = [
        'paciente_id',
        'medicamento_id',
        'fecha',
        'dosis_programadas',
        'dosis_administradas',
        'dosis_omitidas',
        'porcentaje_adherencia',
        'efectos_reportados',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'dosis_programadas' => 'integer',
        'dosis_administradas' => 'integer',
        'dosis_omitidas' => 'integer',
        'porcentaje_adherencia' => 'decimal:2',
        'efectos_reportados' => 'array'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function scopeFechaRango($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    public function calcularAdherencia()
    {
        if ($this->dosis_programadas == 0) return 0;
        return round(($this->dosis_administradas / $this->dosis_programadas) * 100, 2);
    }
} 