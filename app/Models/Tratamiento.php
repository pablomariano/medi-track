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
    const TIPO_PROGRAMADO = 'Programado';
    const TIPO_PRN = 'PRN';

    // Constantes para estados
    const ESTADO_ACTIVO = 'Activo';
    const ESTADO_PAUSADO = 'Pausado';
    const ESTADO_FINALIZADO = 'Completado';
    const ESTADO_CANCELADO = 'Suspendido';

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