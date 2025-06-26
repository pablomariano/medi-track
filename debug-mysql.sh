#!/bin/bash

echo "🔍 Diagnóstico de Conectividad MySQL"
echo "=================================="

# Verificar contenedores
echo "📦 Estado de contenedores:"
docker-compose -f docker-compose.prod.yml ps

echo ""
echo "🏥 Estado de MySQL:"
docker-compose -f docker-compose.prod.yml exec mysql mysqladmin ping -h localhost || echo "❌ MySQL no responde"

echo ""
echo "🌐 Conectividad de red desde app a mysql:"
docker-compose -f docker-compose.prod.yml exec app ping -c 3 mysql || echo "❌ No se puede alcanzar MySQL"

echo ""
echo "🔧 Variables de entorno de DB en app:"
docker-compose -f docker-compose.prod.yml exec app env | grep DB_

echo ""
echo "📋 Logs recientes de MySQL:"
docker-compose -f docker-compose.prod.yml logs mysql --tail=10

echo ""
echo "📋 Logs recientes de App:"
docker-compose -f docker-compose.prod.yml logs app --tail=10

echo ""
echo "🔍 Intentando conexión desde Laravel:"
docker-compose -f docker-compose.prod.yml exec app php artisan db:show 2>&1 || echo "❌ Laravel no puede conectarse" 