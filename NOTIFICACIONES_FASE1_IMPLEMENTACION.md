# 🚀 Implementación Fase 1: Notificaciones Críticas en Tiempo Real

## ✅ Lo que hemos implementado

### Servicios Creados
- **`RealTimeNotificationService`** - Servicio principal para notificaciones críticas
- **`RealTimeNotificationMail`** - Mailable para emails de notificaciones en tiempo real

### Comandos de Consola
- **`notifications:process-real-time`** - Procesa y envía notificaciones automáticamente

### Plantillas de Email
- **`dose-omitted.blade.php`** - Notificación de dosis omitida
- **`adverse-effects.blade.php`** - Notificación de efectos adversos (CRÍTICA)

### Tipos de Notificaciones Implementadas
1. **🚨 Dosis Omitidas** - Prioridad alta/crítica
2. **⚠️ Efectos Adversos** - Prioridad crítica
3. **⏰ Dosis Tardías** - Prioridad media
4. **📋 Cambios de Tratamiento** - Prioridad alta
5. **🚨 Escalamiento** - Alertas por omisiones consecutivas

## 🛠️ Configuración e Instalación

### Paso 1: Configurar el Cron Job

Agrega esta línea al crontab de tu servidor para ejecutar cada 15 minutos:

```bash
# En el servidor, ejecuta:
crontab -e

# Agrega esta línea:
*/15 * * * * cd /var/www/meditrack && php artisan notifications:process-real-time >> /var/log/meditrack-notifications.log 2>&1
```

### Paso 2: Configurar en Laravel Scheduler (Alternativa)

Agrega al archivo `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Notificaciones críticas cada 15 minutos
    $schedule->command('notifications:process-real-time')
             ->everyFifteenMinutes()
             ->withoutOverlapping()
             ->appendOutputTo(storage_path('logs/notifications.log'));

    // Reportes de adherencia existentes
    $schedule->command('adherence:send-reports --type=weekly')
             ->weekly()
             ->mondays()
             ->at('09:00');

    $schedule->command('adherence:send-alerts --threshold=70')
             ->daily()
             ->at('08:00');
}
```

### Paso 3: Crear Logs Directory

```bash
# En el servidor
mkdir -p /var/log/meditrack
touch /var/log/meditrack-notifications.log
chown www-data:www-data /var/log/meditrack-notifications.log
```

## 🧪 Testing y Pruebas

### Probar en Modo Dry-Run

```bash
# Probar sin enviar emails reales
php artisan notifications:process-real-time --dry-run

# Probar solo dosis omitidas
php artisan notifications:process-real-time --dry-run --type=omitted

# Probar para un paciente específico
php artisan notifications:process-real-time --dry-run --patient-id=1

# Ver últimas 6 horas
php artisan notifications:process-real-time --dry-run --hours-back=6
```

### Probar Notificaciones Específicas

```bash
# 1. Crear una administración omitida para prueba
php artisan tinker

# En tinker:
$administracion = App\Models\Administracion::where('estado', 'Pendiente')->first();
$administracion->update(['estado' => 'Omitida']);

# 2. Procesar notificaciones
php artisan notifications:process-real-time --type=omitted --patient-id=1
```

### Probar Template de Email

```bash
php artisan tinker

# En tinker, probar envío manual:
$paciente = App\Models\Paciente::first();
$admin = App\Models\Administracion::first();
$alerta = App\Models\Alerta::first();
$user = App\Models\User::first();

$mail = App\Mail\RealTimeNotificationMail::doseOmitted($paciente, $admin, $alerta, $user);
Mail::to('tu-email@test.com')->send($mail);
```

## 📊 Monitoreo y Logs

### Ver Logs de Notificaciones

```bash
# Ver logs del comando
tail -f /var/log/meditrack-notifications.log

# Ver logs de Laravel
tail -f storage/logs/laravel.log | grep "notification"

# Ver alertas creadas recientemente
php artisan tinker
# En tinker:
App\Models\Alerta::where('created_at', '>=', now()->subHours(24))->get();
```

### Métricas Importantes

```bash
# Verificar cuántas notificaciones se han enviado hoy
php artisan tinker

# En tinker:
$today = App\Models\Alerta::whereDate('created_at', today())->count();
echo "Alertas creadas hoy: {$today}";

$omittedToday = App\Models\Alerta::where('tipo', 'dosis_omitida')
    ->whereDate('created_at', today())->count();
echo "Dosis omitidas hoy: {$omittedToday}";
```

## 🔧 Configuración Avanzada

### Personalizar Frecuencias

Para cambiar la frecuencia de notificaciones, modifica el cron job:

```bash
# Cada 10 minutos (más frecuente)
*/10 * * * * cd /var/www/meditrack && php artisan notifications:process-real-time

# Cada 30 minutos (menos frecuente)
*/30 * * * * cd /var/www/meditrack && php artisan notifications:process-real-time

# Solo horario laboral (8 AM - 6 PM)
*/15 8-18 * * * cd /var/www/meditrack && php artisan notifications:process-real-time
```

### Configurar Umbrales

En el archivo `RealTimeNotificationService.php` puedes ajustar:

```php
// Línea 120: Cambiar umbral para dosis tardías
->where('minutos_retraso', '>', 30) // Cambiar 30 por el valor deseado

// Línea 25: Cambiar umbral para escalamiento crítico
$priority = $consecutiveOmissions >= 3 ? 'critica' : 'alta'; // Cambiar 3 por el valor deseado
```

## 🚨 Troubleshooting

### Error: "Class RealTimeNotificationMail not found"

```bash
# Regenerar autoload
composer dump-autoload

# Limpiar cache
php artisan config:clear
php artisan cache:clear
```

### Error: "Table 'alerta' doesn't exist"

```bash
# Verificar que el modelo Alerta tenga la tabla correcta
php artisan tinker
# En tinker:
App\Models\Alerta::first(); // Debe funcionar sin errores
```

### Emails no se envían

```bash
# Verificar configuración de Resend
php artisan tinker
# En tinker:
config('mail.from.address'); // Debe mostrar tu email de Resend
config('services.resend.key'); // Debe mostrar tu API key
```

### Cron job no ejecuta

```bash
# Verificar que el cron esté activo
sudo service cron status

# Ver logs del cron
sudo tail -f /var/log/syslog | grep CRON

# Probar el comando manualmente
cd /var/www/meditrack && php artisan notifications:process-real-time --dry-run
```

## 📈 Próximos Pasos (Fases 2-4)

### Fase 2: Reportes Automáticos Ampliados
- Reportes diarios de adherencia
- Dashboard médico semanal
- Reportes mensuales consolidados

### Fase 3: Recordatorios Inteligentes
- Recordatorios pre-dosis
- Recordatorios de citas médicas
- Informes bajo demanda

### Fase 4: Personalización y Optimización
- Panel de configuración por usuario
- Inteligencia predictiva
- Análisis de efectividad

## 🎯 KPIs para Medir Éxito

- **Reducción de dosis omitidas**: Meta 15% en primer mes
- **Tiempo de respuesta a efectos adversos**: Meta <2 horas
- **Satisfacción del equipo médico**: Encuesta mensual
- **Adherencia general**: Meta incremento 10% en 3 meses

¿Listo para implementar? ¡Empecemos con las pruebas! 