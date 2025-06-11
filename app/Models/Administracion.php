<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administracion extends Model
{
    use HasFactory;

    protected $table = 'administraciones';

    protected $fillable = [
        'tratamiento_id',
        'medicamento_id',
        'fecha_hora_programada',
        'fecha_hora_administrada',
        'dosis_administrada',
        'unidad_dosis',
        'administrado_por_usuario_id',
        'estado',
        'motivo_no_administracion',
        'observaciones',
        'efectos_observados'
    ];

    protected $casts = [
        'fecha_hora_programada' => 'datetime',
        'fecha_hora_administrada' => 'datetime'
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_ADMINISTRADO = 'administrado';
    const ESTADO_OMITIDO = 'omitido';
    const ESTADO_RECHAZADO = 'rechazado';

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function administradoPor()
    {
        return $this->belongsTo(User::class, 'administrado_por_usuario_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeAdministradas($query)
    {
        return $query->where('estado', self::ESTADO_ADMINISTRADO);
    }
} 