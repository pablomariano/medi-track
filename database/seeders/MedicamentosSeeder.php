<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicamentos = [
            // Analgésicos y Antipiréticos (PRN comunes)
            [
                'nombre' => 'Paracetamol 500mg',
                'nombre_comercial' => 'Paracetamol',
                'principio_activo' => 'Paracetamol',
                'concentracion' => 500.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Laboratorio Nacional',
                'descripcion' => 'Analgésico y antipirético para dolor y fiebre',
                'activo' => true,
                'requiere_receta' => false,
                'controlado' => false
            ],
            [
                'nombre' => 'Ibuprofeno 400mg',
                'nombre_comercial' => 'Ibuprofeno',
                'principio_activo' => 'Ibuprofeno',
                'concentracion' => 400.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Pharma Plus',
                'descripcion' => 'Antiinflamatorio no esteroideo para dolor e inflamación',
                'activo' => true,
                'requiere_receta' => false,
                'controlado' => false
            ],
            [
                'nombre' => 'Ketorolaco 10mg',
                'nombre_comercial' => 'Ketorolaco',
                'principio_activo' => 'Ketorolaco',
                'concentracion' => 10.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'MediFarm',
                'descripcion' => 'Analgésico potente para dolor moderado a severo',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            
            // Medicamentos para síntomas gastrointestinales (PRN)
            [
                'nombre' => 'Ondansetrón 4mg',
                'nombre_comercial' => 'Ondansetrón',
                'principio_activo' => 'Ondansetrón',
                'concentracion' => 4.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'BioPharma',
                'descripcion' => 'Antiemético para náuseas y vómitos',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            [
                'nombre' => 'Metoclopramida 10mg',
                'nombre_comercial' => 'Metoclopramida',
                'principio_activo' => 'Metoclopramida',
                'concentracion' => 10.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'GenericMed',
                'descripcion' => 'Procinético y antiemético',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            
            // Medicamentos para ansiedad y trastornos del sueño (PRN)
            [
                'nombre' => 'Lorazepam 1mg',
                'nombre_comercial' => 'Lorazepam',
                'principio_activo' => 'Lorazepam',
                'concentracion' => 1.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'PsychoPharma',
                'descripcion' => 'Ansiolítico para crisis de ansiedad',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => true
            ],
            [
                'nombre' => 'Zolpidem 10mg',
                'nombre_comercial' => 'Zolpidem',
                'principio_activo' => 'Zolpidem',
                'concentracion' => 10.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'SleepMed',
                'descripcion' => 'Hipnótico para insomnio',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => true
            ],
            
            // Medicamentos programados típicos
            [
                'nombre' => 'Losartán 50mg',
                'nombre_comercial' => 'Losartán',
                'principio_activo' => 'Losartán',
                'concentracion' => 50.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'CardioMed',
                'descripcion' => 'Antihipertensivo para control de presión arterial',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            [
                'nombre' => 'Metformina 850mg',
                'nombre_comercial' => 'Metformina',
                'principio_activo' => 'Metformina',
                'concentracion' => 850.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'DiabetesCare',
                'descripcion' => 'Antidiabético para control de glucemia',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            [
                'nombre' => 'Amoxicilina 500mg',
                'nombre_comercial' => 'Amoxicilina',
                'principio_activo' => 'Amoxicilina',
                'concentracion' => 500.00,
                'forma_farmaceutica' => 'Cápsula',
                'via_administracion' => 'Oral',
                'laboratorio' => 'AntibioLab',
                'descripcion' => 'Antibiótico betalactámico',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            [
                'nombre' => 'Omeprazol 20mg',
                'nombre_comercial' => 'Omeprazol',
                'principio_activo' => 'Omeprazol',
                'concentracion' => 20.00,
                'forma_farmaceutica' => 'Cápsula',
                'via_administracion' => 'Oral',
                'laboratorio' => 'GastroMed',
                'descripcion' => 'Inhibidor de bomba de protones',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            [
                'nombre' => 'Simvastatina 20mg',
                'nombre_comercial' => 'Simvastatina',
                'principio_activo' => 'Simvastatina',
                'concentracion' => 20.00,
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'laboratorio' => 'LipidControl',
                'descripcion' => 'Estatina para control de colesterol',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            
            // Medicamentos de emergencia/urgencia (PRN)
            [
                'nombre' => 'Salbutamol 100mcg',
                'nombre_comercial' => 'Salbutamol',
                'principio_activo' => 'Salbutamol',
                'concentracion' => 0.1, // 100mcg = 0.1mg
                'forma_farmaceutica' => 'Inhalador',
                'via_administracion' => 'Inhalatoria',
                'laboratorio' => 'RespiraMed',
                'descripcion' => 'Broncodilatador para crisis asmáticas',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            [
                'nombre' => 'Captopril 25mg sublingual',
                'nombre_comercial' => 'Captopril',
                'principio_activo' => 'Captopril',
                'concentracion' => 25.00,
                'forma_farmaceutica' => 'Comprimido sublingual',
                'via_administracion' => 'Sublingual',
                'laboratorio' => 'EmergencyMed',
                'descripcion' => 'Para crisis hipertensivas',
                'activo' => true,
                'requiere_receta' => true,
                'controlado' => false
            ],
            
            // Medicamentos tópicos (PRN)
            [
                'nombre' => 'Hidrocortisona 1% crema',
                'nombre_comercial' => 'Hidrocortisona',
                'principio_activo' => 'Hidrocortisona',
                'concentracion' => 10.00, // 1% = 10mg/g
                'forma_farmaceutica' => 'Crema',
                'via_administracion' => 'Tópica',
                'laboratorio' => 'DermaCare',
                'descripcion' => 'Corticoide tópico para inflamación cutánea',
                'activo' => true,
                'requiere_receta' => false,
                'controlado' => false
            ]
        ];

        foreach ($medicamentos as $medicamento) {
            DB::table('medicamentos')->insertOrIgnore([
                'nombre' => $medicamento['nombre'],
                'nombre_comercial' => $medicamento['nombre_comercial'],
                'principio_activo' => $medicamento['principio_activo'],
                'principio_activo_id' => 1, // Valor por defecto
                'concentracion' => $medicamento['concentracion'],
                'unidad_concentracion_id' => 1, // mg por defecto
                'forma_farmaceutica' => $medicamento['forma_farmaceutica'],
                'forma_farmaceutica_id' => $this->getFormaFarmaceuticaId($medicamento['forma_farmaceutica']),
                'via_administracion' => $medicamento['via_administracion'],
                'via_administracion_id' => $this->getViaAdministracionId($medicamento['via_administracion']),
                'laboratorio' => $medicamento['laboratorio'],
                'descripcion' => $medicamento['descripcion'],
                'activo' => $medicamento['activo'],
                'requiere_receta' => $medicamento['requiere_receta'],
                'controlado' => $medicamento['controlado'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getFormaFarmaceuticaId($forma)
    {
        $formas = [
            'Comprimido' => 1,
            'Tableta' => 1,
            'Cápsula' => 2,
            'Jarabe' => 3,
            'Inhalador' => 1, // Default to tableta
            'Crema' => 1, // Default to tableta
        ];
        
        return $formas[$forma] ?? 1;
    }

    private function getViaAdministracionId($via)
    {
        $vias = [
            'Oral' => 1,
            'Intravenosa' => 2,
            'Intramuscular' => 3,
            'Subcutánea' => 4,
            'Tópica' => 5,
            'Inhalatoria' => 6,
            'Sublingual' => 1, // Default to oral
        ];
        
        return $vias[$via] ?? 1;
    }
}
