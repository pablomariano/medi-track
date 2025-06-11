<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TratamientosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunos pacientes y médicos para referenciar
        $pacientes = DB::table('pacientes')->limit(3)->get();
        $medicos = DB::table('personal_medico')->limit(2)->get();
        
        if ($pacientes->isEmpty() || $medicos->isEmpty()) {
            $this->command->info('No hay pacientes o médicos disponibles. Ejecutar seeders de usuarios primero.');
            return;
        }

        $tratamientos = [
            // Tratamientos PROGRAMADOS
            [
                'paciente_id' => $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Control de Hipertensión',
                'diagnostico' => 'Hipertensión arterial esencial',
                'tipo' => 'Programado',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addMonths(3)->format('Y-m-d'),
                'observaciones' => 'Control estricto de presión arterial. Monitoreo diario.'
            ],
            [
                'paciente_id' => $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Control de Diabetes',
                'diagnostico' => 'Diabetes mellitus tipo 2',
                'tipo' => 'Programado',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addMonths(6)->format('Y-m-d'),
                'observaciones' => 'Control glicémico con metformina. Dieta y ejercicio.'
            ],
            [
                'paciente_id' => $pacientes->skip(1)->first()->id ?? $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Antibioticoterapia',
                'diagnostico' => 'Infección respiratoria alta',
                'tipo' => 'Programado',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addDays(7)->format('Y-m-d'),
                'observaciones' => 'Tratamiento antibiótico por 7 días. Importante completar ciclo.'
            ],
            [
                'paciente_id' => $pacientes->skip(1)->first()->id ?? $pacientes->first()->id,
                'medico_usuario_id' => $medicos->count() > 1 ? $medicos->skip(1)->first()->usuario_id : $medicos->first()->usuario_id,
                'nombre' => 'Protección Gástrica',
                'diagnostico' => 'Gastritis crónica',
                'tipo' => 'Programado',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addMonths(2)->format('Y-m-d'),
                'observaciones' => 'Inhibidor de bomba de protones para protección gástrica.'
            ],
            
            // Tratamientos PRN
            [
                'paciente_id' => $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Manejo de Dolor PRN',
                'diagnostico' => 'Dolor crónico musculoesquelético',
                'tipo' => 'PRN',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addMonths(1)->format('Y-m-d'),
                'observaciones' => 'Analgésicos PRN para episodios de dolor. Evaluar efectividad.'
            ],
            [
                'paciente_id' => $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Control de Fiebre PRN',
                'diagnostico' => 'Síndrome febril',
                'tipo' => 'PRN',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addDays(14)->format('Y-m-d'),
                'observaciones' => 'Antipiréticos PRN para fiebre >38°C.'
            ],
            [
                'paciente_id' => $pacientes->skip(1)->first()->id ?? $pacientes->first()->id,
                'medico_usuario_id' => $medicos->count() > 1 ? $medicos->skip(1)->first()->usuario_id : $medicos->first()->usuario_id,
                'nombre' => 'Manejo de Náuseas PRN',
                'diagnostico' => 'Náuseas post-quimioterapia',
                'tipo' => 'PRN',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addDays(21)->format('Y-m-d'),
                'observaciones' => 'Antieméticos PRN para episodios de náuseas.'
            ],
            [
                'paciente_id' => $pacientes->skip(2)->first()->id ?? $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Crisis de Ansiedad PRN',
                'diagnostico' => 'Trastorno de ansiedad generalizada',
                'tipo' => 'PRN',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addMonths(1)->format('Y-m-d'),
                'observaciones' => 'Ansiolítico PRN para crisis. Máximo 2 dosis/día.'
            ],
            [
                'paciente_id' => $pacientes->skip(2)->first()->id ?? $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Insomnio PRN',
                'diagnostico' => 'Trastorno del sueño',
                'tipo' => 'PRN',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addDays(14)->format('Y-m-d'),
                'observaciones' => 'Hipnótico PRN para insomnio. Uso ocasional.'
            ],
            
            // Tratamiento PRN de emergencia
            [
                'paciente_id' => $pacientes->skip(1)->first()->id ?? $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Crisis Asmática PRN',
                'diagnostico' => 'Asma bronquial',
                'tipo' => 'PRN',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addMonths(3)->format('Y-m-d'),
                'observaciones' => 'Broncodilatador PRN para crisis asmáticas.'
            ]
        ];

        foreach ($tratamientos as $tratamiento) {
            DB::table('tratamientos')->insertOrIgnore([
                'paciente_id' => $tratamiento['paciente_id'],
                'nombre' => $tratamiento['nombre'],
                'diagnostico' => $tratamiento['diagnostico'],
                'tipo' => $tratamiento['tipo'],
                'estado' => $tratamiento['estado'],
                'fecha_inicio' => $tratamiento['fecha_inicio'],
                'fecha_fin' => $tratamiento['fecha_fin_estimada'], // Map to the correct column
                'observaciones' => $tratamiento['observaciones'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
