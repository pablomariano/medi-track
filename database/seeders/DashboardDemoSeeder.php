<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Paciente;
use App\Models\PersonalMedico;
use App\Models\Cuidador;
use App\Models\Apoderado;
use App\Models\PrincipioActivo;
use App\Models\Medicamento;
use App\Models\Tratamiento;
use App\Models\MedicamentoTratamiento;
use App\Models\AdministracionMedicamento;
use App\Models\AlertaMedicamento;
use App\Models\AutorizacionTratamiento;
use App\Models\FormaFarmaceutica;
use App\Models\ViaAdministracion;
use App\Models\UnidadMedida;
use Carbon\Carbon;

class DashboardDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->info('🏗️  Poblando base de datos con datos completos para el dashboard...');
            
            $this->createBasicCatalogs();
            $this->createManyUsers();
            $this->createManyPatients();
            $this->createManyMedicamentos();
            $this->createManyTratamientos();
            $this->createManyAlertas();
            $this->createAdministraciones();
            
            $this->info('✅ Base de datos poblada exitosamente con datos completos!');
        });
    }

    private function info($message) 
    {
        echo $message . "\n";
    }

    private function createBasicCatalogs()
    {
        $this->info('📚 Asegurando catálogos básicos...');
        
        // Principios Activos más completos
        $principiosActivos = [
            'Acetaminofén', 'Ibuprofeno', 'Losartán', 'Metformina', 'Atorvastatina',
            'Amoxicilina', 'Omeprazol', 'Simvastatina', 'Aspirina', 'Diclofenaco',
            'Captopril', 'Furosemida', 'Prednisona', 'Warfarina', 'Insulina'
        ];

        foreach ($principiosActivos as $nombre) {
            PrincipioActivo::firstOrCreate([
                'nombre_generico' => $nombre
            ], [
                'grupo_farmacologico' => 'Diversos',
                'activo' => true
            ]);
        }

        // Formas farmacéuticas más completas
        $formas = [
            ['nombre' => 'Tableta', 'tipo' => 'sólido'],
            ['nombre' => 'Cápsula', 'tipo' => 'sólido'],
            ['nombre' => 'Jarabe', 'tipo' => 'líquido'],
            ['nombre' => 'Inyección', 'tipo' => 'líquido'],
            ['nombre' => 'Gotas', 'tipo' => 'líquido'],
            ['nombre' => 'Crema', 'tipo' => 'semisólido'],
            ['nombre' => 'Supositorio', 'tipo' => 'sólido']
        ];
        
        foreach ($formas as $forma) {
            FormaFarmaceutica::firstOrCreate([
                'nombre' => $forma['nombre']
            ], [
                'tipo' => $forma['tipo'],
                'descripcion' => 'Forma farmacéutica ' . $forma['nombre']
            ]);
        }

        // Vías de administración más completas
        $vias = [
            ['nombre' => 'Oral', 'abreviatura' => 'VO'],
            ['nombre' => 'Intramuscular', 'abreviatura' => 'IM'],
            ['nombre' => 'Intravenosa', 'abreviatura' => 'IV'],
            ['nombre' => 'Subcutánea', 'abreviatura' => 'SC'],
            ['nombre' => 'Tópica', 'abreviatura' => 'TOP'],
            ['nombre' => 'Rectal', 'abreviatura' => 'PR'],
            ['nombre' => 'Oftálmica', 'abreviatura' => 'OFT']
        ];
        
        foreach ($vias as $via) {
            ViaAdministracion::firstOrCreate([
                'nombre' => $via['nombre']
            ], [
                'abreviatura' => $via['abreviatura'],
                'descripcion' => 'Vía de administración ' . $via['nombre']
            ]);
        }

        // Unidades de medida
        $unidades = [
            ['nombre' => 'mg', 'tipo' => 'peso'],
            ['nombre' => 'g', 'tipo' => 'peso'],
            ['nombre' => 'ml', 'tipo' => 'volumen'],
            ['nombre' => 'UI', 'tipo' => 'unidad'],
            ['nombre' => 'mcg', 'tipo' => 'peso']
        ];
        
        foreach ($unidades as $unidad) {
            UnidadMedida::firstOrCreate([
                'nombre' => $unidad['nombre']
            ], [
                'tipo' => $unidad['tipo'],
                'equivalencia_base' => 1.0
            ]);
        }
    }

    private function createManyUsers()
    {
        $this->info('👥 Creando usuarios variados...');
        
        // Admin principal
        User::firstOrCreate(['email' => 'admin@meditrack.com'], [
            'name' => 'Dr. Administrator',
            'password' => bcrypt('password'),
            'email_verified_at' => now()
        ]);

        // Médicos
        for ($i = 1; $i <= 8; $i++) {
            User::firstOrCreate(["email" => "medico{$i}@meditrack.com"], [
                'name' => "Dr. Médico {$i}",
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
        }

        // Cuidadores
        for ($i = 1; $i <= 5; $i++) {
            User::firstOrCreate(["email" => "cuidador{$i}@meditrack.com"], [
                'name' => "Cuidador {$i}",
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
        }

        // Apoderados/Familiares
        for ($i = 1; $i <= 7; $i++) {
            User::firstOrCreate(["email" => "familiar{$i}@meditrack.com"], [
                'name' => "Familiar {$i}",
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
        }
    }

    private function createManyPatients()
    {
        $this->info('🏥 Creando pacientes diversos...');
        
        $nombres = [
            'Ana García López', 'Carlos Martínez Ruiz', 'María Fernández Silva',
            'José González Pérez', 'Isabel Rodríguez Moreno', 'Antonio López García',
            'Carmen Sánchez Díaz', 'Francisco Jiménez Ruiz', 'Pilar Muñoz López',
            'Manuel Álvarez Pérez', 'Dolores Romero Gil', 'Juan Torres Morales',
            'Rosa Navarro Castro', 'Pedro Ramos Ortega', 'Concepción Herrera Luna'
        ];

        foreach ($nombres as $index => $nombre) {
            DB::table('pacientes')->insertOrIgnore([
                'nombre' => $nombre,
                'fecha_nacimiento' => now()->subYears(rand(25, 80)),
                'activo' => true,
                'created_at' => now()->subDays(rand(1, 365))
            ]);
        }
    }

    private function createManyMedicamentos()
    {
        $this->info('💊 Creando variedad de medicamentos...');
        
        $medicamentos = [
            ['nombre' => 'Paracetamol 500mg', 'dias_vencimiento' => 365, 'precio' => 5.50],
            ['nombre' => 'Ibuprofeno 400mg', 'dias_vencimiento' => 730, 'precio' => 8.75],
            ['nombre' => 'Losartán 50mg', 'dias_vencimiento' => 180, 'precio' => 12.30],
            ['nombre' => 'Metformina 850mg', 'dias_vencimiento' => -30, 'precio' => 15.60], // Vencido
            ['nombre' => 'Amoxicilina 500mg', 'dias_vencimiento' => 15, 'precio' => 22.45], // Por vencer
            ['nombre' => 'Omeprazol 20mg', 'dias_vencimiento' => 450, 'precio' => 18.90],
            ['nombre' => 'Simvastatina 20mg', 'dias_vencimiento' => 10, 'precio' => 25.30], // Por vencer
            ['nombre' => 'Aspirina 100mg', 'dias_vencimiento' => 600, 'precio' => 6.80],
            ['nombre' => 'Captopril 25mg', 'dias_vencimiento' => -15, 'precio' => 11.25], // Vencido
            ['nombre' => 'Furosemida 40mg', 'dias_vencimiento' => 300, 'precio' => 9.75],
            ['nombre' => 'Prednisona 5mg', 'dias_vencimiento' => 20, 'precio' => 14.80], // Por vencer
            ['nombre' => 'Insulina Glargina', 'dias_vencimiento' => 90, 'precio' => 45.60],
            ['nombre' => 'Warfarina 5mg', 'dias_vencimiento' => 540, 'precio' => 19.25],
            ['nombre' => 'Diclofenaco Gel', 'dias_vencimiento' => -60, 'precio' => 16.40], // Vencido
            ['nombre' => 'Vitamina D3 1000UI', 'dias_vencimiento' => 720, 'precio' => 13.50]
        ];

        $principio = PrincipioActivo::first();
        $forma = FormaFarmaceutica::first();
        $via = ViaAdministracion::first();
        $unidad = UnidadMedida::first();

        if ($principio && $forma && $via && $unidad) {
            foreach ($medicamentos as $med) {
                DB::table('medicamentos')->insertOrIgnore([
                    'nombre_comercial' => $med['nombre'],
                    'principio_activo_id' => PrincipioActivo::inRandomOrder()->first()->id,
                    'forma_farmaceutica_id' => FormaFarmaceutica::inRandomOrder()->first()->id,
                    'via_administracion_id' => ViaAdministracion::inRandomOrder()->first()->id,
                    'unidad_concentracion_id' => UnidadMedida::inRandomOrder()->first()->id,
                    'concentracion' => rand(50, 1000),
                    'fecha_vencimiento' => now()->addDays($med['dias_vencimiento']),
                    'precio_unitario' => $med['precio'],
                    'laboratorio' => 'Laboratorio ' . ['Alpha', 'Beta', 'Gamma', 'Delta'][array_rand(['Alpha', 'Beta', 'Gamma', 'Delta'])],
                    'activo' => true,
                    'requiere_receta' => rand(0, 1),
                    'controlado' => rand(0, 1) < 0.2, // 20% controlados
                    'creado_en' => now()->subDays(rand(1, 200))
                ]);
            }
        }
    }

    private function createManyTratamientos()
    {
        $this->info('🩺 Creando tratamientos variados...');
        
        $pacientes = DB::table('pacientes')->pluck('id')->toArray();
        $tratamientos = [
            ['nombre' => 'Tratamiento Hipertensión', 'estado' => 'Activo'],
            ['nombre' => 'Control Diabetes Tipo 2', 'estado' => 'Activo'],
            ['nombre' => 'Terapia Antiinflamatoria', 'estado' => 'Pausado'],
            ['nombre' => 'Tratamiento Cardiovascular', 'estado' => 'Activo'],
            ['nombre' => 'Control Colesterol', 'estado' => 'Activo'],
            ['nombre' => 'Terapia Dolor Crónico', 'estado' => 'Activo'],
            ['nombre' => 'Tratamiento Infección', 'estado' => 'Completado'],
            ['nombre' => 'Control Tiroides', 'estado' => 'Activo'],
            ['nombre' => 'Terapia Respiratoria', 'estado' => 'Pausado'],
            ['nombre' => 'Tratamiento Gastritis', 'estado' => 'Activo'],
            ['nombre' => 'Control Ansiedad', 'estado' => 'Activo'],
            ['nombre' => 'Terapia Artritis', 'estado' => 'Activo']
        ];

        foreach ($tratamientos as $index => $trat) {
            $pacienteId = $pacientes[array_rand($pacientes)];
            DB::table('tratamientos')->insertOrIgnore([
                'nombre' => $trat['nombre'],
                'paciente_id' => $pacienteId,
                'diagnostico' => 'Diagnóstico para ' . $trat['nombre'],
                'objetivo_terapeutico' => 'Controlar y mejorar condición del paciente',
                'estado' => $trat['estado'],
                'fecha_inicio' => now()->subDays(rand(1, 180)),
                'fecha_fin_estimada' => now()->addDays(rand(30, 365)),
                'observaciones' => 'Tratamiento en curso bajo supervisión médica',
                'creado_en' => now()->subDays(rand(1, 100)),
                'modificado_en' => now()->subDays(rand(0, 10))
            ]);
        }
    }

    private function createManyAlertas()
    {
        $this->info('🚨 Creando alertas del sistema...');
        
        $tratamientos = DB::table('tratamientos')->pluck('id')->toArray();
        $pacientes = DB::table('pacientes')->pluck('id')->toArray();
        
        if (empty($tratamientos) || empty($pacientes)) {
            $this->info('⚠️  No hay tratamientos o pacientes disponibles, saltando alertas...');
            return;
        }
        
        $alertas = [
            ['tipo' => 'Vencimiento', 'nivel' => 'Critica', 'mensaje' => 'Se detectó medicamento vencido en inventario'],
            ['tipo' => 'Dosis_Excedida', 'nivel' => 'Alta', 'mensaje' => 'Paciente no recibió dosis programada'],
            ['tipo' => 'Falta_Stock', 'nivel' => 'Media', 'mensaje' => 'Inventario de medicamento por debajo del mínimo'],
            ['tipo' => 'Interaccion', 'nivel' => 'Critica', 'mensaje' => 'Posible interacción entre medicamentos'],
            ['tipo' => 'Alergia', 'nivel' => 'Alta', 'mensaje' => 'Paciente reporta efectos secundarios'],
            ['tipo' => 'Vencimiento', 'nivel' => 'Media', 'mensaje' => 'Medicamentos próximos a fecha de vencimiento'],
            ['tipo' => 'Dosis_Excedida', 'nivel' => 'Alta', 'mensaje' => 'Paciente muestra baja adherencia al tratamiento'],
            ['tipo' => 'Falta_Stock', 'nivel' => 'Baja', 'mensaje' => 'Recordatorio de cita médica pendiente'],
            ['tipo' => 'Interaccion', 'nivel' => 'Media', 'mensaje' => 'Resultados de laboratorio pendientes']
        ];

        foreach ($alertas as $alerta) {
            $tratamientoId = $tratamientos[array_rand($tratamientos)];
            $pacienteId = $pacientes[array_rand($pacientes)];
            
            DB::table('alertas_medicamentos')->insertOrIgnore([
                'paciente_id' => $pacienteId,
                'tratamiento_id' => $tratamientoId,
                'tipo_alerta' => $alerta['tipo'],
                'nivel_severidad' => $alerta['nivel'],
                'mensaje' => $alerta['mensaje'],
                'medicamentos_involucrados' => json_encode([]),
                'fecha_generada' => now()->subDays(rand(0, 30)),
                'revisada' => rand(0, 1) ? true : false,
                'fecha_revision' => rand(0, 1) ? now()->subDays(rand(0, 5)) : null,
                'accion_tomada' => rand(0, 1) ? 'Acción correctiva tomada' : null
            ]);
        }
    }

    private function createAdministraciones()
    {
        $this->info('💉 Saltando administraciones por ahora...');
        
        // La estructura de administraciones es muy compleja y requiere
        // medicamentos_tratamientos que es otra tabla intermedia.
        // Por ahora omitimos esto para que el dashboard tenga los datos básicos
        
        $this->info('✨ Los datos básicos del dashboard ya están disponibles');
    }
}
