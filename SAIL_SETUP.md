# 🚢 Laravel Sail - Configuración y Uso

Este documento describe cómo usar Laravel Sail en el proyecto Medi-Track.

## 📋 Configuración Completada

✅ **Laravel Sail instalado**: v1.43.1  
✅ **Docker Compose configurado**: Incluye MySQL y PHPMyAdmin  
✅ **Scripts npm agregados**: Para comandos frecuentes  
✅ **Variables de entorno**: Configuradas para Sail  

## 🚀 Inicio Rápido

### 1. Configuración Inicial (Solo una vez)
```bash
# Ejecutar el script de configuración
./setup-sail.sh

# O configurar manualmente:
# - Asegúrate de que Docker Desktop esté corriendo
# - Agrega las variables de Sail al .env (ver abajo)
# - Crea un alias: echo 'alias sail="./vendor/bin/sail"' >> ~/.zshrc
```

### 2. Iniciar el Entorno
```bash
# Usando el script directo
./vendor/bin/sail up -d

# O usando npm scripts
npm run sail:up

# O usando el alias (si configuraste el shell)
sail up -d
```

### 3. Detener el Entorno
```bash
./vendor/bin/sail down
# O: npm run sail:down
# O: sail down
```

## 🔧 Variables de Entorno Requeridas

Agrega estas variables a tu archivo `.env`:

```env
# Variables específicas para Laravel Sail
WWWUSER=501
WWWGROUP=20
APP_PORT=80
VITE_PORT=5173

# Puertos de reenvío para servicios
FORWARD_DB_PORT=3306
FORWARD_PHPMYADMIN_PORT=8081

# Configuración de Xdebug para Sail
SAIL_XDEBUG_MODE=off
SAIL_XDEBUG_CONFIG="client_host=host.docker.internal"
```

## 📝 Comandos Útiles

### Comandos Básicos
```bash
# Iniciar servicios en segundo plano
sail up -d

# Ver logs en tiempo real
sail logs -f

# Detener todos los servicios
sail down

# Reconstruir contenedores
sail build --no-cache
```

### Comandos de Laravel
```bash
# Artisan commands
sail artisan migrate
sail artisan db:seed
sail artisan queue:work

# Composer
sail composer install
sail composer update

# NPM
sail npm install
sail npm run dev
sail npm run build
```

### Comandos de Base de Datos
```bash
# Migrar base de datos
sail artisan migrate

# Resetear y sembrar base de datos
sail artisan migrate:fresh --seed

# Crear nueva migración
sail artisan make:migration create_example_table
```

## 🌐 URLs de Servicios

Una vez que los contenedores estén ejecutándose:

- **Aplicación principal**: http://localhost
- **PHPMyAdmin**: http://localhost:8081
- **Vite Dev Server**: http://localhost:5173

## 🗄️ Datos de Conexión a Base de Datos

Para conectarte desde herramientas externas:

- **Host**: localhost
- **Puerto**: 3306
- **Base de datos**: medi_track_production
- **Usuario**: medi_track_user
- **Contraseña**: MediTrack123!

## 🐛 Solución de Problemas

### Docker no está corriendo
```bash
# Verificar que Docker esté corriendo
docker info

# Si no está corriendo, inicia Docker Desktop
```

### Puertos ocupados
```bash
# Verificar qué está usando el puerto
lsof -i :80
lsof -i :3306

# Cambiar puertos en .env si es necesario
APP_PORT=8080
FORWARD_DB_PORT=3307
```

### Permisos en macOS
```bash
# Si tienes problemas de permisos
sail down
docker system prune -a
sail up -d
```

### Limpiar contenedores y volúmenes
```bash
# Detener todo
sail down

# Limpiar volúmenes (¡CUIDADO: elimina datos de BD!)
docker volume prune

# Reconstruir desde cero
sail build --no-cache
sail up -d
```

## 🔄 Workflows de Desarrollo

### Desarrollo Frontend
```bash
# Terminal 1: Iniciar servicios
sail up -d

# Terminal 2: Desarrollo frontend
sail npm run dev

# La aplicación estará en http://localhost
# Vite dev server en http://localhost:5173
```

### Desarrollo Backend
```bash
# Ejecutar migraciones
sail artisan migrate

# Ejecutar tests
sail artisan test

# Ver logs de la aplicación
sail logs laravel.test -f
```

### Deploy de Cambios
```bash
# Actualizar dependencias
sail composer install --no-dev --optimize-autoloader

# Compilar assets para producción
sail npm run build

# Limpiar caché
sail artisan config:cache
sail artisan route:cache
sail artisan view:cache
```

## 💡 Tips y Mejores Prácticas

1. **Siempre usa Sail para comandos de PHP/Laravel** cuando esté corriendo
2. **Usa el alias `sail`** para comandos más cortos
3. **Mantén Docker Desktop actualizado**
4. **Usa `sail down` antes de cerrar la laptop** para evitar problemas
5. **Monitorea el uso de recursos** especialmente en desarrollos largos

## 🔧 Scripts NPM Disponibles

```json
"sail:up": "./vendor/bin/sail up -d",
"sail:down": "./vendor/bin/sail down",
"sail:build": "./vendor/bin/sail build --no-cache",
"sail:fresh": "./vendor/bin/sail artisan migrate:fresh --seed",
"sail:test": "./vendor/bin/sail artisan test",
"sail:composer": "./vendor/bin/sail composer"
```

Uso: `npm run sail:up`, `npm run sail:down`, etc. 