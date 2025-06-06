<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrincipioActivo extends Model
{
    use HasFactory;

    protected $table = 'principios_activos';

    protected $fillable = [
        'nombre_generico',
        'nombre_comercial',
        'clasificacion_atc',
        'grupo_farmacologico',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'creado_en' => 'datetime'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    // Relaciones
    public function medicamentos(): HasMany
    {
        return $this->hasMany(Medicamento::class);
    }

    public function interaccionesComoA1(): HasMany
    {
        return $this->hasMany(InteraccionMedicamento::class, 'principio_activo_1_id');
    }

    public function interaccionesComoA2(): HasMany
    {
        return $this->hasMany(InteraccionMedicamento::class, 'principio_activo_2_id');
    }

    // Métodos útiles
    public function todasLasInteracciones()
    {
        return $this->interaccionesComoA1->merge($this->interaccionesComoA2);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorGrupoFarmacologico($query, $grupo)
    {
        return $query->where('grupo_farmacologico', $grupo);
    }
}
