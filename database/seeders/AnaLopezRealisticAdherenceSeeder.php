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
    private $fechaInicio;
    private $diasTotales;

    public function run(): void
    {
        // 1. Buscar a Ana López
        $ana = Paciente::where('nombre', 'Ana López')->first();
        if (!$ana) {
            $this->command->error('No se encontró a Ana López.');
            return;
        }

        // 2. Inicializar fechas
        $this->fechaInicio = Carbon::now()->subDays(14)->startOfDay();
        $this->diasTotales = 14;

        // 3. Crear tratamiento activo hace 14 días
        $tratamiento = Tratamiento::firstOrCreate([
            'paciente_id' => $ana->id,
            'nombre' => 'Tratamiento Realista Seeder',
            'fecha_inicio' => $this->fechaInicio->toDateString(),
        ], [
            'estado' => 'Activo',
            'medico_usuario_id' => 2,
        ]);

        // 4. Crear medicamento y asociar
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
            'dosis_cantidad' => 1,
            'unidad_dosis' => 'tableta',
            'frecuencia_horas' => 8,
            'duracion_dias' => 14,
        ]);

        // 5. Crear horarios programados variables (1-3 por día, variando por día de la semana)
        $horasPorDia = [
            0 => ['08:00'], // Domingo: 1 toma
            1 => ['08:00', '20:00'], // Lunes: 2 tomas
            2 => ['08:00', '14:00', '20:00'], // Martes: 3 tomas
            3 => ['08:00', '20:00'], // Miércoles: 2 tomas
            4 => ['08:00'], // Jueves: 1 toma
            5 => ['08:00', '14:00'], // Viernes: 2 tomas
            6 => ['08:00', '20:00'], // Sábado: 2 tomas
        ];

        // 6. Limpiar horarios y administraciones previas de este tratamiento en el rango
        $horarios = HorarioProgramado::where('paciente_id', $ana->id)
            ->where('medicamento_tratamiento_id', $medTrat->id)
            ->get();
        foreach ($horarios as $h) {
            Administracion::where('horario_programado_id', $h->id)
                ->whereDate('fecha_hora_programada', '>=', $this->fechaInicio->toDateString())
                ->delete();
            $h->delete();
        }

        // 7. Crear nuevos horarios programados para los próximos 14 días
        $horariosNuevos = [];
        foreach ($horasPorDia as $dow => $horas) {
            foreach ($horas as $hora) {
                $horariosNuevos[] = HorarioProgramado::create([
                    'medicamento_tratamiento_id' => $medTrat->id,
                    'paciente_id' => $ana->id,
                    'hora_programada' => $hora,
                    'dias_semana' => (string)$dow,
                    'fecha_inicio' => $this->fechaInicio->toDateString(),
                    'fecha_fin' => null,
                    'activo' => true,
                ]);
            }
        }

        // 8. Generar administraciones realistas para los últimos 14 días con variabilidad descendiente
        for ($i = 0; $i < 14; $i++) {
            $fecha = $this->fechaInicio->copy()->addDays($i);
            $dow = $fecha->dayOfWeek; // 0=domingo
            $horas = $horasPorDia[$dow];
            
            foreach ($horas as $hora) {
                $fechaProgramada = $fecha->copy()->setTimeFromTimeString($hora);
                
                // Calcular variación descendiente
                $variacion = $this->calcularVariabilidadDescendiente($fecha, $hora);
                
                $fechaAdministrada = $fechaProgramada->copy()->addMinutes($variacion);
                $adelanto = $variacion < 0 ? abs($variacion) : 0;
                $retraso = $variacion > 0 ? $variacion : 0;
                
                // Score: 100 si |variacion| <= 15, decae exponencialmente después
                $score = abs($variacion) <= 15 ? 100 : round(100 * exp(-0.05 * (abs($variacion) - 15)), 2);
                
                // Categoría temporal
                $categoria = $this->determinarCategoria($variacion);
                
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
        
        $this->command->info('Administraciones realistas con variabilidad descendiente generadas para Ana López.');
    }

    /**
     * Calcula la variabilidad descendiente - comienza alta y mejora con el tiempo
     */
    private function calcularVariabilidadDescendiente(Carbon $fecha, string $hora): int
    {
        // Calcular el factor de progresión inverso (1.0 al inicio, 0.0 al final)
        $diasTranscurridos = $this->fechaInicio->diffInDays($fecha);
        $factorProgresion = 1.0 - ($diasTranscurridos / ($this->diasTotales - 1));
        
        // Aplicar una curva exponencial suave para el descenso
        $factorDecrecimiento = pow($factorProgresion, 1.3);
        
        // Modificadores por contexto
        $esFinDeSemana = $fecha->isWeekend();
        $esManana = (int)explode(':', $hora)[0] < 12;
        
        // Rangos iniciales (primeros días - alta variabilidad)
        $rangoInicial = ['min' => -45, 'max' => 50];
        
        // Rangos finales (últimos días - baja variabilidad)
        $rangoFinal = ['min' => -8, 'max' => 12];
        
        // Calcular rangos actuales basados en el factor de decrecimiento
        $rangoActual = [
            'min' => $rangoFinal['min'] + ($rangoInicial['min'] - $rangoFinal['min']) * $factorDecrecimiento,
            'max' => $rangoFinal['max'] + ($rangoInicial['max'] - $rangoFinal['max']) * $factorDecrecimiento
        ];
        
        // Modificadores por contexto temporal
        if ($esFinDeSemana) {
            // Fines de semana: ligeramente mejor adherencia
            $rangoActual['min'] *= 0.8;
            $rangoActual['max'] *= 0.7;
        }
        
        if ($esManana) {
            // Mañanas: mejor adherencia
            $rangoActual['min'] *= 0.9;
            $rangoActual['max'] *= 0.8;
        }
        
        // Generar variación base
        $variacion = rand((int)$rangoActual['min'], (int)$rangoActual['max']);
        
        // Asegurar que nunca sea exactamente 0
        if ($variacion == 0) {
            $variacion = rand(1, 3) * (rand(0, 1) == 0 ? -1 : 1);
        }
        
        // Sesgo hacia mejora conforme avanza el tiempo (menos retrasos)
        $sesgoMejora = $factorDecrecimiento * -5; // Máximo 5 minutos de sesgo hacia adelanto
        
        $variacionFinal = (int)($variacion + $sesgoMejora);
        
        // Verificar nuevamente que no sea 0
        if ($variacionFinal == 0) {
            $variacionFinal = rand(1, 2) * (rand(0, 1) == 0 ? -1 : 1);
        }
        
        return $variacionFinal;
    }

    /**
     * Determina la categoría temporal basada en la variación
     */
    private function determinarCategoria(int $variacion): string
    {
        if ($variacion <= -30) {
            return 'muy_temprano';
        } elseif ($variacion < -15) {
            return 'temprano';
        } elseif ($variacion <= 15) {
            return 'puntual';
        } elseif ($variacion <= 30) {
            return 'tardio';
        } else {
            return 'muy_tardio';
        }
    }
}