<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tratamiento;
use App\Models\AdministracionMedicamento;
use App\Models\EsquemaPosologico;
use App\Models\DosisPrn;
use App\Models\HistorialTratamiento;
use App\Models\AlertaMedicamento;
use App\Models\AutorizacionTratamiento;
use App\Models\User;
use App\Models\Medicamento;
use Carbon\Carbon;

class TratamientosYAdministracionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "💉 Creando tratamientos y administraciones de prueba...\n";
        
        $this->seedEsquemasPosologicos();
        $this->seedTratamientos();
        $this->seedAdministraciones();
        $this->seedDosisPrn();
        $this->seedAlertas();
        $this->seedAutorizaciones();
        $this->seedHistorialTratamientos();
        
        echo "✅ Tratamientos y administraciones creados exitosamente\n";
    }

    private function seedEsquemasPosologicos(): void
    {
        $esquemas = [
            [
                'nombre' => 'Una vez al día (QD)',
                'descripcion' => 'Administración una vez cada 24 horas',
                'frecuencia_horas' => 24,
                'dosis_diarias' => 1,
                'activo' => true
            ],
            [
                'nombre' => 'Dos veces al día (BID)',
                'descripcion' => 'Administración cada 12 horas',
                'frecuencia_horas' => 12,
                'dosis_diarias' => 2,
                'activo' => true
            ],
            [
                'nombre' => 'Tres veces al día (TID)',
                'descripcion' => 'Administración cada 8 horas',
                'frecuencia_horas' => 8,
                'dosis_diarias' => 3,
                'activo' => true
            ],
            [
                'nombre' => 'Cuatro veces al día (QID)',
                'descripcion' => 'Administración cada 6 horas',
                'frecuencia_horas' => 6,
                'dosis_diarias' => 4,
                'activo' => true
            ],
            [
                'nombre' => 'Cada 4 horas',
                'descripcion' => 'Administración cada 4 horas (6 veces/día)',
                'frecuencia_horas' => 4,
                'dosis_diarias' => 6,
                'activo' => true
            ],
            [
                'nombre' => 'Cada 6 horas',
                'descripcion' => 'Administración cada 6 horas',
                'frecuencia_horas' => 6,
                'dosis_diarias' => 4,
                'activo' => true
            ],
            [
                'nombre' => 'Semanal',
                'descripcion' => 'Administración una vez por semana',
                'frecuencia_horas' => 168,
                'dosis_diarias' => 0.14,
                'activo' => true
            ],
            [
                'nombre' => 'Mensual',
                'descripcion' => 'Administración una vez al mes',
                'frecuencia_horas' => 720,
                'dosis_diarias' => 0.033,
                'activo' => true
            ]
        ];

        foreach ($esquemas as $esquema) {
            EsquemaPosologico::firstOrCreate(
                ['nombre' => $esquema['nombre']],
                $esquema
            );
        }

        echo "  ✅ " . count($esquemas) . " esquemas posológicos creados\n";
    }

    private function seedTratamientos(): void
    {
        $pacientes = User::whereHas('roles', function($q) {
            $q->where('name', 'paciente');
        })->get();

        $medicos = User::whereHas('roles', function($q) {
            $q->where('name', 'medico');
        })->get();

        if ($pacientes->isEmpty() || $medicos->isEmpty()) {
            echo "  ⚠️ No se encontraron pacientes o médicos para crear tratamientos\n";
            return;
        }

        $tratamientos = [
            [
                'paciente_email' => 'paciente@meditrack.com',
                'medico_email' => 'medico@meditrack.com',
                'medicamento_comercial' => 'Paracetamol 500mg',
                'esquema' => 'Tres veces al día (TID)',
                'diagnostico' => 'Cefalea tensional',
                'dosis' => 500,
                'instrucciones' => 'Tomar con abundante agua, preferiblemente después de las comidas',
                'fecha_inicio' => '2024-01-15',
                'fecha_fin' => '2024-01-22',
                'duracion_dias' => 7,
                'estado' => 'completado'
            ],
            [
                'paciente_email' => 'paciente@meditrack.com',
                'medico_email' => 'medico@meditrack.com',
                'medicamento_comercial' => 'Amoxil',
                'esquema' => 'Tres veces al día (TID)',
                'diagnostico' => 'Faringitis bacteriana',
                'dosis' => 500,
                'instrucciones' => 'Completar todo el tratamiento aunque se sienta mejor. Tomar con el estómago vacío',
                'fecha_inicio' => '2024-01-20',
                'fecha_fin' => '2024-01-30',
                'duracion_dias' => 10,
                'estado' => 'activo'
            ],
            [
                'paciente_email' => 'paciente@meditrack.com',
                'medico_email' => 'medico@meditrack.com',
                'medicamento_comercial' => 'Renitec',
                'esquema' => 'Una vez al día (QD)',
                'diagnostico' => 'Hipertensión arterial',
                'dosis' => 10,
                'instrucciones' => 'Tomar siempre a la misma hora, preferiblemente por la mañana. Controlar presión arterial',
                'fecha_inicio' => '2024-01-01',
                'fecha_fin' => null,
                'duracion_dias' => null,
                'estado' => 'activo'
            ],
            [
                'paciente_email' => 'paciente@meditrack.com',
                'medico_email' => 'medico@meditrack.com',
                'medicamento_comercial' => 'Ventolin HFA',
                'esquema' => 'Cuatro veces al día (QID)',
                'diagnostico' => 'Asma bronquial',
                'dosis' => 2,
                'instrucciones' => '2 puffs cada 6 horas. Uso adicional según necesidad (PRN)',
                'fecha_inicio' => '2024-01-10',
                'fecha_fin' => null,
                'duracion_dias' => null,
                'estado' => 'activo'
            ],
            [
                'paciente_email' => 'paciente@meditrack.com',
                'medico_email' => 'medico@meditrack.com',
                'medicamento_comercial' => 'Glucophage',
                'esquema' => 'Dos veces al día (BID)',
                'diagnostico' => 'Diabetes mellitus tipo 2',
                'dosis' => 850,
                'instrucciones' => 'Tomar con las comidas principales (desayuno y cena)',
                'fecha_inicio' => '2023-12-01',
                'fecha_fin' => null,
                'duracion_dias' => null,
                'estado' => 'activo'
            ]
        ];

        foreach ($tratamientos as $tratData) {
            $paciente = User::where('email', $tratData['paciente_email'])->first();
            $medico = User::where('email', $tratData['medico_email'])->first();
            $medicamento = Medicamento::where('nombre_comercial', 'like', '%' . explode(' ', $tratData['medicamento_comercial'])[0] . '%')->first();
            $esquema = EsquemaPosologico::where('nombre', $tratData['esquema'])->first();

            if ($paciente && $medico && $medicamento && $esquema) {
                Tratamiento::firstOrCreate(
                    [
                        'paciente_id' => $paciente->id,
                        'medicamento_id' => $medicamento->id,
                        'fecha_inicio' => $tratData['fecha_inicio']
                    ],
                    [
                        'medico_id' => $medico->id,
                        'esquema_posologico_id' => $esquema->id,
                        'diagnostico' => $tratData['diagnostico'],
                        'dosis' => $tratData['dosis'],
                        'instrucciones_especiales' => $tratData['instrucciones'],
                        'fecha_fin' => $tratData['fecha_fin'],
                        'duracion_dias' => $tratData['duracion_dias'],
                        'estado' => $tratData['estado'],
                        'activo' => true,
                        'observaciones' => 'Tratamiento de prueba creado por seeder'
                    ]
                );
            }
        }

        echo "  ✅ " . count($tratamientos) . " tratamientos creados\n";
    }

    private function seedAdministraciones(): void
    {
        $tratamientos = Tratamiento::where('activo', true)->get();
        $cuidadores = User::whereHas('roles', function($q) {
            $q->where('name', 'cuidador');
        })->get();

        if ($tratamientos->isEmpty() || $cuidadores->isEmpty()) {
            echo "  ⚠️ No se encontraron tratamientos o cuidadores para crear administraciones\n";
            return;
        }

        $administraciones = [];
        $cuidador = $cuidadores->first();

        foreach ($tratamientos as $tratamiento) {
            $fechaInicio = Carbon::parse($tratamiento->fecha_inicio);
            $fechaFin = $tratamiento->fecha_fin ? Carbon::parse($tratamiento->fecha_fin) : Carbon::now();
            
            // Crear administraciones para los últimos 7 días
            $fecha = Carbon::now()->subDays(7);
            
            while ($fecha <= Carbon::now() && $fecha >= $fechaInicio && $fecha <= $fechaFin) {
                $horasDelDia = [];
                
                // Determinar horarios según el esquema
                switch ($tratamiento->esquemaPosologico->dosis_diarias) {
                    case 1:
                        $horasDelDia = ['08:00'];
                        break;
                    case 2:
                        $horasDelDia = ['08:00', '20:00'];
                        break;
                    case 3:
                        $horasDelDia = ['08:00', '14:00', '20:00'];
                        break;
                    case 4:
                        $horasDelDia = ['06:00', '12:00', '18:00', '24:00'];
                        break;
                    case 6:
                        $horasDelDia = ['06:00', '10:00', '14:00', '18:00', '22:00', '02:00'];
                        break;
                }

                foreach ($horasDelDia as $hora) {
                    $fechaHora = $fecha->copy()->format('Y-m-d') . ' ' . $hora;
                    $estado = 'administrado';
                    
                    // Simular algunas dosis omitidas (5% probabilidad)
                    if (rand(1, 100) <= 5) {
                        $estado = 'omitido';
                    }

                    $administraciones[] = [
                        'tratamiento_id' => $tratamiento->id,
                        'administrado_por_id' => $cuidador->id,
                        'fecha_programada' => $fechaHora,
                        'fecha_administracion' => $estado === 'administrado' ? $fechaHora : null,
                        'dosis_administrada' => $estado === 'administrado' ? $tratamiento->dosis : 0,
                        'estado' => $estado,
                        'observaciones' => $estado === 'omitido' ? 'Paciente rechazó medicamento' : 'Administrado correctamente',
                        'reacciones_adversas' => null
                    ];
                }

                $fecha->addDay();
            }
        }

        foreach ($administraciones as $admin) {
            AdministracionMedicamento::create($admin);
        }

        echo "  ✅ " . count($administraciones) . " administraciones creadas\n";
    }

    private function seedDosisPrn(): void
    {
        $tratamientos = Tratamiento::where('estado', 'activo')->get();
        $cuidadores = User::whereHas('roles', function($q) {
            $q->where('name', 'cuidador');
        })->get();

        if ($tratamientos->isEmpty() || $cuidadores->isEmpty()) {
            echo "  ⚠️ No se encontraron tratamientos activos para crear dosis PRN\n";
            return;
        }

        $dosisPrn = [
            [
                'tratamiento_nombre' => 'Ventolin HFA',
                'motivo' => 'Crisis asmática leve',
                'dosis_extra' => 2,
                'fecha' => '2024-01-25 14:30:00',
                'efectividad' => 'alta'
            ],
            [
                'tratamiento_nombre' => 'Ventolin HFA',
                'motivo' => 'Disnea post-ejercicio',
                'dosis_extra' => 1,
                'fecha' => '2024-01-26 09:15:00',
                'efectividad' => 'media'
            ],
            [
                'tratamiento_nombre' => 'Paracetamol 500mg',
                'motivo' => 'Dolor de cabeza intenso',
                'dosis_extra' => 500,
                'fecha' => '2024-01-24 16:45:00',
                'efectividad' => 'alta'
            ]
        ];

        $cuidador = $cuidadores->first();

        foreach ($dosisPrn as $dosisData) {
            $tratamiento = Tratamiento::whereHas('medicamento', function($q) use ($dosisData) {
                $q->where('nombre_comercial', 'like', '%' . explode(' ', $dosisData['tratamiento_nombre'])[0] . '%');
            })->first();

            if ($tratamiento) {
                DosisPrn::create([
                    'tratamiento_id' => $tratamiento->id,
                    'administrado_por_id' => $cuidador->id,
                    'motivo' => $dosisData['motivo'],
                    'dosis_administrada' => $dosisData['dosis_extra'],
                    'fecha_administracion' => $dosisData['fecha'],
                    'efectividad' => $dosisData['efectividad'],
                    'observaciones' => 'Dosis PRN administrada según protocolo de emergencia'
                ]);
            }
        }

        echo "  ✅ " . count($dosisPrn) . " dosis PRN creadas\n";
    }

    private function seedAlertas(): void
    {
        $tratamientos = Tratamiento::where('estado', 'activo')->get();
        $usuarios = User::all();

        if ($tratamientos->isEmpty()) {
            echo "  ⚠️ No se encontraron tratamientos activos para crear alertas\n";
            return;
        }

        $alertas = [
            [
                'tipo' => 'dosis_omitida',
                'titulo' => 'Dosis omitida - Amoxicilina',
                'mensaje' => 'El paciente Ana López omitió la dosis de las 14:00 de Amoxicilina',
                'gravedad' => 'media',
                'fecha_alerta' => '2024-01-25 14:30:00'
            ],
            [
                'tipo' => 'interaccion_detectada',
                'titulo' => 'Posible interacción medicamentosa',
                'mensaje' => 'Se detectó una posible interacción entre Enalapril y nuevo medicamento prescrito',
                'gravedad' => 'alta',
                'fecha_alerta' => '2024-01-24 10:15:00'
            ],
            [
                'tipo' => 'medicamento_vencido',
                'titulo' => 'Medicamento próximo a vencer',
                'mensaje' => 'El Ventolin HFA vence en 15 días. Programar reposición',
                'gravedad' => 'baja',
                'fecha_alerta' => '2024-01-23 08:00:00'
            ],
            [
                'tipo' => 'revision_tratamiento',
                'titulo' => 'Revisión de tratamiento programada',
                'mensaje' => 'El tratamiento con Metformina requiere evaluación médica',
                'gravedad' => 'media',
                'fecha_alerta' => '2024-01-26 09:00:00'
            ],
            [
                'tipo' => 'reaccion_adversa',
                'titulo' => 'Reacción adversa reportada',
                'mensaje' => 'Paciente reporta náuseas después de tomar Amoxicilina',
                'gravedad' => 'alta',
                'fecha_alerta' => '2024-01-25 16:20:00'
            ]
        ];

        foreach ($alertas as $alertaData) {
            $tratamiento = $tratamientos->random();
            $usuario = $usuarios->where('email', 'medico@meditrack.com')->first();

            AlertaMedicamento::create([
                'tratamiento_id' => $tratamiento->id,
                'usuario_id' => $usuario->id,
                'tipo_alerta' => $alertaData['tipo'],
                'titulo' => $alertaData['titulo'],
                'mensaje' => $alertaData['mensaje'],
                'gravedad' => $alertaData['gravedad'],
                'fecha_alerta' => $alertaData['fecha_alerta'],
                'leida' => rand(0, 1) ? true : false,
                'fecha_lectura' => rand(0, 1) ? Carbon::parse($alertaData['fecha_alerta'])->addHours(2) : null,
                'activa' => true
            ]);
        }

        echo "  ✅ " . count($alertas) . " alertas creadas\n";
    }

    private function seedAutorizaciones(): void
    {
        $tratamientos = Tratamiento::where('estado', 'activo')->get();
        $medicos = User::whereHas('roles', function($q) {
            $q->where('name', 'medico');
        })->get();

        if ($tratamientos->isEmpty() || $medicos->isEmpty()) {
            echo "  ⚠️ No se encontraron tratamientos o médicos para crear autorizaciones\n";
            return;
        }

        $autorizaciones = [
            [
                'tipo' => 'modificacion_dosis',
                'motivo' => 'Ajuste de dosis por efectividad insuficiente',
                'descripcion' => 'Aumentar dosis de Enalapril de 10mg a 15mg una vez al día',
                'estado' => 'aprobado',
                'fecha_solicitud' => '2024-01-20 10:00:00',
                'fecha_respuesta' => '2024-01-20 14:30:00'
            ],
            [
                'tipo' => 'suspension_temporal',
                'motivo' => 'Reacción adversa leve',
                'descripcion' => 'Suspender Amoxicilina por 48 horas debido a molestias gastrointestinales',
                'estado' => 'aprobado',
                'fecha_solicitud' => '2024-01-25 09:15:00',
                'fecha_respuesta' => '2024-01-25 10:00:00'
            ],
            [
                'tipo' => 'dosis_adicional',
                'motivo' => 'Control de síntomas',
                'descripcion' => 'Autorización para dosis adicional de Ventolin en caso de crisis',
                'estado' => 'pendiente',
                'fecha_solicitud' => '2024-01-26 16:00:00',
                'fecha_respuesta' => null
            ]
        ];

        $medico = $medicos->first();

        foreach ($autorizaciones as $authData) {
            $tratamiento = $tratamientos->random();

            AutorizacionTratamiento::create([
                'tratamiento_id' => $tratamiento->id,
                'medico_autorizador_id' => $medico->id,
                'tipo_autorizacion' => $authData['tipo'],
                'motivo' => $authData['motivo'],
                'descripcion' => $authData['descripcion'],
                'estado' => $authData['estado'],
                'fecha_solicitud' => $authData['fecha_solicitud'],
                'fecha_respuesta' => $authData['fecha_respuesta'],
                'observaciones' => 'Autorización gestionada por sistema de prueba'
            ]);
        }

        echo "  ✅ " . count($autorizaciones) . " autorizaciones creadas\n";
    }

    private function seedHistorialTratamientos(): void
    {
        $tratamientos = Tratamiento::all();

        if ($tratamientos->isEmpty()) {
            echo "  ⚠️ No se encontraron tratamientos para crear historial\n";
            return;
        }

        $eventos = [
            'inicio_tratamiento',
            'modificacion_dosis',
            'suspension_temporal',
            'reaccion_adversa',
            'mejoria_sintomas',
            'cumplimiento_completo',
            'incumplimiento',
            'revision_medica'
        ];

        foreach ($tratamientos as $tratamiento) {
            // Crear 2-4 eventos por tratamiento
            $numEventos = rand(2, 4);
            
            for ($i = 0; $i < $numEventos; $i++) {
                $fecha = Carbon::parse($tratamiento->fecha_inicio)->addDays(rand(0, 30));
                $evento = $eventos[array_rand($eventos)];
                
                $descripcion = $this->getDescripcionEvento($evento, $tratamiento);

                HistorialTratamiento::create([
                    'tratamiento_id' => $tratamiento->id,
                    'fecha_evento' => $fecha,
                    'tipo_evento' => $evento,
                    'descripcion' => $descripcion,
                    'observaciones' => 'Evento registrado automáticamente por el sistema'
                ]);
            }
        }

        echo "  ✅ Historial de tratamientos creado\n";
    }

    private function getDescripcionEvento($evento, $tratamiento): string
    {
        $medicamento = $tratamiento->medicamento->nombre_comercial;
        
        switch ($evento) {
            case 'inicio_tratamiento':
                return "Inicio de tratamiento con {$medicamento} para {$tratamiento->diagnostico}";
            case 'modificacion_dosis':
                return "Modificación de dosis de {$medicamento} por evaluación clínica";
            case 'suspension_temporal':
                return "Suspensión temporal de {$medicamento} por motivos clínicos";
            case 'reaccion_adversa':
                return "Reacción adversa leve reportada con {$medicamento}";
            case 'mejoria_sintomas':
                return "Mejoría significativa de síntomas con {$medicamento}";
            case 'cumplimiento_completo':
                return "Cumplimiento completo del esquema de {$medicamento}";
            case 'incumplimiento':
                return "Incumplimiento del tratamiento con {$medicamento}";
            case 'revision_medica':
                return "Revisión médica del tratamiento con {$medicamento}";
            default:
                return "Evento relacionado con {$medicamento}";
        }
    }
}
