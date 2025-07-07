<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'medida',
        'unidad_medida',
        'descripcion',
        'principio_activo',
        'forma_farmaceutica',
        'via_administracion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación con tratamientos a través de la tabla pivot
    public function tratamientos()
    {
        return $this->belongsToMany(Tratamiento::class, 'medicamentos_tratamientos')
                    ->withPivot([
                        'dosis_cantidad',
                        'unidad_dosis',
                        'frecuencia_horas',
                        'tolerancia_antes_minutos',
                        'tolerancia_despues_minutos',
                        'duracion_dias',
                        'instrucciones_especiales',
                        'estado',
                        'activo',
                        'motivo_suspension',
                        'orden'
                    ])
                    ->withTimestamps();
    }

    // Relación con administraciones (a través de la tabla pivot medicamentos_tratamientos)
    public function administraciones()
    {
        return $this->hasManyThrough(
            Administracion::class,
            \App\Models\MedicamentoTratamiento::class,
            'medicamento_id', // Foreign key en medicamentos_tratamientos
            'medicamento_tratamiento_id', // Foreign key en administraciones
            'id', // Local key en medicamentos
            'id' // Local key en medicamentos_tratamientos
        );
    }

    // Scope para medicamentos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }



    // Método para obtener el nombre completo con medida
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->medida} {$this->unidad_medida}";
    }
} 