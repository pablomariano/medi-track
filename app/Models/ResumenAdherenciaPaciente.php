<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumenAdherenciaPaciente extends Model
{
    use HasFactory;

    protected $table = 'resumen_adherencia_paciente';

    protected $fillable = [
        'paciente_id',
        'mes',
        'ano',
        'total_dosis_programadas',
        'total_dosis_administradas',
        'total_dosis_omitidas',
        'porcentaje_adherencia_global',
        'mejor_medicamento',
        'peor_medicamento',
        'tendencia_mes_anterior'
    ];

    protected $casts = [
        'mes' => 'integer',
        'ano' => 'integer',
        'total_dosis_programadas' => 'integer',
        'total_dosis_administradas' => 'integer',
        'total_dosis_omitidas' => 'integer',
        'porcentaje_adherencia_global' => 'decimal:2'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function mejorMedicamento()
    {
        return $this->belongsTo(Medicamento::class, 'mejor_medicamento');
    }

    public function peorMedicamento()
    {
        return $this->belongsTo(Medicamento::class, 'peor_medicamento');
    }

    public function scopeAno($query, $ano)
    {
        return $query->where('ano', $ano);
    }

    public function scopeMes($query, $mes)
    {
        return $query->where('mes', $mes);
    }
} 