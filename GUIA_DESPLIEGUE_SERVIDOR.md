# 🚀 Guía de Despliegue MediTrack en Servidor

## 📋 Información del Servidor
- **IP:** `138.197.33.202`
- **Usuario:** `root`
- **Sistema:** Ubuntu/Debian (presumiblemente)

---

## 🛠️ Paso 1: Preparación Local (EN TU MÁQUINA)

### 1.1 Hacer Scripts Ejecutables
```bash
chmod +x deploy-server.sh
chmod +x transfer-to-server.sh
```

### 1.2 Transferir Archivos al Servidor
```bash
# Ejecutar desde tu directorio local de MediTrack
./transfer-to-server.sh
```

Este script:
- ✅ Verifica conexión SSH
- ✅ Crea directorio `/var/www/meditrack`
- ✅ Transfiere todos los archivos (excepto node_modules, vendor, .git)
- ✅ Transfiere script de despliegue

---

## 🌐 Paso 2: Conectar al Servidor

```bash
ssh root@138.197.33.202
```

Una vez conectado, navega al directorio:
```bash
cd /var/www/meditrack
ls -la  # Verificar que los archivos están ahí
```

---

## 🔧 Paso 3: Ejecutar Script de Despliegue

```bash
./deploy-server.sh
```

Este script automáticamente:
- ✅ Actualiza el sistema
- ✅ Instala Docker y Docker Compose
- ✅ Instala PHP 8.4 y Composer
- ✅ Configura archivo `.env` de producción
- ✅ Crea `docker-compose.prod.yml`
- ✅ Crea `Dockerfile.prod`
- ✅ Configura Apache
- ✅ Establece permisos correctos

---

## 🔐 Paso 4: Configuración Post-Instalación

### 4.1 Generar Clave de Aplicación
```bash
php artisan key:generate
```

### 4.2 Actualizar Contraseñas de Base de Datos
```bash
# Editar archivo .env
nano .env

# Cambiar estas líneas:
DB_PASSWORD=TU_PASSWORD_SEGURA_AQUI

# También editar docker-compose.prod.yml
nano docker-compose.prod.yml

# Cambiar estas líneas:
MYSQL_ROOT_PASSWORD: TU_ROOT_PASSWORD_AQUI
MYSQL_PASSWORD: TU_PASSWORD_SEGURA_AQUI
```

### 4.3 Configurar URL de Producción
```bash
# En .env, cambiar:
APP_URL=http://138.197.33.202
# O tu dominio si tienes uno configurado
```

---

## 🐳 Paso 5: Construir y Ejecutar Contenedores

```bash
# Construir y ejecutar en segundo plano
docker-compose -f docker-compose.prod.yml up -d --build
```

### 5.1 Verificar Estado de Contenedores
```bash
docker-compose -f docker-compose.prod.yml ps
```

Deberías ver:
- ✅ `app` - Puerto 80/443
- ✅ `mysql` - Puerto 3306  
- ✅ `redis` - Puerto 6379
- ✅ `phpmyadmin` - Puerto 8080

---

## 🗄️ Paso 6: Configurar Base de Datos

### 6.1 Ejecutar Migraciones
```bash
# Ejecutar dentro del contenedor de la aplicación
docker-compose -f docker-compose.prod.yml exec app php artisan migrate

# O instalar dependencias primero si es necesario
docker-compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader
docker-compose -f docker-compose.prod.yml exec app php artisan migrate
```

### 6.2 Limpiar Cache
```bash
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec app php artisan view:clear
docker-compose -f docker-compose.prod.yml exec app php artisan route:clear
```

---

## 📧 Paso 7: Probar Sistema de Emails

### 7.1 Verificar Configuración de Email
```bash
docker-compose -f docker-compose.prod.yml exec app php artisan tinker

# Dentro de Tinker:
echo config('mail.from.address');  // Debería mostrar: noreply@correos.meditrack.cl
echo config('mail.default');       // Debería mostrar: resend
echo env('RESEND_KEY') ? 'OK' : 'MISSING';  // Debería mostrar: OK
exit
```

### 7.2 Probar Envío de Email
```bash
docker-compose -f docker-compose.prod.yml exec app php artisan tinker

# Crear usuario de prueba y enviar reset de contraseña
$user = App\Models\User::create([
    'name' => 'Usuario Prueba',
    'email' => 'pablomariano@gmail.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now()
]);

use Illuminate\Support\Facades\Password;
$status = Password::sendResetLink(['email' => 'pablomariano@gmail.com']);
echo $status;  // Debería mostrar: passwords.sent
exit
```

---

## 🔐 Paso 8: Configurar Firewall (Importante)

```bash
# Instalar UFW si no está instalado
apt install ufw

# Configurar reglas básicas
ufw default deny incoming
ufw default allow outgoing

# Permitir SSH (¡IMPORTANTE!)
ufw allow 22

# Permitir HTTP y HTTPS
ufw allow 80
ufw allow 443

# Permitir PHPMyAdmin (opcional, solo si necesitas acceso externo)
ufw allow 8080

# Activar firewall
ufw enable

# Verificar estado
ufw status
```

---

## 🌍 Paso 9: Verificar Funcionamiento

### 9.1 Acceder a la Aplicación
- **Aplicación principal:** http://138.197.33.202
- **PHPMyAdmin:** http://138.197.33.202:8080

### 9.2 Probar Funcionalidades
1. **Registro de usuario** - Verificar que llega email de confirmación
2. **Recuperación de contraseña** - Ir a `/forgot-password`
3. **Login** - Verificar que funciona correctamente

---

## 📊 Paso 10: Configurar Reportes Automáticos

### 10.1 Configurar Cron Jobs
```bash
# Editar crontab
crontab -e

# Agregar estas líneas para reportes automáticos:
# Reportes semanales (todos los lunes a las 8:00 AM)
0 8 * * 1 cd /var/www/meditrack && docker-compose -f docker-compose.prod.yml exec -T app php artisan adherence:send-reports

# Alertas diarias (todos los días a las 10:00 AM)
0 10 * * * cd /var/www/meditrack && docker-compose -f docker-compose.prod.yml exec -T app php artisan adherence:send-alerts

# Limpiar logs semanalmente
0 2 * * 0 cd /var/www/meditrack && docker-compose -f docker-compose.prod.yml exec -T app php artisan log:clear
```

---

## 🔧 Comandos Útiles de Mantenimiento

### Reiniciar Servicios
```bash
# Reiniciar todos los contenedores
docker-compose -f docker-compose.prod.yml restart

# Reiniciar solo la aplicación
docker-compose -f docker-compose.prod.yml restart app
```

### Ver Logs
```bash
# Logs de la aplicación
docker-compose -f docker-compose.prod.yml logs app

# Logs en tiempo real
docker-compose -f docker-compose.prod.yml logs -f app
```

### Backups
```bash
# Backup de base de datos
docker-compose -f docker-compose.prod.yml exec mysql mysqldump -u meditrack_user -p meditrack_prod > backup_$(date +%Y%m%d).sql

# Backup de archivos
tar -czf meditrack_backup_$(date +%Y%m%d).tar.gz /var/www/meditrack
```

---

## ✅ Checklist Final

- [ ] ✅ Archivos transferidos al servidor
- [ ] ✅ Script de despliegue ejecutado
- [ ] ✅ Contenedores Docker ejecutándose
- [ ] ✅ Migraciones de base de datos aplicadas
- [ ] ✅ Sistema de emails funcionando
- [ ] ✅ Firewall configurado
- [ ] ✅ Aplicación accesible en http://138.197.33.202
- [ ] ✅ Cron jobs configurados (opcional)
- [ ] ✅ Backups configurados (recomendado)

---

## 🆘 Solución de Problemas

### Problema: Contenedores no inician
```bash
docker-compose -f docker-compose.prod.yml logs
```

### Problema: Error de permisos
```bash
chown -R www-data:www-data /var/www/meditrack
chmod -R 775 /var/www/meditrack/storage
```

### Problema: Emails no se envían
```bash
# Verificar configuración
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
# Luego verificar: config('mail.from.address')
```

---

## 🎯 ¡Listo para Producción!

Una vez completados todos los pasos, MediTrack estará completamente operacional con:

✅ **Sistema de emails funcionando** (Resend + dominio verificado)  
✅ **Recuperación de contraseñas**  
✅ **Reportes automáticos de adherencia**  
✅ **Alertas de baja adherencia**  
✅ **Arquitectura Docker escalable**  
✅ **Base de datos MySQL**  
✅ **Cache Redis**  
✅ **Seguridad con firewall**  

🚀 **¡Tu aplicación MediTrack está lista para usuarios reales!** 