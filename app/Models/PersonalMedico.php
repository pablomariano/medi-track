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

    // Relación con pacientes asignados
    public function pacientes()
    {
        return $this->belongsToMany(
            Paciente::class,
            'paciente_medicos',
            'medico_usuario_id',
            'paciente_id',
            'usuario_id',
            'id'
        )->withPivot('es_medico_principal', 'fecha_asignacion', 'fecha_fin', 'especialidad_tratamiento');
    }

    // Relación con pacientes vigentes (sin fecha fin o fecha fin futura)
    public function pacientesVigentes()
    {
        return $this->pacientes()
            ->where(function($query) {
                $query->whereNull('paciente_medicos.fecha_fin')
                      ->orWhere('paciente_medicos.fecha_fin', '>', now());
            });
    }

    // Relación con pacientes como médico principal
    public function pacientesPrincipales()
    {
        return $this->pacientes()
            ->wherePivot('es_medico_principal', true)
            ->where(function($query) {
                $query->whereNull('paciente_medicos.fecha_fin')
                      ->orWhere('paciente_medicos.fecha_fin', '>', now());
            });
    }

    // Relación directa con asignaciones de pacientes
    public function asignacionesPacientes()
    {
        return $this->hasMany(PacienteMedico::class, 'medico_usuario_id', 'usuario_id');
    }

    // Relación con tratamientos creados
    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'medico_usuario_id', 'usuario_id');
    }
} 