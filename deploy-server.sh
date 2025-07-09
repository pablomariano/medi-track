#!/bin/bash
# MediTrack Server Deployment Script
# Para usar en: root@138.197.33.202

set -e

echo "🚀 Starting MediTrack deployment with email system..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# 0. Verificar API key de Resend
print_info "Verificando configuración de Resend..."
if [ -z "$RESEND_API_KEY" ]; then
    print_warning "RESEND_API_KEY no está configurada como variable de ambiente."
    echo -n "Por favor, ingresa tu API key de Resend: "
    read -s RESEND_API_KEY
    echo
    if [ -z "$RESEND_API_KEY" ]; then
        print_error "API key de Resend es requerida para el despliegue."
        exit 1
    fi
fi
print_status "API key de Resend configurada correctamente"

# 1. Update system
print_info "Updating system packages..."
apt update && apt upgrade -y

# 2. Install required packages
print_info "Installing required packages..."
apt install -y curl wget git unzip software-properties-common

# 3. Install Docker and Docker Compose
print_info "Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    systemctl enable docker
    systemctl start docker
    print_status "Docker installed and started"
else
    print_status "Docker already installed"
fi

# 4. Install Docker Compose
print_info "Installing Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    print_status "Docker Compose installed"
else
    print_status "Docker Compose already installed"
fi

# 5. Install PHP and Composer (for local commands)
print_info "Installing PHP and Composer..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.4 php8.4-cli php8.4-common php8.4-curl php8.4-mbstring php8.4-xml php8.4-zip

if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    print_status "Composer installed"
fi

# 6. Create project directory
PROJECT_DIR="/var/www/meditrack"
print_info "Creating project directory: $PROJECT_DIR"
mkdir -p $PROJECT_DIR
cd $PROJECT_DIR

# 7. Set up the repository (if not exists)
if [ ! -d ".git" ]; then
    print_info "Initializing git repository..."
    git init
    print_warning "You'll need to add your repository remote and pull the code"
    echo "Run: git remote add origin YOUR_REPO_URL"
    echo "Run: git pull origin main"
else
    print_info "Pulling latest changes..."
    git pull origin main || print_warning "Could not pull changes. Check git configuration."
fi

# 8. Set up environment file
print_info "Setting up production environment file..."
if [ ! -f ".env" ]; then
    cat > .env << 'EOF'
# Application Configuration
APP_NAME="MediTrack"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=http://138.197.33.202
APP_LOCALE=es
APP_FALLBACK_LOCALE=en

# Database Configuration (Docker)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=meditrack_prod
DB_USERNAME=meditrack_user
DB_PASSWORD=secure_password_change_this

# Email Configuration with Resend
MAIL_MAILER=resend
RESEND_KEY=$RESEND_API_KEY
MAIL_FROM_ADDRESS=noreply@correos.meditrack.cl
MAIL_FROM_NAME="MediTrack"

# Cache and Queue Configuration
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis Configuration (Docker)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Broadcasting
BROADCAST_CONNECTION=log
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Filesystem
FILESYSTEM_DISK=local
EOF
    print_status "Environment file created"
else
    print_warning "Environment file already exists. Please update manually if needed."
fi

# 9. Set up production Docker Compose
print_info "Setting up production Docker Compose..."
cat > docker-compose.prod.yml << 'EOF'
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=production
    depends_on:
      - mysql
      - redis
    networks:
      - meditrack-network
    restart: unless-stopped

  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root_password_change_this
      MYSQL_DATABASE: meditrack_prod
      MYSQL_USER: meditrack_user
      MYSQL_PASSWORD: secure_password_change_this
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - meditrack-network
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - meditrack-network
    restart: unless-stopped

  phpmyadmin:
    image: phpmyadmin/phpmyadmin:latest
    ports:
      - "8080:80"
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
      PMA_USER: meditrack_user
      PMA_PASSWORD: secure_password_change_this
      MYSQL_ROOT_PASSWORD: root_password_change_this
    depends_on:
      - mysql
    networks:
      - meditrack-network
    restart: unless-stopped

volumes:
  mysql_data:
  redis_data:

networks:
  meditrack-network:
    driver: bridge
EOF

# 10. Create production Dockerfile
print_info "Creating production Dockerfile..."
cat > Dockerfile.prod << 'EOF'
FROM php:8.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/storage
RUN chmod -R 755 /var/www/html/bootstrap/cache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy Apache configuration
COPY .docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
EOF

# 11. Create Apache configuration
print_info "Creating Apache configuration..."
mkdir -p .docker/apache
cat > .docker/apache/000-default.conf << 'EOF'
<VirtualHost *:80>
    ServerAdmin admin@correos.meditrack.cl
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# 12. Set proper permissions
print_info "Setting proper permissions..."
chown -R www-data:www-data $PROJECT_DIR
chmod -R 755 $PROJECT_DIR
chmod -R 775 $PROJECT_DIR/storage
chmod -R 775 $PROJECT_DIR/bootstrap/cache

print_status "MediTrack deployment script completed!"
print_info "Next steps:"
echo "1. Update your repository URL in .env"
echo "2. Generate application key: php artisan key:generate"
echo "3. Run migrations: php artisan migrate"
echo "4. Build and start: docker-compose -f docker-compose.prod.yml up -d --build"
echo "5. Test email system: php artisan tinker"

print_warning "Remember to:"
echo "- Change database passwords in .env and docker-compose.prod.yml"
echo "- Configure SSL certificates for HTTPS"
echo "- Set up firewall rules"
echo "- Configure backups" 