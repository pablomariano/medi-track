<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    /**
     * Almacén temporal para datos originales durante actualizaciones
     */
    private static $originalData = [];

    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        if ($this->debeAuditar($model)) {
            AuditService::logCreation($model, [
                'evento' => 'model_created',
                'usuario_actual' => $this->getUsuarioActual()
            ]);
        }
    }

    /**
     * Handle the model "updating" event.
     */
    public function updating(Model $model): void
    {
        // Guardamos el estado original antes de la actualización
        if ($this->debeAuditar($model)) {
            $modelKey = get_class($model) . '_' . $model->getKey();
            self::$originalData[$modelKey] = $model->getOriginal();
        }
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        if ($this->debeAuditar($model)) {
            $modelKey = get_class($model) . '_' . $model->getKey();
            
            if (isset(self::$originalData[$modelKey])) {
                AuditService::logUpdate(
                    $model, 
                    self::$originalData[$modelKey],
                    [
                        'evento' => 'model_updated',
                        'usuario_actual' => $this->getUsuarioActual(),
                        'cambios_detectados' => $this->detectarCambiosImportantes($model, self::$originalData[$modelKey])
                    ]
                );
                
                // Limpiar los datos temporales
                unset(self::$originalData[$modelKey]);
            }
        }
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if ($this->debeAuditar($model)) {
            AuditService::logDeletion($model, [
                'evento' => 'model_deleted',
                'usuario_actual' => $this->getUsuarioActual(),
                'tipo_eliminacion' => method_exists($model, 'trashed') && $model->trashed() ? 'soft_delete' : 'hard_delete'
            ]);
        }
    }

    /**
     * Handle the model "restored" event.
     */
    public function restored(Model $model): void
    {
        if ($this->debeAuditar($model)) {
            AuditService::log('update', [
                'tabla' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'datos_nuevos' => $model->getAttributes(),
                'contexto' => [
                    'evento' => 'model_restored',
                    'usuario_actual' => $this->getUsuarioActual(),
                    'tipo' => 'restauracion_soft_delete'
                ],
                'severidad' => 'high'
            ]);
        }
    }

    /**
     * Determinar si el modelo debe ser auditado
     */
    private function debeAuditar(Model $model): bool
    {
        // No auditar en testing a menos que esté habilitado
        if (app()->environment('testing') && !config('audit.enable_in_testing', false)) {
            return false;
        }

        // Lista de modelos que siempre se auditan
        $modelosAuditables = [
            'App\\Models\\User',
            'App\\Models\\Paciente',
            'App\\Models\\PersonalMedico',
            'App\\Models\\Cuidador',
            'App\\Models\\Apoderado',
            'App\\Models\\Tratamiento',
            'App\\Models\\Medicamento',
            'App\\Models\\MedicamentoTratamiento',
            'App\\Models\\PacienteMedico',
            'App\\Models\\PacienteCuidador',
            'App\\Models\\Role',
            'App\\Models\\Permiso',
            'App\\Models\\Administracion'
        ];

        $modelClass = get_class($model);
        
        // Verificar si el modelo está en la lista de auditables
        if (in_array($modelClass, $modelosAuditables)) {
            return true;
        }

        // Verificar si el modelo tiene el trait Auditable
        if (method_exists($model, 'isAuditable') && $model->isAuditable()) {
            return true;
        }

        return false;
    }

    /**
     * Obtener información del usuario actual
     */
    private function getUsuarioActual(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'id' => null,
                'nombre' => 'Sistema',
                'rol' => null
            ];
        }

        return [
            'id' => $user->id,
            'nombre' => $user->name,
            'email' => $user->email,
            'rol' => $user->role ? $user->role->nombre : null
        ];
    }

    /**
     * Detectar cambios importantes en el modelo
     */
    private function detectarCambiosImportantes(Model $model, array $originalData): array
    {
        $camposImportantes = $this->getCamposImportantesPorModelo($model);
        $cambiosImportantes = [];

        foreach ($model->getDirty() as $campo => $valorNuevo) {
            if (in_array($campo, $camposImportantes)) {
                $valorOriginal = $originalData[$campo] ?? null;
                
                $cambiosImportantes[] = [
                    'campo' => $campo,
                    'valor_anterior' => $valorOriginal,
                    'valor_nuevo' => $valorNuevo,
                    'es_critico' => $this->esCambioCritico($model, $campo, $valorOriginal, $valorNuevo)
                ];
            }
        }

        return $cambiosImportantes;
    }

    /**
     * Obtener campos importantes por modelo
     */
    private function getCamposImportantesPorModelo(Model $model): array
    {
        $modelClass = get_class($model);

        $camposPorModelo = [
            'App\\Models\\User' => ['name', 'email', 'rol_id', 'email_verified_at'],
            'App\\Models\\Paciente' => ['nombre', 'apellido', 'numero_documento', 'fecha_nacimiento', 'activo'],
            'App\\Models\\PersonalMedico' => ['nombre', 'apellido', 'numero_documento', 'especialidad', 'activo'],
            'App\\Models\\Tratamiento' => ['paciente_id', 'medico_usuario_id', 'nombre', 'fecha_inicio', 'fecha_fin', 'estado'],
            'App\\Models\\PacienteMedico' => ['paciente_id', 'medico_id', 'es_principal', 'fecha_asignacion', 'fecha_fin'],
            'App\\Models\\Administracion' => ['tratamiento_id', 'medicamento_id', 'fecha_hora_programada', 'fecha_hora_administrada', 'estado']
        ];

        return $camposPorModelo[$modelClass] ?? [];
    }

    /**
     * Determinar si un cambio es crítico
     */
    private function esCambioCritico(Model $model, string $campo, $valorAnterior, $valorNuevo): bool
    {
        $modelClass = get_class($model);
        
        // Cambios críticos por modelo
        $camposCriticos = [
            'App\\Models\\User' => ['email', 'rol_id', 'activo'],
            'App\\Models\\Paciente' => ['numero_documento', 'activo'],
            'App\\Models\\Tratamiento' => ['paciente_id', 'medico_usuario_id', 'estado'],
            'App\\Models\\Administracion' => ['estado', 'fecha_hora_administrada']
        ];

        $criticos = $camposCriticos[$modelClass] ?? [];
        
        if (in_array($campo, $criticos)) {
            // Casos específicos de criticidad
            if ($campo === 'activo' && $valorAnterior == true && $valorNuevo == false) {
                return true; // Desactivación
            }
            
            if ($campo === 'estado' && in_array($valorAnterior, ['Activo', 'Pendiente']) && 
                in_array($valorNuevo, ['Suspendido', 'Omitida', 'Cancelado'])) {
                return true; // Cambio de estado activo a inactivo
            }
            
            return true;
        }

        return false;
    }
} 