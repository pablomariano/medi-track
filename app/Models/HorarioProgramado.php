<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioProgramado extends Model
{
    use HasFactory;

    protected $table = 'horarios_programados';

    protected $fillable = [
        'tratamiento_id',
        'hora',
        'dias_semana',
        'activo'
    ];

    protected $casts = [
        'hora' => 'datetime:H:i',
        'dias_semana' => 'array',
        'activo' => 'boolean'
    ];

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
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