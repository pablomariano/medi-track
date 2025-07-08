<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AuditController;
use Illuminate\Http\Request;

class TestAuditPagination extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:audit-pagination {--page=1 : Página a probar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la paginación del sistema de auditoría';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $page = (int) $this->option('page');
        
        $this->info("🔍 Probando paginación de auditoría - Página {$page}");
        $this->info('==================================================');

        // Simular request con parámetros de paginación
        $request = Request::create('/audit', 'GET', ['page' => $page]);
        app()->instance('request', $request);
        
        // Configurar la página actual en el paginator
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });
        
        try {
            // Simular la llamada al método index (sin usar Inertia)
            $query = \App\Services\AuditService::buscar([]);
            $logs = $query->paginate(25)->withQueryString();
            
            $this->info("📊 Resultados de paginación:");
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Página actual', $logs->currentPage()],
                    ['Última página', $logs->lastPage()],
                    ['Total registros', $logs->total()],
                    ['Por página', $logs->perPage()],
                    ['Registros en esta página', $logs->count()],
                    ['Desde registro', $logs->firstItem() ?: 0],
                    ['Hasta registro', $logs->lastItem() ?: 0],
                ]
            );

            if ($logs->count() > 0) {
                $this->info("📋 Primeros registros de la página:");
                $registrosMuestra = $logs->take(3);
                $this->table(
                    ['ID', 'Usuario', 'Acción', 'IP', 'Fecha'],
                    $registrosMuestra->map(fn($log) => [
                        $log->id,
                        $log->created_by_name,
                        $log->accion,
                        $log->ip_address,
                        $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A'
                    ])->toArray()
                );
            }

            // Verificar enlaces de paginación
            $this->info("🔗 Enlaces de paginación disponibles:");
            $links = $logs->links();
            
            // Parsear los enlaces manualmente
            $linkData = [];
            if ($logs->previousPageUrl()) {
                $linkData[] = ['Anterior', $logs->previousPageUrl(), 'No'];
            }
            
            // Enlaces de páginas específicas
            $start = max(1, $logs->currentPage() - 2);
            $end = min($logs->lastPage(), $logs->currentPage() + 2);
            
            for ($i = $start; $i <= $end; $i++) {
                $linkData[] = [
                    (string) $i,
                    $logs->url($i),
                    $i === $logs->currentPage() ? 'Sí' : 'No'
                ];
            }
            
            if ($logs->nextPageUrl()) {
                $linkData[] = ['Siguiente', $logs->nextPageUrl(), 'No'];
            }

            $this->table(
                ['Etiqueta', 'URL', 'Activo'],
                $linkData
            );

        } catch (\Exception $e) {
            $this->error("❌ Error al probar paginación: " . $e->getMessage());
            $this->error("Stacktrace: " . $e->getTraceAsString());
        }

        $this->info('✅ Prueba completada');
    }
} 