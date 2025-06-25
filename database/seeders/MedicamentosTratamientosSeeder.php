<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentosTratamientosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener tratamientos y medicamentos
        $tratamientos = DB::table('tratamientos')->get()->keyBy('nombre');
        $medicamentos = DB::table('medicamentos')->get()->keyBy('nombre');

        // Configuraciones realistas de medicamentos-tratamientos (solo programados)
        $configuraciones = [
            // Control Hipertensión - Losartán
            [
                'tratamiento' => 'Control Hipertensión',
                'medicamento' => 'Losartán 50mg',
                'dosis' => '50mg',
                'frecuencia_horas' => 24,
                'duracion_dias' => 90,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Tomar una vez al día por la mañana',
                'tolerancia_minutos' => 60,
                'activo' => true
            ],
            
            // Control Diabetes - Metformina
            [
                'tratamiento' => 'Control Diabetes',
                'medicamento' => 'Metformina 850mg',
                'dosis' => '850mg',
                'frecuencia_horas' => 12,
                'duracion_dias' => 180,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Tomar con las comidas principales (desayuno y cena)',
                'tolerancia_minutos' => 60,
                'activo' => true
            ],
            
            // Antibioticoterapia - Amoxicilina
            [
                'tratamiento' => 'Antibioticoterapia',
                'medicamento' => 'Amoxicilina 500mg',
                'dosis' => '500mg',
                'frecuencia_horas' => 8,
                'duracion_dias' => 7,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Tomar cada 8 horas. IMPORTANTE: Completar todo el tratamiento',
                'tolerancia_minutos' => 30,
                'activo' => true
            ],
            
            // Protección Gástrica - Omeprazol
            [
                'tratamiento' => 'Protección Gástrica',
                'medicamento' => 'Omeprazol 20mg',
                'dosis' => '20mg',
                'frecuencia_horas' => 24,
                'duracion_dias' => 60,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Tomar en ayunas, 30 minutos antes del desayuno',
                'tolerancia_minutos' => 60,
                'activo' => true
            ],

            // Tratamiento de Ansiedad - Lorazepam programado
            [
                'tratamiento' => 'Control de Ansiedad',
                'medicamento' => 'Lorazepam 1mg',
                'dosis' => '1mg',
                'frecuencia_horas' => 12,
                'duracion_dias' => 30,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Tomar cada 12 horas con las comidas',
                'tolerancia_minutos' => 60,
                'activo' => true
            ],

            // Suplemento Vitamínico
            [
                'tratamiento' => 'Suplementación Vitamínica',
                'medicamento' => 'Complejo B',
                'dosis' => '1 tableta',
                'frecuencia_horas' => 24,
                'duracion_dias' => 90,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Tomar una tableta diaria con el desayuno',
                'tolerancia_minutos' => 120,
                'activo' => true
            ]
        ];

        foreach ($configuraciones as $config) {
            $tratamiento = $tratamientos->get($config['tratamiento']);
            $medicamento = $medicamentos->get($config['medicamento']);
            
            if ($tratamiento && $medicamento) {
                DB::table('medicamentos_tratamientos')->insertOrIgnore([
                    'tratamiento_id' => $tratamiento->id,
                    'medicamento_id' => $medicamento->id,
                    'dosis_cantidad' => $this->extractNumericDose($config['dosis']),
                    'unidad_dosis' => $this->extractDoseUnit($config['dosis']),
                    'frecuencia_horas' => $config['frecuencia_horas'],
                    'tolerancia_antes_minutos' => $config['tolerancia_minutos'] ?? 30,
                    'tolerancia_despues_minutos' => $config['tolerancia_minutos'] ?? 60,
                    'duracion_dias' => $config['duracion_dias'] ?? null,
                    'activo' => $config['activo'] ?? true,
                    'instrucciones_especiales' => $config['instrucciones'],
                    'estado' => $config['activo'] ? 'Activo' : 'Pausado',
                    'motivo_suspension' => null,
                    'orden' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Configuraciones de medicamentos-tratamientos creadas: ' . count($configuraciones));
    }

    private function extractNumericDose($dosis)
    {
        // Extraer el número de dosis como "500mg" -> 500
        preg_match('/(\d+(?:\.\d+)?)/', $dosis, $matches);
        return $matches[1] ?? 1;
    }

    private function extractDoseUnit($dosis)
    {
        // Extraer la unidad de dosis como "500mg" -> "mg"
        preg_match('/\d+(?:\.\d+)?(.*)/', $dosis, $matches);
        $unit = trim($matches[1] ?? 'mg');
        return $unit ?: 'mg';
    }
}
