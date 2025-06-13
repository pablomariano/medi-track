# PLAN DE TESTING - SISTEMA MEDI-TRACK

## ÍNDICE
1. [Objetivo y Alcance](#objetivo-y-alcance)
2. [Estrategia de Testing](#estrategia-de-testing)
3. [Tests Unitarios](#tests-unitarios)
4. [Tests de Integración](#tests-de-integración)
5. [Tests Funcionales/Feature](#tests-funcionalesfeature)
6. [Tests de API](#tests-de-api)
7. [Tests de Frontend](#tests-de-frontend)
8. [Criterios de Aceptación](#criterios-de-aceptación)
9. [Estructura de Ejecución](#estructura-de-ejecución)

---

## OBJETIVO Y ALCANCE

### Objetivo Principal
Garantizar la calidad, confiabilidad y seguridad del sistema Medi-Track, enfocándose en las funcionalidades críticas de gestión médica y administración de medicamentos.

### Alcance del Testing
- **Gestión de Usuarios** (Médicos, Cuidadores, Apoderados, Pacientes)
- **Gestión de Pacientes** y relaciones
- **Catálogo de Medicamentos**
- **Tratamientos Programados y PRN**
- **Administración de Medicamentos**
- **Sistema de Alertas**
- **Cronogramas y Seguimiento**
- **Reportes y Estadísticas**
- **Seguridad y Permisos**

---

## ESTRATEGIA DE TESTING

### Pirámide de Testing
```
           E2E Tests (5%)
       ├─────────────────────┤
      Integration Tests (25%)
   ├──────────────────────────────┤
  Unit Tests (70%)
├─────────────────────────────────────┤
```

### Tecnologías Utilizadas
- **Backend:** PHPUnit + Pest (Laravel)
- **Frontend:** Vitest + React Testing Library
- **E2E:** Playwright (futuro)
- **Mock/Stub:** Mockery (Laravel)

---

## TESTS UNITARIOS

### 1. MODELOS (Models)

#### Tratamiento Model
- ✅ Constantes de tipos y estados
- ✅ Relaciones (paciente, médico, medicamentos)
- ✅ Scopes (activos, programados, PRN)
- ✅ Métodos de utilidad (isActivo, isPrn)
- ✅ Validaciones de datos

#### Administracion Model
- ✅ Estados de administración
- ✅ Cálculo de ventanas de tolerancia
- ✅ Relaciones con medicamentos y pacientes
- ✅ Scopes de estado

#### Medicamento Model
- ✅ Validaciones de concentración
- ✅ Relaciones con tratamientos
- ✅ Búsquedas por principio activo

#### Paciente Model
- ✅ Validaciones de datos personales
- ✅ Relaciones con apoderados y cuidadores
- ✅ Cálculo de edad

#### User Model (Roles)
- ✅ Asignación de roles
- ✅ Permisos por rol
- ✅ Validaciones específicas por tipo

### 2. SERVICIOS (Services)

#### UserRegistrationService
- ✅ Registro por tipo de usuario
- ✅ Validaciones específicas
- ✅ Asignación de roles correcta

#### TreatmentCalculationService
- ✅ Cálculo de horarios programados
- ✅ Ventanas de tolerancia
- ✅ Validaciones PRN

#### AlertService
- ✅ Generación de alertas
- ✅ Criterios de notificación
- ✅ Priorización de alertas

---

## TESTS DE INTEGRACIÓN

### 1. BASE DE DATOS
- ✅ Migraciones completas
- ✅ Seeding de datos de prueba
- ✅ Relaciones entre entidades
- ✅ Constraints e integridad referencial

### 2. FLUJOS COMPLETOS
- ✅ Creación de tratamiento con medicamentos
- ✅ Administración de dosis programada
- ✅ Proceso PRN completo
- ✅ Generación de alertas automáticas

---

## TESTS FUNCIONALES/FEATURE

### 1. AUTENTICACIÓN Y AUTORIZACIÓN
- ✅ Login/logout exitoso
- ✅ Acceso restringido por rol
- ✅ Redirección según permisos
- ✅ Protección de rutas sensibles

### 2. GESTIÓN DE USUARIOS
- ✅ Crear usuario por tipo
- ✅ Editar información
- ✅ Desactivar usuario
- ✅ Validaciones de formulario

### 3. GESTIÓN DE PACIENTES
- ✅ Registro completo de paciente
- ✅ Asignación de cuidadores
- ✅ Vinculación con apoderados
- ✅ Actualización de historial médico

### 4. MEDICAMENTOS
- ✅ Agregar al catálogo
- ✅ Búsqueda y filtrado
- ✅ Edición de información
- ✅ Validaciones farmacológicas

### 5. TRATAMIENTOS
- ✅ Crear tratamiento programado
- ✅ Crear tratamiento PRN
- ✅ Editar tratamiento existente
- ✅ Cambiar estado del tratamiento
- ✅ Validar restricciones médicas

### 6. ADMINISTRACIÓN
- ✅ Registrar dosis programada
- ✅ Administración PRN por síntomas
- ✅ Marcar dosis como omitida
- ✅ Reportar efectos adversos
- ✅ Validar ventanas de tiempo

### 7. CRONOGRAMA
- ✅ Vista diaria de medicamentos
- ✅ Vista semanal completa
- ✅ Filtros por paciente/medicamento
- ✅ Alertas visuales de estado

### 8. REPORTES
- ✅ Reporte de adherencia
- ✅ Estadísticas de medicación PRN
- ✅ Exportación en múltiples formatos
- ✅ Filtros de fecha y paciente

---

## TESTS DE API

### 1. ENDPOINTS PRINCIPALES
- ✅ GET /api/tratamientos - Lista tratamientos
- ✅ POST /api/tratamientos - Crear tratamiento
- ✅ PUT /api/tratamientos/{id} - Actualizar
- ✅ DELETE /api/tratamientos/{id} - Eliminar

- ✅ GET /api/administraciones - Lista administraciones
- ✅ POST /api/administraciones - Registrar dosis
- ✅ PATCH /api/administraciones/{id}/estado - Cambiar estado

- ✅ GET /api/cronograma - Obtener cronograma
- ✅ GET /api/alertas - Lista de alertas
- ✅ POST /api/alertas/{id}/procesar - Procesar alerta

### 2. VALIDACIONES API
- ✅ Estructura de respuesta JSON
- ✅ Códigos de estado HTTP correctos
- ✅ Validación de parámetros
- ✅ Manejo de errores

---

## TESTS DE FRONTEND

### 1. COMPONENTES REACT
- ✅ Renderizado correcto de formularios
- ✅ Validaciones en tiempo real
- ✅ Estados de carga
- ✅ Manejo de errores

### 2. NAVEGACIÓN E INTERACCIÓN
- ✅ Routing entre páginas
- ✅ Menú lateral funcional
- ✅ Modales y diálogos
- ✅ Tablas con paginación

### 3. DASHBOARD
- ✅ Widgets informativos
- ✅ Gráficos de estadísticas
- ✅ Alertas en tiempo real
- ✅ Accesos rápidos

---

## CRITERIOS DE ACEPTACIÓN

### Cobertura de Código
- **Mínimo requerido:** 80%
- **Objetivo:** 90%
- **Crítico (modelos core):** 95%

### Performance
- **Tiempo de respuesta API:** < 200ms
- **Carga de página:** < 3s
- **Query de base de datos:** < 100ms

### Seguridad
- ✅ Validación de permisos
- ✅ Sanitización de inputs
- ✅ Protección CSRF
- ✅ Autenticación JWT válida

### Funcionalidad Crítica
- ✅ 100% flujos de administración
- ✅ 100% cálculos de dosis
- ✅ 100% sistema de alertas
- ✅ 100% validaciones médicas

---

## ESTRUCTURA DE EJECUCIÓN

### Comandos de Testing
```bash
# Tests completos
composer test

# Solo tests unitarios
php artisan test --testsuite=Unit

# Solo tests feature
php artisan test --testsuite=Feature

# Con cobertura
php artisan test --coverage

# Tests específicos
php artisan test tests/Feature/TratamientoTest.php
```

### Pipeline CI/CD
1. **Validación sintáctica** (Pint, ESLint)
2. **Tests unitarios** (rápidos)
3. **Tests de integración** (base de datos)
4. **Tests funcionales** (flujos completos)
5. **Análisis de cobertura**
6. **Deploy condicional**

### Entornos de Testing
- **Local:** SQLite en memoria
- **CI:** PostgreSQL/MySQL
- **Staging:** Réplica de producción

---

## CASOS DE PRUEBA PRIORITARIOS

### P0 - Crítico (Bloqueante)
1. Login de usuarios por rol
2. Crear tratamiento programado
3. Registrar administración de medicamento
4. Generar alertas por dosis omitida
5. Validar restricciones PRN

### P1 - Alto (Importante)
1. Editar tratamiento existente
2. Cronograma diario completo
3. Reportes de adherencia
4. Gestión de pacientes
5. Catálogo de medicamentos

### P2 - Medio (Deseable)
1. Estadísticas avanzadas
2. Exportación de reportes
3. Configuración de alertas
4. Dashboard interactivo
5. Búsquedas y filtros

### P3 - Bajo (Mejoras)
1. Interfaz responsive
2. Performance optimizada
3. Validaciones UX
4. Accesibilidad
5. Integración externa

---

**Fecha de creación:** Enero 2025  
**Versión:** 1.0  
**Responsable:** Equipo de Desarrollo Medi-Track 