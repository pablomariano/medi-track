<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndicacionesPrnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener datos relacionados
        $medicamentosTratamientos = DB::table('medicamentos_tratamientos')
            ->join('medicamentos', 'medicamentos_tratamientos.medicamento_id', '=', 'medicamentos.id')
            ->join('tratamientos', 'medicamentos_tratamientos.tratamiento_id', '=', 'tratamientos.id')
            ->where('tipo_esquema', 'PRN')
            ->select('medicamentos_tratamientos.id as mt_id', 'medicamentos.nombre as medicamento', 'tratamientos.nombre as tratamiento')
            ->get();
            
        $sintomas = DB::table('sintomas_prn')->get()->keyBy('nombre');
        $criterios = DB::table('criterios_prn')->get()->groupBy('sintoma_id');

        if ($medicamentosTratamientos->isEmpty() || $sintomas->isEmpty()) {
            $this->command->info('No hay datos PRN disponibles para crear indicaciones.');
            return;
        }

        $indicaciones = [];

        // Mapeo simplificado de medicamentos a síntomas
        $medicamentoSintomas = [
            'Paracetamol 500mg' => ['Fiebre', 'Dolor de cabeza'],
            'Ibuprofeno 400mg' => ['Dolor muscular', 'Dolor articular'],
            'Ondansetrón 4mg' => ['Náuseas', 'Vómito'],
            'Lorazepam 1mg' => ['Ansiedad'],
            'Zolpidem 10mg' => ['Insomnio'],
            'Salbutamol 100mcg' => ['Disnea']
        ];

        // Para cada medicamento-tratamiento PRN, crear indicaciones
        foreach ($medicamentosTratamientos as $mt) {
            if (isset($medicamentoSintomas[$mt->medicamento])) {
                foreach ($medicamentoSintomas[$mt->medicamento] as $sintomaNombre) {
                    if ($sintomas->has($sintomaNombre)) {
                        $sintoma = $sintomas->get($sintomaNombre);
                        $criteriosSintoma = $criterios->get($sintoma->id);
                        
                        if ($criteriosSintoma) {
                            // Tomar el primer criterio disponible para simplicidad
                            $criterio = $criteriosSintoma->first();
                            
                            $indicaciones[] = [
                                'medicamento_tratamiento_id' => $mt->mt_id,
                                'sintoma_id' => $sintoma->id,
                                'criterio_id' => $criterio->id,
                                'descripcion_personalizada' => $this->getDescripcionPorMedicamento($mt->medicamento, $sintomaNombre, $criterio->descripcion),
                                'es_criterio_principal' => true
                            ];
                        }
                    }
                }
            }
        }

        // Insertar todas las indicaciones
        foreach ($indicaciones as $indicacion) {
            DB::table('indicaciones_prn')->insertOrIgnore([
                'medicamento_tratamiento_id' => $indicacion['medicamento_tratamiento_id'],
                'sintoma_id' => $indicacion['sintoma_id'],
                'criterio_id' => $indicacion['criterio_id'],
                'descripcion_personalizada' => $indicacion['descripcion_personalizada'],
                'es_criterio_principal' => $indicacion['es_criterio_principal'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Indicaciones PRN creadas: ' . count($indicaciones));
    }

    private function getDescripcionPorMedicamento($medicamento, $sintoma, $criterioDesc)
    {
        $descripciones = [
            'Paracetamol 500mg' => [
                'Fiebre' => 'Administrar cuando la temperatura sea ' . $criterioDesc,
                'Dolor de cabeza' => 'Para alivio de dolor de cabeza ' . $criterioDesc
            ],
            'Ibuprofeno 400mg' => [
                'Dolor muscular' => 'Para dolor muscular ' . $criterioDesc,
                'Dolor articular' => 'Para dolor articular ' . $criterioDesc
            ],
            'Ondansetrón 4mg' => [
                'Náuseas' => 'Administrar ante episodios de náuseas ' . $criterioDesc,
                'Vómito' => 'Administrar ante episodios de vómito ' . $criterioDesc
            ],
            'Lorazepam 1mg' => [
                'Ansiedad' => 'Solo para crisis de ansiedad ' . $criterioDesc
            ],
            'Zolpidem 10mg' => [
                'Insomnio' => 'Administrar para insomnio ' . $criterioDesc
            ],
            'Salbutamol 100mcg' => [
                'Disnea' => 'Usar inhalador ante dificultad respiratoria ' . $criterioDesc
            ]
        ];

        return $descripciones[$medicamento][$sintoma] ?? 'Administrar según criterio: ' . $criterioDesc;
    }
}
