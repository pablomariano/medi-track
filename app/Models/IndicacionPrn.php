<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicacionPrn extends Model
{
    use HasFactory;

    protected $table = 'indicaciones_prn';

    protected $fillable = [
        'medicamento_tratamiento_id',
        'sintoma_id',
        'criterio_id',
        'descripcion_personalizada',
        'es_criterio_principal'
    ];

    protected $casts = [
        'es_criterio_principal' => 'boolean'
    ];

    // Relación con medicamento_tratamiento (pivot table)
    public function medicamentoTratamiento()
    {
        return $this->belongsTo(\App\Models\MedicamentoTratamiento::class, 'medicamento_tratamiento_id');
    }

    // Accessor para obtener el tratamiento a través de la relación pivot
    public function getTratamientoAttribute()
    {
        return $this->medicamentoTratamiento?->tratamiento;
    }

    // Accessor para obtener el medicamento a través de la relación pivot
    public function getMedicamentoAttribute()
    {
        return $this->medicamentoTratamiento?->medicamento;
    }

    public function sintoma()
    {
        return $this->belongsTo(SintomaPrn::class, 'sintoma_id');
    }

    public function criterio()
    {
        return $this->belongsTo(CriterioPrn::class, 'criterio_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('es_criterio_principal', true);
    }
} 