<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SintomasPrnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sintomas = [
            // Síntomas de dolor
            [
                'nombre' => 'Dolor de cabeza',
                'categoria' => 'Neurológico',
                'descripcion' => 'Cefalea o dolor en la región craneal',
                'activo' => true
            ],
            [
                'nombre' => 'Dolor muscular',
                'categoria' => 'Musculoesquelético',
                'descripcion' => 'Mialgia o dolor en músculos',
                'activo' => true
            ],
            [
                'nombre' => 'Dolor articular',
                'categoria' => 'Musculoesquelético',
                'descripcion' => 'Artralgia o dolor en articulaciones',
                'activo' => true
            ],
            [
                'nombre' => 'Dolor abdominal',
                'categoria' => 'Gastrointestinal',
                'descripcion' => 'Dolor en región abdominal',
                'activo' => true
            ],
            
            // Síntomas respiratorios
            [
                'nombre' => 'Disnea',
                'categoria' => 'Respiratorio',
                'descripcion' => 'Dificultad para respirar o falta de aire',
                'activo' => true
            ],
            [
                'nombre' => 'Tos',
                'categoria' => 'Respiratorio',
                'descripcion' => 'Tos seca o productiva',
                'activo' => true
            ],
            
            // Síntomas generales
            [
                'nombre' => 'Fiebre',
                'categoria' => 'General',
                'descripcion' => 'Elevación de la temperatura corporal',
                'activo' => true
            ],
            [
                'nombre' => 'Náuseas',
                'categoria' => 'Gastrointestinal',
                'descripcion' => 'Sensación de ganas de vomitar',
                'activo' => true
            ],
            [
                'nombre' => 'Vómito',
                'categoria' => 'Gastrointestinal',
                'descripcion' => 'Expulsión del contenido gástrico',
                'activo' => true
            ],
            
            // Síntomas psicológicos
            [
                'nombre' => 'Ansiedad',
                'categoria' => 'Psicológico',
                'descripcion' => 'Estado de inquietud o nerviosismo',
                'activo' => true
            ],
            [
                'nombre' => 'Insomnio',
                'categoria' => 'Neurológico',
                'descripcion' => 'Dificultad para conciliar o mantener el sueño',
                'activo' => true
            ],
            [
                'nombre' => 'Agitación',
                'categoria' => 'Psicológico',
                'descripcion' => 'Estado de inquietud motora o psíquica',
                'activo' => true
            ],
            
            // Síntomas cardiovasculares
            [
                'nombre' => 'Palpitaciones',
                'categoria' => 'Cardiovascular',
                'descripcion' => 'Percepción anormal de latidos cardíacos',
                'activo' => true
            ],
            [
                'nombre' => 'Hipertensión',
                'categoria' => 'Cardiovascular',
                'descripcion' => 'Presión arterial elevada',
                'activo' => true
            ],
            
            // Síntomas dermatológicos
            [
                'nombre' => 'Prurito',
                'categoria' => 'Dermatológico',
                'descripcion' => 'Sensación de picazón en la piel',
                'activo' => true
            ],
            [
                'nombre' => 'Rash cutáneo',
                'categoria' => 'Dermatológico',
                'descripcion' => 'Erupción o irritación en la piel',
                'activo' => true
            ]
        ];

        foreach ($sintomas as $sintoma) {
            DB::table('sintomas_prn')->insertOrIgnore([
                'nombre' => $sintoma['nombre'],
                'categoria' => $sintoma['categoria'],
                'descripcion' => $sintoma['descripcion'],
                'activo' => $sintoma['activo'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
