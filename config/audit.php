<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración centralizada para el sistema de auditoría
    |
    */

    // Habilitar auditoría en diferentes entornos
    'enabled' => env('AUDIT_ENABLED', true),
    'enable_in_testing' => env('AUDIT_ENABLE_IN_TESTING', false),
    'enable_in_console' => env('AUDIT_ENABLE_IN_CONSOLE', false),

    // Configuración de retención de logs
    'retention_days' => env('AUDIT_RETENTION_DAYS', 90),
    'max_log_size_mb' => env('AUDIT_MAX_LOG_SIZE_MB', 100),

    // Configuración de severidad
    'default_severity' => env('AUDIT_DEFAULT_SEVERITY', 'medium'),
    'log_level_threshold' => env('AUDIT_LOG_LEVEL_THRESHOLD', 'info'),

    // Modelos auditables
    'auditable_models' => [
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
        'App\\Models\\Administracion',
    ],

    // Rutas sensibles que siempre se auditan
    'sensitive_routes' => [
        'pacientes',
        'personal-medico',
        'tratamientos',
        'administraciones',
        'asignaciones-medicos',
        'asignaciones-cuidadores',
        'usuarios',
        'roles',
        'permisos',
        'audit-logs',
        'login',
        'logout',
    ],

    // Rutas críticas con alta severidad
    'critical_routes' => [
        'usuarios',
        'roles',
        'permisos',
        'login',
        'logout',
    ],

    // Rutas médicas importantes
    'medical_routes' => [
        'pacientes',
        'tratamientos',
        'administraciones',
        'asignaciones',
    ],

    // Campos sensibles que se filtran automáticamente
    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'token',
        '_token',
        'api_key',
        'secret',
    ],

    // Configuración de filtrado de requests
    'exclude_asset_extensions' => [
        '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2'
    ],

    // Rutas de polling que no se auditan
    'polling_routes' => [
        'notifications/count',
        'dashboard/live',
        'health-check',
        'heartbeat',
        'live-stats',
    ],

    // Configuración de exportación
    'export_formats' => ['json', 'csv', 'xlsx'],
    'export_max_records' => 10000,

    // Configuración de alertas
    'alerts' => [
        'enabled' => env('AUDIT_ALERTS_ENABLED', false),
        'critical_threshold' => env('AUDIT_CRITICAL_THRESHOLD', 10),
        'time_window_minutes' => env('AUDIT_TIME_WINDOW_MINUTES', 60),
        'notification_channels' => ['mail', 'slack'],
    ],

    // Configuración de rendimiento
    'performance' => [
        'batch_size' => env('AUDIT_BATCH_SIZE', 100),
        'queue_jobs' => env('AUDIT_QUEUE_JOBS', false),
        'queue_name' => env('AUDIT_QUEUE_NAME', 'audit'),
        'cache_ttl' => env('AUDIT_CACHE_TTL', 300),
    ],
]; 