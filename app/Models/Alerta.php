<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'tratamiento_id',
        'medicamento_id',
        'tipo',
        'prioridad',
        'titulo',
        'mensaje',
        'fecha_hora',
        'leida',
        'fecha_lectura',
        'usuario_lector_id',
        'datos_adicionales'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'leida' => 'boolean',
        'fecha_lectura' => 'datetime',
        'datos_adicionales' => 'array'
    ];

    const TIPO_DOSIS_OMITIDA = 'dosis_omitida';
    const TIPO_INTERACCION = 'interaccion';
    const TIPO_EFECTO_ADVERSO = 'efecto_adverso';
    const TIPO_ADHERENCIA_BAJA = 'adherencia_baja';

    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_CRITICA = 'critica';

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function usuarioLector()
    {
        return $this->belongsTo(User::class, 'usuario_lector_id');
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopePrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }
} 