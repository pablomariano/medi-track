# 🔍 Diagnóstico del Servidor MediTrack

## 📋 Instrucciones para Ejecutar Diagnóstico

### Paso 1: Conectar al Servidor
```bash
ssh root@138.197.33.202
```

### Paso 2: Transferir Script de Diagnóstico
Desde tu máquina local, ejecuta:
```bash
# Transferir el script de diagnóstico
scp diagnostico-servidor.sh root@138.197.33.202:/root/

# O si ya estás conectado al servidor, crear el script directamente
```

### Paso 3: Ejecutar en el Servidor
```bash
# Hacer el script ejecutable
chmod +x /root/diagnostico-servidor.sh

# Ejecutar el diagnóstico
/root/diagnostico-servidor.sh
```

### Paso 4: Copiar la Salida Completa
Una vez que ejecutes el script, copia **TODA** la salida y compártela para que pueda ayudarte a configurar Resend correctamente.

## 🎯 Información que Recolecta el Script

- ✅ Estado del sistema operativo y recursos
- ✅ Servicios instalados (Docker, PHP, etc.)
- ✅ Archivos del proyecto MediTrack
- ✅ Configuración de variables de ambiente
- ✅ Estado de contenedores Docker
- ✅ Conectividad de red y puertos
- ✅ Logs de aplicación
- ✅ Conectividad a base de datos
- ✅ Configuración de email/Resend

## 🚨 Si No Puedes Transferir el Script

Ejecuta este comando directamente en tu servidor para crear el script:

```bash
cat > diagnostico-servidor.sh << 'EOF'
#!/bin/bash
# Script de Diagnóstico del Servidor MediTrack

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() { echo -e "${GREEN}✅ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
print_separator() { echo -e "${BLUE}================================${NC}"; }

echo "🔍 Diagnóstico del Servidor MediTrack"
print_separator

# 1. Sistema
print_info "1. INFORMACIÓN DEL SISTEMA"
echo "OS: $(lsb_release -d -s 2>/dev/null || echo 'N/A')"
echo "Memoria: $(free -h | grep '^Mem:' | awk '{print $2}')"
echo "Disco: $(df -h / | tail -1 | awk '{print $4}')"
echo "IP Externa: $(curl -s ifconfig.me 2>/dev/null || echo 'N/A')"
print_separator

# 2. Servicios
print_info "2. SERVICIOS INSTALADOS"
command -v docker &> /dev/null && print_status "Docker: $(docker --version)" || print_error "Docker NO instalado"
command -v docker-compose &> /dev/null && print_status "Docker Compose: $(docker-compose --version)" || print_error "Docker Compose NO instalado"
command -v php &> /dev/null && print_status "PHP: $(php --version | head -1)" || print_error "PHP NO instalado"
command -v nginx &> /dev/null && print_status "Nginx: $(nginx -v 2>&1)" || echo "Nginx: NO instalado"
command -v apache2 &> /dev/null && print_status "Apache: $(apache2 -v | head -1)" || echo "Apache: NO instalado"
print_separator

# 3. Proyecto MediTrack
print_info "3. PROYECTO MEDITRACK"
PROJECT_DIR="/var/www/meditrack"
if [ -d "$PROJECT_DIR" ]; then
    print_status "Directorio existe: $PROJECT_DIR"
    cd "$PROJECT_DIR"
    [ -f "composer.json" ] && print_status "composer.json ✓" || print_error "composer.json ✗"
    [ -f ".env" ] && print_status ".env ✓" || print_error ".env ✗"
    [ -f "docker-compose.prod.yml" ] && print_status "docker-compose.prod.yml ✓" || print_error "docker-compose.prod.yml ✗"
    [ -f "artisan" ] && print_status "artisan ✓" || print_error "artisan ✗"
else
    print_error "Directorio NO existe: $PROJECT_DIR"
fi
print_separator

# 4. Variables de ambiente
print_info "4. CONFIGURACIÓN DE EMAIL (.env)"
if [ -f "$PROJECT_DIR/.env" ]; then
    cd "$PROJECT_DIR"
    echo "APP_URL: $(grep "^APP_URL=" .env | cut -d= -f2- || echo 'NO_SET')"
    echo "MAIL_MAILER: $(grep "^MAIL_MAILER=" .env | cut -d= -f2 || echo 'NO_SET')"
    echo "MAIL_FROM_ADDRESS: $(grep "^MAIL_FROM_ADDRESS=" .env | cut -d= -f2 || echo 'NO_SET')"
    RESEND_KEY=$(grep "^RESEND_KEY=" .env | cut -d= -f2 || echo 'NO_SET')
    if [[ "$RESEND_KEY" =~ ^re_[A-Za-z0-9] ]]; then
        print_status "RESEND_KEY: Configurado (re_***)"
    else
        print_error "RESEND_KEY: $RESEND_KEY"
    fi
else
    print_error "Archivo .env no encontrado"
fi
print_separator

# 5. Docker containers
print_info "5. CONTENEDORES DOCKER"
if [ -f "$PROJECT_DIR/docker-compose.prod.yml" ]; then
    cd "$PROJECT_DIR"
    docker-compose -f docker-compose.prod.yml ps 2>/dev/null || echo "Contenedores no ejecutándose"
else
    print_error "docker-compose.prod.yml no encontrado"
fi
print_separator

# 6. Puertos y Red
print_info "6. PUERTOS Y CONECTIVIDAD"
echo "Puertos abiertos:"
ss -tlnp | grep -E ":80|:443|:3306|:6379|:8080" || echo "Ningún puerto web activo"
echo ""
echo "Probando conectividad externa:"
curl -s -o /dev/null -w "HTTP %{http_code} - %{time_total}s" http://138.197.33.202 || echo "No responde en puerto 80"
print_separator

# 7. Logs
print_info "7. LOGS DEL SISTEMA"
echo "Últimos errores del sistema:"
journalctl --no-pager -n 5 -p err 2>/dev/null || echo "No se pueden leer logs del sistema"
echo ""
if [ -f "$PROJECT_DIR/docker-compose.prod.yml" ]; then
    echo "Logs de Docker:"
    cd "$PROJECT_DIR"
    docker-compose -f docker-compose.prod.yml logs --tail=5 2>/dev/null || echo "No hay logs de Docker"
fi
print_separator

# 8. Espacio y permisos
print_info "8. ESPACIO Y PERMISOS"
if [ -d "$PROJECT_DIR" ]; then
    echo "Tamaño del proyecto: $(du -sh $PROJECT_DIR 2>/dev/null || echo 'N/A')"
    echo "Permisos del directorio:"
    ls -la $PROJECT_DIR | head -5
fi
print_separator

# 9. Verificación de Resend
print_info "9. PRUEBA DE CONFIGURACIÓN RESEND"
if [ -f "$PROJECT_DIR/.env" ] && docker-compose -f "$PROJECT_DIR/docker-compose.prod.yml" ps app | grep -q "Up"; then
    cd "$PROJECT_DIR"
    echo "Probando desde contenedor de aplicación..."
    docker-compose -f docker-compose.prod.yml exec -T app php -r "
        echo 'Cargando Laravel...' . PHP_EOL;
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        echo 'MAIL_DEFAULT: ' . config('mail.default') . PHP_EOL;
        echo 'RESEND_KEY configurado: ' . (config('services.resend.key') ? 'SÍ' : 'NO') . PHP_EOL;
        echo 'FROM_ADDRESS: ' . config('mail.from.address') . PHP_EOL;
        echo 'APP_URL: ' . config('app.url') . PHP_EOL;
    " 2>/dev/null || print_error "No se pudo probar configuración de Laravel"
else
    print_error "Aplicación no está ejecutándose o .env no existe"
fi

print_separator
echo "🏁 DIAGNÓSTICO COMPLETADO"
echo "📋 Copia toda esta salida y compártela para recibir ayuda específica"
EOF

chmod +x diagnostico-servidor.sh
```

Luego ejecuta:
```bash
./diagnostico-servidor.sh
``` 