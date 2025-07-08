<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Medicamento;
use App\Models\MedicamentoTratamiento;
use App\Models\HorarioProgramado;
use App\Models\Administracion;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AnaLopezRealisticAdherenceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buscar a Ana López
        $ana = Paciente::where('nombre', 'Ana López')->first();
        if (!$ana) {
            $this->command->error('No se encontró a Ana López.');
            return;
        }

        // 2. Crear tratamiento activo hace 14 días
        $fechaInicio = Carbon::now()->subDays(14)->startOfDay();
        $tratamiento = Tratamiento::firstOrCreate([
            'paciente_id' => $ana->id,
            'nombre' => 'Tratamiento Realista Seeder',
            'fecha_inicio' => $fechaInicio->toDateString(),
        ], [
            'estado' => 'Activo',
            'medico_usuario_id' => 2,
        ]);

        // 3. Crear medicamento y asociar
        $medicamento = Medicamento::firstOrCreate([
            'nombre' => 'Paracetamol Seeder',
        ], [
            'medida' => 'mg',
            'unidad_medida' => 'tableta',
        ]);

        $medTrat = MedicamentoTratamiento::firstOrCreate([
            'tratamiento_id' => $tratamiento->id,
            'medicamento_id' => $medicamento->id,
        ], [
            //'dosis' => '1 tableta', // ELIMINADO
            //'frecuencia' => 'variable', // ELIMINADO
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'frecuencia_horas' => 8,
            'duracion_dias' => 14,
        ]);

        // 4. Crear horarios programados variables (1-3 por día, variando por día de la semana)
        $horasPorDia = [
            0 => ['08:00'], // Domingo: 1 toma
            1 => ['08:00', '20:00'], // Lunes: 2 tomas
            2 => ['08:00', '14:00', '20:00'], // Martes: 3 tomas
            3 => ['08:00', '20:00'], // Miércoles: 2 tomas
            4 => ['08:00'], // Jueves: 1 toma
            5 => ['08:00', '14:00'], // Viernes: 2 tomas
            6 => ['08:00', '20:00'], // Sábado: 2 tomas
        ];

        // Limpiar horarios y administraciones previas de este tratamiento en el rango
        $horarios = HorarioProgramado::where('paciente_id', $ana->id)
            ->where('medicamento_tratamiento_id', $medTrat->id)
            ->get();
        foreach ($horarios as $h) {
            Administracion::where('horario_programado_id', $h->id)
                ->whereDate('fecha_hora_programada', '>=', $fechaInicio->toDateString())
                ->delete();
            $h->delete();
        }

        // 5. Crear nuevos horarios programados para los próximos 14 días
        $horariosNuevos = [];
        foreach ($horasPorDia as $dow => $horas) {
            foreach ($horas as $hora) {
                $horariosNuevos[] = HorarioProgramado::create([
                    'medicamento_tratamiento_id' => $medTrat->id,
                    'paciente_id' => $ana->id,
                    'hora_programada' => $hora,
                    'dias_semana' => (string)$dow,
                    'fecha_inicio' => $fechaInicio->toDateString(),
                    'fecha_fin' => null,
                    'activo' => true,
                ]);
            }
        }

        // 6. Generar administraciones realistas para los últimos 14 días
        for ($i = 0; $i < 14; $i++) {
            $fecha = $fechaInicio->copy()->addDays($i);
            $dow = $fecha->dayOfWeek; // 0=domingo
            $horas = $horasPorDia[$dow];
            foreach ($horas as $hora) {
                $fechaProgramada = $fecha->copy()->setTimeFromTimeString($hora);
                // Simular variación realista: -20 a +40 minutos
                $variacion = rand(-20, 40);
                $fechaAdministrada = $fechaProgramada->copy()->addMinutes($variacion);
                $adelanto = $variacion < 0 ? abs($variacion) : 0;
                $retraso = $variacion > 0 ? $variacion : 0;
                // Score: 100 si |variacion| <= 15, decae exponencialmente después
                $score = abs($variacion) <= 15 ? 100 : round(100 * exp(-0.05 * (abs($variacion) - 15)), 2);
                // Categoría
                if ($variacion <= -30) {
                    $categoria = 'muy_temprano';
                } elseif ($variacion < -15) {
                    $categoria = 'temprano';
                } elseif ($variacion <= 15) {
                    $categoria = 'puntual';
                } elseif ($variacion <= 30) {
                    $categoria = 'tardio';
                } else {
                    $categoria = 'muy_tardio';
                }
                // Buscar horario programado
                $horario = HorarioProgramado::where('paciente_id', $ana->id)
                    ->where('medicamento_tratamiento_id', $medTrat->id)
                    ->where('hora_programada', $hora)
                    ->where('dias_semana', (string)$dow)
                    ->first();
                if (!$horario) continue;
                // Crear administración
                Administracion::create([
                    'paciente_id' => $ana->id,
                    'medicamento_tratamiento_id' => $medTrat->id,
                    'horario_programado_id' => $horario->id,
                    'fecha_hora_programada' => $fechaProgramada,
                    'fecha_hora_administrada' => $fechaAdministrada,
                    'dosis_administrada' => 1,
                    'minutos_adelanto' => $adelanto,
                    'minutos_retraso' => $retraso,
                    'score_puntualidad' => $score,
                    'categoria_temporal' => $categoria,
                    'estado' => 'administrada',
                    'created_at' => $fechaAdministrada,
                    'updated_at' => $fechaAdministrada,
                ]);
            }
        }
        $this->command->info('Administraciones realistas generadas para Ana López.');
    }
} 