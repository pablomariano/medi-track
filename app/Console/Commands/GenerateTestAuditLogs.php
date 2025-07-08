<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Services\AuditService;
use App\Models\User;

class GenerateTestAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:generate-audit-logs {--count=5 : Número de logs a generar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar logs de auditoría de prueba con diferentes IPs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        
        $this->info("🔧 Generando {$count} logs de auditoría de prueba...");

        // IPs de prueba
        $testIps = [
            '192.168.1.100',
            '10.0.0.50',
            '172.16.0.25',
            '203.0.113.10',
            '198.51.100.5'
        ];

        $actions = ['create', 'update', 'access', 'login'];
        $tables = ['users', 'pacientes', 'tratamientos', 'medicamentos'];

        for ($i = 0; $i < $count; $i++) {
            $this->generateTestLog($testIps[$i % count($testIps)], $actions[$i % count($actions)], $tables[$i % count($tables)]);
            $this->line("✅ Log " . ($i + 1) . " generado");
        }

        $this->info('🎉 Logs de prueba generados exitosamente');
        
        // Mostrar estadísticas
        $this->showStatistics();
    }

    /**
     * Generar un log de prueba
     */
    private function generateTestLog(string $ip, string $action, string $table): void
    {
        // Simular un usuario autenticado
        $user = User::first();
        
        // Generar datos de prueba
        $testData = [
            'usuario_id' => $user ? $user->id : null,
            'created_by_name' => $user ? $user->name : 'Sistema',
            'accion' => $action,
            'tabla_afectada' => $table,
            'registro_id' => rand(1, 1000),
            'datos_nuevos' => [
                'nombre' => 'Test User ' . rand(1, 100),
                'email' => 'test' . rand(1, 100) . '@example.com',
                'created_at' => now()->toISOString()
            ],
            'ip_address' => $ip,
            'user_agent' => 'Test User Agent - ' . $ip,
            'metodo_http' => 'GET',
            'url' => 'http://localhost/test',
            'ruta' => 'test.route',
            'contexto_adicional' => [
                'test' => true,
                'ip_simulada' => $ip,
                'timestamp' => now()->toISOString()
            ],
            'session_id' => 'test-session-' . rand(1000, 9999),
            'severidad' => $this->getRandomSeverity(),
            'created_at' => now()
        ];

        // Crear el log directamente
        \App\Models\AuditLog::create($testData);
    }

    /**
     * Obtener severidad aleatoria
     */
    private function getRandomSeverity(): string
    {
        $severities = ['low', 'medium', 'high', 'critical'];
        return $severities[array_rand($severities)];
    }

    /**
     * Mostrar estadísticas de los logs generados
     */
    private function showStatistics(): void
    {
        $this->info('📊 Estadísticas de logs generados:');
        
        $recentLogs = \App\Models\AuditLog::where('created_at', '>=', now()->subMinutes(5))
            ->orderBy('created_at', 'desc')
            ->get();

        if ($recentLogs->count() > 0) {
            $uniqueIps = $recentLogs->pluck('ip_address')->unique();
            
            $this->table(
                ['IP', 'Registros', 'Último acceso'],
                $uniqueIps->map(function($ip) use ($recentLogs) {
                    $logsForIp = $recentLogs->where('ip_address', $ip);
                    return [
                        $ip,
                        $logsForIp->count(),
                        $logsForIp->first()->created_at->format('H:i:s')
                    ];
                })->toArray()
            );
        }
    }
} 