<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalMedico extends Model
{
    use HasFactory;

    protected $table = 'personal_medico';
    
    protected $primaryKey = 'usuario_id';
    public $incrementing = false;

    protected $fillable = [
        'usuario_id',
        'especialidad',
        'numero_colegiatura',
        'institucion',
        'anos_experiencia'
    ];

    protected $casts = [
        'anos_experiencia' => 'integer',
    ];

    public $timestamps = false;

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relaciones con sistema de medicamentos
    public function tratamientosPrescritos()
    {
        return $this->hasMany(Tratamiento::class, 'medico_usuario_id', 'usuario_id');
    }

    public function tratamientosActivos()
    {
        return $this->hasMany(Tratamiento::class, 'medico_usuario_id', 'usuario_id')
                   ->where('estado', Tratamiento::ESTADO_ACTIVO);
    }

    public function pacientesEnTratamiento()
    {
        return $this->hasManyThrough(
            Paciente::class,
            Tratamiento::class,
            'medico_usuario_id',
            'id',
            'usuario_id',
            'paciente_id'
        )->distinct();
    }

    // Método para obtener el nombre del médico
    public function getNombreAttribute()
    {
        return $this->user ? $this->user->name : 'Usuario no encontrado';
    }

    // Método para obtener el email del médico
    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : '';
    }
} 