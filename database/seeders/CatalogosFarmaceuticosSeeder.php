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
                'tipo' => 'Sólida',
                'descripcion' => 'Forma farmacéutica sólida obtenida por compresión'
            ],
            [
                'nombre' => 'Cápsula',
                'tipo' => 'Sólida',
                'descripcion' => 'Forma farmacéutica sólida con cubierta de gelatina'
            ],
            [
                'nombre' => 'Comprimido',
                'tipo' => 'Sólida',
                'descripcion' => 'Forma farmacéutica sólida compacta'
            ],
            [
                'nombre' => 'Comprimido recubierto',
                'tipo' => 'Sólida',
                'descripcion' => 'Comprimido con recubrimiento entérico o de liberación controlada'
            ],
            [
                'nombre' => 'Tableta masticable',
                'tipo' => 'Sólida',
                'descripcion' => 'Tableta diseñada para ser masticada antes de tragar'
            ],
            [
                'nombre' => 'Tableta sublingual',
                'tipo' => 'Sólida',
                'descripcion' => 'Tableta para disolución bajo la lengua'
            ],
            [
                'nombre' => 'Tableta efervescente',
                'tipo' => 'Sólida',
                'descripcion' => 'Tableta que se disuelve en agua formando burbujas'
            ],
            [
                'nombre' => 'Polvo',
                'tipo' => 'Sólida',
                'descripcion' => 'Preparación farmacéutica en forma de polvo fino'
            ],
            [
                'nombre' => 'Granulado',
                'tipo' => 'Sólida',
                'descripcion' => 'Agregados de partículas de polvo'
            ],
            [
                'nombre' => 'Sobres',
                'tipo' => 'Sólida',
                'descripcion' => 'Polvo o granulado envasado en sobres unitarios'
            ],

            // Formas líquidas
            [
                'nombre' => 'Jarabe',
                'tipo' => 'Líquida',
                'descripcion' => 'Solución acuosa azucarada para administración oral'
            ],
            [
                'nombre' => 'Suspensión',
                'tipo' => 'Líquida',
                'descripcion' => 'Dispersión de sólidos finamente divididos en líquido'
            ],
            [
                'nombre' => 'Solución oral',
                'tipo' => 'Líquida',
                'descripcion' => 'Preparación líquida para administración por vía oral'
            ],
            [
                'nombre' => 'Gotas orales',
                'tipo' => 'Líquida',
                'descripcion' => 'Solución en gotas para administración oral'
            ],
            [
                'nombre' => 'Elixir',
                'tipo' => 'Líquida',
                'descripcion' => 'Preparación líquida hidroalcohólica edulcorada'
            ],
            [
                'nombre' => 'Emulsión',
                'tipo' => 'Líquida',
                'descripcion' => 'Sistema disperso de dos líquidos inmiscibles'
            ],

            // Formas inyectables
            [
                'nombre' => 'Ampolla',
                'tipo' => 'Inyectable',
                'descripcion' => 'Envase de vidrio sellado para inyectables'
            ],
            [
                'nombre' => 'Vial',
                'tipo' => 'Inyectable',
                'descripcion' => 'Frasco de vidrio para inyectables con tapón'
            ],
            [
                'nombre' => 'Jeringa prellenada',
                'tipo' => 'Inyectable',
                'descripcion' => 'Jeringa lista para usar con medicamento precargado'
            ],
            [
                'nombre' => 'Solución inyectable',
                'tipo' => 'Inyectable',
                'descripcion' => 'Preparación estéril para administración parenteral'
            ],
            [
                'nombre' => 'Polvo para inyección',
                'tipo' => 'Inyectable',
                'descripcion' => 'Polvo estéril para reconstituir antes de inyectar'
            ],

            // Formas tópicas
            [
                'nombre' => 'Crema',
                'tipo' => 'Tópica',
                'descripcion' => 'Emulsión semisólida para aplicación tópica'
            ],
            [
                'nombre' => 'Pomada',
                'tipo' => 'Tópica',
                'descripcion' => 'Preparación semisólida grasa para uso tópico'
            ],
            [
                'nombre' => 'Gel',
                'tipo' => 'Tópica',
                'descripcion' => 'Sistema coloidal semisólido transparente'
            ],
            [
                'nombre' => 'Loción',
                'tipo' => 'Tópica',
                'descripcion' => 'Preparación líquida para aplicación externa'
            ],
            [
                'nombre' => 'Parche transdérmico',
                'tipo' => 'Tópica',
                'descripcion' => 'Sistema de liberación controlada a través de la piel'
            ],

            // Formas especiales
            [
                'nombre' => 'Supositorio',
                'tipo' => 'Especial',
                'descripcion' => 'Forma farmacéutica sólida para inserción rectal o vaginal'
            ],
            [
                'nombre' => 'Óvulo vaginal',
                'tipo' => 'Especial',
                'descripcion' => 'Forma farmacéutica sólida para inserción vaginal'
            ],
            [
                'nombre' => 'Aerosol',
                'tipo' => 'Especial',
                'descripcion' => 'Dispersión de partículas líquidas o sólidas en gas'
            ],
            [
                'nombre' => 'Inhalador',
                'tipo' => 'Especial',
                'descripcion' => 'Dispositivo para administración por vía respiratoria'
            ],
            [
                'nombre' => 'Colirio',
                'tipo' => 'Especial',
                'descripcion' => 'Solución estéril para aplicación ocular'
            ],
            [
                'nombre' => 'Gotas nasales',
                'tipo' => 'Especial',
                'descripcion' => 'Solución para aplicación en cavidad nasal'
            ],
            [
                'nombre' => 'Spray nasal',
                'tipo' => 'Especial',
                'descripcion' => 'Pulverización para administración nasal'
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
                'abreviatura' => 'PO',
                'descripcion' => 'Administración por la boca, deglución al estómago'
            ],
            [
                'nombre' => 'Sublingual',
                'abreviatura' => 'SL',
                'descripcion' => 'Absorción bajo la lengua'
            ],
            [
                'nombre' => 'Bucal',
                'abreviatura' => 'BUC',
                'descripcion' => 'Absorción a través de la mucosa bucal'
            ],
            [
                'nombre' => 'Rectal',
                'abreviatura' => 'PR',
                'descripcion' => 'Administración por vía rectal (supositorios)'
            ],

            // Vías parenterales
            [
                'nombre' => 'Intravenosa',
                'abreviatura' => 'IV',
                'descripcion' => 'Inyección directa en vena'
            ],
            [
                'nombre' => 'Intramuscular',
                'abreviatura' => 'IM',
                'descripcion' => 'Inyección en músculo'
            ],
            [
                'nombre' => 'Subcutánea',
                'abreviatura' => 'SC',
                'descripcion' => 'Inyección en tejido subcutáneo'
            ],
            [
                'nombre' => 'Intradérmica',
                'abreviatura' => 'ID',
                'descripcion' => 'Inyección en dermis'
            ],
            [
                'nombre' => 'Intraperitoneal',
                'abreviatura' => 'IP',
                'descripcion' => 'Inyección en cavidad peritoneal'
            ],
            [
                'nombre' => 'Intratecal',
                'abreviatura' => 'IT',
                'descripcion' => 'Inyección en espacio subaracnoideo'
            ],
            [
                'nombre' => 'Epidural',
                'abreviatura' => 'EP',
                'descripcion' => 'Inyección en espacio epidural'
            ],
            [
                'nombre' => 'Intraósea',
                'abreviatura' => 'IO',
                'descripcion' => 'Inyección directa en médula ósea'
            ],

            // Vías tópicas
            [
                'nombre' => 'Tópica cutánea',
                'abreviatura' => 'TOPC',
                'descripcion' => 'Aplicación sobre la piel'
            ],
            [
                'nombre' => 'Transdérmica',
                'abreviatura' => 'TD',
                'descripcion' => 'Absorción sistémica a través de la piel'
            ],
            [
                'nombre' => 'Oftálmica',
                'abreviatura' => 'OFT',
                'descripcion' => 'Aplicación en el ojo'
            ],
            [
                'nombre' => 'Ótica',
                'abreviatura' => 'OT',
                'descripcion' => 'Aplicación en el oído'
            ],
            [
                'nombre' => 'Nasal',
                'abreviatura' => 'NAS',
                'descripcion' => 'Aplicación en cavidad nasal'
            ],
            [
                'nombre' => 'Vaginal',
                'abreviatura' => 'VAG',
                'descripcion' => 'Aplicación en cavidad vaginal'
            ],

            // Vías respiratorias
            [
                'nombre' => 'Inhalatoria',
                'abreviatura' => 'INH',
                'descripcion' => 'Inhalación para absorción pulmonar'
            ],
            [
                'nombre' => 'Nebulización',
                'abreviatura' => 'NEB',
                'descripcion' => 'Administración por nebulizador'
            ],

            // Vías especiales
            [
                'nombre' => 'Intraventricular',
                'abreviatura' => 'IVT',
                'descripcion' => 'Inyección en ventrículo cerebral'
            ],
            [
                'nombre' => 'Intraarticular',
                'abreviatura' => 'IA',
                'descripcion' => 'Inyección en articulación'
            ],
            [
                'nombre' => 'Intracardiaca',
                'abreviatura' => 'IC',
                'descripcion' => 'Inyección directa en corazón'
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
                'tipo' => 'peso',
                'equivalencia_base' => 1000000.0
            ],
            [
                'nombre' => 'Gramo',
                'tipo' => 'peso',
                'equivalencia_base' => 1000.0
            ],
            [
                'nombre' => 'Miligramo',
                'tipo' => 'peso',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'Microgramo',
                'tipo' => 'peso',
                'equivalencia_base' => 0.001
            ],
            [
                'nombre' => 'Nanogramo',
                'tipo' => 'peso',
                'equivalencia_base' => 0.000001
            ],

            // Unidades de volumen
            [
                'nombre' => 'Litro',
                'tipo' => 'volumen',
                'equivalencia_base' => 1000.0
            ],
            [
                'nombre' => 'Mililitro',
                'tipo' => 'volumen',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'Microlitro',
                'tipo' => 'volumen',
                'equivalencia_base' => 0.001
            ],
            [
                'nombre' => 'Gota',
                'tipo' => 'volumen',
                'equivalencia_base' => 0.05
            ],

            // Unidades de concentración
            [
                'nombre' => 'mg/mL',
                'tipo' => 'concentracion',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'mcg/mL',
                'tipo' => 'concentracion',
                'equivalencia_base' => 0.001
            ],
            [
                'nombre' => '% p/p',
                'tipo' => 'concentracion',
                'equivalencia_base' => 10.0
            ],
            [
                'nombre' => '% p/v',
                'tipo' => 'concentracion',
                'equivalencia_base' => 10.0
            ],
            [
                'nombre' => 'ppm',
                'tipo' => 'concentracion',
                'equivalencia_base' => 0.001
            ],

            // Unidades internacionales
            [
                'nombre' => 'UI',
                'tipo' => 'actividad',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'mUI',
                'tipo' => 'actividad',
                'equivalencia_base' => 0.001
            ],
            [
                'nombre' => 'μUI',
                'tipo' => 'actividad',
                'equivalencia_base' => 0.000001
            ],

            // Unidades especiales
            [
                'nombre' => 'mEq',
                'tipo' => 'actividad',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'mOsm',
                'tipo' => 'actividad',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'comp',
                'tipo' => 'unitario',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'cap',
                'tipo' => 'unitario',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'tab',
                'tipo' => 'unitario',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'amp',
                'tipo' => 'unitario',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'vial',
                'tipo' => 'unitario',
                'equivalencia_base' => 1.0
            ],
            [
                'nombre' => 'sob',
                'tipo' => 'unitario',
                'equivalencia_base' => 1.0
            ]
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::firstOrCreate(
                ['nombre' => $unidad['nombre']],
                $unidad
            );
        }

        echo "  ✅ " . count($unidades) . " unidades de medida creadas\n";
    }
}
