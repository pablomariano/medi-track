<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'fecha_nacimiento',
        'genero_id',
        'numero_documento',
        'tipo_documento',
        'tipo_sangre',
        'altura',
        'direccion',
        'telefono_emergencia',
        'observaciones_medicas',
        'activo'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'altura' => 'decimal:2',
        'activo' => 'boolean',
        'created_at' => 'datetime'
    ];

    public $timestamps = false;

    // Relación con el usuario (opcional)
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con género
    public function genero()
    {
        return $this->belongsTo(Genero::class, 'genero_id');
    }

    // Relación con tratamientos
    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'paciente_id');
    }

    // Método para calcular la edad
    public function getEdadAttribute()
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }
        return $this->fecha_nacimiento->age;
    }

    // Método para obtener el nombre completo o del usuario
    public function getNombreCompletoAttribute()
    {
        return $this->nombre ?: ($this->user ? $this->user->name : 'Sin nombre');
    }

    // Método para formatear la altura
    public function getAlturaFormateadaAttribute()
    {
        return $this->altura ? $this->altura . ' cm' : '';
    }

    // Método para obtener el tipo de documento formateado
    public function getTipoDocumentoFormateadoAttribute()
    {
        $tipos = [
            'rut' => 'RUT',
            'ci' => 'Cédula de Identidad',
            'passport' => 'Pasaporte',
            'otro' => 'Otro'
        ];

        return $tipos[$this->tipo_documento] ?? ucfirst($this->tipo_documento ?? '');
    }

    // Método para obtener el tipo de sangre formateado
    public function getTipoSangreFormateadoAttribute()
    {
        return $this->tipo_sangre ? strtoupper($this->tipo_sangre) : '';
    }

    // Scope para pacientes activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para buscar por nombre o documento
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('numero_documento', 'like', "%{$termino}%");
        });
    }
} 