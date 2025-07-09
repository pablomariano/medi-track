#!/bin/bash
echo "🔍 Diagnóstico MediTrack - Servidor $(hostname) - $(date)"
echo "=================================================="

echo -e "\n1️⃣  SISTEMA:"
echo "OS: $(lsb_release -d -s 2>/dev/null || echo 'N/A')"
echo "IP: $(curl -s ifconfig.me 2>/dev/null || echo 'N/A')"
echo "Memoria: $(free -h | grep '^Mem:' | awk '{print $2,$3}')"
echo "Disco: $(df -h / | tail -1 | awk '{print $3"/"$2" ("$5")"}')"

echo -e "\n2️⃣  SERVICIOS:"
command -v docker >/dev/null && echo "✅ Docker: $(docker --version | cut -d' ' -f3)" || echo "❌ Docker: NO"
command -v docker-compose >/dev/null && echo "✅ Docker Compose: $(docker-compose --version | cut -d' ' -f3)" || echo "❌ Docker Compose: NO"
command -v php >/dev/null && echo "✅ PHP: $(php --version | head -1 | cut -d' ' -f2)" || echo "❌ PHP: NO"

echo -e "\n3️⃣  PROYECTO MEDITRACK:"
PROJECT_DIR="/var/www/meditrack"
if [ -d "$PROJECT_DIR" ]; then
    echo "✅ Directorio: $PROJECT_DIR"
    cd "$PROJECT_DIR"
    [ -f "composer.json" ] && echo "✅ composer.json" || echo "❌ composer.json"
    [ -f ".env" ] && echo "✅ .env" || echo "❌ .env"
    [ -f "docker-compose.prod.yml" ] && echo "✅ docker-compose.prod.yml" || echo "❌ docker-compose.prod.yml"
    [ -f "artisan" ] && echo "✅ artisan" || echo "❌ artisan"
else
    echo "❌ Directorio NO existe: $PROJECT_DIR"
fi

echo -e "\n4️⃣  CONFIGURACIÓN EMAIL:"
if [ -f "$PROJECT_DIR/.env" ]; then
    cd "$PROJECT_DIR"
    echo "APP_URL: $(grep "^APP_URL=" .env 2>/dev/null | cut -d= -f2- || echo 'NO_SET')"
    echo "MAIL_MAILER: $(grep "^MAIL_MAILER=" .env 2>/dev/null | cut -d= -f2 || echo 'NO_SET')"
    echo "MAIL_FROM: $(grep "^MAIL_FROM_ADDRESS=" .env 2>/dev/null | cut -d= -f2 || echo 'NO_SET')"
    RESEND_KEY=$(grep "^RESEND_KEY=" .env 2>/dev/null | cut -d= -f2 || echo 'NO_SET')
    if [[ "$RESEND_KEY" =~ ^re_[A-Za-z0-9] ]]; then
        echo "✅ RESEND_KEY: Configurado (${RESEND_KEY:0:8}...)"
    else
        echo "❌ RESEND_KEY: $RESEND_KEY"
    fi
else
    echo "❌ .env no encontrado"
fi

echo -e "\n5️⃣  DOCKER CONTAINERS:"
if [ -f "$PROJECT_DIR/docker-compose.prod.yml" ]; then
    cd "$PROJECT_DIR"
    echo "Estado de contenedores:"
    docker-compose -f docker-compose.prod.yml ps 2>/dev/null || echo "❌ No hay contenedores ejecutándose"
else
    echo "❌ docker-compose.prod.yml no encontrado"
fi

echo -e "\n6️⃣  PUERTOS ACTIVOS:"
ss -tlnp 2>/dev/null | grep -E ":80|:443|:3306|:6379|:8080" | awk '{print $1, $4}' || echo "❌ Ningún puerto web activo"

echo -e "\n7️⃣  CONECTIVIDAD WEB:"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://138.197.33.202 2>/dev/null || echo "000")
if [ "$HTTP_STATUS" = "200" ] || [ "$HTTP_STATUS" = "302" ]; then
    echo "✅ Web responde: HTTP $HTTP_STATUS"
else
    echo "❌ Web NO responde: HTTP $HTTP_STATUS"
fi

echo -e "\n8️⃣  ÚLTIMOS LOGS:"
if [ -f "$PROJECT_DIR/docker-compose.prod.yml" ]; then
    cd "$PROJECT_DIR"
    echo "Docker logs (últimas 3 líneas):"
    docker-compose -f docker-compose.prod.yml logs --tail=3 app 2>/dev/null || echo "❌ Sin logs de Docker"
fi

echo -e "\n9️⃣  TEST RESEND CONFIG:"
if [ -f "$PROJECT_DIR/.env" ] && docker-compose -f "$PROJECT_DIR/docker-compose.prod.yml" ps app 2>/dev/null | grep -q "Up"; then
    cd "$PROJECT_DIR"
    echo "Probando configuración Laravel..."
    docker-compose -f docker-compose.prod.yml exec -T app php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        echo 'MAIL_DEFAULT: ' . config('mail.default') . PHP_EOL;
        echo 'RESEND_KEY: ' . (config('services.resend.key') ? 'CONFIGURADO' : 'NO_CONFIGURADO') . PHP_EOL;
        echo 'FROM_ADDRESS: ' . config('mail.from.address') . PHP_EOL;
    " 2>/dev/null || echo "❌ Error al probar configuración Laravel"
else
    echo "❌ Aplicación no ejecutándose o .env faltante"
fi

echo -e "\n🏁 DIAGNÓSTICO COMPLETADO"
echo "📋 Copia toda esta salida y compártela para recibir ayuda"
echo "==================================================" 