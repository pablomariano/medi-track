<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicamentoTratamiento extends Model
{
    use HasFactory;

    protected $table = 'medicamentos_tratamientos';

    protected $fillable = [
        'tratamiento_id',
        'medicamento_id',
        'tipo_esquema',
        'dosis_cantidad',
        'unidad_dosis_id',
        'frecuencia_horas',
        'dosis_diaria_total',
        'duracion_dias',
        'fecha_inicio',
        'fecha_fin',
        'indicaciones_uso',
        'activo',
        'motivo_suspension',
        'orden_prescripcion'
    ];

    protected $casts = [
        'dosis_cantidad' => 'decimal:3',
        'dosis_diaria_total' => 'decimal:3',
        'frecuencia_horas' => 'integer',
        'duracion_dias' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
        'orden_prescripcion' => 'integer',
        'creado_en' => 'datetime',
        'modificado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'modificado_en';

    // Tipos de esquema
    const ESQUEMA_REGULAR = 'regular';
    const ESQUEMA_PRN = 'prn';
    const ESQUEMA_UNICA_DOSIS = 'unica_dosis';

    // Relaciones principales
    public function tratamiento(): BelongsTo
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function unidadDosis(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_dosis_id');
    }

    // Relaciones de gestión
    public function esquemasPosologicos(): HasMany
    {
        return $this->hasMany(EsquemaPosologico::class);
    }

    public function dosisPrn(): HasMany
    {
        return $this->hasMany(DosisPrn::class);
    }

    public function administraciones(): HasMany
    {
        return $this->hasMany(AdministracionMedicamento::class);
    }

    // Scopes útiles
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipoEsquema($query, $tipo)
    {
        return $query->where('tipo_esquema', $tipo);
    }

    public function scopeVigentes($query)
    {
        return $query->where('activo', true)
                    ->where(function($q) {
                        $q->whereNull('fecha_fin')
                          ->orWhere('fecha_fin', '>=', now());
                    });
    }

    public function scopeOrdenadosPorPrescripcion($query)
    {
        return $query->orderBy('orden_prescripcion');
    }

    // Métodos útiles
    public function esEsquemaRegular(): bool
    {
        return $this->tipo_esquema === self::ESQUEMA_REGULAR;
    }

    public function esEsquemaPrn(): bool
    {
        return $this->tipo_esquema === self::ESQUEMA_PRN;
    }

    public function getDosisFormateadaAttribute(): string
    {
        return "{$this->dosis_cantidad} {$this->unidadDosis->simbolo}";
    }

    public function getFrecuenciaFormateadaAttribute(): string
    {
        if ($this->esEsquemaPrn()) {
            return 'Según necesidad (PRN)';
        }

        if ($this->frecuencia_horas == 24) {
            return 'Una vez al día';
        }

        $vecesAlDia = 24 / $this->frecuencia_horas;
        return "Cada {$this->frecuencia_horas} horas ({$vecesAlDia} veces al día)";
    }

    public function calcularProximaDosis()
    {
        if (!$this->esEsquemaRegular()) {
            return null;
        }

        $ultimaAdministracion = $this->administraciones()
            ->orderBy('fecha_hora_programada', 'desc')
            ->first();

        if (!$ultimaAdministracion) {
            return $this->fecha_inicio ?? now();
        }

        return $ultimaAdministracion->fecha_hora_programada
            ->addHours($this->frecuencia_horas);
    }

    public function puedeAdministrarse(): bool
    {
        if (!$this->activo) return false;
        
        if ($this->fecha_fin && $this->fecha_fin < now()) return false;

        if ($this->esEsquemaPrn()) return true;

        $proximaDosis = $this->calcularProximaDosis();
        return $proximaDosis && $proximaDosis <= now();
    }

    public function getDosisRestantesAttribute(): ?int
    {
        if (!$this->duracion_dias || !$this->frecuencia_horas) return null;

        $dosisTotal = ($this->duracion_dias * 24) / $this->frecuencia_horas;
        $dosisAdministradas = $this->administraciones()
            ->where('estado', 'administrado')
            ->count();

        return max(0, $dosisTotal - $dosisAdministradas);
    }

    public function suspender($motivo, $usuario = null)
    {
        $this->update([
            'activo' => false,
            'motivo_suspension' => $motivo
        ]);

        // Registrar en historial del tratamiento
        $this->tratamiento->historial()->create([
            'usuario_id' => $usuario ?: auth()->id(),
            'accion' => 'Medicamento suspendido',
            'campo_modificado' => 'medicamento_tratamiento_id',
            'valor_anterior' => 'activo',
            'valor_nuevo' => 'suspendido',
            'motivo' => $motivo
        ]);
    }
}
