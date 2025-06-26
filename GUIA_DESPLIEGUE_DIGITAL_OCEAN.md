# 🚀 Guía de Despliegue en Digital Ocean

Esta guía te ayudará a desplegar tu aplicación Medi-Track en Digital Ocean usando Docker.

## 📋 Prerrequisitos

1. **Cuenta en Digital Ocean**
2. **Droplet Ubuntu 22.04 LTS** (mínimo 2GB RAM recomendado)
3. **Dominio configurado** (opcional, pero recomendado)

## 🛠️ Configuración del Servidor

### 1. Conectar al Droplet
```bash
ssh root@tu-ip-del-droplet
```

### 2. Actualizar el sistema
```bash
apt update && apt upgrade -y
```

### 3. Instalar Docker y Docker Compose
```bash
# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Instalar Docker Compose
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Verificar instalación
docker --version
docker-compose --version
```

### 4. Configurar Firewall
```bash
# Permitir puertos necesarios
ufw allow 22    # SSH
ufw allow 80    # HTTP
ufw allow 443   # HTTPS
ufw enable
```

## 📦 Despliegue de la Aplicación

### 1. Subir el código al servidor

#### Opción A: Git (Recomendado)
```bash
# En el servidor
cd /var/www
git clone https://github.com/tu-usuario/medi-track.git
cd medi-track
```

#### Opción B: SCP/SFTP
```bash
# Desde tu máquina local
scp -r . root@tu-ip:/var/www/medi-track/
```

### 2. Configurar variables de entorno
```bash
cd /var/www/medi-track
cp .env.production .env

# Editar configuraciones
nano .env
```

**Configuraciones importantes en `.env`:**
```env
APP_URL=https://tu-dominio.com
DB_DATABASE=medi_track_production
DB_USERNAME=medi_track_user
DB_PASSWORD=tu_password_muy_seguro
DB_ROOT_PASSWORD=tu_root_password_muy_seguro
```

### 3. Hacer ejecutable el script de despliegue
```bash
chmod +x deploy.sh
```

### 4. Ejecutar el despliegue
```bash
./deploy.sh
```

## 🔧 Configuraciones Adicionales

### SSL/HTTPS con Let's Encrypt

1. **Instalar Certbot:**
```bash
apt install certbot
```

2. **Obtener certificado:**
```bash
certbot certonly --standalone -d tu-dominio.com
```

3. **Crear configuración SSL para Nginx:**
```bash
mkdir -p docker/ssl
cp /etc/letsencrypt/live/tu-dominio.com/fullchain.pem docker/ssl/
cp /etc/letsencrypt/live/tu-dominio.com/privkey.pem docker/ssl/
```

4. **Crear configuración del proxy:**
```bash
cat > docker/proxy.conf << 'EOF'
server {
    listen 443 ssl http2;
    server_name tu-dominio.com;

    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;

    location / {
        proxy_pass http://app:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

server {
    listen 80;
    server_name tu-dominio.com;
    return 301 https://$server_name$request_uri;
}
EOF
```

### Backup Automático

1. **Crear script de backup:**
```bash
cat > backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/medi-track"
mkdir -p $BACKUP_DIR

# Backup de la base de datos
docker-compose -f docker-compose.prod.yml exec -T mysql mysqldump -u root -p$DB_ROOT_PASSWORD medi_track_production > $BACKUP_DIR/db_backup_$DATE.sql

# Backup de archivos de storage
tar -czf $BACKUP_DIR/storage_backup_$DATE.tar.gz storage/

# Limpiar backups antiguos (mantener últimos 7 días)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
EOF

chmod +x backup.sh
```

2. **Configurar cron para backup diario:**
```bash
crontab -e
# Agregar línea:
0 2 * * * /var/www/medi-track/backup.sh
```

## 🎯 Comandos Útiles

### Gestión de contenedores
```bash
# Ver estado
docker-compose -f docker-compose.prod.yml ps

# Ver logs
docker-compose -f docker-compose.prod.yml logs -f

# Reiniciar aplicación
docker-compose -f docker-compose.prod.yml restart app

# Acceder al contenedor
docker-compose -f docker-compose.prod.yml exec app bash

# Actualizar aplicación
git pull
docker-compose -f docker-compose.prod.yml build app
docker-compose -f docker-compose.prod.yml up -d
```

### Comandos Laravel dentro del contenedor
```bash
# Limpiar cache
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache

# Ejecutar migraciones
docker-compose -f docker-compose.prod.yml exec app php artisan migrate

# Ver rutas
docker-compose -f docker-compose.prod.yml exec app php artisan route:list
```

## 🔍 Troubleshooting

### Problema: Aplicación no carga
```bash
# Verificar logs
docker-compose -f docker-compose.prod.yml logs app

# Verificar permisos
docker-compose -f docker-compose.prod.yml exec app chown -R laravel:laravel /var/www/html/storage
```

### Problema: Error de base de datos
```bash
# Verificar conexión a MySQL
docker-compose -f docker-compose.prod.yml exec mysql mysql -u root -p

# Recrear base de datos
docker-compose -f docker-compose.prod.yml exec app php artisan migrate:fresh --seed --force
```

### Problema: Assets no cargan
```bash
# Rebuild frontend
docker-compose -f docker-compose.prod.yml exec app npm run build
```

## 📊 Monitoreo

### Logs del sistema
```bash
# Logs de aplicación
tail -f /var/log/docker-compose.log

# Uso de recursos
docker stats

# Espacio en disco
df -h
```

### Configurar alertas (opcional)
Considera usar servicios como:
- Digital Ocean Monitoring
- Uptime Robot
- New Relic

## 🔒 Seguridad

1. **Cambiar puerto SSH por defecto**
2. **Configurar fail2ban**
3. **Mantener sistema actualizado**
4. **Usar claves SSH en lugar de contraseñas**
5. **Configurar backup regular**

## 🎉 ¡Listo!

Tu aplicación Medi-Track debería estar funcionando en:
- **HTTP:** `http://tu-ip-del-droplet`
- **HTTPS:** `https://tu-dominio.com` (si configuraste SSL)

Para soporte adicional, revisa los logs y la documentación de Laravel. 