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

    // Relaciones con sistema de medicamentos
    public function administracionesMedicamentos()
    {
        return $this->hasMany(AdministracionMedicamento::class, 'cuidador_usuario_id', 'usuario_id');
    }

    public function administracionesHoy()
    {
        return $this->hasMany(AdministracionMedicamento::class, 'cuidador_usuario_id', 'usuario_id')
                   ->whereBetween('fecha_hora_programada', [
                       now()->startOfDay(),
                       now()->endOfDay()
                   ]);
    }

    public function administracionesPendientes()
    {
        return $this->hasMany(AdministracionMedicamento::class, 'cuidador_usuario_id', 'usuario_id')
                   ->where('estado', AdministracionMedicamento::ESTADO_PROGRAMADO)
                   ->where('fecha_hora_programada', '<=', now()->addHours(2));
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
} 