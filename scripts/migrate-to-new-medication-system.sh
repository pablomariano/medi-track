#!/bin/bash

# =================================================
# SCRIPT DE MIGRACIÓN AL NUEVO SISTEMA DE MEDICAMENTOS
# Medi-Track - Migración Segura
# =================================================

echo "🚀 Iniciando migración al nuevo sistema de medicamentos..."
echo "=================================================="

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo artisan. Ejecuta este script desde la raíz del proyecto Laravel."
    exit 1
fi

echo "📋 Paso 1: Verificando estado actual de la base de datos..."
php artisan migrate:status

echo ""
echo "⚠️  IMPORTANTE: Este proceso hará lo siguiente:"
echo "   1. Creará backup de la tabla 'medicamentos' actual"
echo "   2. Reemplazará la tabla con nueva estructura compleja"
echo "   3. Creará todas las nuevas tablas del sistema"
echo "   4. Insertará datos de prueba básicos"
echo ""

read -p "¿Deseas continuar? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Migración cancelada por el usuario."
    exit 1
fi

echo ""
echo "💾 Paso 2: Creando backup de la base de datos actual..."
timestamp=$(date +"%Y%m%d_%H%M%S")
backup_file="database/backups/backup_before_medication_migration_${timestamp}.sql"

# Crear directorio de backups si no existe
mkdir -p database/backups

# Crear backup (ajusta según tu configuración de DB)
if command -v mysqldump &> /dev/null; then
    echo "Creando backup MySQL..."
    # Descomentar y ajustar según tu configuración:
    # mysqldump -u root -p your_database_name > $backup_file
    echo "⚠️  Recuerda hacer backup manual de tu base de datos"
elif [ -f "database/database.sqlite" ]; then
    echo "Copiando base de datos SQLite..."
    cp database/database.sqlite "database/backups/database_backup_${timestamp}.sqlite"
    echo "✅ Backup SQLite creado: database/backups/database_backup_${timestamp}.sqlite"
fi

echo ""
echo "🔄 Paso 3: Ejecutando migraciones del nuevo sistema..."

# Ejecutar las migraciones una por una para mejor control
migrations=(
    "2024_12_18_000001_create_principios_activos_table"
    "2024_12_18_000002_create_formas_farmaceuticas_table"
    "2024_12_18_000003_create_vias_administracion_table"
    "2024_12_18_000004_create_unidades_medida_table"
    "2024_12_18_000005_backup_and_recreate_medicamentos_table"
    "2024_12_18_000006_create_tratamientos_table"
    "2024_12_18_000007_create_medicamentos_tratamientos_table"
    "2024_12_18_000008_create_esquemas_posologicos_table"
    "2024_12_18_000009_create_dosis_prn_table"
    "2024_12_18_000010_create_interacciones_medicamentos_table"
    "2024_12_18_000011_create_administraciones_medicamentos_table"
    "2024_12_18_000012_create_autorizaciones_tratamiento_table"
    "2024_12_18_000013_create_alertas_medicamentos_table"
    "2024_12_18_000014_create_historial_tratamientos_table"
    "2024_12_18_000015_seed_basic_medication_data"
)

for migration in "${migrations[@]}"; do
    echo "  📦 Ejecutando: $migration"
    if php artisan migrate --path="database/migrations/${migration}.php" --step; then
        echo "  ✅ $migration - Completada"
    else
        echo "  ❌ Error en $migration"
        echo "  🛑 Deteniendo migración. Revisa los errores arriba."
        exit 1
    fi
done

echo ""
echo "🎯 Paso 4: Verificando estado final..."
php artisan migrate:status

echo ""
echo "📊 Paso 5: Mostrando resumen de nuevas tablas..."
echo "   📁 Tablas de catálogo:"
echo "      - principios_activos"
echo "      - formas_farmaceuticas"
echo "      - vias_administracion"
echo "      - unidades_medida"
echo ""
echo "   💊 Medicamentos:"
echo "      - medicamentos (nueva estructura)"
echo "      - medicamentos_backup (datos anteriores)"
echo ""
echo "   🏥 Tratamientos:"
echo "      - tratamientos"
echo "      - medicamentos_tratamientos"
echo "      - esquemas_posologicos"
echo "      - dosis_prn"
echo ""
echo "   🔐 Integración y Seguridad:"
echo "      - administraciones_medicamentos"
echo "      - autorizaciones_tratamiento"
echo "      - alertas_medicamentos"
echo "      - historial_tratamientos"
echo "      - interacciones_medicamentos"

echo ""
echo "✅ ¡Migración completada exitosamente!"
echo "=================================================="
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Actualizar modelos Eloquent para las nuevas relaciones"
echo "2. Actualizar controladores para manejar nuevas estructuras"
echo "3. Crear seeders para poblar datos de catálogo"
echo "4. Implementar lógica de detección de interacciones"
echo "5. Crear interfaces de usuario para el nuevo sistema"
echo ""
echo "💾 BACKUP: Los datos anteriores están en 'medicamentos_backup'"
echo "🔄 ROLLBACK: Si necesitas revertir, usa: php artisan migrate:rollback --step=15"
echo ""

# Opcional: Mostrar algunos datos de prueba
echo "🔍 Datos de prueba insertados:"
echo ""
echo "Principios activos:"
php artisan tinker --execute="echo App\Models\PrincipioActivo::select('nombre_generico', 'grupo_farmacologico')->get();"
echo ""
echo "Medicamentos:"
php artisan tinker --execute="echo App\Models\Medicamento::with('principioActivo')->select('nombre_comercial', 'concentracion')->get();"

echo ""
echo "🎉 Sistema de medicamentos listo para usar!" 