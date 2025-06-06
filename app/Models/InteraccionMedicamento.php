<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InteraccionMedicamento extends Model
{
    use HasFactory;

    protected $table = 'interacciones_medicamentos';
    public $timestamps = false;

    protected $fillable = [
        'principio_activo_1_id',
        'principio_activo_2_id',
        'tipo_interaccion',
        'efecto_clinico',
        'severidad',
        'mecanismo',
        'recomendacion',
        'fuente',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function principioActivo1(): BelongsTo
    {
        return $this->belongsTo(PrincipioActivo::class, 'principio_activo_1_id');
    }

    public function principioActivo2(): BelongsTo
    {
        return $this->belongsTo(PrincipioActivo::class, 'principio_activo_2_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorSeveridad($query, $severidad)
    {
        return $query->where('severidad', $severidad);
    }
}
