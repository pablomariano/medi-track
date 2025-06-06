<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViaAdministracion extends Model
{
    use HasFactory;

    protected $table = 'vias_administracion';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'abreviatura',
        'descripcion'
    ];

    protected $casts = [
        'requiere_supervision' => 'boolean',
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

    public function scopeRequierenSupervision($query)
    {
        return $query->where('requiere_supervision', true);
    }
}
