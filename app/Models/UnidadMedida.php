<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medida';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo',
        'equivalencia_base',
        'unidad_base_id'
    ];

    protected $casts = [
        'equivalencia_base' => 'decimal:6',
        'activo' => 'boolean'
    ];

    // Tipos de unidad
    const TIPO_PESO = 'peso';
    const TIPO_VOLUMEN = 'volumen';
    const TIPO_CANTIDAD = 'cantidad';
    const TIPO_CONCENTRACION = 'concentracion';

    public function medicamentosConcentracion(): HasMany
    {
        return $this->hasMany(Medicamento::class, 'unidad_concentracion_id');
    }

    public function unidadBase()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_base_id');
    }

    public function unidadesDerivadas(): HasMany
    {
        return $this->hasMany(UnidadMedida::class, 'unidad_base_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeUnidadesBase($query)
    {
        return $query->whereNull('unidad_base_id');
    }

    // Métodos de conversión
    public function convertirA($cantidad, UnidadMedida $unidadDestino)
    {
        if ($this->tipo !== $unidadDestino->tipo) {
            throw new \InvalidArgumentException('No se pueden convertir unidades de tipos diferentes');
        }

        // Si son la misma unidad
        if ($this->id === $unidadDestino->id) {
            return $cantidad;
        }

        // Convertir a unidad base
        $cantidadBase = $cantidad * $this->equivalencia_base;
        
        // Convertir de unidad base a destino
        return $cantidadBase / $unidadDestino->equivalencia_base;
    }
}
