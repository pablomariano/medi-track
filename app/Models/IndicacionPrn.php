<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicacionPrn extends Model
{
    use HasFactory;

    protected $table = 'indicaciones_prn';

    protected $fillable = [
        'tratamiento_id',
        'sintoma_id',
        'criterio_id',
        'medicamento_id',
        'dosis',
        'unidad_dosis',
        'via_administracion',
        'intervalo_minimo_horas',
        'dosis_maxima_24h',
        'instrucciones_administracion',
        'observaciones_importantes',
        'activo'
    ];

    protected $casts = [
        'intervalo_minimo_horas' => 'integer',
        'dosis_maxima_24h' => 'integer',
        'activo' => 'boolean'
    ];

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function sintoma()
    {
        return $this->belongsTo(SintomaPrn::class, 'sintoma_id');
    }

    public function criterio()
    {
        return $this->belongsTo(CriterioPrn::class, 'criterio_id');
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
} 