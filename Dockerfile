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
RUN npm ci

# Copiar código fuente del frontend
COPY resources/ ./resources/
COPY public/ ./public/

# Build del frontend
RUN npm run build

# Etapa principal - PHP/Laravel
FROM php:8.3-fpm-alpine

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
    libzip-dev

# Instalar extensiones de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml \
    zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario para Laravel
RUN addgroup -g 1000 laravel && adduser -u 1000 -G laravel -s /bin/sh -D laravel

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Cambiar ownership del directorio de trabajo
RUN chown -R laravel:laravel /var/www/html

# Cambiar a usuario laravel para composer
USER laravel

# Copiar archivos de composer
COPY --chown=laravel:laravel composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Cambiar de vuelta a root para resto de configuración
USER root

# Copiar código fuente de Laravel (excluyendo .env)
COPY --chown=laravel:laravel . .

# Copiar archivo de configuración de producción
COPY --chown=laravel:laravel .env.production .env

# Copiar archivos construidos del frontend
COPY --from=frontend-builder --chown=laravel:laravel /app/public/build ./public/build

# Configurar Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Configurar PHP-FPM
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.conf

# Configurar Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Crear directorios necesarios y configurar permisos
RUN mkdir -p /var/log/nginx \
    && mkdir -p /var/log/supervisor \
    && mkdir -p storage/logs \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 755 storage \
    && chmod -R 755 bootstrap/cache

# Exponer puerto
EXPOSE 80

# Comando de inicio
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"] 