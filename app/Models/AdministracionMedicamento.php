<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdministracionMedicamento extends Model
{
    use HasFactory;

    protected $table = 'administraciones_medicamentos';

    protected $fillable = [
        'medicamento_tratamiento_id',
        'fecha_hora_programada',
        'fecha_hora_real',
        'dosis_administrada',
        'unidad_dosis_id',
        'estado',
        'cuidador_usuario_id',
        'metodo_confirmacion',
        'observaciones',
        'efectos_adversos',
        'motivo_no_administracion'
    ];

    protected $casts = [
        'fecha_hora_programada' => 'datetime',
        'fecha_hora_real' => 'datetime',
        'dosis_administrada' => 'decimal:3',
        'creado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    // Estados posibles
    const ESTADO_PROGRAMADO = 'programado';
    const ESTADO_ADMINISTRADO = 'administrado';
    const ESTADO_OMITIDO = 'omitido';
    const ESTADO_RECHAZADO = 'rechazado_paciente';
    const ESTADO_NO_DISPONIBLE = 'medicamento_no_disponible';

    // Métodos de confirmación
    const CONFIRMACION_VISUAL = 'confirmacion_visual';
    const CONFIRMACION_CODIGO_QR = 'codigo_qr';
    const CONFIRMACION_FIRMA_DIGITAL = 'firma_digital';
    const CONFIRMACION_BIOMETRICA = 'biometrica';

    // Relaciones principales
    public function medicamentoTratamiento(): BelongsTo
    {
        return $this->belongsTo(MedicamentoTratamiento::class);
    }

    public function cuidador(): BelongsTo
    {
        return $this->belongsTo(Cuidador::class, 'cuidador_usuario_id', 'usuario_id');
    }

    public function unidadDosis(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_dosis_id');
    }

    // Relaciones a través de medicamento_tratamiento
    public function tratamiento()
    {
        return $this->hasOneThrough(
            Tratamiento::class,
            MedicamentoTratamiento::class,
            'id',
            'id',
            'medicamento_tratamiento_id',
            'tratamiento_id'
        );
    }

    public function medicamento()
    {
        return $this->hasOneThrough(
            Medicamento::class,
            MedicamentoTratamiento::class,
            'id',
            'id',
            'medicamento_tratamiento_id',
            'medicamento_id'
        );
    }

    public function paciente()
    {
        return $this->hasOneThrough(
            Paciente::class,
            Tratamiento::class,
            'id',
            'id',
            'tratamiento_id',
            'paciente_id'
        )->through('medicamentoTratamiento');
    }

    // Scopes útiles
    public function scopeProgramadas($query)
    {
        return $query->where('estado', self::ESTADO_PROGRAMADO);
    }

    public function scopeAdministradas($query)
    {
        return $query->where('estado', self::ESTADO_ADMINISTRADO);
    }

    public function scopeOmitidas($query)
    {
        return $query->whereIn('estado', [
            self::ESTADO_OMITIDO,
            self::ESTADO_RECHAZADO,
            self::ESTADO_NO_DISPONIBLE
        ]);
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', self::ESTADO_PROGRAMADO)
                    ->where('fecha_hora_programada', '<', now()->subHours(2));
    }

    public function scopePendientesHoy($query)
    {
        return $query->where('estado', self::ESTADO_PROGRAMADO)
                    ->whereBetween('fecha_hora_programada', [
                        now()->startOfDay(),
                        now()->endOfDay()
                    ]);
    }

    public function scopePorPaciente($query, $pacienteId)
    {
        return $query->whereHas('medicamentoTratamiento.tratamiento', function($q) use ($pacienteId) {
            $q->where('paciente_id', $pacienteId);
        });
    }

    public function scopePorCuidador($query, $cuidadorId)
    {
        return $query->where('cuidador_usuario_id', $cuidadorId);
    }

    // Métodos útiles
    public function estaVencida(): bool
    {
        return $this->estado === self::ESTADO_PROGRAMADO &&
               $this->fecha_hora_programada < now()->subHours(2);
    }

    public function puedeAdministrarse(): bool
    {
        return $this->estado === self::ESTADO_PROGRAMADO &&
               $this->fecha_hora_programada <= now()->addMinutes(30);
    }

    public function esAdministracionTardia(): bool
    {
        return $this->estado === self::ESTADO_ADMINISTRADO &&
               $this->fecha_hora_real > $this->fecha_hora_programada->addMinutes(30);
    }

    public function getRetrasoMinutosAttribute(): ?int
    {
        if (!$this->fecha_hora_real || $this->estado !== self::ESTADO_ADMINISTRADO) {
            return null;
        }

        return $this->fecha_hora_programada->diffInMinutes($this->fecha_hora_real, false);
    }

    public function getDosisFormateadaAttribute(): string
    {
        return "{$this->dosis_administrada} {$this->unidadDosis->simbolo}";
    }

    // Métodos de acción
    public function administrar($cuidadorId, $dosisAdministrada = null, $observaciones = null, $metodoConfirmacion = null)
    {
        $this->update([
            'estado' => self::ESTADO_ADMINISTRADO,
            'fecha_hora_real' => now(),
            'cuidador_usuario_id' => $cuidadorId,
            'dosis_administrada' => $dosisAdministrada ?? $this->medicamentoTratamiento->dosis_cantidad,
            'observaciones' => $observaciones,
            'metodo_confirmacion' => $metodoConfirmacion ?? self::CONFIRMACION_VISUAL
        ]);

        // Programar siguiente dosis si es esquema regular
        if ($this->medicamentoTratamiento->esEsquemaRegular()) {
            $this->programarSiguienteDosis();
        }

        return $this;
    }

    public function omitir($motivo, $cuidadorId = null)
    {
        $this->update([
            'estado' => self::ESTADO_OMITIDO,
            'cuidador_usuario_id' => $cuidadorId,
            'motivo_no_administracion' => $motivo
        ]);

        return $this;
    }

    public function marcarRechazada($motivo, $cuidadorId = null)
    {
        $this->update([
            'estado' => self::ESTADO_RECHAZADO,
            'cuidador_usuario_id' => $cuidadorId,
            'motivo_no_administracion' => $motivo
        ]);

        return $this;
    }

    private function programarSiguienteDosis()
    {
        $siguienteFecha = $this->fecha_hora_programada
            ->addHours($this->medicamentoTratamiento->frecuencia_horas);

        // Solo programar si no excede la fecha fin del tratamiento
        if (!$this->medicamentoTratamiento->fecha_fin || 
            $siguienteFecha <= $this->medicamentoTratamiento->fecha_fin) {
            
            self::create([
                'medicamento_tratamiento_id' => $this->medicamento_tratamiento_id,
                'fecha_hora_programada' => $siguienteFecha,
                'estado' => self::ESTADO_PROGRAMADO
            ]);
        }
    }
}
