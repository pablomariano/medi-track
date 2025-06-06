<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EsquemaPosologico extends Model
{
    use HasFactory;

    protected $table = 'esquemas_posologicos';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'intervalo_horas',
        'dosis_por_dia',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
