<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormaFarmaceutica extends Model
{
    use HasFactory;

    protected $table = 'formas_farmaceuticas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo_forma',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'creado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    public function medicamentos(): HasMany
    {
        return $this->hasMany(Medicamento::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_forma', $tipo);
    }
}
