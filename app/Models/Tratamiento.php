<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';

    protected $fillable = [
        'paciente_id',
        'medico_usuario_id',
        'nombre',
        'diagnostico',
        'objetivo_terapeutico',
        'estado',
        'fecha_inicio',
        'fecha_fin_estimada',
        'fecha_fin_real',
        'medico_prescriptor',
        'institucion',
        'observaciones'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_fin_real' => 'date',
        'creado_en' => 'datetime',
        'modificado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'modificado_en';

    // Estados posibles
    const ESTADO_ACTIVO = 'Activo';
    const ESTADO_PAUSADO = 'Pausado';
    const ESTADO_COMPLETADO = 'Completado';
    const ESTADO_SUSPENDIDO = 'Suspendido';
    const ESTADO_MODIFICADO = 'Modificado';

    // Relaciones principales
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(PersonalMedico::class, 'medico_usuario_id', 'usuario_id');
    }

    // Relaciones con medicamentos
    public function medicamentoTratamientos(): HasMany
    {
        return $this->hasMany(MedicamentoTratamiento::class);
    }

    public function medicamentos()
    {
        return $this->belongsToMany(Medicamento::class, 'medicamentos_tratamientos')
                    ->withPivot([
                        'tipo_esquema', 'dosis_cantidad', 'unidad_dosis_id',
                        'frecuencia_horas', 'dosis_diaria_total', 'duracion_dias',
                        'fecha_inicio', 'fecha_fin', 'indicaciones_uso',
                        'activo', 'motivo_suspension', 'orden_prescripcion'
                    ]);
    }

    // Relaciones de gestión
    public function administraciones(): HasMany
    {
        return $this->hasManyThrough(
            AdministracionMedicamento::class,
            MedicamentoTratamiento::class
        );
    }

    public function autorizaciones(): HasMany
    {
        return $this->hasMany(AutorizacionTratamiento::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(AlertaMedicamento::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialTratamiento::class);
    }

    // Scopes útiles
    public function scopeActivos($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    public function scopePorPaciente($query, $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopePorMedico($query, $medicoId)
    {
        return $query->where('medico_usuario_id', $medicoId);
    }

    public function scopeVigentes($query)
    {
        return $query->whereIn('estado', [self::ESTADO_ACTIVO, self::ESTADO_PAUSADO])
                    ->where(function($q) {
                        $q->whereNull('fecha_fin_estimada')
                          ->orWhere('fecha_fin_estimada', '>=', now());
                    });
    }

    // Métodos útiles
    public function estaActivo(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    public function puedeSerModificado(): bool
    {
        return in_array($this->estado, [self::ESTADO_ACTIVO, self::ESTADO_PAUSADO]);
    }

    public function getDuracionDiasAttribute(): ?int
    {
        if (!$this->fecha_fin_estimada) return null;
        return $this->fecha_inicio->diffInDays($this->fecha_fin_estimada);
    }

    public function getPorcentajeCompletadoAttribute(): ?float
    {
        if (!$this->fecha_fin_estimada) return null;
        
        $totalDias = $this->fecha_inicio->diffInDays($this->fecha_fin_estimada);
        $diasTranscurridos = $this->fecha_inicio->diffInDays(now());
        
        return min(100, ($diasTranscurridos / $totalDias) * 100);
    }

    // Métodos de acción
    public function pausar($motivo = null, $usuario = null)
    {
        $this->update(['estado' => self::ESTADO_PAUSADO]);
        $this->registrarCambio('Pausado', null, null, $motivo, $usuario);
    }

    public function reanudar($usuario = null)
    {
        $this->update(['estado' => self::ESTADO_ACTIVO]);
        $this->registrarCambio('Reanudado', null, null, null, $usuario);
    }

    public function completar($usuario = null)
    {
        $this->update([
            'estado' => self::ESTADO_COMPLETADO,
            'fecha_fin_real' => now()
        ]);
        $this->registrarCambio('Finalizado', null, null, null, $usuario);
    }

    private function registrarCambio($accion, $campo = null, $valorAnterior = null, $motivo = null, $usuario = null)
    {
        $this->historial()->create([
            'usuario_id' => $usuario ?: auth()->id(),
            'accion' => $accion,
            'campo_modificado' => $campo,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $this->getAttributeValue($campo),
            'motivo' => $motivo
        ]);
    }
}
