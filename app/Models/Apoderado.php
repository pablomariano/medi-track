<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apoderado extends Model
{
    use HasFactory;

    protected $table = 'apoderados';
    
    protected $primaryKey = 'usuario_id';
    public $incrementing = false;

    protected $fillable = [
        'usuario_id',
        'relacion_paciente',
        'es_contacto_emergencia'
    ];

    protected $casts = [
        'es_contacto_emergencia' => 'boolean',
    ];

    public $timestamps = false;

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relaciones con sistema de medicamentos
    public function autorizacionesTratamiento()
    {
        return $this->hasMany(AutorizacionTratamiento::class, 'apoderado_usuario_id', 'usuario_id');
    }

    public function autorizacionesPendientes()
    {
        return $this->hasMany(AutorizacionTratamiento::class, 'apoderado_usuario_id', 'usuario_id')
                   ->where('estado', AutorizacionTratamiento::ESTADO_PENDIENTE);
    }

    public function tratamientosBajoCuidado()
    {
        return $this->hasManyThrough(
            Tratamiento::class,
            AutorizacionTratamiento::class,
            'apoderado_usuario_id',
            'id',
            'usuario_id',
            'tratamiento_id'
        )->distinct();
    }

    // Método para obtener el nombre del apoderado
    public function getNombreAttribute()
    {
        return $this->user ? $this->user->name : 'Usuario no encontrado';
    }

    // Método para obtener el email del apoderado
    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : '';
    }

    // Método para obtener el texto de la relación
    public function getRelacionFormateadaAttribute()
    {
        $relaciones = [
            'padre' => 'Padre',
            'madre' => 'Madre',
            'hermano' => 'Hermano/a',
            'abuelo' => 'Abuelo/a',
            'tutor' => 'Tutor Legal',
            'otro' => 'Otro'
        ];

        return $relaciones[$this->relacion_paciente] ?? ucfirst($this->relacion_paciente ?? '');
    }
} 