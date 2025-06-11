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
                'principio_activo' => 'Paracetamol',
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'medida' => '500',
                'unidad_medida' => 'mg',
                'descripcion' => 'Analgésico y antipirético para dolor y fiebre',
                'activo' => true
            ],
            [
                'nombre' => 'Ibuprofeno 400mg',
                'principio_activo' => 'Ibuprofeno',
                'forma_farmaceutica' => 'Comprimido',
                'via_administracion' => 'Oral',
                'medida' => '400',
                'unidad_medida' => 'mg',
                'descripcion' => 'Antiinflamatorio no esteroideo para dolor e inflamación',
                'activo' => true
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
            // Extract medida and unidad_medida from concentration
            $medida = '0';
            $unidad_medida = 'mg';
            
            if (isset($medicamento['concentracion'])) {
                $medida = (string) $medicamento['concentracion'];
                $unidad_medida = $medicamento['concentracion'] < 1 ? 'mcg' : 'mg';
            }
            
            DB::table('medicamentos')->insertOrIgnore([
                'nombre' => $medicamento['nombre'],
                'medida' => $medida,
                'unidad_medida' => $unidad_medida,
                'principio_activo' => $medicamento['principio_activo'] ?? null,
                'forma_farmaceutica' => $medicamento['forma_farmaceutica'] ?? null,
                'via_administracion' => $medicamento['via_administracion'] ?? null,
                'descripcion' => $medicamento['descripcion'] ?? null,
                'activo' => $medicamento['activo'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


}
