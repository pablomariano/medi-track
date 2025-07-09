# 🔐 Seguridad de API Keys - MediTrack

## ⚠️ PROBLEMA SOLUCIONADO

Se detectó y corrigió una **exposición crítica de seguridad**: la API key de Resend estaba hardcodeada en el archivo `deploy-server.sh` y siendo rastreada por git.

## ✅ Correcciones Aplicadas

### 1. Script de Despliegue Seguro
- ❌ **ANTES**: API key hardcodeada en `deploy-server.sh`
- ✅ **AHORA**: Script solicita la API key como variable de ambiente o input del usuario

### 2. Uso Seguro del Script
```bash
# Opción 1: Variable de ambiente (recomendado)
RESEND_API_KEY=tu_api_key_real ./deploy-server.sh

# Opción 2: Input interactivo
./deploy-server.sh
# El script solicitará la API key de forma segura
```

### 3. Archivo .env.example
Crear este archivo manualmente para documentar las variables necesarias:

```bash
# Crear .env.example (sin valores sensibles)
cat > .env.example << 'EOF'
APP_NAME="MediTrack"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=America/Santiago
APP_URL=http://localhost

APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=medi_track
DB_USERNAME=sail
DB_PASSWORD=

CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
MAIL_MAILER=resend
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@correos.meditrack.cl"
MAIL_FROM_NAME="${APP_NAME}"

# Resend Configuration
RESEND_KEY=your_resend_api_key_here

VITE_APP_NAME="${APP_NAME}"

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
EOF
```

## 🛡️ Buenas Prácticas de Seguridad

### 1. Variables de Ambiente
```bash
# En tu perfil de shell (~/.bashrc o ~/.zshrc)
export RESEND_API_KEY="tu_api_key_real"
```

### 2. Archivos Protegidos por .gitignore
El `.gitignore` ya está configurado correctamente para proteger:
- `.env`
- `.env.backup`  
- `.env.production`
- `.env.clean`
- `.env.old`

### 3. Verificación de Seguridad
```bash
# Verificar que las API keys no estén en el repositorio
git log --all --full-history -- "*" | grep -i "resend"
grep -r "re_[A-Za-z0-9]" . --exclude-dir=node_modules --exclude-dir=vendor
```

## 🚨 Acciones Inmediatas Requeridas

### 1. Regenerar API Key (CRÍTICO)
Dado que la API key estuvo expuesta:
1. Ve a [Resend Dashboard](https://resend.com/dashboard)
2. Desactiva la API key actual: `re_YHrNScci_Kztb9gp7hFLDti4stzx2DwxS`
3. Genera una nueva API key
4. Actualiza tu archivo `.env` local con la nueva key

### 2. Limpiar Historial de Git (OPCIONAL)
Si quieres eliminar la API key del historial de git:
```bash
# ⚠️ CUIDADO: Esto reescribe el historial
git filter-branch --force --index-filter \
'git rm --cached --ignore-unmatch deploy-server.sh' \
--prune-empty --tag-name-filter cat -- --all

# Forzar push (solo si es necesario)
git push origin --force --all
```

### 3. Configurar Variables de Ambiente
```bash
# En desarrollo
echo 'export RESEND_API_KEY="tu_nueva_api_key"' >> ~/.bashrc
source ~/.bashrc

# En producción (servidor)
echo 'export RESEND_API_KEY="tu_nueva_api_key"' >> /root/.bashrc
```

## 📋 Lista de Verificación

- [x] ✅ API key removida de `deploy-server.sh`
- [x] ✅ Script modificado para usar variables de ambiente
- [x] ✅ Documentación de seguridad creada
- [ ] ⏳ **PENDIENTE**: Regenerar API key en Resend
- [ ] ⏳ **PENDIENTE**: Crear `.env.example` manualmente
- [ ] ⏳ **PENDIENTE**: Configurar variable de ambiente en servidor

## 🔍 Verificación Final

```bash
# Verificar que no hay API keys expuestas
grep -r "re_[A-Za-z0-9]" . --exclude-dir=node_modules --exclude-dir=vendor --exclude="*.md"

# El comando anterior NO debería devolver resultados
```

## 🆘 En Caso de Emergencia

Si sospechas que tu API key está comprometida:
1. **Inmediatamente** desactiva la key en Resend Dashboard
2. Genera una nueva API key
3. Actualiza todos tus entornos
4. Monitorea el uso en Resend Dashboard por actividad inusual

---

**✅ Tu API key ahora está protegida y el despliegue es seguro.** 