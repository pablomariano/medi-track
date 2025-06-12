<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioProgramado extends Model
{
    use HasFactory;

    protected $table = 'horarios_programados';

    protected $fillable = [
        'medicamento_tratamiento_id',
        'paciente_id', 
        'hora_programada',
        'dias_semana',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];

    protected $casts = [
        'hora_programada' => 'datetime:H:i',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean'
    ];

    // Obtener tratamiento a través de medicamento_tratamiento
    public function getTratamientoAttribute()
    {
        $pivot = \DB::table('medicamentos_tratamientos')->where('id', $this->medicamento_tratamiento_id)->first();
        return $pivot ? Tratamiento::find($pivot->tratamiento_id) : null;
    }

    // Obtener medicamento a través de medicamento_tratamiento  
    public function getMedicamentoAttribute()
    {
        $pivot = \DB::table('medicamentos_tratamientos')->where('id', $this->medicamento_tratamiento_id)->first();
        return $pivot ? \App\Models\Medicamento::find($pivot->medicamento_id) : null;
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function esHoyDiaValido()
    {
        $diaHoy = date('N'); // 1=Lunes, 7=Domingo
        return in_array($diaHoy, $this->dias_semana ?? []);
    }
} 