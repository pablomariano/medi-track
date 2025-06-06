<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PrincipioActivo;
use App\Models\Medicamento;
use App\Models\FormaFarmaceutica;
use App\Models\ViaAdministracion;
use App\Models\UnidadMedida;
use App\Models\InteraccionMedicamento;

class MedicamentosCompletos extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "💊 Creando catálogo completo de medicamentos...\n";
        
        $this->seedPrincipiosActivosAdicionales();
        $this->seedMedicamentosCompletos();
        $this->seedInteraccionesMedicamentosas();
        
        echo "✅ Catálogo de medicamentos creado exitosamente\n";
    }

    private function seedPrincipiosActivosAdicionales(): void
    {
        $principios = [
            // Analgésicos y antiinflamatorios
            [
                'nombre_generico' => 'Aspirina',
                'nombre_comercial' => 'Aspirina',
                'clasificacion_atc' => 'N02BA01',
                'grupo_farmacologico' => 'Analgésicos AINE',
                'descripcion' => 'Ácido acetilsalicílico, analgésico y antiinflamatorio',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Diclofenaco',
                'nombre_comercial' => 'Voltaren',
                'clasificacion_atc' => 'M01AB05',
                'grupo_farmacologico' => 'Analgésicos AINE',
                'descripcion' => 'Antiinflamatorio no esteroideo potente',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Ketorolaco',
                'nombre_comercial' => 'Dolac',
                'clasificacion_atc' => 'M01AB15',
                'grupo_farmacologico' => 'Analgésicos AINE',
                'descripcion' => 'Analgésico AINE para dolor moderado a severo',
                'activo' => true
            ],

            // Antibióticos
            [
                'nombre_generico' => 'Ciprofloxacino',
                'nombre_comercial' => 'Cipro',
                'clasificacion_atc' => 'J01MA02',
                'grupo_farmacologico' => 'Antibióticos quinolonas',
                'descripcion' => 'Antibiótico fluoroquinolona de amplio espectro',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Azitromicina',
                'nombre_comercial' => 'Zitromax',
                'clasificacion_atc' => 'J01FA10',
                'grupo_farmacologico' => 'Antibióticos macrólidos',
                'descripcion' => 'Antibiótico macrólido de amplio espectro',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Ceftriaxona',
                'nombre_comercial' => 'Rocephin',
                'clasificacion_atc' => 'J01DD04',
                'grupo_farmacologico' => 'Antibióticos cefalosporinas',
                'descripcion' => 'Cefalosporina de tercera generación',
                'activo' => true
            ],

            // Cardiovasculares
            [
                'nombre_generico' => 'Enalapril',
                'nombre_comercial' => 'Renitec',
                'clasificacion_atc' => 'C09AA02',
                'grupo_farmacologico' => 'Antihipertensivos IECA',
                'descripcion' => 'Inhibidor de la enzima convertidora de angiotensina',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Losartán',
                'nombre_comercial' => 'Cozaar',
                'clasificacion_atc' => 'C09CA01',
                'grupo_farmacologico' => 'Antihipertensivos ARA II',
                'descripcion' => 'Antagonista de receptores de angiotensina II',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Amlodipino',
                'nombre_comercial' => 'Norvasc',
                'clasificacion_atc' => 'C08CA01',
                'grupo_farmacologico' => 'Antihipertensivos CCB',
                'descripcion' => 'Bloqueador de canales de calcio',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Simvastatina',
                'nombre_comercial' => 'Zocor',
                'clasificacion_atc' => 'C10AA01',
                'grupo_farmacologico' => 'Hipolipemiantes estatinas',
                'descripcion' => 'Inhibidor de HMG-CoA reductasa',
                'activo' => true
            ],

            // Diabetes
            [
                'nombre_generico' => 'Metformina',
                'nombre_comercial' => 'Glucophage',
                'clasificacion_atc' => 'A10BA02',
                'grupo_farmacologico' => 'Antidiabéticos biguanidas',
                'descripcion' => 'Antidiabético oral, primera línea en diabetes tipo 2',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Insulina glargina',
                'nombre_comercial' => 'Lantus',
                'clasificacion_atc' => 'A10AE04',
                'grupo_farmacologico' => 'Insulinas de acción prolongada',
                'descripcion' => 'Insulina de acción prolongada',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Sitagliptina',
                'nombre_comercial' => 'Januvia',
                'clasificacion_atc' => 'A10BH01',
                'grupo_farmacologico' => 'Antidiabéticos DPP-4',
                'descripcion' => 'Inhibidor de dipeptidil peptidasa-4',
                'activo' => true
            ],

            // Neuropsiquiátricos
            [
                'nombre_generico' => 'Sertralina',
                'nombre_comercial' => 'Zoloft',
                'clasificacion_atc' => 'N06AB06',
                'grupo_farmacologico' => 'Antidepresivos ISRS',
                'descripcion' => 'Inhibidor selectivo de recaptación de serotonina',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Lorazepam',
                'nombre_comercial' => 'Ativan',
                'clasificacion_atc' => 'N05BA06',
                'grupo_farmacologico' => 'Ansiolíticos benzodiacepinas',
                'descripcion' => 'Benzodiacepina de acción intermedia',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Fenitoína',
                'nombre_comercial' => 'Epamin',
                'clasificacion_atc' => 'N03AB02',
                'grupo_farmacologico' => 'Anticonvulsivantes',
                'descripcion' => 'Anticonvulsivante para epilepsia',
                'activo' => true
            ],

            // Respiratorios
            [
                'nombre_generico' => 'Salbutamol',
                'nombre_comercial' => 'Ventolin',
                'clasificacion_atc' => 'R03AC02',
                'grupo_farmacologico' => 'Broncodilatadores beta2',
                'descripcion' => 'Broncodilatador beta2 agonista de acción corta',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Montelukast',
                'nombre_comercial' => 'Singulair',
                'clasificacion_atc' => 'R03DC03',
                'grupo_farmacologico' => 'Antiasmáticos antileucotrienos',
                'descripcion' => 'Antagonista de receptores de leucotrienos',
                'activo' => true
            ],

            // Oncológicos
            [
                'nombre_generico' => 'Paclitaxel',
                'nombre_comercial' => 'Taxol',
                'clasificacion_atc' => 'L01CD01',
                'grupo_farmacologico' => 'Antineoplásicos taxanos',
                'descripcion' => 'Agente antimitótico para quimioterapia',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Rituximab',
                'nombre_comercial' => 'MabThera',
                'clasificacion_atc' => 'L01XC02',
                'grupo_farmacologico' => 'Antineoplásicos monoclonales',
                'descripcion' => 'Anticuerpo monoclonal anti-CD20',
                'activo' => true
            ]
        ];

        foreach ($principios as $principio) {
            PrincipioActivo::firstOrCreate(
                ['nombre_generico' => $principio['nombre_generico']],
                $principio
            );
        }

        echo "  ✅ " . count($principios) . " principios activos adicionales creados\n";
    }

    private function seedMedicamentosCompletos(): void
    {
        $medicamentos = [
            // Paracetamol - múltiples presentaciones
            [
                'principio_activo' => 'Paracetamol',
                'nombre_comercial' => 'Paracetamol Forte',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => 1000,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Laboratorio Chile',
                'registro_sanitario' => 'F-12345/20',
                'precio_unitario' => 250.00,
                'requiere_receta' => false,
                'controlado' => false,
                'fecha_vencimiento' => '2026-12-31'
            ],
            [
                'principio_activo' => 'Paracetamol',
                'nombre_comercial' => 'Paracetamol Pediátrico',
                'forma_farmaceutica' => 'Jarabe',
                'concentracion' => 160,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Farmacias Chile',
                'registro_sanitario' => 'F-12346/20',
                'precio_unitario' => 3500.00,
                'requiere_receta' => false,
                'controlado' => false,
                'fecha_vencimiento' => '2025-08-15'
            ],

            // Ibuprofeno
            [
                'principio_activo' => 'Ibuprofeno',
                'nombre_comercial' => 'Ibupirac Forte',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => 800,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Laboratorio Biosintética',
                'registro_sanitario' => 'F-23456/21',
                'precio_unitario' => 450.00,
                'requiere_receta' => false,
                'controlado' => false,
                'fecha_vencimiento' => '2026-03-20'
            ],
            [
                'principio_activo' => 'Ibuprofeno',
                'nombre_comercial' => 'Ibupirac Gel',
                'forma_farmaceutica' => 'Gel',
                'concentracion' => 5,
                'unidad_concentracion' => '% p/p',
                'via_administracion' => 'Tópica cutánea',
                'laboratorio' => 'Laboratorio Biosintética',
                'registro_sanitario' => 'F-23457/21',
                'precio_unitario' => 8500.00,
                'requiere_receta' => false,
                'controlado' => false,
                'fecha_vencimiento' => '2025-11-30'
            ],

            // Antibióticos
            [
                'principio_activo' => 'Amoxicilina',
                'nombre_comercial' => 'Amoxil',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => 500,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'GlaxoSmithKline',
                'registro_sanitario' => 'F-34567/19',
                'precio_unitario' => 890.00,
                'requiere_receta' => true,
                'controlado' => false,
                'fecha_vencimiento' => '2026-01-15'
            ],
            [
                'principio_activo' => 'Ciprofloxacino',
                'nombre_comercial' => 'Ciproxina',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => 500,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Bayer',
                'registro_sanitario' => 'F-45678/20',
                'precio_unitario' => 1250.00,
                'requiere_receta' => true,
                'controlado' => false,
                'fecha_vencimiento' => '2025-09-10'
            ],
            [
                'principio_activo' => 'Ceftriaxona',
                'nombre_comercial' => 'Rocephin',
                'forma_farmaceutica' => 'Vial',
                'concentracion' => 1,
                'unidad_concentracion' => 'g',
                'via_administracion' => 'Intravenosa',
                'laboratorio' => 'Roche',
                'registro_sanitario' => 'F-56789/21',
                'precio_unitario' => 12500.00,
                'requiere_receta' => true,
                'controlado' => false,
                'fecha_vencimiento' => '2026-06-30'
            ],

            // Cardiovasculares
            [
                'principio_activo' => 'Enalapril',
                'nombre_comercial' => 'Renitec',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => 10,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'MSD',
                'registro_sanitario' => 'F-67890/19',
                'precio_unitario' => 320.00,
                'requiere_receta' => true,
                'controlado' => false,
                'fecha_vencimiento' => '2025-12-25'
            ],
            [
                'principio_activo' => 'Amlodipino',
                'nombre_comercial' => 'Norvasc',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => 5,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Pfizer',
                'registro_sanitario' => 'F-78901/20',
                'precio_unitario' => 480.00,
                'requiere_receta' => true,
                'controlado' => false,
                'fecha_vencimiento' => '2026-04-18'
            ],

            // Diabetes
            [
                'principio_activo' => 'Metformina',
                'nombre_comercial' => 'Glucophage',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => 850,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Merck Serono',
                'registro_sanitario' => 'F-89012/19',
                'precio_unitario' => 180.00,
                'requiere_receta' => true,
                'controlado' => false,
                'fecha_vencimiento' => '2026-02-28'
            ],
            [
                'principio_activo' => 'Insulina glargina',
                'nombre_comercial' => 'Lantus SoloStar',
                'forma_farmaceutica' => 'Jeringa prellenada',
                'concentracion' => 100,
                'unidad_concentracion' => 'UI',
                'via_administracion' => 'Subcutánea',
                'laboratorio' => 'Sanofi',
                'registro_sanitario' => 'F-90123/21',
                'precio_unitario' => 45000.00,
                'requiere_receta' => true,
                'controlado' => true,
                'fecha_vencimiento' => '2025-07-31'
            ],

            // Respiratorios
            [
                'principio_activo' => 'Salbutamol',
                'nombre_comercial' => 'Ventolin HFA',
                'forma_farmaceutica' => 'Inhalador',
                'concentracion' => 100,
                'unidad_concentracion' => 'mcg',
                'via_administracion' => 'Inhalatoria',
                'laboratorio' => 'GlaxoSmithKline',
                'registro_sanitario' => 'F-01234/20',
                'precio_unitario' => 8900.00,
                'requiere_receta' => true,
                'controlado' => false,
                'fecha_vencimiento' => '2025-10-15'
            ],

            // Neuropsiquiátricos
            [
                'principio_activo' => 'Lorazepam',
                'nombre_comercial' => 'Ativan',
                'forma_farmaceutica' => 'Tableta',
                'concentracion' => 1,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'Pfizer',
                'registro_sanitario' => 'F-11111/19',
                'precio_unitario' => 650.00,
                'requiere_receta' => true,
                'controlado' => true,
                'fecha_vencimiento' => '2026-05-20'
            ],

            // Gastrointestinales
            [
                'principio_activo' => 'Omeprazol',
                'nombre_comercial' => 'Prilosec',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => 20,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Oral',
                'laboratorio' => 'AstraZeneca',
                'registro_sanitario' => 'F-22222/20',
                'precio_unitario' => 420.00,
                'requiere_receta' => false,
                'controlado' => false,
                'fecha_vencimiento' => '2025-11-12'
            ],

            // Oncológicos
            [
                'principio_activo' => 'Paclitaxel',
                'nombre_comercial' => 'Taxol',
                'forma_farmaceutica' => 'Vial',
                'concentracion' => 30,
                'unidad_concentracion' => 'mg',
                'via_administracion' => 'Intravenosa',
                'laboratorio' => 'Bristol-Myers Squibb',
                'registro_sanitario' => 'F-33333/21',
                'precio_unitario' => 85000.00,
                'requiere_receta' => true,
                'controlado' => true,
                'fecha_vencimiento' => '2025-12-01'
            ]
        ];

        foreach ($medicamentos as $medData) {
            $principio = PrincipioActivo::where('nombre_generico', $medData['principio_activo'])->first();
            $forma = FormaFarmaceutica::where('nombre', $medData['forma_farmaceutica'])->first();
            $via = ViaAdministracion::where('nombre', $medData['via_administracion'])->first();
            $unidad = UnidadMedida::where('simbolo', $medData['unidad_concentracion'])->first();

            if ($principio && $forma && $via && $unidad) {
                Medicamento::firstOrCreate(
                    [
                        'nombre_comercial' => $medData['nombre_comercial'],
                        'laboratorio' => $medData['laboratorio']
                    ],
                    [
                        'principio_activo_id' => $principio->id,
                        'forma_farmaceutica_id' => $forma->id,
                        'concentracion' => $medData['concentracion'],
                        'unidad_concentracion_id' => $unidad->id,
                        'via_administracion_id' => $via->id,
                        'registro_sanitario' => $medData['registro_sanitario'],
                        'precio_unitario' => $medData['precio_unitario'],
                        'requiere_receta' => $medData['requiere_receta'],
                        'controlado' => $medData['controlado'],
                        'fecha_vencimiento' => $medData['fecha_vencimiento'],
                        'activo' => true,
                        'observaciones' => 'Medicamento de prueba creado por seeder'
                    ]
                );
            }
        }

        echo "  ✅ " . count($medicamentos) . " medicamentos completos creados\n";
    }

    private function seedInteraccionesMedicamentosas(): void
    {
        $interacciones = [
            [
                'principio_activo_1' => 'Paracetamol',
                'principio_activo_2' => 'Ibuprofeno',
                'tipo_interaccion' => 'menor',
                'descripcion' => 'Puede aumentar el riesgo de efectos gastrointestinales cuando se usan conjuntamente',
                'gravedad' => 'leve',
                'mecanismo' => 'Sinergia en efectos adversos gastrointestinales',
                'recomendacion' => 'Monitorear síntomas gastrointestinales. Considerar alternar horarios de administración.'
            ],
            [
                'principio_activo_1' => 'Enalapril',
                'principio_activo_2' => 'Losartán',
                'tipo_interaccion' => 'mayor',
                'descripcion' => 'Combinación contraindicada - doble bloqueo del sistema renina-angiotensina',
                'gravedad' => 'severa',
                'mecanismo' => 'Doble inhibición del sistema renina-angiotensina-aldosterona',
                'recomendacion' => 'CONTRAINDICADO. No usar conjuntamente. Elegir un solo agente.'
            ],
            [
                'principio_activo_1' => 'Ciprofloxacino',
                'principio_activo_2' => 'Lorazepam',
                'tipo_interaccion' => 'moderada',
                'descripcion' => 'El ciprofloxacino puede aumentar los niveles séricos de lorazepam',
                'gravedad' => 'moderada',
                'mecanismo' => 'Inhibición del metabolismo de benzodiazepinas',
                'recomendacion' => 'Monitorear signos de sedación excesiva. Considerar reducir dosis de lorazepam.'
            ],
            [
                'principio_activo_1' => 'Metformina',
                'principio_activo_2' => 'Enalapril',
                'tipo_interaccion' => 'beneficiosa',
                'descripcion' => 'Combinación sinérgica en pacientes diabéticos con hipertensión',
                'gravedad' => 'ninguna',
                'mecanismo' => 'Efectos complementarios en protección cardiovascular',
                'recomendacion' => 'Combinación recomendada en diabéticos hipertensos. Monitorear función renal.'
            ],
            [
                'principio_activo_1' => 'Salbutamol',
                'principio_activo_2' => 'Enalapril',
                'tipo_interaccion' => 'menor',
                'descripcion' => 'Los beta2 agonistas pueden reducir la eficacia antihipertensiva',
                'gravedad' => 'leve',
                'mecanismo' => 'Estimulación beta2 puede contrarrestar efectos hipotensores',
                'recomendacion' => 'Monitorear presión arterial más frecuentemente'
            ],
            [
                'principio_activo_1' => 'Paclitaxel',
                'principio_activo_2' => 'Ciprofloxacino',
                'tipo_interaccion' => 'mayor',
                'descripcion' => 'Riesgo aumentado de neuropatía periférica',
                'gravedad' => 'severa',
                'mecanismo' => 'Efectos neurotóxicos aditivos',
                'recomendacion' => 'Evitar uso concomitante. Si es necesario, monitoreo neurológico estrecho.'
            ]
        ];

        foreach ($interacciones as $interaccionData) {
            $principio1 = PrincipioActivo::where('nombre_generico', $interaccionData['principio_activo_1'])->first();
            $principio2 = PrincipioActivo::where('nombre_generico', $interaccionData['principio_activo_2'])->first();

            if ($principio1 && $principio2) {
                InteraccionMedicamento::firstOrCreate(
                    [
                        'principio_activo_1_id' => $principio1->id,
                        'principio_activo_2_id' => $principio2->id
                    ],
                    [
                        'tipo_interaccion' => $interaccionData['tipo_interaccion'],
                        'descripcion' => $interaccionData['descripcion'],
                        'gravedad' => $interaccionData['gravedad'],
                        'mecanismo' => $interaccionData['mecanismo'],
                        'recomendacion' => $interaccionData['recomendacion'],
                        'activo' => true
                    ]
                );
            }
        }

        echo "  ✅ " . count($interacciones) . " interacciones medicamentosas creadas\n";
    }
}
