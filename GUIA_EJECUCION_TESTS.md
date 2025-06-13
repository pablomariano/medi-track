# GUÍA DE EJECUCIÓN DE TESTS - MEDI-TRACK

## ÍNDICE
1. [Configuración Inicial](#configuración-inicial)
2. [Comandos de Ejecución](#comandos-de-ejecución)
3. [Tipos de Tests](#tipos-de-tests)
4. [Cobertura de Código](#cobertura-de-código)
5. [Tests en CI/CD](#tests-en-cicd)
6. [Debugging y Troubleshooting](#debugging-y-troubleshooting)
7. [Mejores Prácticas](#mejores-prácticas)

---

## CONFIGURACIÓN INICIAL

### Requisitos Previos
- PHP 8.2+
- Composer
- Base de datos SQLite/MySQL/PostgreSQL
- Node.js (para tests frontend)

### Configuración del Entorno de Testing

```bash
# 1. Copiar archivo de configuración de testing
cp .env.example .env.testing

# 2. Configurar base de datos de testing en .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
# O para base de datos persistente:
# DB_DATABASE=database/testing.sqlite

# 3. Instalar dependencias
composer install
npm install

# 4. Generar clave de aplicación
php artisan key:generate --env=testing

# 5. Ejecutar migraciones de testing
php artisan migrate --env=testing

# 6. Instalar Pest (si no está incluido)
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev
```

### Configuración de Base de Datos

#### SQLite en Memoria (Recomendado para CI)
```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

#### SQLite Persistente (Para debugging)
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/testing.sqlite
```

#### MySQL/PostgreSQL (Para tests de integración)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medi_track_testing
DB_USERNAME=root
DB_PASSWORD=
```

---

## COMANDOS DE EJECUCIÓN

### Comandos Básicos

```bash
# Ejecutar todos los tests
composer test
# o
php artisan test

# Ejecutar con Pest
./vendor/bin/pest

# Ejecutar tests específicos por directorio
php artisan test tests/Unit/
php artisan test tests/Feature/

# Ejecutar test específico
php artisan test tests/Unit/TratamientoModelTest.php
```

### Comandos Avanzados

```bash
# Tests con cobertura de código
php artisan test --coverage
php artisan test --coverage-html coverage-report

# Tests con output detallado
php artisan test --verbose

# Tests en paralelo (requiere configuración)
php artisan test --parallel

# Tests con filtros
php artisan test --filter="test_can_create_treatment"
php artisan test --group=integration

# Re-ejecutar tests fallidos
php artisan test --fail-fast
```

### Scripts Personalizados

Agregar al `composer.json`:

```json
{
  "scripts": {
    "test": "php artisan test",
    "test:unit": "php artisan test --testsuite=Unit",
    "test:feature": "php artisan test --testsuite=Feature",
    "test:coverage": "php artisan test --coverage-text --coverage-clover=coverage.xml",
    "test:watch": "php artisan test --watch",
    "test:models": "php artisan test tests/Unit/ --filter=Model",
    "test:controllers": "php artisan test tests/Feature/ --filter=Controller",
    "test:integration": "php artisan test --group=integration",
    "test:critical": "php artisan test --group=critical"
  }
}
```

---

## TIPOS DE TESTS

### 1. Tests Unitarios (`tests/Unit/`)

**Propósito:** Testear componentes individuales aisladamente.

```bash
# Ejecutar solo tests unitarios
composer test:unit

# Tests específicos de modelos
php artisan test tests/Unit/TratamientoModelTest.php
php artisan test tests/Unit/AdministracionModelTest.php
php artisan test tests/Unit/AlertServiceTest.php
```

**Archivos principales:**
- `TratamientoModelTest.php` - Modelo de tratamientos
- `AdministracionModelTest.php` - Modelo de administraciones
- `AlertServiceTest.php` - Servicio de alertas
- `UserRegistrationServiceTest.php` - Servicio de registro

### 2. Tests de Feature (`tests/Feature/`)

**Propósito:** Testear flujos completos y endpoints.

```bash
# Ejecutar solo tests feature
composer test:feature

# Tests específicos de features
php artisan test tests/Feature/TratamientoFeatureTest.php
php artisan test tests/Feature/AdministracionMedicamentosTest.php
```

**Archivos principales:**
- `TratamientoFeatureTest.php` - Gestión de tratamientos
- `AdministracionMedicamentosTest.php` - Administración de medicamentos
- `MediTrackIntegrationTest.php` - Flujos completos

### 3. Tests de Integración

**Propósito:** Testear la interacción entre múltiples componentes.

```bash
# Ejecutar tests marcados como integración
php artisan test --group=integration
```

### 4. Tests de API

**Propósito:** Validar endpoints de API.

```bash
# Tests de API específicos
php artisan test tests/Feature/ --filter=Api
```

---

## COBERTURA DE CÓDIGO

### Generar Reportes de Cobertura

```bash
# Reporte en consola
php artisan test --coverage-text

# Reporte HTML (navegable)
php artisan test --coverage-html coverage-report

# Reporte XML (para CI)
php artisan test --coverage-clover coverage.xml

# Reporte completo
php artisan test --coverage-text --coverage-html coverage-report --coverage-clover coverage.xml
```

### Configuración de Cobertura

En `phpunit.xml`:

```xml
<coverage>
    <include>
        <directory suffix=".php">./app</directory>
    </include>
    <exclude>
        <directory>./app/Console</directory>
        <directory>./app/Exceptions</directory>
        <file>./app/Http/Kernel.php</file>
    </exclude>
    <report>
        <html outputDirectory="coverage-report"/>
        <clover outputFile="coverage.xml"/>
        <text outputFile="coverage.txt"/>
    </report>
</coverage>
```

### Umbrales de Cobertura

```bash
# Forzar cobertura mínima
php artisan test --coverage --min=80

# Por tipo de archivo
--coverage-php coverage.php
--coverage-json coverage.json
```

---

## TESTS EN CI/CD

### GitHub Actions

Crear `.github/workflows/tests.yml`:

```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  tests:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: [8.2, 8.3]
        database: [sqlite, mysql]

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: medi_track_testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, mysql, pdo_mysql

    - name: Copy environment file
      run: cp .env.example .env.testing

    - name: Install dependencies
      run: composer install --prefer-dist --no-progress --no-suggest

    - name: Generate key
      run: php artisan key:generate --env=testing

    - name: Run migrations
      run: php artisan migrate --env=testing

    - name: Execute tests
      run: php artisan test --coverage-clover coverage.xml

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
        fail_ci_if_error: true
```

### GitLab CI

Crear `.gitlab-ci.yml`:

```yaml
stages:
  - test
  - coverage

variables:
  MYSQL_ROOT_PASSWORD: password
  MYSQL_DATABASE: medi_track_testing

test:php82:
  stage: test
  image: php:8.2-cli
  services:
    - mysql:8.0
  before_script:
    - apt-get update -yqq
    - apt-get install -yqq git curl libmcrypt-dev libjpeg-dev libpng-dev libfreetype6-dev libbz2-dev
    - docker-php-ext-install pdo pdo_mysql
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install
    - cp .env.example .env.testing
    - php artisan key:generate --env=testing
  script:
    - php artisan migrate --env=testing
    - php artisan test --coverage-text --coverage-cobertura=coverage.xml
  artifacts:
    reports:
      coverage_report:
        coverage_format: cobertura
        path: coverage.xml
  coverage: '/^\s*Lines:\s*\d+.\d+\%/'
```

---

## DEBUGGING Y TROUBLESHOOTING

### Debug de Tests Fallidos

```bash
# Ejecutar test específico con debug
php artisan test tests/Unit/TratamientoModelTest.php --verbose

# Usar dd() o dump() en tests
public function test_example()
{
    $result = someFunction();
    dd($result); // Detiene ejecución y muestra datos
    // o
    dump($result); // Muestra datos y continúa
}

# Ver logs durante tests
tail -f storage/logs/laravel.log
```

### Tests con Base de Datos

```bash
# Limpiar base de datos entre tests
php artisan migrate:fresh --env=testing

# Seedear datos específicos
php artisan db:seed --class=TestSeeder --env=testing

# Ver queries SQL ejecutadas
DB::enableQueryLog();
// ... ejecutar código
dd(DB::getQueryLog());
```

### Problemas Comunes

#### Error de Memoria
```bash
# Incrementar límite de memoria
php -d memory_limit=512M artisan test
```

#### Tests Lentos
```bash
# Ejecutar en paralelo
php artisan test --parallel --processes=4
```

#### Fallos Intermitentes
```bash
# Ejecutar múltiples veces
for i in {1..10}; do php artisan test || break; done
```

---

## MEJORES PRÁCTICAS

### Estructura de Tests

```php
<?php
// tests/Unit/ExampleTest.php

describe('Component Name', function () {
    
    beforeEach(function () {
        // Setup común para todos los tests
        $this->user = User::factory()->create();
    });

    describe('Specific Functionality', function () {
        
        it('should perform expected behavior', function () {
            // Arrange
            $data = ['key' => 'value'];
            
            // Act
            $result = someFunction($data);
            
            // Assert
            expect($result)->toBe($expected);
        });
        
        it('should handle edge cases', function () {
            // Test edge cases
        });
        
        it('should validate inputs', function () {
            // Test validation
        });
    });
    
    afterEach(function () {
        // Cleanup si es necesario
    });
});
```

### Naming Conventions

```php
// ✅ Buenos nombres
it('creates treatment when valid data provided')
it('prevents duplicate administrations within interval')
it('calculates adherence percentage correctly')

// ❌ Malos nombres  
it('test 1')
it('works')
it('checks stuff')
```

### Factory Usage

```php
// ✅ Usar factories para datos de test
$patient = Paciente::factory()->create(['name' => 'Test Patient']);
$treatment = Tratamiento::factory()->create(['patient_id' => $patient->id]);

// ❌ Crear datos manualmente
$patient = new Paciente();
$patient->name = 'Test Patient';
$patient->save();
```

### Assertions

```php
// ✅ Assertions específicas
expect($response->status())->toBe(200);
expect($user->email)->toBe('test@example.com');
expect($collection)->toHaveCount(3);

// ❌ Assertions genéricas
expect($response->status())->toBeTruthy();
expect($user->email)->not->toBeNull();
```

### Test Data Management

```php
// ✅ Usar TestHelper para setup común
beforeEach(function () {
    $scenario = TestHelper::createCompleteScenario();
    $this->medico = $scenario['team']['medico'];
    $this->paciente = $scenario['paciente'];
});

// ✅ Limpiar estado entre tests
afterEach(function () {
    TestHelper::resetTestEnvironment();
});
```

### Performance

```bash
# Optimizar tests
- Usar base de datos en memoria
- Minimizar I/O operations
- Agrupar tests relacionados
- Usar parallel execution
- Evitar sleep() en tests

# Monitorear rendimiento
php artisan test --profile
```

---

## COMANDOS RÁPIDOS DE REFERENCIA

```bash
# Ejecución básica
composer test                    # Todos los tests
composer test:unit              # Solo unitarios
composer test:feature          # Solo feature
composer test:coverage         # Con cobertura

# Debugging
php artisan test --verbose      # Output detallado
php artisan test --filter=name  # Test específico
php artisan test --group=group  # Grupo específico

# Desarrollo
php artisan test --watch        # Re-ejecutar en cambios
php artisan test --fail-fast    # Parar en primer fallo
php artisan test --parallel     # Ejecución paralela

# CI/CD
php artisan test --coverage-clover coverage.xml
php artisan test --junit junit.xml
```

---

**Mantenido por:** Equipo de Desarrollo Medi-Track  
**Última actualización:** Enero 2025  
**Versión:** 1.0 