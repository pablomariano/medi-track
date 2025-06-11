<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintomaPrn extends Model
{
    use HasFactory;

    protected $table = 'sintomas_prn';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'severidad_minima',
        'severidad_maxima'
    ];

    protected $casts = [
        'severidad_minima' => 'integer',
        'severidad_maxima' => 'integer'
    ];

    // Relación con criterios PRN
    public function criterios()
    {
        return $this->hasMany(CriterioPrn::class, 'sintoma_id');
    }

    // Relación con indicaciones PRN
    public function indicaciones()
    {
        return $this->hasMany(IndicacionPrn::class, 'sintoma_id');
    }

    // Scope por categoría
    public function scopeCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    // Método para verificar si una severidad está en el rango
    public function esSeveridadValida($severidad)
    {
        return $severidad >= $this->severidad_minima && $severidad <= $this->severidad_maxima;
    }
} 