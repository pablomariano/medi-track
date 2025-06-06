<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormaFarmaceutica extends Model
{
    use HasFactory;

    protected $table = 'formas_farmaceuticas';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

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
        return $query->where('tipo', $tipo);
    }
}
