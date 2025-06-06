<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicamento extends Model
{
    use HasFactory;

    protected $table = 'medicamentos';

    protected $fillable = [
        'principio_activo_id',
        'nombre_comercial',
        'forma_farmaceutica_id',
        'concentracion',
        'unidad_concentracion_id',
        'via_administracion_id',
        'laboratorio',
        'registro_sanitario',
        'lote',
        'fecha_vencimiento',
        'precio_unitario',
        'requiere_receta',
        'controlado',
        'activo',
        'observaciones'
    ];

    protected $casts = [
        'concentracion' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'requiere_receta' => 'boolean',
        'controlado' => 'boolean',
        'activo' => 'boolean',
        'creado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    // Relaciones hacia catálogos
    public function principioActivo(): BelongsTo
    {
        return $this->belongsTo(PrincipioActivo::class);
    }

    public function formaFarmaceutica(): BelongsTo
    {
        return $this->belongsTo(FormaFarmaceutica::class);
    }

    public function unidadConcentracion(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_concentracion_id');
    }

    public function viaAdministracion(): BelongsTo
    {
        return $this->belongsTo(ViaAdministracion::class);
    }

    // Relaciones hacia tratamientos
    public function medicamentoTratamientos(): HasMany
    {
        return $this->hasMany(MedicamentoTratamiento::class);
    }

    public function tratamientos()
    {
        return $this->belongsToMany(Tratamiento::class, 'medicamentos_tratamientos')
                    ->withPivot(['tipo_esquema', 'dosis_cantidad', 'frecuencia_horas', 'activo'])
                    ->withTimestamps();
    }

    // Scopes útiles
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeRequiereReceta($query)
    {
        return $query->where('requiere_receta', true);
    }

    public function scopeControlados($query)
    {
        return $query->where('controlado', true);
    }

    public function scopePorLaboratorio($query, $laboratorio)
    {
        return $query->where('laboratorio', 'like', "%{$laboratorio}%");
    }

    public function scopeProximosAVencer($query, $dias = 30)
    {
        return $query->where('fecha_vencimiento', '<=', now()->addDays($dias))
                    ->where('fecha_vencimiento', '>=', now());
    }

    // Métodos útiles
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre_comercial} ({$this->principioActivo->nombre_generico} {$this->concentracion}{$this->unidadConcentracion->nombre})";
    }

    public function estaVencido()
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento < now();
    }

    public function diasParaVencer()
    {
        return $this->fecha_vencimiento ? now()->diffInDays($this->fecha_vencimiento, false) : null;
    }
}
