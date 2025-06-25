<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    use HasFactory;

    // Constantes para los estados
    const ESTADO_ACTIVO = 'Activo';
    const ESTADO_PAUSADO = 'Pausado';
    const ESTADO_COMPLETADO = 'Completado';
    const ESTADO_SUSPENDIDO = 'Suspendido';

    // Constantes para los tipos - Solo Programado
    const TIPO_PROGRAMADO = 'Programado';

    protected $fillable = [
        'paciente_id',
        'medico_usuario_id',
        'nombre',
        'diagnostico',
        'tipo',
        'estado',
        'objetivo',
        'fecha_inicio',
        'fecha_fin',
        'observaciones'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    // Relación con paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // Relación con médico
    public function medico()
    {
        return $this->belongsTo(PersonalMedico::class, 'medico_usuario_id', 'usuario_id');
    }

    // Relación many-to-many con medicamentos
    public function medicamentos()
    {
        return $this->belongsToMany(
            Medicamento::class,
            'medicamentos_tratamientos',
            'tratamiento_id',
            'medicamento_id'
        )->withPivot([
            'id',
            'dosis_cantidad',
            'unidad_dosis',
            'frecuencia_horas',
            'tolerancia_antes_minutos',
            'tolerancia_despues_minutos',
            'duracion_dias',
            'instrucciones_especiales',
            'estado',
            'activo',
            'motivo_suspension',
            'orden'
        ])->withTimestamps();
    }

    // Relación directa con medicamento_tratamientos
    public function medicamentoTratamientos()
    {
        return $this->hasMany(MedicamentoTratamiento::class, 'tratamiento_id');
    }

    // Obtener horarios programados de este tratamiento
    public function horarios()
    {
        $medicamentoTratamientoIds = $this->medicamentos()->pluck('medicamentos_tratamientos.id');
        return HorarioProgramado::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds);
    }

    // Obtener administraciones de este tratamiento  
    public function administraciones()
    {
        $medicamentoTratamientoIds = $this->medicamentos()->pluck('medicamentos_tratamientos.id');
        return \App\Models\Administracion::whereIn('medicamento_tratamiento_id', $medicamentoTratamientoIds);
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

    // Scope para tratamientos programados (único tipo disponible)
    public function scopeProgramados($query)
    {
        return $query->where('tipo', self::TIPO_PROGRAMADO);
    }

    // Método para verificar si el tratamiento está activo
    public function isActivo()
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    // Método para verificar si es tratamiento programado (siempre true ahora)
    public function isProgramado()
    {
        return $this->tipo === self::TIPO_PROGRAMADO;
    }

    // Método para finalizar el tratamiento
    public function finalizar($motivo = null)
    {
        $this->update([
            'estado' => self::ESTADO_COMPLETADO,
            'fecha_fin' => now(),
            'observaciones' => $this->observaciones . ($motivo ? "\n\nMotivo de finalización: " . $motivo : '')
        ]);
    }

    // Método para pausar el tratamiento
    public function pausar($motivo = null)
    {
        $this->update([
            'estado' => self::ESTADO_PAUSADO,
            'observaciones' => $this->observaciones . ($motivo ? "\n\nMotivo de pausa: " . $motivo : '')
        ]);
    }

    // Método para reactivar el tratamiento
    public function activar()
    {
        $this->update([
            'estado' => self::ESTADO_ACTIVO
        ]);
    }
} 