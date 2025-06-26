#!/bin/bash

# Script de despliegue para Medi-Track en Digital Ocean
# Asegúrate de ejecutar: chmod +x deploy.sh

set -e

echo "🚀 Iniciando despliegue de Medi-Track..."

# Verificar que estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    echo "❌ Error: No se encontró composer.json. Ejecuta este script desde el directorio raíz del proyecto."
    exit 1
fi

# Verificar que Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Error: Docker no está instalado."
    exit 1
fi

# Verificar que Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Error: Docker Compose no está instalado."
    exit 1
fi

# Crear archivo .env para producción si no existe
if [ ! -f ".env" ]; then
    echo "📋 Creando archivo .env desde .env.production..."
    cp .env.production .env
    echo "⚠️  IMPORTANTE: Edita el archivo .env con tus configuraciones específicas antes de continuar."
    echo "⚠️  Especialmente: APP_KEY, DB_PASSWORD, DB_ROOT_PASSWORD, APP_URL"
    read -p "¿Has configurado el archivo .env? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Configuración cancelada. Configura .env y vuelve a ejecutar el script."
        exit 1
    fi
fi

# Generar APP_KEY si no existe
echo "🔑 Verificando APP_KEY..."
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generando APP_KEY..."
    # Generar una key temporal para usar con artisan
    APP_KEY=$(openssl rand -base64 32)
    sed -i.bak "s/APP_KEY=/APP_KEY=base64:$APP_KEY/" .env
fi

# Detener contenedores existentes
echo "🛑 Deteniendo contenedores existentes..."
docker-compose -f docker-compose.prod.yml down --remove-orphans || true

# Limpiar imágenes antiguas
echo "🧹 Limpiando imágenes antiguas..."
docker system prune -f || true

# Construir las imágenes
echo "🔨 Construyendo imágenes Docker..."
docker-compose -f docker-compose.prod.yml build --no-cache

# Iniciar los servicios
echo "🚀 Iniciando servicios..."
docker-compose -f docker-compose.prod.yml up -d

# Esperar a que MySQL esté listo
echo "⏳ Esperando a que MySQL esté listo..."
sleep 30

# Ejecutar migraciones y seeders
echo "🗄️  Ejecutando migraciones..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

echo "🌱 Ejecutando seeders..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan db:seed --force

# Limpiar cache
echo "🧹 Limpiando caché..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache

# Verificar el estado
echo "✅ Verificando el estado de los contenedores..."
docker-compose -f docker-compose.prod.yml ps

echo ""
echo "🎉 ¡Despliegue completado!"
echo "🌐 Tu aplicación debería estar disponible en: http://tu-servidor-ip"
echo ""
echo "📋 Comandos útiles:"
echo "  Ver logs: docker-compose -f docker-compose.prod.yml logs -f"
echo "  Reiniciar: docker-compose -f docker-compose.prod.yml restart"
echo "  Detener: docker-compose -f docker-compose.prod.yml down"
echo "  Acceder al contenedor: docker-compose -f docker-compose.prod.yml exec app bash" 