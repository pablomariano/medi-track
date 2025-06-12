<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'sintoma_reportado_id',
        'intensidad_sintoma',
        'criterio_cumplido',
        'observaciones',
        'efectos_adversos'
    ];

    protected $casts = [
        'fecha_hora_programada' => 'datetime',
        'fecha_hora_administrada' => 'datetime',
        'es_dentro_ventana_tolerancia' => 'boolean'
    ];

    const ESTADO_PENDIENTE = 'Pendiente';
    const ESTADO_ADMINISTRADA = 'Administrada';
    const ESTADO_OMITIDA = 'Omitida';
    const ESTADO_TARDIA = 'Tardía';

    // Relación a través de la tabla pivot para obtener tratamiento y medicamento
    public function getTratamientoAttribute()
    {
        $pivot = \DB::table('medicamentos_tratamientos')->where('id', $this->medicamento_tratamiento_id)->first();
        return $pivot ? Tratamiento::find($pivot->tratamiento_id) : null;
    }

    public function getMedicamentoAttribute()
    {
        $pivot = \DB::table('medicamentos_tratamientos')->where('id', $this->medicamento_tratamiento_id)->first();
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
} 