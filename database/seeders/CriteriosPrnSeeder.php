<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriosPrnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primero obtenemos los IDs de los síntomas para referenciarlos
        $sintomas = DB::table('sintomas_prn')->get()->keyBy('nombre');
        
        $criterios = [
            // Criterios para Fiebre
            [
                'sintoma' => 'Fiebre',
                'descripcion' => 'Temperatura mayor a 38°C',
                'valor_minimo' => '38.0',
                'valor_maximo' => null,
                'unidad' => '°C'
            ],
            [
                'sintoma' => 'Fiebre',
                'descripcion' => 'Temperatura mayor a 38.5°C',
                'valor_minimo' => '38.5',
                'valor_maximo' => null,
                'unidad' => '°C'
            ],
            [
                'sintoma' => 'Fiebre',
                'descripcion' => 'Fiebre alta mayor a 39°C',
                'valor_minimo' => '39.0',
                'valor_maximo' => null,
                'unidad' => '°C'
            ],
            
            // Criterios para Dolor (escala 1-10)
            [
                'sintoma' => 'Dolor de cabeza',
                'descripcion' => 'Dolor moderado (escala 5-7)',
                'valor_minimo' => '5',
                'valor_maximo' => '7',
                'unidad' => '/10'
            ],
            [
                'sintoma' => 'Dolor de cabeza',
                'descripcion' => 'Dolor severo (escala 8-10)',
                'valor_minimo' => '8',
                'valor_maximo' => '10',
                'unidad' => '/10'
            ],
            [
                'sintoma' => 'Dolor muscular',
                'descripcion' => 'Dolor moderado a severo (>5)',
                'valor_minimo' => '5',
                'valor_maximo' => null,
                'unidad' => '/10'
            ],
            [
                'sintoma' => 'Dolor articular',
                'descripcion' => 'Dolor que interfiere con actividades',
                'valor_minimo' => '4',
                'valor_maximo' => null,
                'unidad' => '/10'
            ],
            [
                'sintoma' => 'Dolor abdominal',
                'descripcion' => 'Dolor moderado a severo',
                'valor_minimo' => '5',
                'valor_maximo' => null,
                'unidad' => '/10'
            ],
            
            // Criterios para Presión Arterial
            [
                'sintoma' => 'Hipertensión',
                'descripcion' => 'Presión sistólica >140 mmHg',
                'valor_minimo' => '140',
                'valor_maximo' => null,
                'unidad' => 'mmHg'
            ],
            [
                'sintoma' => 'Hipertensión',
                'descripcion' => 'Crisis hipertensiva >180 mmHg',
                'valor_minimo' => '180',
                'valor_maximo' => null,
                'unidad' => 'mmHg'
            ],
            
            // Criterios para síntomas subjetivos (escalas cualitativas)
            [
                'sintoma' => 'Ansiedad',
                'descripcion' => 'Ansiedad moderada a severa',
                'valor_minimo' => 'moderada',
                'valor_maximo' => 'severa',
                'unidad' => 'escala'
            ],
            [
                'sintoma' => 'Ansiedad',
                'descripcion' => 'Crisis de ansiedad',
                'valor_minimo' => 'crisis',
                'valor_maximo' => null,
                'unidad' => 'episodio'
            ],
            [
                'sintoma' => 'Náuseas',
                'descripcion' => 'Náuseas persistentes',
                'valor_minimo' => 'persistente',
                'valor_maximo' => null,
                'unidad' => 'intensidad'
            ],
            [
                'sintoma' => 'Vómito',
                'descripcion' => 'Episodios de vómito',
                'valor_minimo' => '1',
                'valor_maximo' => null,
                'unidad' => 'episodios'
            ],
            [
                'sintoma' => 'Agitación',
                'descripcion' => 'Agitación que requiere intervención',
                'valor_minimo' => 'moderada',
                'valor_maximo' => null,
                'unidad' => 'nivel'
            ],
            [
                'sintoma' => 'Insomnio',
                'descripcion' => 'Dificultad para dormir >30 min',
                'valor_minimo' => '30',
                'valor_maximo' => null,
                'unidad' => 'minutos'
            ],
            
            // Criterios para síntomas respiratorios
            [
                'sintoma' => 'Disnea',
                'descripcion' => 'Dificultad respiratoria en reposo',
                'valor_minimo' => 'reposo',
                'valor_maximo' => null,
                'unidad' => 'situación'
            ],
            [
                'sintoma' => 'Tos',
                'descripcion' => 'Tos persistente que interfiere',
                'valor_minimo' => 'persistente',
                'valor_maximo' => null,
                'unidad' => 'intensidad'
            ]
        ];

        foreach ($criterios as $criterio) {
            $sintoma = $sintomas->get($criterio['sintoma']);
            if ($sintoma) {
                DB::table('criterios_prn')->insertOrIgnore([
                    'sintoma_id' => $sintoma->id,
                    'descripcion' => $criterio['descripcion'],
                    'valor_minimo' => $criterio['valor_minimo'],
                    'valor_maximo' => $criterio['valor_maximo'],
                    'unidad' => $criterio['unidad'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
