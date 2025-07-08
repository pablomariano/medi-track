<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditLog;
use Carbon\Carbon;

class AnalyzeLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:analyze {--days=7 : Días a analizar} {--type=all : Tipo de análisis (all, errors, audit, security)}';

    /**
     * The console command description.
     */
    protected $description = 'Analizar logs del sistema y generar reportes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dias = $this->option('days');
        $tipo = $this->option('type');

        $this->info("📊 Analizando logs del sistema...");
        $this->info("📅 Período: últimos {$dias} días");
        $this->info("🔍 Tipo: {$tipo}");

        $fechaInicio = Carbon::now()->subDays($dias);

        try {
            switch ($tipo) {
                case 'errors':
                    $this->analizarErrores($fechaInicio);
                    break;
                case 'audit':
                    $this->analizarAuditoria($fechaInicio);
                    break;
                case 'security':
                    $this->analizarSeguridad($fechaInicio);
                    break;
                default:
                    $this->analizarErrores($fechaInicio);
                    $this->newLine();
                    $this->analizarAuditoria($fechaInicio);
                    $this->newLine();
                    $this->analizarSeguridad($fechaInicio);
                    break;
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error al analizar logs: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Analizar errores en logs
     */
    private function analizarErrores(Carbon $fechaInicio)
    {
        $this->info("🚨 ANÁLISIS DE ERRORES");
        $this->info("=====================");

        // Leer archivo de logs de Laravel
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            $this->warn("⚠️ Archivo de logs no encontrado: {$logFile}");
            return;
        }

        // Contar errores por tipo
        $contenido = file_get_contents($logFile);
        $lineas = explode("\n", $contenido);
        
        $errores = [
            'ERROR' => 0,
            'CRITICAL' => 0,
            'EMERGENCY' => 0,
            'WARNING' => 0,
        ];

        $erroresPorFecha = [];
        $erroresPorTipo = [];

        foreach ($lineas as $linea) {
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+)/', $linea, $matches)) {
                $fecha = $matches[1];
                $nivel = $matches[3];
                $mensaje = $matches[4];

                $fechaObj = Carbon::parse($fecha);
                
                if ($fechaObj->gte($fechaInicio)) {
                    // Contar por nivel
                    if (isset($errores[$nivel])) {
                        $errores[$nivel]++;
                    }

                    // Contar por fecha
                    $fechaKey = $fechaObj->format('Y-m-d');
                    $erroresPorFecha[$fechaKey] = ($erroresPorFecha[$fechaKey] ?? 0) + 1;

                    // Analizar tipos de error
                    if (str_contains($mensaje, 'SQLSTATE')) {
                        $erroresPorTipo['Database'] = ($erroresPorTipo['Database'] ?? 0) + 1;
                    } elseif (str_contains($mensaje, 'Connection')) {
                        $erroresPorTipo['Connection'] = ($erroresPorTipo['Connection'] ?? 0) + 1;
                    } elseif (str_contains($mensaje, 'Column not found')) {
                        $erroresPorTipo['Schema'] = ($erroresPorTipo['Schema'] ?? 0) + 1;
                    } else {
                        $erroresPorTipo['Other'] = ($erroresPorTipo['Other'] ?? 0) + 1;
                    }
                }
            }
        }

        // Mostrar estadísticas
        $this->table(
            ['Nivel', 'Cantidad'],
            collect($errores)->map(fn($count, $level) => [$level, $count])->toArray()
        );

        if (!empty($erroresPorTipo)) {
            $this->info("📈 Errores por tipo:");
            $this->table(
                ['Tipo', 'Cantidad'],
                collect($erroresPorTipo)->map(fn($count, $type) => [$type, $count])->toArray()
            );
        }

        // Mostrar días con más errores
        if (!empty($erroresPorFecha)) {
            arsort($erroresPorFecha);
            $this->info("📅 Días con más errores:");
            $this->table(
                ['Fecha', 'Errores'],
                collect($erroresPorFecha)->take(5)->map(fn($count, $date) => [$date, $count])->toArray()
            );
        }
    }

    /**
     * Analizar logs de auditoría
     */
    private function analizarAuditoria(Carbon $fechaInicio)
    {
        $this->info("🔍 ANÁLISIS DE AUDITORÍA");
        $this->info("======================");

        $stats = AuditLog::where('created_at', '>=', $fechaInicio);

        if ($stats->count() === 0) {
            $this->warn("⚠️ No hay registros de auditoría en el período especificado");
            return;
        }

        // Estadísticas generales
        $totalAcciones = $stats->count();
        $accionesCriticas = $stats->whereIn('severidad', ['high', 'critical'])->count();
        $usuariosUnicos = $stats->distinct('usuario_id')->count();

        $this->info("📊 Estadísticas generales:");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total acciones', $totalAcciones],
                ['Acciones críticas', $accionesCriticas],
                ['Usuarios únicos', $usuariosUnicos],
            ]
        );

        // Acciones por tipo
        $accionesPorTipo = $stats->selectRaw('accion, COUNT(*) as total')
            ->groupBy('accion')
            ->orderBy('total', 'desc')
            ->get();

        $this->info("📈 Acciones por tipo:");
        $this->table(
            ['Acción', 'Cantidad'],
            $accionesPorTipo->map(fn($item) => [$item->accion, $item->total])->toArray()
        );

        // Usuarios más activos
        $usuariosActivos = $stats->selectRaw('created_by_name, COUNT(*) as total')
            ->groupBy('created_by_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $this->info("👥 Usuarios más activos:");
        $this->table(
            ['Usuario', 'Acciones'],
            $usuariosActivos->map(fn($item) => [$item->created_by_name, $item->total])->toArray()
        );

        // Actividad por día
        $actividadPorDia = $stats->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc')
            ->limit(7)
            ->get();

        $this->info("📅 Actividad por día:");
        $this->table(
            ['Fecha', 'Acciones'],
            $actividadPorDia->map(fn($item) => [$item->fecha, $item->total])->toArray()
        );
    }

    /**
     * Analizar seguridad
     */
    private function analizarSeguridad(Carbon $fechaInicio)
    {
        $this->info("🔒 ANÁLISIS DE SEGURIDAD");
        $this->info("======================");

        $auditLogs = AuditLog::where('created_at', '>=', $fechaInicio);

        // Intentos de acceso fallidos
        $accesosFallidos = $auditLogs->where('accion', 'access')
            ->whereJsonContains('contexto_adicional', ['status_code' => 403])
            ->count();

        // Logins sospechosos
        $loginsSospechosos = $auditLogs->where('accion', 'login')
            ->where('severidad', 'high')
            ->count();

        // Acciones críticas
        $accionesCriticas = $auditLogs->where('severidad', 'critical')->count();

        // IPs únicas
        $ipsUnicas = $auditLogs->distinct('ip_address')->count();

        $this->info("🛡️ Métricas de seguridad:");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Accesos fallidos', $accesosFallidos],
                ['Logins sospechosos', $loginsSospechosos],
                ['Acciones críticas', $accionesCriticas],
                ['IPs únicas', $ipsUnicas],
            ]
        );

        // IPs más activas
        $ipsActivas = $auditLogs->selectRaw('ip_address, COUNT(*) as total')
            ->groupBy('ip_address')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $this->info("🌐 IPs más activas:");
        $this->table(
            ['IP', 'Acciones'],
            $ipsActivas->map(fn($item) => [$item->ip_address, $item->total])->toArray()
        );

        // Alertas de seguridad
        if ($accesosFallidos > 10) {
            $this->warn("⚠️ ALERTA: Muchos accesos fallidos ({$accesosFallidos})");
        }

        if ($loginsSospechosos > 5) {
            $this->warn("⚠️ ALERTA: Logins sospechosos detectados ({$loginsSospechosos})");
        }

        if ($accionesCriticas > 0) {
            $this->error("🚨 ALERTA: Acciones críticas detectadas ({$accionesCriticas})");
        }
    }
} 