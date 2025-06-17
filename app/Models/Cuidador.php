<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuidador extends Model
{
    use HasFactory;

    protected $table = 'cuidadores';
    
    protected $primaryKey = 'usuario_id';
    public $incrementing = false;

    protected $fillable = [
        'usuario_id',
        'certificaciones',
        'experiencia_anos',
        'disponibilidad_horaria',
        'tarifa_hora'
    ];

    protected $casts = [
        'experiencia_anos' => 'integer',
        'tarifa_hora' => 'integer',
    ];

    public $timestamps = false;

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Método para obtener el nombre del cuidador
    public function getNombreAttribute()
    {
        return $this->user ? $this->user->name : 'Usuario no encontrado';
    }

    // Método para obtener el email del cuidador
    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : '';
    }

    // Método para formatear la tarifa en peso chileno
    public function getTarifaFormateadaAttribute()
    {
        return $this->tarifa_hora ? '$' . number_format($this->tarifa_hora, 0, ',', '.') : '';
    }

    // Relación con pacientes asignados
    public function pacientes()
    {
        return $this->belongsToMany(
            Paciente::class,
            'paciente_cuidadores',
            'cuidador_usuario_id',
            'paciente_id',
            'usuario_id',
            'id'
        )->withPivot('fecha_asignacion', 'fecha_fin', 'activo');
    }

    // Relación con pacientes activos
    public function pacientesActivos()
    {
        return $this->pacientes()->wherePivot('activo', true);
    }

    // Relación con pacientes vigentes (activos y sin fecha fin vencida)
    public function pacientesVigentes()
    {
        return $this->pacientes()
            ->wherePivot('activo', true)
            ->where(function($query) {
                $query->whereNull('paciente_cuidadores.fecha_fin')
                      ->orWhere('paciente_cuidadores.fecha_fin', '>', now());
            });
    }

    // Relación directa con asignaciones de pacientes
    public function asignacionesPacientes()
    {
        return $this->hasMany(PacienteCuidador::class, 'cuidador_usuario_id', 'usuario_id');
    }
} 