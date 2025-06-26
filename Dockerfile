# Multi-stage build para Laravel con React/Inertia.js
FROM node:18-alpine AS frontend-builder

WORKDIR /app

# Copiar archivos de configuración de Node.js
COPY package*.json ./
COPY tsconfig.json ./
COPY vite.config.ts ./
COPY eslint.config.js ./
COPY components.json ./

# Instalar dependencias de Node.js
RUN npm ci --only=production

# Copiar código fuente del frontend
COPY resources/ ./resources/
COPY public/ ./public/

# Build del frontend
RUN npm run build

# Etapa principal - PHP/Laravel
FROM php:8.4-fpm-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libxml2-dev \
    mysql-client \
    nodejs \
    npm

# Instalar extensiones de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario para Laravel
RUN addgroup -g 1000 laravel && adduser -u 1000 -G laravel -s /bin/sh -D laravel

# Configurar Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Configurar Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de composer
COPY composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar código fuente de Laravel
COPY . .

# Copiar archivos construidos del frontend
COPY --from=frontend-builder /app/public/build ./public/build

# Configurar permisos
RUN chown -R laravel:laravel /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Crear directorio para logs de Nginx
RUN mkdir -p /var/log/nginx

# Exponer puerto
EXPOSE 80

# Comando de inicio
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"] 