<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tratamiento;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Medicamento;
use Carbon\Carbon;

class TratamientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener pacientes y médicos existentes
        $pacientes = Paciente::all();
        $medicos = User::whereHas('role', function($query) {
            $query->where('nombre', 'medico');
        })->get();
        $medicamentos = Medicamento::where('activo', true)->get();

        if ($pacientes->isEmpty() || $medicos->isEmpty() || $medicamentos->isEmpty()) {
            $this->command->info('No hay suficientes datos para crear tratamientos. Asegúrate de tener pacientes, médicos y medicamentos.');
            return;
        }

        $tratamientos = [
            [
                'nombre' => 'Tratamiento Hipertensión',
                'tipo' => 'Programado',
                'objetivo' => 'Control de presión arterial y prevención de complicaciones cardiovasculares',
                'fecha_inicio' => Carbon::now()->subDays(30),
                'fecha_fin' => Carbon::now()->addDays(60),
                'estado' => 'Activo',
                'observaciones' => 'Paciente responde bien al tratamiento. Controlar presión semanalmente.',
                'medicamentos' => ['Losartán 50mg', 'Amlodipino 5mg']
            ],
            [
                'nombre' => 'Tratamiento Diabetes Tipo 2',
                'tipo' => 'Programado',
                'objetivo' => 'Control glucémico y prevención de complicaciones diabéticas',
                'fecha_inicio' => Carbon::now()->subDays(15),
                'fecha_fin' => Carbon::now()->addDays(90),
                'estado' => 'Activo',
                'observaciones' => 'Ajustar dosis según niveles de glucosa. Dieta balanceada.',
                'medicamentos' => ['Metformina 850mg']
            ],
            [
                'nombre' => 'Antibiótico Infección Respiratoria',
                'tipo' => 'Programado',
                'objetivo' => 'Eliminación de infección bacteriana del tracto respiratorio',
                'fecha_inicio' => Carbon::now()->subDays(3),
                'fecha_fin' => Carbon::now()->addDays(7),
                'estado' => 'Activo',
                'observaciones' => 'Completar curso completo de antibiótico. Evaluar evolución en 5 días.',
                'medicamentos' => ['Amoxicilina 500mg']
            ],
            [
                'nombre' => 'Manejo del Dolor Crónico',
                'tipo' => 'PRN',
                'objetivo' => 'Control del dolor según necesidad del paciente',
                'fecha_inicio' => Carbon::now()->subDays(60),
                'fecha_fin' => null,
                'estado' => 'Activo',
                'observaciones' => 'Administrar según escala de dolor. Máximo 3 dosis por día.',
                'medicamentos' => ['Ibuprofeno 400mg', 'Paracetamol 500mg']
            ],
            [
                'nombre' => 'Tratamiento Ansiedad',
                'tipo' => 'Programado',
                'objetivo' => 'Estabilización del estado emocional y reducción de síntomas ansiosos',
                'fecha_inicio' => Carbon::now()->subDays(45),
                'fecha_fin' => Carbon::now()->addDays(120),
                'estado' => 'Activo',
                'observaciones' => 'Paciente muestra mejoría. Continuar con terapia psicológica.',
                'medicamentos' => ['Sertralina 50mg']
            ],
            [
                'nombre' => 'Tratamiento Finalizado - Gripe',
                'tipo' => 'Programado',
                'objetivo' => 'Alivio de síntomas gripales y recuperación completa',
                'fecha_inicio' => Carbon::now()->subDays(20),
                'fecha_fin' => Carbon::now()->subDays(10),
                'estado' => 'Completado',
                'observaciones' => 'Paciente recuperado completamente. Tratamiento exitoso.',
                'medicamentos' => ['Paracetamol 500mg']
            ],
            [
                'nombre' => 'Tratamiento Pausado - Alergia',
                'tipo' => 'Programado',
                'objetivo' => 'Control de reacción alérgica estacional',
                'fecha_inicio' => Carbon::now()->subDays(10),
                'fecha_fin' => Carbon::now()->addDays(30),
                'estado' => 'Pausado',
                'observaciones' => 'Pausado por reacción adversa. Evaluar alternativas.',
                'medicamentos' => ['Loratadina 10mg']
            ]
        ];

        foreach ($tratamientos as $index => $tratamientoData) {
            $paciente = $pacientes->random();
            $medico = $medicos->random();

            $tratamiento = Tratamiento::create([
                'paciente_id' => $paciente->id,
                'medico_usuario_id' => $medico->id,
                'nombre' => $tratamientoData['nombre'],
                'tipo' => $tratamientoData['tipo'],
                'objetivo' => $tratamientoData['objetivo'],
                'fecha_inicio' => $tratamientoData['fecha_inicio'],
                'fecha_fin' => $tratamientoData['fecha_fin'],
                'estado' => $tratamientoData['estado'],
                'observaciones' => $tratamientoData['observaciones']
            ]);

            // Asociar medicamentos al tratamiento
            foreach ($tratamientoData['medicamentos'] as $nombreMedicamento) {
                $medicamento = $medicamentos->where('nombre', 'like', '%' . explode(' ', $nombreMedicamento)[0] . '%')->first();
                
                if ($medicamento) {
                    $pivotData = [
                        'dosis_cantidad' => rand(1, 3),
                        'unidad_dosis' => $medicamento->unidad_concentracion === 'mg' ? 'tableta' : 'ml',
                        'instrucciones_especiales' => 'Tomar con alimentos',
                        'estado' => 'Activo',
                        'orden' => 1
                    ];

                    // Si es programado, agregar frecuencia en horas
                    if ($tratamientoData['tipo'] === 'Programado') {
                        $pivotData['frecuencia_horas'] = 8; // Cada 8 horas
                        $pivotData['tolerancia_antes_minutos'] = 30;
                        $pivotData['tolerancia_despues_minutos'] = 60;
                    } else {
                        // Si es PRN, configurar límites
                        $pivotData['intervalo_minimo_horas'] = 4; // Mínimo 4 horas entre dosis
                        $pivotData['dosis_maxima_dia'] = 3; // Máximo 3 dosis por día
                        $pivotData['dosis_maxima_consecutiva'] = 2; // Máximo 2 dosis seguidas
                    }

                    $tratamiento->medicamentos()->attach($medicamento->id, $pivotData);
                }
            }

            $this->command->info("Tratamiento creado: {$tratamientoData['nombre']} para paciente {$paciente->nombre}");
        }

        $this->command->info('TratamientoSeeder completado. Se crearon ' . count($tratamientos) . ' tratamientos.');
    }
}
