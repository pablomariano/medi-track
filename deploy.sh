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
    echo "📋 Creando archivo .env para producción..."
    if [ -f ".env.production" ]; then
        cp .env.production .env
    elif [ -f ".env.example" ]; then
        cp .env.example .env
        echo "⚠️  Usando .env.example como base. Configura para producción."
    else
        echo "❌ No se encontró .env.production ni .env.example"
        exit 1
    fi
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
echo "🔍 Verificando conectividad con MySQL..."

# Función para verificar MySQL
wait_for_mysql() {
    local max_attempts=60
    local attempt=1
    
    echo "🕐 Esperando a que MySQL esté disponible..."
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose -f docker-compose.prod.yml exec -T mysql mysqladmin ping -h localhost --silent; then
            echo "✅ MySQL está listo!"
            return 0
        fi
        
        echo "⏳ Intento $attempt/$max_attempts - MySQL aún no está listo..."
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ MySQL no respondió después de $max_attempts intentos"
    return 1
}

# Ejecutar la función
if ! wait_for_mysql; then
    echo "❌ Error: No se pudo conectar a MySQL"
    echo "📋 Logs de MySQL:"
    docker-compose -f docker-compose.prod.yml logs mysql --tail=20
    exit 1
fi

# Verificar conectividad desde Laravel
echo "🔍 Verificando conectividad desde Laravel..."
if ! docker-compose -f docker-compose.prod.yml exec -T app php artisan db:show 2>/dev/null; then
    echo "⚠️  Conexión inicial fallida, esperando más tiempo..."
    sleep 15
    
    if ! docker-compose -f docker-compose.prod.yml exec -T app php artisan db:show 2>/dev/null; then
        echo "❌ Laravel no puede conectarse a MySQL"
        echo "📋 Configuración de red:"
        docker-compose -f docker-compose.prod.yml exec -T app ping -c 3 mysql || true
        echo "📋 Variables de entorno:"
        docker-compose -f docker-compose.prod.yml exec -T app env | grep DB_
        exit 1
    fi
fi

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