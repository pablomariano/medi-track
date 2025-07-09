#!/bin/bash
# Script para transferir MediTrack al servidor
# Ejecutar desde tu máquina local

SERVER_IP="138.197.33.202"
SERVER_USER="root"
LOCAL_PROJECT_DIR="."
SERVER_PROJECT_DIR="/var/www/meditrack"

echo "🚀 Transferring MediTrack to server..."

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Verificar conexión SSH
print_info "Testing SSH connection..."
if ssh -o ConnectTimeout=5 $SERVER_USER@$SERVER_IP "echo 'SSH connection successful'"; then
    print_status "SSH connection working"
else
    echo "❌ Cannot connect to server. Check SSH keys or password."
    exit 1
fi

# Crear directorio en servidor
print_info "Creating project directory on server..."
ssh $SERVER_USER@$SERVER_IP "mkdir -p $SERVER_PROJECT_DIR"

# Transferir archivos principales (excluir node_modules, vendor, etc.)
print_info "Transferring project files..."
rsync -avz --progress \
    --exclude 'node_modules' \
    --exclude 'vendor' \
    --exclude '.git' \
    --exclude 'storage/app' \
    --exclude 'storage/framework/cache' \
    --exclude 'storage/framework/sessions' \
    --exclude 'storage/framework/views' \
    --exclude 'storage/logs' \
    --exclude '.env' \
    --exclude 'deploy-server.sh' \
    $LOCAL_PROJECT_DIR/ $SERVER_USER@$SERVER_IP:$SERVER_PROJECT_DIR/

# Transferir script de despliegue
print_info "Transferring deployment script..."
scp deploy-server.sh $SERVER_USER@$SERVER_IP:$SERVER_PROJECT_DIR/

# Hacer ejecutable el script
ssh $SERVER_USER@$SERVER_IP "chmod +x $SERVER_PROJECT_DIR/deploy-server.sh"

print_status "Transfer completed!"
print_info "Next steps:"
echo "1. SSH to your server: ssh $SERVER_USER@$SERVER_IP"
echo "2. Go to project directory: cd $SERVER_PROJECT_DIR"
echo "3. Run deployment script: ./deploy-server.sh"
echo "4. Follow the instructions in the script output"

print_warning "Files transferred. Now connect to your server to complete deployment." 