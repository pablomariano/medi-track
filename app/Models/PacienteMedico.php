<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PacienteMedico extends Model
{
    use HasFactory;

    protected $table = 'paciente_medicos';

    protected $fillable = [
        'paciente_id',
        'medico_usuario_id',
        'es_medico_principal',
        'fecha_asignacion',
        'fecha_fin',
        'especialidad_tratamiento'
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'fecha_fin' => 'date',
        'es_medico_principal' => 'boolean'
    ];

    public $timestamps = false;

    // Clave primaria compuesta
    protected $primaryKey = ['paciente_id', 'medico_usuario_id'];
    public $incrementing = false;

    // Relación con el paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Relación con el médico
    public function medico()
    {
        return $this->belongsTo(PersonalMedico::class, 'medico_usuario_id', 'usuario_id');
    }

    // Scope para asignaciones principales
    public function scopePrincipales($query)
    {
        return $query->where('es_medico_principal', true);
    }

    // Scope para asignaciones vigentes (sin fecha fin o fecha fin futura)
    public function scopeVigentes($query)
    {
        return $query->where(function($q) {
            $q->whereNull('fecha_fin')
              ->orWhere('fecha_fin', '>', now());
        });
    }

    // Método para verificar si la asignación está vigente
    public function getEsVigenteAttribute()
    {
        if ($this->fecha_fin && $this->fecha_fin->isPast()) {
            return false;
        }

        return true;
    }

    // Método para obtener la duración de la asignación
    public function getDuracionAttribute()
    {
        $inicio = $this->fecha_asignacion;
        $fin = $this->fecha_fin ?: now();
        
        return $inicio->diffInDays($fin) + 1;
    }

    // Método para obtener días restantes si hay fecha fin
    public function getDiasRestantesAttribute()
    {
        if (!$this->fecha_fin) {
            return null; // Asignación indefinida
        }

        if ($this->fecha_fin->isPast()) {
            return 0; // Ya venció
        }

        return now()->diffInDays($this->fecha_fin);
    }

    // Método para finalizar asignación
    public function finalizar($fechaFin = null)
    {
        $this->update([
            'fecha_fin' => $fechaFin ?: now()->format('Y-m-d')
        ]);
    }

    // Método para obtener el estado de la asignación
    public function getEstadoAttribute()
    {
        if ($this->fecha_fin && $this->fecha_fin->isPast()) {
            return 'finalizada';
        }

        if ($this->es_medico_principal) {
            return 'principal';
        }

        return 'secundaria';
    }

    // Método para obtener el estado con colores
    public function getEstadoFormateadoAttribute()
    {
        $estados = [
            'principal' => ['texto' => 'Médico Principal', 'clase' => 'bg-blue-100 text-blue-800'],
            'secundaria' => ['texto' => 'Médico Secundario', 'clase' => 'bg-green-100 text-green-800'],
            'finalizada' => ['texto' => 'Finalizada', 'clase' => 'bg-gray-100 text-gray-800'],
        ];

        return $estados[$this->estado] ?? ['texto' => 'Desconocido', 'clase' => 'bg-gray-100 text-gray-800'];
    }
} 