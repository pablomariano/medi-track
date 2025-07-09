# Configuración de Resend para MediTrack

## 🚀 Instalación y Configuración Completa

### 1. Instalar Dependencias

```bash
# Instalar el SDK de Resend (ya incluido en composer.json)
composer install

# Si el SDK no se instaló automáticamente, ejecutar:
composer require resend/resend-php
```

### 2. Variables de Entorno Requeridas

Agrega las siguientes variables a tu archivo `.env`:

```env
# Mail Configuration
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="no-reply@meditrack.com"
MAIL_FROM_NAME="${APP_NAME}"

# Resend Configuration
RESEND_KEY=tu_clave_api_resend_aqui
```

### 3. Pasos para Configurar Resend

#### 3.1 Obtener API Key de Resend
1. Ve a [resend.com](https://resend.com) y crea una cuenta
2. En el dashboard, ve a "API Keys"
3. Crea una nueva API Key con permisos de "Sending access"
4. Copia la clave y agrégala a tu `.env` como `RESEND_KEY`

#### 3.2 Configurar Dominio (Opcional pero Recomendado)
1. En Resend dashboard, ve a "Domains"
2. Agrega tu dominio (ej: `meditrack.com`)
3. Configura los registros DNS como indica Resend
4. Actualiza `MAIL_FROM_ADDRESS` con tu dominio verificado

#### 3.3 Verificar Configuración
```bash
# Probar configuración básica
php artisan tinker

# En tinker, ejecutar:
Mail::raw('Test email from MediTrack', function($message) {
    $message->to('test@example.com')->subject('Test MediTrack Email');
});
```

## 📊 Sistema de Reportes de Adherencia

### Comandos Disponibles

#### Envío de Reportes Semanales
```bash
# Enviar reportes semanales a todos los pacientes
php artisan adherence:send-reports --type=weekly

# Enviar reporte a un paciente específico
php artisan adherence:send-reports --type=weekly --patient-id=1

# Modo dry-run (sin enviar emails)
php artisan adherence:send-reports --type=weekly --dry-run
```

#### Envío de Alertas de Adherencia
```bash
# Enviar alertas para pacientes con adherencia < 70%
php artisan adherence:send-alerts --threshold=70 --days=3

# Alerta para paciente específico
php artisan adherence:send-alerts --patient-id=1 --dry-run
```

### Programación Automática

Agrega a tu `app/Console/Kernel.php` o archivo de tareas programadas:

```php
// Reportes semanales todos los lunes a las 8:00 AM
$schedule->command('adherence:send-reports --type=weekly')
         ->weekly()
         ->mondays()
         ->at('08:00');

// Alertas diarias para adherencia baja
$schedule->command('adherence:send-alerts --threshold=70')
         ->daily()
         ->at('09:00');

// Reportes mensuales el primer día del mes
$schedule->command('adherence:send-reports --type=monthly')
         ->monthly()
         ->at('08:00');
```

## 📋 Limitaciones del Plan Gratuito de Resend

- ✅ **3,000 emails por mes**
- ✅ **100 emails por día**
- ✅ **Ideal para desarrollo y pruebas**
- ✅ **Perfecto para proyectos pequeños a medianos**

### Estimación de Uso para MediTrack:
- **Verificación de email**: ~30-50 emails/mes
- **Reportes semanales**: ~40-80 emails/mes (10-20 pacientes)
- **Alertas de adherencia**: ~20-40 emails/mes
- **Total estimado**: ~90-170 emails/mes

## 🔐 Seguridad y Mejores Prácticas

### Dominios Verificados Recomendados
Para producción, usa un dominio verificado:
- `no-reply@tudominio.com` - Para notificaciones automáticas
- `notificaciones@tudominio.com` - Para reportes de adherencia
- `alertas@tudominio.com` - Para alertas médicas críticas

### Variables de Entorno Seguras
```env
# Desarrollo
MAIL_FROM_ADDRESS="no-reply@meditrack-dev.com"

# Producción
MAIL_FROM_ADDRESS="no-reply@meditrack.com"
```

## 🧪 Testing

### Test de Verificación de Email
```bash
# Crear un usuario y probar verificación
php artisan tinker

# En tinker:
$user = App\Models\User::factory()->unverified()->create();
$user->sendEmailVerificationNotification();
```

### Test de Reportes de Adherencia
```bash
# Test con modo dry-run
php artisan adherence:send-reports --type=weekly --dry-run
php artisan adherence:send-alerts --threshold=80 --dry-run
```

## 🚨 Troubleshooting

### Error: "Driver [resend] not supported"
- Verificar que `resend/resend-php` esté instalado
- Revisar que `MAIL_MAILER=resend` en `.env`
- Ejecutar `php artisan config:clear`

### Emails no se envían
- Verificar API key de Resend
- Confirmar que el dominio esté verificado (si corresponde)
- Revisar logs: `php artisan log:show`

### Límite de envío alcanzado
- Verificar estadísticas en dashboard de Resend
- Considerar upgrade del plan si es necesario
- Implementar throttling en comandos programados 