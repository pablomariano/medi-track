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
            // Tratamientos PROGRAMADOS únicamente
            [
                'paciente_id' => $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Control Hipertensión',
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
                'nombre' => 'Control Diabetes',
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
            [
                'paciente_id' => $pacientes->skip(2)->first()->id ?? $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Control de Ansiedad',
                'diagnostico' => 'Trastorno de ansiedad generalizada',
                'tipo' => 'Programado',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addMonths(1)->format('Y-m-d'),
                'observaciones' => 'Tratamiento ansiolítico programado. Seguimiento semanal.'
            ],
            [
                'paciente_id' => $pacientes->skip(2)->first()->id ?? $pacientes->first()->id,
                'medico_usuario_id' => $medicos->first()->usuario_id,
                'nombre' => 'Suplementación Vitamínica',
                'diagnostico' => 'Deficiencia de vitaminas del complejo B',
                'tipo' => 'Programado',
                'estado' => 'Activo',
                'fecha_inicio' => now()->format('Y-m-d'),
                'fecha_fin_estimada' => now()->addMonths(3)->format('Y-m-d'),
                'observaciones' => 'Suplemento vitamínico diario con las comidas.'
            ]
        ];

        foreach ($tratamientos as $tratamiento) {
            DB::table('tratamientos')->insertOrIgnore([
                'paciente_id' => $tratamiento['paciente_id'],
                'medico_usuario_id' => $tratamiento['medico_usuario_id'], // Añadir este campo
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
