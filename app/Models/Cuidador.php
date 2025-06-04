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
} 