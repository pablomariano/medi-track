<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Datos básicos para unidades de medida
        DB::table('unidades_medida')->insert([
            ['nombre' => 'mg', 'tipo' => 'peso', 'equivalencia_base' => 1.0, 'unidad_base_id' => null],
            ['nombre' => 'g', 'tipo' => 'peso', 'equivalencia_base' => 1000.0, 'unidad_base_id' => 1],
            ['nombre' => 'ml', 'tipo' => 'volumen', 'equivalencia_base' => 1.0, 'unidad_base_id' => null],
            ['nombre' => 'L', 'tipo' => 'volumen', 'equivalencia_base' => 1000.0, 'unidad_base_id' => 3],
            ['nombre' => 'UI', 'tipo' => 'unidad', 'equivalencia_base' => 1.0, 'unidad_base_id' => null],
            ['nombre' => 'tableta', 'tipo' => 'forma', 'equivalencia_base' => 1.0, 'unidad_base_id' => null],
            ['nombre' => 'capsula', 'tipo' => 'forma', 'equivalencia_base' => 1.0, 'unidad_base_id' => null],
        ]);

        // Formas farmacéuticas básicas
        DB::table('formas_farmaceuticas')->insert([
            ['nombre' => 'Tableta', 'tipo' => 'Sólido', 'descripcion' => 'Forma farmacéutica sólida'],
            ['nombre' => 'Cápsula', 'tipo' => 'Sólido', 'descripcion' => 'Forma farmacéutica en cápsula'],
            ['nombre' => 'Jarabe', 'tipo' => 'Líquido', 'descripcion' => 'Forma farmacéutica líquida'],
            ['nombre' => 'Inyección', 'tipo' => 'Líquido', 'descripcion' => 'Forma farmacéutica para inyección'],
            ['nombre' => 'Crema', 'tipo' => 'Tópico', 'descripcion' => 'Forma farmacéutica tópica'],
            ['nombre' => 'Gel', 'tipo' => 'Tópico', 'descripcion' => 'Forma farmacéutica en gel'],
        ]);

        // Vías de administración básicas
        DB::table('vias_administracion')->insert([
            ['nombre' => 'Oral', 'abreviatura' => 'PO', 'descripcion' => 'Administración por vía oral'],
            ['nombre' => 'Intravenosa', 'abreviatura' => 'IV', 'descripcion' => 'Administración intravenosa'],
            ['nombre' => 'Intramuscular', 'abreviatura' => 'IM', 'descripcion' => 'Administración intramuscular'],
            ['nombre' => 'Subcutánea', 'abreviatura' => 'SC', 'descripcion' => 'Administración subcutánea'],
            ['nombre' => 'Tópica', 'abreviatura' => 'TOP', 'descripcion' => 'Aplicación tópica'],
            ['nombre' => 'Sublingual', 'abreviatura' => 'SL', 'descripcion' => 'Administración sublingual'],
        ]);

        // Principios activos básicos para pruebas
        DB::table('principios_activos')->insert([
            [
                'nombre_generico' => 'Paracetamol',
                'nombre_comercial' => 'Acetaminofén',
                'clasificacion_atc' => 'N02BE01',
                'grupo_farmacologico' => 'Analgésico no opioide',
                'descripcion' => 'Analgésico y antipirético',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Ibuprofeno',
                'nombre_comercial' => 'Advil',
                'clasificacion_atc' => 'M01AE01',
                'grupo_farmacologico' => 'AINE',
                'descripcion' => 'Antiinflamatorio no esteroideo',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Amoxicilina',
                'nombre_comercial' => 'Amoxil',
                'clasificacion_atc' => 'J01CA04',
                'grupo_farmacologico' => 'Antibiótico betalactámico',
                'descripcion' => 'Antibiótico de amplio espectro',
                'activo' => true
            ],
            [
                'nombre_generico' => 'Omeprazol',
                'nombre_comercial' => 'Prilosec',
                'clasificacion_atc' => 'A02BC01',
                'grupo_farmacologico' => 'Inhibidor de bomba de protones',
                'descripcion' => 'Reductor de acidez gástrica',
                'activo' => true
            ],
        ]);

        // Medicamentos de ejemplo
        DB::table('medicamentos')->insert([
            [
                'principio_activo_id' => 1, // Paracetamol
                'nombre_comercial' => 'Paracetamol 500mg',
                'forma_farmaceutica_id' => 1, // Tableta
                'concentracion' => 500.0,
                'unidad_concentracion_id' => 1, // mg
                'via_administracion_id' => 1, // Oral
                'laboratorio' => 'Laboratorio Nacional',
                'requiere_receta' => false,
                'controlado' => false,
                'activo' => true
            ],
            [
                'principio_activo_id' => 2, // Ibuprofeno
                'nombre_comercial' => 'Ibuprofeno 400mg',
                'forma_farmaceutica_id' => 1, // Tableta
                'concentracion' => 400.0,
                'unidad_concentracion_id' => 1, // mg
                'via_administracion_id' => 1, // Oral
                'laboratorio' => 'Farmacia Central',
                'requiere_receta' => false,
                'controlado' => false,
                'activo' => true
            ],
            [
                'principio_activo_id' => 3, // Amoxicilina
                'nombre_comercial' => 'Amoxicilina 500mg',
                'forma_farmaceutica_id' => 2, // Cápsula
                'concentracion' => 500.0,
                'unidad_concentracion_id' => 1, // mg
                'via_administracion_id' => 1, // Oral
                'laboratorio' => 'Antibióticos SA',
                'requiere_receta' => true,
                'controlado' => false,
                'activo' => true
            ],
        ]);
    }

    public function down(): void
    {
        // Limpiar datos en orden inverso para respetar foreign keys
        DB::table('medicamentos')->truncate();
        DB::table('principios_activos')->truncate();
        DB::table('vias_administracion')->truncate();
        DB::table('formas_farmaceuticas')->truncate();
        DB::table('unidades_medida')->truncate();
    }
}; 