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
        // Obtener datos relacionados
        $medicamentos = DB::table('medicamentos')->get()->keyBy('nombre');
        $tratamientos = DB::table('tratamientos')->get()->keyBy('nombre');
        
        if ($medicamentos->isEmpty() || $tratamientos->isEmpty()) {
            $this->command->info('No hay medicamentos o tratamientos disponibles. Ejecutar seeders correspondientes primero.');
            return;
        }

        $configuraciones = [
            // TRATAMIENTOS PROGRAMADOS
            
            // Control de Hipertensión - Losartán
            [
                'tratamiento' => 'Control de Hipertensión',
                'medicamento' => 'Losartán 50mg',
                'dosis' => '50mg',
                'frecuencia_horas' => 24,
                'duracion_dias' => 90,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Tomar una vez al día, preferiblemente en la mañana',
                'tolerancia_minutos' => 120, // 2 horas de tolerancia
                'es_prn' => false,
                'activo' => true
            ],
            
            // Control de Diabetes - Metformina
            [
                'tratamiento' => 'Control de Diabetes',
                'medicamento' => 'Metformina 850mg',
                'dosis' => '850mg',
                'frecuencia_horas' => 12,
                'duracion_dias' => 180,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Tomar con las comidas principales (desayuno y cena)',
                'tolerancia_minutos' => 60, // 1 hora de tolerancia
                'es_prn' => false,
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
                'tolerancia_minutos' => 30, // Antibióticos requieren precisión
                'es_prn' => false,
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
                'es_prn' => false,
                'activo' => true
            ],
            
            // TRATAMIENTOS PRN
            
            // Manejo de Dolor PRN - Paracetamol
            [
                'tratamiento' => 'Manejo de Dolor PRN',
                'medicamento' => 'Paracetamol 500mg',
                'dosis' => '500mg',
                'frecuencia_horas' => 6, // Mínimo 6 horas entre dosis
                'duracion_dias' => 30,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Máximo 4 dosis por día (2g/día)',
                'tolerancia_minutos' => null, // PRN no tiene tolerancia programada
                'es_prn' => true,
                'max_dosis_dia' => 4,
                'max_dosis_semana' => null,
                'activo' => true
            ],
            
            // Manejo de Dolor PRN - Ibuprofeno (alternativo)
            [
                'tratamiento' => 'Manejo de Dolor PRN',
                'medicamento' => 'Ibuprofeno 400mg',
                'dosis' => '400mg',
                'frecuencia_horas' => 8,
                'duracion_dias' => 30,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Máximo 3 dosis por día. Tomar con alimentos',
                'tolerancia_minutos' => null,
                'es_prn' => true,
                'max_dosis_dia' => 3,
                'max_dosis_semana' => null,
                'activo' => true
            ],
            
            // Control de Fiebre PRN - Paracetamol
            [
                'tratamiento' => 'Control de Fiebre PRN',
                'medicamento' => 'Paracetamol 500mg',
                'dosis' => '500mg',
                'frecuencia_horas' => 6,
                'duracion_dias' => 14,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Administrar solo si fiebre >38°C',
                'tolerancia_minutos' => null,
                'es_prn' => true,
                'max_dosis_dia' => 4,
                'max_dosis_semana' => null,
                'activo' => true
            ],
            
            // Manejo de Náuseas PRN - Ondansetrón
            [
                'tratamiento' => 'Manejo de Náuseas PRN',
                'medicamento' => 'Ondansetrón 4mg',
                'dosis' => '4mg',
                'frecuencia_horas' => 8,
                'duracion_dias' => 21,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Administrar ante episodios de náuseas',
                'tolerancia_minutos' => null,
                'es_prn' => true,
                'max_dosis_dia' => 3,
                'max_dosis_semana' => null,
                'activo' => true
            ],
            
            // Crisis de Ansiedad PRN - Lorazepam
            [
                'tratamiento' => 'Crisis de Ansiedad PRN',
                'medicamento' => 'Lorazepam 1mg',
                'dosis' => '1mg',
                'frecuencia_horas' => 12,
                'duracion_dias' => 30,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Solo para crisis. Máximo 2 dosis/día',
                'tolerancia_minutos' => null,
                'es_prn' => true,
                'max_dosis_dia' => 2,
                'max_dosis_semana' => 10,
                'activo' => true
            ],
            
            // Insomnio PRN - Zolpidem
            [
                'tratamiento' => 'Insomnio PRN',
                'medicamento' => 'Zolpidem 10mg',
                'dosis' => '10mg',
                'frecuencia_horas' => 24,
                'duracion_dias' => 14,
                'via_administracion' => 'Oral',
                'instrucciones' => 'Solo al acostarse si no puede dormir en 30 min',
                'tolerancia_minutos' => null,
                'es_prn' => true,
                'max_dosis_dia' => 1,
                'max_dosis_semana' => 5,
                'activo' => true
            ],
            
            // Crisis Asmática PRN - Salbutamol
            [
                'tratamiento' => 'Crisis Asmática PRN',
                'medicamento' => 'Salbutamol 100mcg',
                'dosis' => '2 puffs',
                'frecuencia_horas' => 4,
                'duracion_dias' => 90,
                'via_administracion' => 'Inhalatoria',
                'instrucciones' => 'Ante dificultad respiratoria. Si no mejora en 15 min, repetir',
                'tolerancia_minutos' => null,
                'es_prn' => true,
                'max_dosis_dia' => 8, // 8 inhalaciones máximo
                'max_dosis_semana' => null,
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
                    'intervalo_minimo_horas' => $config['es_prn'] ? $config['frecuencia_horas'] : null,
                    'dosis_maxima_dia' => isset($config['max_dosis_dia']) ? $this->extractNumericDose($config['dosis']) * $config['max_dosis_dia'] : null,
                    'dosis_maxima_consecutiva' => $config['max_dosis_dia'] ?? null,
                    'instrucciones_especiales' => $config['instrucciones'],
                    'estado' => $config['activo'] ? 'Activo' : 'Pausado',
                    'motivo_suspension' => null,
                    'orden' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
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
