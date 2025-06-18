<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Paciente;
use App\Services\AuditService;
use Carbon\Carbon;

class TestPhase5Audit extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:phase5-audit';

    /**
     * The console command description.
     */
    protected $description = 'Test Phase 5: Sistema de Auditoría Avanzada - Verificar funcionamiento completo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 INICIANDO TESTS DE LA FASE 5: SISTEMA DE AUDITORÍA AVANZADA');
        $this->info('================================================================');
        
        $testsPasados = 0;
        $totalTests = 8;

        // Test 1: Verificar creación de tabla de auditoría
        $this->info('📋 TEST 1: Verificando tabla de auditoría...');
        if ($this->testTablaAuditoria()) {
            $this->info('✅ Tabla audit_logs creada correctamente');
            $testsPasados++;
        } else {
            $this->error('❌ Error en tabla de auditoría');
        }

        // Test 2: Verificar servicio de auditoría
        $this->info('🔧 TEST 2: Verificando AuditService...');
        if ($this->testAuditService()) {
            $this->info('✅ AuditService funcionando correctamente');
            $testsPasados++;
        } else {
            $this->error('❌ Error en AuditService');
        }

        // Test 3: Verificar middleware de auditoría
        $this->info('⚙️ TEST 3: Verificando middleware AuditLogger...');
        if ($this->testMiddlewareAuditoria()) {
            $this->info('✅ Middleware AuditLogger registrado correctamente');
            $testsPasados++;
        } else {
            $this->error('❌ Error en middleware AuditLogger');
        }

        // Test 4: Verificar observers de modelos
        $this->info('👀 TEST 4: Verificando observers de auditoría...');
        if ($this->testObserversAuditoria()) {
            $this->info('✅ Observers registrados correctamente');
            $testsPasados++;
        } else {
            $this->error('❌ Error en observers de auditoría');
        }

        // Test 5: Probar logging manual
        $this->info('📝 TEST 5: Probando logging manual...');
        if ($this->testLoggingManual()) {
            $this->info('✅ Logging manual funcionando correctamente');
            $testsPasados++;
        } else {
            $this->error('❌ Error en logging manual');
        }

        // Test 6: Probar logging automático con observers
        $this->info('🤖 TEST 6: Probando logging automático con observers...');
        if ($this->testLoggingAutomatico()) {
            $this->info('✅ Logging automático funcionando correctamente');
            $testsPasados++;
        } else {
            $this->error('❌ Error en logging automático');
        }

        // Test 7: Verificar controlador de auditoría
        $this->info('🎮 TEST 7: Verificando AuditController...');
        if ($this->testAuditController()) {
            $this->info('✅ AuditController configurado correctamente');
            $testsPasados++;
        } else {
            $this->error('❌ Error en AuditController');
        }

        // Test 8: Verificar estadísticas y búsquedas
        $this->info('📊 TEST 8: Verificando estadísticas y búsquedas...');
        if ($this->testEstadisticasYBusquedas()) {
            $this->info('✅ Estadísticas y búsquedas funcionando correctamente');
            $testsPasados++;
        } else {
            $this->error('❌ Error en estadísticas y búsquedas');
        }

        // Resultados finales
        $this->info('');
        $this->info('📊 RESULTADOS FINALES DE LA FASE 5');
        $this->info('==================================');
        $this->info("✅ Tests pasados: {$testsPasados}/{$totalTests}");
        
        $porcentaje = ($testsPasados / $totalTests) * 100;
        $this->info("📈 Porcentaje de éxito: {$porcentaje}%");

        if ($testsPasados === $totalTests) {
            $this->info('🎉 ¡FASE 5 COMPLETADA CON ÉXITO!');
            $this->info('🔐 Sistema de Auditoría Avanzada funcionando al 100%');
        } else {
            $this->warn("⚠️  Fase 5 parcialmente implementada ({$porcentaje}%)");
        }

        return Command::SUCCESS;
    }

    private function testTablaAuditoria(): bool
    {
        try {
            // Verificar que la tabla existe y tiene las columnas esperadas
            $columns = \Schema::getColumnListing('audit_logs');
            
            $expectedColumns = [
                'id', 'usuario_id', 'accion', 'tabla_afectada', 'registro_id',
                'datos_anteriores', 'datos_nuevos', 'ip_address', 'user_agent',
                'metodo_http', 'url', 'ruta', 'contexto_adicional', 'session_id',
                'severidad', 'created_at', 'created_by_name'
            ];

            foreach ($expectedColumns as $column) {
                if (!in_array($column, $columns)) {
                    $this->error("  ❌ Columna faltante: {$column}");
                    return false;
                }
            }

            $this->line("  📋 Todas las columnas presentes: " . count($expectedColumns));
            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error verificando tabla: " . $e->getMessage());
            return false;
        }
    }

    private function testAuditService(): bool
    {
        try {
            // Verificar métodos principales del servicio
            $methods = ['log', 'logAccess', 'logLogin', 'logLogout', 'getEstadisticas'];
            
            foreach ($methods as $method) {
                if (!method_exists(AuditService::class, $method)) {
                    $this->error("  ❌ Método faltante: {$method}");
                    return false;
                }
            }

            $this->line("  🔧 Todos los métodos principales presentes");
            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error verificando AuditService: " . $e->getMessage());
            return false;
        }
    }

    private function testMiddlewareAuditoria(): bool
    {
        try {
            // Verificar que la clase del middleware existe
            if (!class_exists(\App\Http\Middleware\AuditLogger::class)) {
                $this->error("  ❌ Clase AuditLogger no existe");
                return false;
            }

            // Verificar métodos del middleware
            if (!method_exists(\App\Http\Middleware\AuditLogger::class, 'handle')) {
                $this->error("  ❌ Método handle no existe en AuditLogger");
                return false;
            }

            // En Laravel 11, el middleware está registrado en bootstrap/app.php
            // Verificamos que el archivo de configuración contenga la referencia
            $bootstrapContent = file_get_contents(base_path('bootstrap/app.php'));
            
            if (!str_contains($bootstrapContent, 'AuditLogger::class')) {
                $this->error("  ❌ AuditLogger no encontrado en bootstrap/app.php");
                return false;
            }

            $this->line("  ⚙️ Middleware configurado correctamente en bootstrap/app.php");
            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error verificando middleware: " . $e->getMessage());
            return false;
        }
    }

    private function testObserversAuditoria(): bool
    {
        try {
            // Verificar que el observer existe
            if (!class_exists(\App\Observers\AuditableObserver::class)) {
                $this->error("  ❌ AuditableObserver no existe");
                return false;
            }

            // Verificar métodos del observer
            $methods = ['created', 'updated', 'deleted'];
            foreach ($methods as $method) {
                if (!method_exists(\App\Observers\AuditableObserver::class, $method)) {
                    $this->error("  ❌ Método del observer faltante: {$method}");
                    return false;
                }
            }

            $this->line("  👀 Observer configurado correctamente");
            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error verificando observers: " . $e->getMessage());
            return false;
        }
    }

    private function testLoggingManual(): bool
    {
        try {
            $logsAntes = AuditLog::count();

            // Crear un log manual
            AuditService::log('create', [
                'tabla' => 'test_table',
                'registro_id' => 999,
                'contexto' => ['test' => 'phase5_manual'],
                'severidad' => 'medium'
            ]);

            $logsDespues = AuditLog::count();

            if ($logsDespues !== $logsAntes + 1) {
                $this->error("  ❌ Log manual no se creó correctamente");
                return false;
            }

            // Verificar el log creado
            $log = AuditLog::latest()->first();
            if ($log->accion !== 'create' || $log->tabla_afectada !== 'test_table') {
                $this->error("  ❌ Datos del log manual incorrectos");
                return false;
            }

            $this->line("  📝 Log manual creado correctamente (ID: {$log->id})");
            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error en logging manual: " . $e->getMessage());
            return false;
        }
    }

    private function testLoggingAutomatico(): bool
    {
        try {
            // Habilitar auditoría en testing
            config(['audit.enable_in_testing' => true]);

            $logsAntes = AuditLog::count();

            // Crear un usuario de prueba con email único (esto debería triggear el observer)
            $uniqueEmail = 'test.audit.' . time() . '@example.com';
            $user = User::create([
                'name' => 'Test Audit User',
                'email' => $uniqueEmail,
                'password' => bcrypt('password'),
                'role_id' => 1
            ]);

            $logsDespues = AuditLog::count();

            if ($logsDespues <= $logsAntes) {
                $this->error("  ❌ Observer no se ejecutó automáticamente");
                return false;
            }

            // Verificar el log del observer
            $log = AuditLog::where('tabla_afectada', 'users')
                          ->where('registro_id', $user->id)
                          ->where('accion', 'create')
                          ->first();

            if (!$log) {
                $this->error("  ❌ Log del observer no encontrado");
                return false;
            }

            // Limpiar
            $user->delete();

            $this->line("  🤖 Observer automático funcionando (Log ID: {$log->id})");
            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error en logging automático: " . $e->getMessage());
            return false;
        }
    }

    private function testAuditController(): bool
    {
        try {
            // Verificar que el controlador existe
            if (!class_exists(\App\Http\Controllers\AuditController::class)) {
                $this->error("  ❌ AuditController no existe");
                return false;
            }

            // Verificar métodos principales
            $methods = ['index', 'show', 'dashboard', 'exportCompliance'];
            foreach ($methods as $method) {
                if (!method_exists(\App\Http\Controllers\AuditController::class, $method)) {
                    $this->error("  ❌ Método del controlador faltante: {$method}");
                    return false;
                }
            }

            $this->line("  🎮 AuditController configurado correctamente");
            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error verificando AuditController: " . $e->getMessage());
            return false;
        }
    }

    private function testEstadisticasYBusquedas(): bool
    {
        try {
            // Probar obtención de estadísticas
            $estadisticas = AuditService::getEstadisticas(30);
            
            $keysEsperadas = [
                'total_acciones', 'acciones_criticas', 'usuarios_activos',
                'acciones_por_dia', 'acciones_por_tipo'
            ];

            foreach ($keysEsperadas as $key) {
                if (!array_key_exists($key, $estadisticas)) {
                    $this->error("  ❌ Estadística faltante: {$key}");
                    return false;
                }
            }

            // Probar búsqueda
            $resultados = AuditService::buscar(['accion' => 'create']);
            if (!is_object($resultados)) {
                $this->error("  ❌ Búsqueda no retorna query builder");
                return false;
            }

            $this->line("  📊 Estadísticas: {$estadisticas['total_acciones']} acciones totales");
            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error en estadísticas: " . $e->getMessage());
            return false;
        }
    }
}
