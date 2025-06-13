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
        'concentracion',
        'unidad_concentracion',
        'forma_farmaceutica',
        'via_administracion',
        'presentacion',
        'unidades_por_presentacion',
        'requiere_receta',
        'contraindicaciones',
        'efectos_secundarios',
        'interacciones',
        'categoria_terapeutica',
        'laboratorio',
        'codigo_barras',
        'registro_sanitario',
        'activo'
    ];

    protected $casts = [
        'requiere_receta' => 'boolean',
        'activo' => 'boolean',
        'unidades_por_presentacion' => 'integer'
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
                        'intervalo_minimo_horas',
                        'dosis_maxima_dia',
                        'dosis_maxima_semana',
                        'dosis_maxima_consecutiva',
                        'instrucciones_especiales',
                        'estado',
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

    // Scope para medicamentos que requieren receta
    public function scopeConReceta($query)
    {
        return $query->where('requiere_receta', true);
    }

    // Método para obtener el nombre completo con concentración
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->concentracion} {$this->unidad_concentracion}";
    }
} 