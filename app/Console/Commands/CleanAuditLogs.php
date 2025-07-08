<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AuditService;
use Carbon\Carbon;

class CleanAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'audit:clean {--days=90 : Días de retención} {--dry-run : Solo mostrar qué se eliminaría}';

    /**
     * The console command description.
     */
    protected $description = 'Limpiar logs de auditoría antiguos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $diasRetencion = $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("🧹 Limpiando logs de auditoría...");
        $this->info("📅 Días de retención: {$diasRetencion}");
        
        if ($dryRun) {
            $this->warn("🔍 Modo dry-run: No se eliminarán registros");
        }

        try {
            // Obtener fecha límite
            $fechaLimite = Carbon::now()->subDays($diasRetencion);
            
            // Contar registros a eliminar
            $registrosAEliminar = \App\Models\AuditLog::where('created_at', '<', $fechaLimite)->count();
            
            if ($registrosAEliminar === 0) {
                $this->info("✅ No hay registros antiguos para eliminar");
                return 0;
            }

            $this->info("📊 Registros a eliminar: {$registrosAEliminar}");

            if ($dryRun) {
                $this->info("🔍 Registros que se eliminarían:");
                $this->table(
                    ['ID', 'Acción', 'Usuario', 'Fecha'],
                    \App\Models\AuditLog::where('created_at', '<', $fechaLimite)
                        ->select('id', 'accion', 'created_by_name', 'created_at')
                        ->limit(10)
                        ->get()
                        ->map(fn($log) => [
                            $log->id,
                            $log->accion,
                            $log->created_by_name,
                            $log->created_at->format('Y-m-d H:i:s')
                        ])
                );
                
                if ($registrosAEliminar > 10) {
                    $this->info("... y " . ($registrosAEliminar - 10) . " registros más");
                }
                
                return 0;
            }

            // Confirmar eliminación
            if (!$this->confirm("¿Estás seguro de que quieres eliminar {$registrosAEliminar} registros?")) {
                $this->info("❌ Operación cancelada");
                return 0;
            }

            // Ejecutar limpieza
            $eliminados = AuditService::limpiarLogsAntiguos($diasRetencion);

            $this->info("✅ Se eliminaron {$eliminados} registros de auditoría");

            // Registrar la acción de limpieza
            AuditService::log('delete', [
                'contexto' => [
                    'tipo' => 'limpieza_logs_automatica',
                    'logs_eliminados' => $eliminados,
                    'dias_retencion' => $diasRetencion,
                    'comando' => 'audit:clean'
                ],
                'severidad' => 'medium'
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error al limpiar logs: " . $e->getMessage());
            return 1;
        }
    }
} 