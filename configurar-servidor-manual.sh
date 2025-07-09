#!/bin/bash
# Configuración Manual del Servidor MediTrack
# Ejecutar en el servidor: root@138.197.33.202

set -e

echo "🛠️  Configuración Manual MediTrack"
echo "=================================="

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_status() { echo -e "${GREEN}✅ $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }

# 1. Verificar que los archivos fueron transferidos
echo "1️⃣  Verificando archivos..."
PROJECT_DIR="/var/www/meditrack"
if [ ! -d "$PROJECT_DIR" ]; then
    print_error "Directorio $PROJECT_DIR no existe"
    echo "Primero ejecuta el script de transferencia desde tu máquina local:"
    echo "./transfer-to-server.sh"
    exit 1
fi

cd "$PROJECT_DIR"
print_status "Directorio del proyecto encontrado"

# 2. Instalar dependencias si no están
echo -e "\n2️⃣  Instalando dependencias..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    print_status "Composer instalado"
fi

# 3. Crear archivo .env con configuración específica
echo -e "\n3️⃣  Configurando archivo .env..."
print_warning "Necesitarás ingresar tu API key de Resend"

read -p "Ingresa tu API key de Resend (re_...): " RESEND_KEY
if [[ ! "$RESEND_KEY" =~ ^re_[A-Za-z0-9] ]]; then
    print_error "API key inválida. Debe comenzar con 're_'"
    exit 1
fi

cat > .env << EOF
# Application Configuration
APP_NAME="MediTrack"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=America/Santiago
APP_URL=http://138.197.33.202
APP_LOCALE=es
APP_FALLBACK_LOCALE=en

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=meditrack_prod
DB_USERNAME=meditrack_user
DB_PASSWORD=meditrack_secure_2024

# Email Configuration with Resend
MAIL_MAILER=resend
RESEND_KEY=$RESEND_KEY
MAIL_FROM_ADDRESS=noreply@correos.meditrack.cl
MAIL_FROM_NAME="MediTrack"

# Cache and Queue
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Other settings
BROADCAST_CONNECTION=log
LOG_CHANNEL=stack
LOG_LEVEL=error
EOF

print_status "Archivo .env creado"

# 4. Generar APP_KEY
echo -e "\n4️⃣  Generando APP_KEY..."
if command -v php &> /dev/null; then
    php artisan key:generate --force
    print_status "APP_KEY generada"
else
    # Generar clave manualmente
    APP_KEY="base64:$(openssl rand -base64 32)"
    sed -i "s/APP_KEY=/APP_KEY=$APP_KEY/" .env
    print_status "APP_KEY generada manualmente"
fi

# 5. Crear docker-compose.prod.yml
echo -e "\n5️⃣  Creando configuración Docker..."
cat > docker-compose.prod.yml << 'EOF'
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: meditrack_app
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    environment:
      - APP_ENV=production
    depends_on:
      - mysql
      - redis
    networks:
      - meditrack

  mysql:
    image: mysql:8.0
    container_name: meditrack_mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: root_secure_2024
      MYSQL_DATABASE: meditrack_prod
      MYSQL_USER: meditrack_user
      MYSQL_PASSWORD: meditrack_secure_2024
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - meditrack

  redis:
    image: redis:7-alpine
    container_name: meditrack_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - meditrack

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: meditrack_phpmyadmin
    restart: unless-stopped
    environment:
      PMA_HOST: mysql
      PMA_USER: meditrack_user
      PMA_PASSWORD: meditrack_secure_2024
    ports:
      - "8080:80"
    depends_on:
      - mysql
    networks:
      - meditrack

volumes:
  mysql_data:

networks:
  meditrack:
    driver: bridge
EOF

print_status "docker-compose.prod.yml creado"

# 6. Crear Dockerfile.prod
echo -e "\n6️⃣  Creando Dockerfile de producción..."
cat > Dockerfile.prod << 'EOF'
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    zip \
    unzip \
    curl \
    oniguruma-dev \
    libxml2-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        xml \
        gd \
        curl \
        zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create user
RUN addgroup -g 1000 laravel && adduser -D -s /bin/sh -u 1000 -G laravel laravel

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=laravel:laravel . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories and set permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create nginx config
RUN mkdir -p /etc/nginx/http.d && cat > /etc/nginx/http.d/default.conf << 'NGINXEOF'
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINXEOF

# Create supervisor config
RUN cat > /etc/supervisor/conf.d/supervisord.conf << 'SUPEOF'
[supervisord]
nodaemon=true
user=root

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
SUPEOF

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
EOF

print_status "Dockerfile.prod creado"

# 7. Configurar permisos
echo -e "\n7️⃣  Configurando permisos..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
print_status "Permisos configurados"

# 8. Construir y ejecutar contenedores
echo -e "\n8️⃣  Construyendo y ejecutando aplicación..."
print_warning "Esto puede tomar varios minutos..."

# Detener contenedores existentes si los hay
docker-compose -f docker-compose.prod.yml down 2>/dev/null || true

# Construir y ejecutar
docker-compose -f docker-compose.prod.yml up -d --build

print_status "Contenedores iniciados"

# 9. Esperar a que la base de datos esté lista
echo -e "\n9️⃣  Esperando base de datos..."
sleep 30

# 10. Ejecutar migraciones
echo -e "\n🔟 Ejecutando migraciones..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:clear
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:clear

print_status "Migraciones ejecutadas"

# 11. Probar configuración de email
echo -e "\n📧 Probando configuración de email..."
docker-compose -f docker-compose.prod.yml exec -T app php -r "
    require 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo 'MAIL_DEFAULT: ' . config('mail.default') . PHP_EOL;
    echo 'RESEND_KEY: ' . (config('services.resend.key') ? 'CONFIGURADO' : 'NO_CONFIGURADO') . PHP_EOL;
    echo 'FROM_ADDRESS: ' . config('mail.from.address') . PHP_EOL;
    echo 'APP_URL: ' . config('app.url') . PHP_EOL;
"

echo -e "\n🎉 CONFIGURACIÓN COMPLETADA"
echo "=================================="
echo "✅ Aplicación: http://138.197.33.202"
echo "✅ PHPMyAdmin: http://138.197.33.202:8080"
echo "✅ Base de datos: meditrack_prod"
echo "✅ Usuario DB: meditrack_user"
echo "✅ Email configurado con Resend"
echo ""
echo "🧪 Para probar recuperación de contraseña:"
echo "1. Ve a http://138.197.33.202/forgot-password"
echo "2. Ingresa un email"
echo "3. Verifica que llegue el email"
echo ""
echo "📊 Estado de contenedores:"
docker-compose -f docker-compose.prod.yml ps 