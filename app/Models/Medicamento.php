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
        'nombre_comercial',
        'principio_activo_id',
        'forma_farmaceutica_id',
        'via_administracion_id',
        'unidad_concentracion_id',
        'concentracion',
        'codigo_barras',
        'lote',
        'fecha_vencimiento',
        'precio_unitario',
        'stock_actual',
        'stock_minimo',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'concentracion' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'fecha_vencimiento' => 'date',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

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

    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock_actual <= stock_minimo');
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<', now());
    }

    public function scopeProximosAVencer($query, $dias = 30)
    {
        return $query->where('fecha_vencimiento', '<=', now()->addDays($dias))
                    ->where('fecha_vencimiento', '>=', now());
    }

    public function scopePorPrincipioActivo($query, $principioActivoId)
    {
        return $query->where('principio_activo_id', $principioActivoId);
    }

    public function scopePorFormaFarmaceutica($query, $formaId)
    {
        return $query->where('forma_farmaceutica_id', $formaId);
    }

    public function scopePorViaAdministracion($query, $viaId)
    {
        return $query->where('via_administracion_id', $viaId);
    }

    // Métodos útiles
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre_comercial} ({$this->principioActivo->nombre_generico} {$this->concentracion}{$this->unidadConcentracion->simbolo})";
    }

    public function estaVencido()
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento < now();
    }

    public function diasParaVencer()
    {
        return $this->fecha_vencimiento ? now()->diffInDays($this->fecha_vencimiento, false) : null;
    }

    public function tieneStockBajo()
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function proximoAVencer($dias = 90)
    {
        if (!$this->fecha_vencimiento) return false;
        
        $fechaLimite = now()->addDays($dias);
        return $this->fecha_vencimiento <= $fechaLimite && $this->fecha_vencimiento >= now();
    }
}
