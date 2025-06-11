<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriterioPrn extends Model
{
    use HasFactory;

    protected $table = 'criterios_prn';

    protected $fillable = [
        'sintoma_id',
        'severidad_requerida',
        'tiempo_desde_ultima_dosis',
        'dosis_maxima_diaria',
        'condiciones_adicionales',
        'contraindicaciones'
    ];

    protected $casts = [
        'severidad_requerida' => 'integer',
        'tiempo_desde_ultima_dosis' => 'integer',
        'dosis_maxima_diaria' => 'integer'
    ];

    // Relación con síntoma PRN
    public function sintoma()
    {
        return $this->belongsTo(SintomaPrn::class, 'sintoma_id');
    }

    // Relación con indicaciones PRN
    public function indicaciones()
    {
        return $this->hasMany(IndicacionPrn::class, 'criterio_id');
    }

    // Método para verificar si se puede administrar según el criterio
    public function puedeAdministrar($severidadActual, $horasDesdeUltimaDosis, $dosisHoy)
    {
        // Verificar severidad
        if ($severidadActual < $this->severidad_requerida) {
            return false;
        }

        // Verificar tiempo desde última dosis
        if ($horasDesdeUltimaDosis < $this->tiempo_desde_ultima_dosis) {
            return false;
        }

        // Verificar dosis máxima diaria
        if ($dosisHoy >= $this->dosis_maxima_diaria) {
            return false;
        }

        return true;
    }
} 