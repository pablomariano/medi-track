<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'medico_usuario_id',
        'nombre',
        'tipo',
        'objetivo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    // Constantes para tipos de tratamiento
    const TIPO_PROGRAMADO = 'programado';
    const TIPO_PRN = 'prn';

    // Constantes para estados
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_PAUSADO = 'pausado';
    const ESTADO_FINALIZADO = 'finalizado';
    const ESTADO_CANCELADO = 'cancelado';

    // Relación con paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // Relación con médico
    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_usuario_id');
    }

    // Relación con medicamentos a través de la tabla pivot
    public function medicamentos()
    {
        return $this->belongsToMany(Medicamento::class, 'medicamentos_tratamientos')
                    ->withPivot([
                        'dosis',
                        'unidad_dosis',
                        'frecuencia_horas',
                        'duracion_dias',
                        'instrucciones_especiales',
                        'activo'
                    ])
                    ->withTimestamps();
    }

    // Relación con horarios programados
    public function horarios()
    {
        return $this->hasMany(HorarioProgramado::class);
    }

    // Relación con indicaciones PRN
    public function indicacionesPrn()
    {
        return $this->hasMany(IndicacionPrn::class);
    }

    // Relación con administraciones
    public function administraciones()
    {
        return $this->hasMany(Administracion::class);
    }

    // Scope para tratamientos activos
    public function scopeActivos($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    // Scope para tratamientos por tipo
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Scope para tratamientos programados
    public function scopeProgramados($query)
    {
        return $query->where('tipo', self::TIPO_PROGRAMADO);
    }

    // Scope para tratamientos PRN
    public function scopePrn($query)
    {
        return $query->where('tipo', self::TIPO_PRN);
    }

    // Método para verificar si el tratamiento está activo
    public function isActivo()
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    // Método para verificar si es tratamiento PRN
    public function isPrn()
    {
        return $this->tipo === self::TIPO_PRN;
    }
} 