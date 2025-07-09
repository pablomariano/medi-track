#!/bin/bash
# Script de Diagnóstico del Servidor MediTrack
# Ejecutar en el servidor para diagnosticar problemas

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_separator() {
    echo -e "${BLUE}================================${NC}"
}

echo "🔍 Diagnóstico del Servidor MediTrack"
print_separator

# 1. Verificar sistema operativo y recursos
print_info "1. INFORMACIÓN DEL SISTEMA"
echo "OS: $(lsb_release -d -s 2>/dev/null || echo 'N/A')"
echo "Memoria total: $(free -h | grep '^Mem:' | awk '{print $2}')"
echo "Espacio en disco: $(df -h / | tail -1 | awk '{print $4}' | sed 's/G/GB/')"
echo "Uptime: $(uptime -p)"
print_separator

# 2. Verificar servicios esenciales
print_info "2. SERVICIOS ESENCIALES"

# Docker
if command -v docker &> /dev/null; then
    print_status "Docker instalado: $(docker --version)"
    if systemctl is-active --quiet docker; then
        print_status "Docker está ejecutándose"
    else
        print_error "Docker NO está ejecutándose"
    fi
else
    print_error "Docker NO está instalado"
fi

# Docker Compose
if command -v docker-compose &> /dev/null; then
    print_status "Docker Compose instalado: $(docker-compose --version)"
else
    print_error "Docker Compose NO está instalado"
fi

# PHP
if command -v php &> /dev/null; then
    print_status "PHP instalado: $(php --version | head -1)"
else
    print_error "PHP NO está instalado"
fi

print_separator

# 3. Verificar directorio del proyecto
print_info "3. DIRECTORIO DEL PROYECTO"
PROJECT_DIR="/var/www/meditrack"
if [ -d "$PROJECT_DIR" ]; then
    print_status "Directorio del proyecto existe: $PROJECT_DIR"
    cd "$PROJECT_DIR"
    
    # Verificar archivos esenciales
    if [ -f "composer.json" ]; then
        print_status "composer.json existe"
    else
        print_error "composer.json NO existe"
    fi
    
    if [ -f ".env" ]; then
        print_status "Archivo .env existe"
    else
        print_error "Archivo .env NO existe"
    fi
    
    if [ -f "docker-compose.prod.yml" ]; then
        print_status "docker-compose.prod.yml existe"
    else
        print_error "docker-compose.prod.yml NO existe"
    fi
else
    print_error "Directorio del proyecto NO existe: $PROJECT_DIR"
    echo "Ejecuta el script de transferencia de archivos primero"
    exit 1
fi

print_separator

# 4. Verificar configuración de email en .env
print_info "4. CONFIGURACIÓN DE EMAIL"
if [ -f ".env" ]; then
    # Verificar variables de email
    MAIL_MAILER=$(grep "^MAIL_MAILER=" .env | cut -d= -f2 || echo "NO_SET")
    RESEND_KEY=$(grep "^RESEND_KEY=" .env | cut -d= -f2 || echo "NO_SET")
    MAIL_FROM_ADDRESS=$(grep "^MAIL_FROM_ADDRESS=" .env | cut -d= -f2 || echo "NO_SET")
    APP_URL=$(grep "^APP_URL=" .env | cut -d= -f2 || echo "NO_SET")
    
    if [ "$MAIL_MAILER" = "resend" ]; then
        print_status "MAIL_MAILER configurado como resend"
    else
        print_error "MAIL_MAILER: $MAIL_MAILER (debería ser 'resend')"
    fi
    
    if [[ "$RESEND_KEY" =~ ^re_[A-Za-z0-9] ]]; then
        print_status "RESEND_KEY configurado correctamente"
    else
        print_error "RESEND_KEY no está configurado o es inválido"
    fi
    
    if [[ "$MAIL_FROM_ADDRESS" =~ @correos\.meditrack\.cl$ ]]; then
        print_status "MAIL_FROM_ADDRESS: $MAIL_FROM_ADDRESS"
    else
        print_warning "MAIL_FROM_ADDRESS: $MAIL_FROM_ADDRESS (verifica el dominio)"
    fi
    
    echo "APP_URL: $APP_URL"
else
    print_error "Archivo .env no encontrado"
fi

print_separator

# 5. Verificar estado de los contenedores Docker
print_info "5. CONTENEDORES DOCKER"
if [ -f "docker-compose.prod.yml" ]; then
    echo "Estado de los contenedores:"
    docker-compose -f docker-compose.prod.yml ps
    
    # Verificar si los contenedores están ejecutándose
    if docker-compose -f docker-compose.prod.yml ps | grep -q "Up"; then
        print_status "Algunos contenedores están ejecutándose"
    else
        print_error "Ningún contenedor está ejecutándose"
    fi
else
    print_error "docker-compose.prod.yml no encontrado"
fi

print_separator

# 6. Verificar conectividad de red
print_info "6. CONECTIVIDAD DE RED"

# Verificar puertos abiertos
echo "Puertos en uso:"
ss -tlnp | grep -E ":80|:443|:3306|:6379|:8080" || echo "Ningún puerto web detectado"

# Verificar si la aplicación responde
APP_URL_CLEAN=$(echo "$APP_URL" | sed 's/[^a-zA-Z0-9.:/-]//g')
if curl -s -o /dev/null -w "%{http_code}" "$APP_URL_CLEAN" | grep -q "200\|302"; then
    print_status "Aplicación responde en $APP_URL_CLEAN"
else
    print_error "Aplicación NO responde en $APP_URL_CLEAN"
fi

print_separator

# 7. Verificar logs recientes
print_info "7. LOGS RECIENTES"
if [ -f "docker-compose.prod.yml" ]; then
    echo "Últimos logs de la aplicación:"
    docker-compose -f docker-compose.prod.yml logs --tail=10 app 2>/dev/null || echo "No se pudieron obtener logs"
fi

print_separator

# 8. Prueba de conectividad a base de datos
print_info "8. CONECTIVIDAD A BASE DE DATOS"
if docker-compose -f docker-compose.prod.yml ps mysql | grep -q "Up"; then
    print_status "Contenedor MySQL está ejecutándose"
    
    # Probar conexión desde el contenedor de la app
    if docker-compose -f docker-compose.prod.yml exec -T app php -r "
        try {
            \$pdo = new PDO('mysql:host=mysql;dbname=' . env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
            echo 'Conexión a MySQL: OK' . PHP_EOL;
        } catch (Exception \$e) {
            echo 'Error de conexión: ' . \$e->getMessage() . PHP_EOL;
        }
    " 2>/dev/null; then
        print_status "Conexión a base de datos exitosa"
    else
        print_error "Error de conexión a base de datos"
    fi
else
    print_error "Contenedor MySQL NO está ejecutándose"
fi

print_separator

# 9. Probar envío de email
print_info "9. PRUEBA DE ENVÍO DE EMAIL"
if docker-compose -f docker-compose.prod.yml ps app | grep -q "Up"; then
    echo "Probando configuración de email..."
    docker-compose -f docker-compose.prod.yml exec -T app php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        
        echo 'MAIL_MAILER: ' . config('mail.default') . PHP_EOL;
        echo 'RESEND_KEY: ' . (config('services.resend.key') ? 'Configurado' : 'NO configurado') . PHP_EOL;
        echo 'FROM_ADDRESS: ' . config('mail.from.address') . PHP_EOL;
    " 2>/dev/null || print_error "No se pudo verificar configuración de email"
else
    print_error "Contenedor de aplicación NO está ejecutándose"
fi

print_separator

echo "🏁 Diagnóstico completado"
echo ""
echo "📋 SIGUIENTE PASO:"
echo "Si hay errores, ejecuta el script de reparación: ./reparar-servidor.sh" 