<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FormaFarmaceutica;
use App\Models\ViaAdministracion;
use App\Models\UnidadMedida;

class CatalogosFarmaceuticosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🏥 Creando catálogos farmacéuticos completos...\n";
        
        $this->seedFormasFarmaceuticas();
        $this->seedViasAdministracion();
        $this->seedUnidadesMedida();
        
        echo "✅ Catálogos farmacéuticos creados exitosamente\n";
    }

    private function seedFormasFarmaceuticas(): void
    {
        $formas = [
            // Formas sólidas
            [
                'nombre' => 'Tableta',
                'descripcion' => 'Forma farmacéutica sólida obtenida por compresión',
                'activo' => true
            ],
            [
                'nombre' => 'Cápsula',
                'descripcion' => 'Forma farmacéutica sólida con cubierta de gelatina',
                'activo' => true
            ],
            [
                'nombre' => 'Comprimido',
                'descripcion' => 'Forma farmacéutica sólida compacta',
                'activo' => true
            ],
            [
                'nombre' => 'Comprimido recubierto',
                'descripcion' => 'Comprimido con recubrimiento entérico o de liberación controlada',
                'activo' => true
            ],
            [
                'nombre' => 'Tableta masticable',
                'descripcion' => 'Tableta diseñada para ser masticada antes de tragar',
                'activo' => true
            ],
            [
                'nombre' => 'Tableta sublingual',
                'descripcion' => 'Tableta para disolución bajo la lengua',
                'activo' => true
            ],
            [
                'nombre' => 'Tableta efervescente',
                'descripcion' => 'Tableta que se disuelve en agua formando burbujas',
                'activo' => true
            ],
            [
                'nombre' => 'Polvo',
                'descripcion' => 'Preparación farmacéutica en forma de polvo fino',
                'activo' => true
            ],
            [
                'nombre' => 'Granulado',
                'descripcion' => 'Agregados de partículas de polvo',
                'activo' => true
            ],
            [
                'nombre' => 'Sobres',
                'descripcion' => 'Polvo o granulado envasado en sobres unitarios',
                'activo' => true
            ],

            // Formas líquidas
            [
                'nombre' => 'Jarabe',
                'descripcion' => 'Solución acuosa azucarada para administración oral',
                'activo' => true
            ],
            [
                'nombre' => 'Suspensión',
                'descripcion' => 'Dispersión de sólidos finamente divididos en líquido',
                'activo' => true
            ],
            [
                'nombre' => 'Solución oral',
                'descripcion' => 'Preparación líquida para administración por vía oral',
                'activo' => true
            ],
            [
                'nombre' => 'Gotas orales',
                'descripcion' => 'Solución en gotas para administración oral',
                'activo' => true
            ],
            [
                'nombre' => 'Elixir',
                'descripcion' => 'Preparación líquida hidroalcohólica edulcorada',
                'activo' => true
            ],
            [
                'nombre' => 'Emulsión',
                'descripcion' => 'Sistema disperso de dos líquidos inmiscibles',
                'activo' => true
            ],

            // Formas inyectables
            [
                'nombre' => 'Ampolla',
                'descripcion' => 'Envase de vidrio sellado para inyectables',
                'activo' => true
            ],
            [
                'nombre' => 'Vial',
                'descripcion' => 'Frasco de vidrio para inyectables con tapón',
                'activo' => true
            ],
            [
                'nombre' => 'Jeringa prellenada',
                'descripcion' => 'Jeringa lista para usar con medicamento precargado',
                'activo' => true
            ],
            [
                'nombre' => 'Solución inyectable',
                'descripcion' => 'Preparación estéril para administración parenteral',
                'activo' => true
            ],
            [
                'nombre' => 'Polvo para inyección',
                'descripcion' => 'Polvo estéril para reconstituir antes de inyectar',
                'activo' => true
            ],

            // Formas tópicas
            [
                'nombre' => 'Crema',
                'descripcion' => 'Emulsión semisólida para aplicación tópica',
                'activo' => true
            ],
            [
                'nombre' => 'Pomada',
                'descripcion' => 'Preparación semisólida grasa para uso tópico',
                'activo' => true
            ],
            [
                'nombre' => 'Gel',
                'descripcion' => 'Sistema coloidal semisólido transparente',
                'activo' => true
            ],
            [
                'nombre' => 'Loción',
                'descripcion' => 'Preparación líquida para aplicación externa',
                'activo' => true
            ],
            [
                'nombre' => 'Parche transdérmico',
                'descripcion' => 'Sistema de liberación controlada a través de la piel',
                'activo' => true
            ],

            // Formas especiales
            [
                'nombre' => 'Supositorio',
                'descripcion' => 'Forma farmacéutica sólida para inserción rectal o vaginal',
                'activo' => true
            ],
            [
                'nombre' => 'Óvulo vaginal',
                'descripcion' => 'Forma farmacéutica sólida para inserción vaginal',
                'activo' => true
            ],
            [
                'nombre' => 'Aerosol',
                'descripcion' => 'Dispersión de partículas líquidas o sólidas en gas',
                'activo' => true
            ],
            [
                'nombre' => 'Inhalador',
                'descripcion' => 'Dispositivo para administración por vía respiratoria',
                'activo' => true
            ],
            [
                'nombre' => 'Colirio',
                'descripcion' => 'Solución estéril para aplicación ocular',
                'activo' => true
            ],
            [
                'nombre' => 'Gotas nasales',
                'descripcion' => 'Solución para aplicación en cavidad nasal',
                'activo' => true
            ],
            [
                'nombre' => 'Spray nasal',
                'descripcion' => 'Pulverización para administración nasal',
                'activo' => true
            ]
        ];

        foreach ($formas as $forma) {
            FormaFarmaceutica::firstOrCreate(
                ['nombre' => $forma['nombre']],
                $forma
            );
        }

        echo "  ✅ " . count($formas) . " formas farmacéuticas creadas\n";
    }

    private function seedViasAdministracion(): void
    {
        $vias = [
            // Vías enterales
            [
                'nombre' => 'Oral',
                'descripcion' => 'Administración por la boca, deglución al estómago',
                'abreviatura' => 'PO',
                'activo' => true
            ],
            [
                'nombre' => 'Sublingual',
                'descripcion' => 'Absorción bajo la lengua',
                'abreviatura' => 'SL',
                'activo' => true
            ],
            [
                'nombre' => 'Bucal',
                'descripcion' => 'Absorción a través de la mucosa bucal',
                'abreviatura' => 'BUC',
                'activo' => true
            ],
            [
                'nombre' => 'Rectal',
                'descripcion' => 'Administración por vía rectal (supositorios)',
                'abreviatura' => 'PR',
                'activo' => true
            ],

            // Vías parenterales
            [
                'nombre' => 'Intravenosa',
                'descripcion' => 'Inyección directa en vena',
                'abreviatura' => 'IV',
                'activo' => true
            ],
            [
                'nombre' => 'Intramuscular',
                'descripcion' => 'Inyección en músculo',
                'abreviatura' => 'IM',
                'activo' => true
            ],
            [
                'nombre' => 'Subcutánea',
                'descripcion' => 'Inyección en tejido subcutáneo',
                'abreviatura' => 'SC',
                'activo' => true
            ],
            [
                'nombre' => 'Intradérmica',
                'descripcion' => 'Inyección en dermis',
                'abreviatura' => 'ID',
                'activo' => true
            ],
            [
                'nombre' => 'Intraperitoneal',
                'descripcion' => 'Inyección en cavidad peritoneal',
                'abreviatura' => 'IP',
                'activo' => true
            ],
            [
                'nombre' => 'Intratecal',
                'descripcion' => 'Inyección en espacio subaracnoideo',
                'abreviatura' => 'IT',
                'activo' => true
            ],
            [
                'nombre' => 'Epidural',
                'descripcion' => 'Inyección en espacio epidural',
                'abreviatura' => 'EP',
                'activo' => true
            ],
            [
                'nombre' => 'Intraósea',
                'descripcion' => 'Inyección directa en médula ósea',
                'abreviatura' => 'IO',
                'activo' => true
            ],

            // Vías tópicas
            [
                'nombre' => 'Tópica cutánea',
                'descripcion' => 'Aplicación sobre la piel',
                'abreviatura' => 'TOP',
                'activo' => true
            ],
            [
                'nombre' => 'Transdérmica',
                'descripcion' => 'Absorción sistémica a través de la piel',
                'abreviatura' => 'TD',
                'activo' => true
            ],
            [
                'nombre' => 'Oftálmica',
                'descripcion' => 'Aplicación en el ojo',
                'abreviatura' => 'OFT',
                'activo' => true
            ],
            [
                'nombre' => 'Ótica',
                'descripcion' => 'Aplicación en el oído',
                'abreviatura' => 'OT',
                'activo' => true
            ],
            [
                'nombre' => 'Nasal',
                'descripcion' => 'Aplicación en cavidad nasal',
                'abreviatura' => 'NAS',
                'activo' => true
            ],
            [
                'nombre' => 'Vaginal',
                'descripcion' => 'Aplicación en cavidad vaginal',
                'abreviatura' => 'VAG',
                'activo' => true
            ],

            // Vías respiratorias
            [
                'nombre' => 'Inhalatoria',
                'descripcion' => 'Inhalación para absorción pulmonar',
                'abreviatura' => 'INH',
                'activo' => true
            ],
            [
                'nombre' => 'Nebulización',
                'descripcion' => 'Administración por nebulizador',
                'abreviatura' => 'NEB',
                'activo' => true
            ],

            // Vías especiales
            [
                'nombre' => 'Intraventricular',
                'descripcion' => 'Inyección en ventrículo cerebral',
                'abreviatura' => 'IVT',
                'activo' => true
            ],
            [
                'nombre' => 'Intraarticular',
                'descripcion' => 'Inyección en articulación',
                'abreviatura' => 'IA',
                'activo' => true
            ],
            [
                'nombre' => 'Intracardiaca',
                'descripcion' => 'Inyección directa en corazón',
                'abreviatura' => 'IC',
                'activo' => true
            ]
        ];

        foreach ($vias as $via) {
            ViaAdministracion::firstOrCreate(
                ['nombre' => $via['nombre']],
                $via
            );
        }

        echo "  ✅ " . count($vias) . " vías de administración creadas\n";
    }

    private function seedUnidadesMedida(): void
    {
        $unidades = [
            // Unidades de peso/masa
            [
                'nombre' => 'Kilogramo',
                'simbolo' => 'kg',
                'tipo_unidad' => 'peso',
                'factor_conversion_base' => 1000000.0,
                'unidad_base' => 'mg',
                'activo' => true
            ],
            [
                'nombre' => 'Gramo',
                'simbolo' => 'g',
                'tipo_unidad' => 'peso',
                'factor_conversion_base' => 1000.0,
                'unidad_base' => 'mg',
                'activo' => true
            ],
            [
                'nombre' => 'Miligramo',
                'simbolo' => 'mg',
                'tipo_unidad' => 'peso',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'mg',
                'activo' => true
            ],
            [
                'nombre' => 'Microgramo',
                'simbolo' => 'mcg',
                'tipo_unidad' => 'peso',
                'factor_conversion_base' => 0.001,
                'unidad_base' => 'mg',
                'activo' => true
            ],
            [
                'nombre' => 'Nanogramo',
                'simbolo' => 'ng',
                'tipo_unidad' => 'peso',
                'factor_conversion_base' => 0.000001,
                'unidad_base' => 'mg',
                'activo' => true
            ],

            // Unidades de volumen
            [
                'nombre' => 'Litro',
                'simbolo' => 'L',
                'tipo_unidad' => 'volumen',
                'factor_conversion_base' => 1000.0,
                'unidad_base' => 'mL',
                'activo' => true
            ],
            [
                'nombre' => 'Mililitro',
                'simbolo' => 'mL',
                'tipo_unidad' => 'volumen',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'mL',
                'activo' => true
            ],
            [
                'nombre' => 'Microlitro',
                'simbolo' => 'μL',
                'tipo_unidad' => 'volumen',
                'factor_conversion_base' => 0.001,
                'unidad_base' => 'mL',
                'activo' => true
            ],
            [
                'nombre' => 'Gota',
                'simbolo' => 'gtt',
                'tipo_unidad' => 'volumen',
                'factor_conversion_base' => 0.05,
                'unidad_base' => 'mL',
                'activo' => true
            ],

            // Unidades de concentración
            [
                'nombre' => 'Miligramo por mililitro',
                'simbolo' => 'mg/mL',
                'tipo_unidad' => 'concentracion',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'mg/mL',
                'activo' => true
            ],
            [
                'nombre' => 'Microgramo por mililitro',
                'simbolo' => 'mcg/mL',
                'tipo_unidad' => 'concentracion',
                'factor_conversion_base' => 0.001,
                'unidad_base' => 'mg/mL',
                'activo' => true
            ],
            [
                'nombre' => 'Porcentaje peso/peso',
                'simbolo' => '% p/p',
                'tipo_unidad' => 'concentracion',
                'factor_conversion_base' => 10.0,
                'unidad_base' => 'mg/mL',
                'activo' => true
            ],
            [
                'nombre' => 'Porcentaje peso/volumen',
                'simbolo' => '% p/v',
                'tipo_unidad' => 'concentracion',
                'factor_conversion_base' => 10.0,
                'unidad_base' => 'mg/mL',
                'activo' => true
            ],
            [
                'nombre' => 'Partes por millón',
                'simbolo' => 'ppm',
                'tipo_unidad' => 'concentracion',
                'factor_conversion_base' => 0.001,
                'unidad_base' => 'mg/mL',
                'activo' => true
            ],

            // Unidades internacionales
            [
                'nombre' => 'Unidad Internacional',
                'simbolo' => 'UI',
                'tipo_unidad' => 'actividad',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'UI',
                'activo' => true
            ],
            [
                'nombre' => 'Mili-Unidad Internacional',
                'simbolo' => 'mUI',
                'tipo_unidad' => 'actividad',
                'factor_conversion_base' => 0.001,
                'unidad_base' => 'UI',
                'activo' => true
            ],
            [
                'nombre' => 'Micro-Unidad Internacional',
                'simbolo' => 'μUI',
                'tipo_unidad' => 'actividad',
                'factor_conversion_base' => 0.000001,
                'unidad_base' => 'UI',
                'activo' => true
            ],

            // Unidades especiales
            [
                'nombre' => 'Miliequivalente',
                'simbolo' => 'mEq',
                'tipo_unidad' => 'actividad',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'mEq',
                'activo' => true
            ],
            [
                'nombre' => 'Miliosmol',
                'simbolo' => 'mOsm',
                'tipo_unidad' => 'actividad',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'mOsm',
                'activo' => true
            ],
            [
                'nombre' => 'Comprimido',
                'simbolo' => 'comp',
                'tipo_unidad' => 'unitario',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'comp',
                'activo' => true
            ],
            [
                'nombre' => 'Cápsula',
                'simbolo' => 'cap',
                'tipo_unidad' => 'unitario',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'cap',
                'activo' => true
            ],
            [
                'nombre' => 'Tableta',
                'simbolo' => 'tab',
                'tipo_unidad' => 'unitario',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'tab',
                'activo' => true
            ],
            [
                'nombre' => 'Ampolla',
                'simbolo' => 'amp',
                'tipo_unidad' => 'unitario',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'amp',
                'activo' => true
            ],
            [
                'nombre' => 'Vial',
                'simbolo' => 'vial',
                'tipo_unidad' => 'unitario',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'vial',
                'activo' => true
            ],
            [
                'nombre' => 'Sobre',
                'simbolo' => 'sob',
                'tipo_unidad' => 'unitario',
                'factor_conversion_base' => 1.0,
                'unidad_base' => 'sob',
                'activo' => true
            ]
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::firstOrCreate(
                ['simbolo' => $unidad['simbolo']],
                $unidad
            );
        }

        echo "  ✅ " . count($unidades) . " unidades de medida creadas\n";
    }
}
