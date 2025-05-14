<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $table = 'medicamentos';

    protected $fillable = [
        'nombre',
        'medida',
        'unidad_medida',
        'descripcion'
    ];
} 