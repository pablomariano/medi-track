<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class TestIpDetection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ip-detection {--request : Simular una request HTTP}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la detección de IP en el sistema de auditoría';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Probando detección de IP en el sistema de auditoría');
        $this->info('==================================================');

        if ($this->option('request')) {
            $this->testWithRequest();
        } else {
            $this->testCurrentConfiguration();
        }

        $this->info('✅ Prueba completada');
    }

    /**
     * Probar la configuración actual
     */
    private function testCurrentConfiguration()
    {
        $this->info('📋 Configuración actual:');
        $this->table(
            ['Configuración', 'Valor'],
            [
                ['APP_ENV', config('app.env')],
                ['APP_DEBUG', config('app.debug') ? 'true' : 'false'],
                ['APP_URL', config('app.url')],
            ]
        );

        // Verificar logs recientes
        $recentLogs = AuditLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['ip_address', 'created_at', 'accion', 'created_by_name']);

        if ($recentLogs->count() > 0) {
            $this->info('📊 Logs recientes:');
            $this->table(
                ['IP', 'Fecha', 'Acción', 'Usuario'],
                $recentLogs->map(fn($log) => [
                    $log->ip_address,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->accion,
                    $log->created_by_name
                ])->toArray()
            );

            // Analizar IPs únicas
            $uniqueIps = $recentLogs->pluck('ip_address')->unique();
            $this->info("🌐 IPs únicas detectadas: {$uniqueIps->count()}");
            
            foreach ($uniqueIps as $ip) {
                $count = $recentLogs->where('ip_address', $ip)->count();
                $this->line("  - {$ip}: {$count} registros");
            }
        } else {
            $this->warn('⚠️ No hay logs recientes para analizar');
        }
    }

    /**
     * Probar con una request simulada
     */
    private function testWithRequest()
    {
        $this->info('🧪 Simulando request HTTP...');

        // Crear una request simulada
        $request = Request::create('/test', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_FOR' => '203.0.113.1, 10.0.0.1',
            'HTTP_X_REAL_IP' => '203.0.113.1',
            'HTTP_CLIENT_IP' => '203.0.113.1',
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ]);

        // Probar el método de detección de IP
        $reflection = new \ReflectionClass(AuditLog::class);
        $method = $reflection->getMethod('getRealIpAddress');
        $method->setAccessible(true);

        $detectedIp = $method->invoke(null, $request);
        
        $this->info("🎯 IP detectada: {$detectedIp}");
        
        // Mostrar todos los headers disponibles
        $this->info('📋 Headers disponibles:');
        $headers = [
            'HTTP_X_FORWARDED_FOR' => $request->server('HTTP_X_FORWARDED_FOR'),
            'HTTP_X_REAL_IP' => $request->server('HTTP_X_REAL_IP'),
            'HTTP_CLIENT_IP' => $request->server('HTTP_CLIENT_IP'),
            'REMOTE_ADDR' => $request->server('REMOTE_ADDR'),
        ];

        $this->table(
            ['Header', 'Valor'],
            collect($headers)->map(fn($value, $key) => [$key, $value ?? 'null'])->toArray()
        );
    }
} 