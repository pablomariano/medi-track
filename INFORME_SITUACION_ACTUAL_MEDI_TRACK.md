# 📋 INFORME SITUACIÓN ACTUAL - MEDI-TRACK
## Análisis Completo del Sistema y Plan de Simplificación

**Fecha de Análisis**: 15 de Enero de 2025  
**Estado Actual**: Sistema funcional con alta complejidad y granularidad  
**Propósito**: Reducir funcionalidades y simplificar estructura de datos

---

## 🏗️ ARQUITECTURA GENERAL

### Stack Tecnológico
- **Backend**: Laravel 12 con Inertia.js 2.0
- **Frontend**: React 19 + TypeScript + Tailwind CSS 4.0
- **Base de Datos**: SQLite (desarrollo) 
- **UI Components**: Radix UI + shadcn/ui
- **Charts**: Recharts 2.15.3
- **Testing**: Pest 3.8
- **Containerización**: Docker + Docker Compose

---

## 🗄️ ENTIDADES PRINCIPALES DEL SISTEMA

### **1. SISTEMA DE USUARIOS (5 ENTIDADES)**

#### **Usuario Base** (`users`)
- **Campos**: id, name, email, password, telefono, rol_id, activo, email_verified_at, ultimo_acceso
- **Propósito**: Tabla central de autenticación
- **Relaciones**: Pertenece a un rol, se extiende a tipos específicos

#### **Roles** (`roles`)  
- **Tipos definidos**: admin, medico, cuidador, apoderado, paciente
- **Sistema**: Roles fijos con permisos específicos por módulo
- **Complejidad**: 28 permisos organizados en 7 módulos

#### **Personal Médico** (`personal_medico`)
- **Campos específicos**: especialidad, numero_colegiatura, institucion, anos_experiencia
- **Propósito**: Datos profesionales del médico
- **Relación 1:1 con usuarios**

#### **Cuidadores** (`cuidadores`)
- **Campos específicos**: certificaciones, experiencia_anos, disponibilidad_horaria, tarifa_hora
- **Propósito**: Personal de enfermería/cuidado
- **Relación 1:1 con usuarios**

#### **Apoderados** (`apoderados`)
- **Campos específicos**: relacion_paciente, es_contacto_emergencia
- **Propósito**: Familiares/tutores responsables
- **Relación 1:1 con usuarios**

### **2. SISTEMA DE PACIENTES (1 ENTIDAD PRINCIPAL + 3 RELACIONES)**

#### **Pacientes** (`pacientes`)
- **Datos básicos**: nombre, fecha_nacimiento, numero_documento, tipo_documento
- **Datos médicos**: genero_id, tipo_sangre, altura, direccion, telefono_emergencia
- **Observaciones**: observaciones_medicas, activo
- **Relación opcional**: usuario_id (puede no tener cuenta de usuario)

#### **Relaciones Paciente-Personal** (Tablas Pivot)
- **`paciente_medicos`**: Asignación médico-paciente (con médico principal)
- **`paciente_cuidadores`**: Asignación cuidador-paciente (con fechas vigencia)
- **`paciente_apoderados`**: Vinculación apoderado-paciente

### **3. SISTEMA DE MEDICAMENTOS ACTUAL (1 ENTIDAD SIMPLE)**

#### **Medicamentos** (`medicamentos`) - **TABLA SIMPLE ACTUAL**
- **Campos básicos**: id, nombre, medida, unidad_medida, descripcion
- **Estado**: Implementación básica sin información farmacológica
- **Limitaciones**: No hay relaciones con tratamientos, sin datos de principios activos

### **4. SISTEMA DE TRATAMIENTOS (ESTRUCTURA COMPLEJA IMPLEMENTADA)**

#### **Tratamientos** (`tratamientos`)
- **Tipos**: Programado (horarios fijos) y PRN (por necesidad)
- **Estados**: Activo, Pausado, Completado, Suspendido
- **Datos**: nombre, diagnostico, objetivo, fechas inicio/fin, observaciones
- **Relación**: paciente_id, medico_usuario_id

#### **MedicamentoTratamiento** (`medicamentos_tratamientos`) - **TABLA PIVOT COMPLEJA**
- **Dosificación**: dosis_cantidad, unidad_dosis, frecuencia_horas
- **Ventanas de tolerancia**: tolerancia_antes_minutos, tolerancia_despues_minutos
- **Límites PRN**: intervalo_minimo_horas, dosis_maxima_dia, dosis_maxima_semana
- **Control**: estado, motivo_suspension, orden

#### **Indicaciones PRN** (`indicaciones_prn`)
- **Relación**: medicamento_tratamiento_id, sintoma_prn_id, criterio_prn_id
- **Datos**: descripcion_personalizada

#### **Horarios Programados** (`horarios_programados`)
- **Relación**: medicamento_tratamiento_id
- **Datos**: dia_semana, hora_administracion, activo

### **5. SISTEMA DE ADMINISTRACIÓN (3 ENTIDADES)**

#### **Administraciones** (`administraciones`)
- **Estados**: Programada, Administrada, Omitida, Tardia
- **Datos**: fecha_programada, fecha_real_administracion, dosis_administrada
- **Personal**: administrado_por_usuario_id
- **Observaciones**: observaciones, efectos_adversos

#### **Estadísticas Consumo** (`estadisticas_consumo`)
- **Métricas**: adherencia_porcentaje, dosis_administradas, dosis_omitidas
- **Períodos**: estadisticas por paciente y fecha

#### **Alertas** (`alertas`)
- **Tipos**: Dosis omitida, Interacción medicamentosa, Efecto adverso
- **Estados**: Pendiente, Vista, Procesada
- **Datos**: descripcion, nivel_urgencia, fecha_generacion

### **6. SISTEMA DE AUDITORÍA (2 ENTIDADES)**

#### **Audit Logs** (`audit_logs`)
- **Acciones**: create, update, delete, access  
- **Datos**: usuario_id, tabla_afectada, registro_id, datos_anteriores, datos_nuevos
- **Contexto**: ip_address, user_agent, contexto_adicional

#### **Permisos Temporales** (`permisos_temporales`)
- **Control temporal**: fecha_inicio, fecha_fin, motivo
- **Auditoría**: otorgado_por, ip_otorgamiento, ultimo_uso, veces_usado

---

## 🔄 PROCESOS PRINCIPALES DEL SISTEMA

### **1. GESTIÓN DE USUARIOS Y ASIGNACIONES**

#### **Registro de Personal Médico**
1. Admin crea usuario médico con datos básicos
2. Sistema genera registro en `personal_medico` con datos profesionales
3. Asignación automática de rol "medico" con permisos correspondientes
4. Envío de credenciales por email

#### **Asignación Médico-Paciente**
1. Admin/Médico accede al módulo de asignaciones
2. Selecciona paciente y médico disponible
3. Define si es médico principal o especialista
4. Establece fechas de vigencia y especialidad de tratamiento
5. Sistema valida que solo haya un médico principal por paciente

#### **Asignación Cuidador-Paciente**
1. Personal autorizado accede a asignaciones de cuidadores
2. Selecciona paciente y cuidador disponible
3. Define fechas de asignación y tipo de cuidado
4. Sistema registra la asignación con estado activo

### **2. PRESCRIPCIÓN DE TRATAMIENTOS**

#### **Tratamiento Programado** (Medicación con horarios fijos)
1. Médico accede al perfil del paciente
2. Crea nuevo tratamiento tipo "Programado"
3. Selecciona medicamento del catálogo básico
4. Configura dosificación:
   - Cantidad y unidad de dosis
   - Frecuencia en horas (ej: cada 8 horas)
   - Ventanas de tolerancia (antes/después)
   - Duración del tratamiento
5. Sistema genera automáticamente horarios programados
6. Crea registros pendientes en tabla `administraciones`

#### **Tratamiento PRN** (Por Necesidad según Síntomas)
1. Médico crea tratamiento tipo "PRN"
2. Selecciona medicamento y configura restricciones:
   - Dosis máxima por día
   - Intervalo mínimo entre dosis
   - Máximo de dosis consecutivas
3. Define indicaciones específicas:
   - Síntomas que ameritan administración
   - Criterios de evaluación (escalas de dolor, temperatura, etc.)
4. Sistema valida disponibilidad antes de cada administración PRN

### **3. ADMINISTRACIÓN DE MEDICAMENTOS**

#### **Administración Programada**
1. Cuidador accede al cronograma del día
2. Identifica dosis pendientes por paciente
3. Verifica información del medicamento y paciente
4. Registra administración:
   - Confirma dosis completa o parcial
   - Hora real de administración
   - Observaciones si las hay
5. Sistema actualiza estado y calcula métricas de adherencia

#### **Administración PRN**
1. Cuidador evalúa síntomas del paciente
2. Accede al módulo PRN para ese paciente
3. Sistema verifica automáticamente:
   - Criterios de administración cumplidos
   - Intervalos mínimos respetados
   - Límites diarios no excedidos
4. Si las validaciones pasan, registra:
   - Síntoma y nivel de intensidad
   - Administración con hora y dosis
   - Observaciones del resultado

### **4. SEGUIMIENTO Y CONTROL**

#### **Generación de Alertas**
1. Sistema ejecuta validaciones periódicas:
   - Dosis omitidas fuera de ventana de tolerancia
   - Patrones de baja adherencia
   - Vencimientos de tratamientos
2. Genera alertas automáticas con niveles de urgencia
3. Notifica a roles correspondientes según tipo de alerta

#### **Cálculo de Estadísticas**
1. Sistema procesa diariamente las administraciones
2. Calcula métricas por paciente:
   - Porcentaje de adherencia
   - Dosis administradas vs programadas
   - Tendencias temporales
3. Actualiza tabla `estadisticas_consumo`
4. Genera datos para dashboard y reportes

---

## 📊 FUNCIONALIDADES ACTUALES IMPLEMENTADAS

### **✅ MÓDULOS COMPLETAMENTE FUNCIONALES**

#### **1. Gestión de Usuarios** 
- CRUD completo para todos los tipos de usuario
- Sistema unificado de creación por tipo
- Validaciones específicas por rol
- Interfaz React con formularios dinámicos

#### **2. Gestión de Pacientes**
- CRUD completo con datos médicos
- Manejo de relaciones con personal médico
- Asignación de cuidadores y apoderados
- Historial de relaciones

#### **3. Sistema de Medicamentos Básico**
- Catálogo simple de medicamentos  
- CRUD básico sin información farmacológica
- Búsqueda y filtrado simple

#### **4. Dashboard Interactivo**
- Estadísticas en tiempo real
- Gráficos de adherencia semanal
- Métricas por paciente
- Actividad reciente del sistema

#### **5. Sistema de Tratamientos**
- Creación de tratamientos programados y PRN
- Configuración compleja de dosificación
- Manejo de horarios programados
- Validaciones de administración PRN

#### **6. Administración de Medicamentos**
- Cronograma diario de administraciones
- Registro de dosis administradas/omitidas
- Control de ventanas de tolerancia
- Gestión de administraciones PRN con validaciones

#### **7. Sistema de Asignaciones**
- Asignación médico-paciente con médico principal
- Asignación cuidador-paciente con fechas
- Historial de asignaciones
- Control de vigencia

#### **8. Sistema de Auditoría**
- Logging completo de acciones del sistema
- Rastreo de cambios en datos críticos
- Dashboard de auditoría para administradores
- Permisos temporales con expiración automática

### **🚧 MÓDULOS PARCIALMENTE IMPLEMENTADOS**

#### **1. Sistema de Alertas**
- Estructura básica creada
- Faltan triggers automáticos
- Interface básica sin notificaciones en tiempo real

#### **2. Reportes y Estadísticas Avanzadas**
- Estadísticas básicas funcionando
- Faltan reportes médicos específicos
- Sin exportación de datos

#### **3. Cronograma Avanzado**
- Vista básica implementada
- Falta integración completa con validaciones
- Sin vista semanal/mensual

---

## 🔍 ANÁLISIS DE COMPLEJIDAD ACTUAL

### **⚠️ PROBLEMAS IDENTIFICADOS**

#### **1. ALTA GRANULARIDAD EN DATOS**
- **Tabla MedicamentoTratamiento**: 13 campos específicos por relación
- **Sistema PRN**: Múltiples tablas para criterios y síntomas
- **Ventanas de tolerancia**: Configuración muy específica por medicamento
- **Auditoría exhaustiva**: Registra cada cambio con contexto completo

#### **2. EXCESO DE ENTIDADES DE CONFIGURACIÓN**
- **Síntomas PRN**: Tabla separada para cada síntoma
- **Criterios PRN**: Tabla separada para criterios de evaluación  
- **Horarios Programados**: Tabla separada para cada horario
- **Estadísticas**: Tabla separada para métricas calculadas

#### **3. FUNCIONALIDADES SOBREESPECIALIZADAS**
- **Permisos temporales**: Sistema complejo para casos excepcionales
- **Múltiples tipos de administración**: Programada, PRN, omitida, tardía
- **Asignaciones temporales**: Control de fechas de vigencia detallado
- **Auditoría granular**: Registra cambios a nivel de campo

#### **4. INTERFAZ COMPLEJA**
- **28 permisos diferentes** organizados en 7 módulos
- **Formularios multi-paso** para creación de tratamientos
- **Múltiples dashboards** especializados por rol
- **Navegación profunda** con muchos niveles

#### **5. CONFIGURACIÓN EXCESIVA**
- **Tolerancias personalizables** por medicamento
- **Restricciones PRN múltiples** (diaria, semanal, consecutiva)
- **Roles y permisos granulares** por módulo
- **Alertas configurables** por tipo y urgencia

---

## 🎯 PLAN DE SIMPLIFICACIÓN PROPUESTO

### **FASE 1: SIMPLIFICACIÓN DE ENTIDADES DE DATOS**

#### **1.1 Consolidar Tabla de Medicamentos**
**Actual**: Tabla simple `medicamentos` (4 campos)
**Simplificado**: Agregar solo campos esenciales
```sql
medicamentos:
- id, nombre, forma_farmaceutica, concentracion
- indicaciones_generales, activo
```
**Eliminar**: Principios activos, laboratorios, códigos complejos

#### **1.2 Simplificar Tratamientos**
**Actual**: `tratamientos` + `medicamentos_tratamientos` (13 campos)
**Simplificado**: Unificar en tabla más simple
```sql
tratamientos_simplificados:
- id, paciente_id, medico_id, medicamento_id
- tipo (programado/prn), dosis, frecuencia_horas
- fecha_inicio, fecha_fin, estado, observaciones
```
**Eliminar**: Ventanas de tolerancia específicas, múltiples límites PRN

#### **1.3 Eliminar Entidades de Configuración**
**Eliminar completamente**:
- `sintomas_prn` → Usar texto libre en tratamiento
- `criterios_prn` → Usar descripción simple
- `horarios_programados` → Calcular dinámicamente
- `estadisticas_consumo` → Calcular on-demand

#### **1.4 Simplificar Administraciones**  
**Actual**: Estados múltiples (Programada, Administrada, Omitida, Tardía)
**Simplificado**: 3 estados simples
```sql
administraciones_simples:
- id, tratamiento_id, fecha, estado (pendiente/administrada/omitida)
- hora_programada, hora_real, observaciones
```

### **FASE 2: REDUCCIÓN DE FUNCIONALIDADES**

#### **2.1 Sistema de Usuarios Simplificado**
**Mantener**: admin, medico, cuidador, paciente
**Eliminar**: 
- Rol "apoderado" (usar campo en paciente)
- Permisos granulares (usar permisos por rol)
- Permisos temporales
- Auditoría detallada (solo acciones críticas)

#### **2.2 Gestión de Pacientes Básica**
**Mantener**: Datos básicos del paciente, un médico asignado
**Eliminar**:
- Múltiples especialistas por paciente
- Fechas de vigencia en asignaciones
- Cuidadores múltiples (solo uno principal)
- Apoderados como usuarios separados

#### **2.3 Tratamientos Básicos**
**Mantener**: 
- Tratamientos programados simples (cada X horas)
- PRN básico (solo cuando sea necesario)
**Eliminar**:
- Ventanas de tolerancia personalizables  
- Límites múltiples en PRN (solo dosis máxima diaria)
- Horarios específicos (usar frecuencia en horas)
- Configuraciones complejas por medicamento

#### **2.4 Administración Simplificada**
**Mantener**: Registro de si se administró o no
**Eliminar**:
- Control de tiempo exacto de administración
- Múltiples estados de administración
- Validaciones complejas de PRN
- Cálculo automático de adherencia

### **FASE 3: INTERFAZ SIMPLIFICADA**

#### **3.1 Dashboard Único**
**Actual**: Dashboards específicos por rol
**Simplificado**: Un dashboard que se adapta al rol
- Vista de resumen para todos
- Acciones específicas según permisos

#### **3.2 Navegación Reducida**
**Eliminar módulos**:
- Asignaciones (integrar en pacientes)
- Cronograma (integrar en dashboard)
- Auditoría (solo para admin, simplificado)
- Permisos temporales

#### **3.3 Formularios Simplificados**
- Crear tratamiento en un solo paso
- Campos obligatorios mínimos
- Validaciones básicas solamente

### **FASE 4: BASE DE DATOS SIMPLIFICADA**

#### **4.1 Estructura Final Propuesta**
```sql
-- USUARIOS (Simplificado)
users: id, name, email, password, role
pacientes: id, nombre, fecha_nacimiento, medico_id, cuidador_id, activo

-- MEDICAMENTOS (Básico)  
medicamentos: id, nombre, concentracion, forma, indicaciones

-- TRATAMIENTOS (Unificado)
tratamientos: id, paciente_id, medicamento_id, tipo, dosis, frecuencia_horas, activo

-- ADMINISTRACIONES (Simple)
administraciones: id, tratamiento_id, fecha, administrada, observaciones
```

#### **4.2 Reducción de Tablas**
**Eliminar 15 tablas**:
- sintomas_prn, criterios_prn
- horarios_programados, indicaciones_prn  
- estadisticas_consumo, resumen_adherencia_paciente
- paciente_medicos, paciente_cuidadores, paciente_apoderados
- permisos_temporales, audit_logs detallados
- personal_medico, cuidadores, apoderados (usar campos en users)
- rol_permisos (usar permisos fijos por rol)

---

## 📋 BENEFICIOS DE LA SIMPLIFICACIÓN

### **🚀 VENTAJAS TÉCNICAS**
1. **Reducción del 70% en complejidad de BD** (de 25 tablas a 8 tablas)
2. **Interfaz más intuitiva** con navegación directa
3. **Mantenimiento simplificado** con menos interdependencias
4. **Performance mejorado** con menos joins complejos
5. **Testing más directo** con menos casos edge

### **👥 BENEFICIOS PARA USUARIOS**
1. **Curva de aprendizaje reducida** para nuevos usuarios
2. **Flujos más directos** sin configuraciones excesivas
3. **Menor posibilidad de errores** por configuración incorrecta
4. **Foco en lo esencial**: administrar medicamentos correctamente

### **💰 VENTAJAS DE NEGOCIO**  
1. **Desarrollo más rápido** de nuevas funcionalidades
2. **Menor costo de entrenamiento** de usuarios
3. **Implementación más rápida** en nuevos sitios
4. **Soporte técnico simplificado**

---

## ⚡ PLAN DE MIGRACIÓN SUGERIDO

### **SEMANA 1-2: ANÁLISIS Y DISEÑO**
- [ ] Definir casos de uso esenciales (solo los más críticos)
- [ ] Diseñar nuevo esquema de BD simplificado
- [ ] Crear plan de migración de datos existentes
- [ ] Definir nueva navegación y UX

### **SEMANA 3-4: BACKEND SIMPLIFICADO**  
- [ ] Crear nuevas migraciones para estructura simple
- [ ] Implementar modelos simplificados
- [ ] Migrar datos críticos del sistema actual
- [ ] Implementar API simplificada

### **SEMANA 5-6: FRONTEND BÁSICO**
- [ ] Crear interfaces simplificadas para gestión de pacientes
- [ ] Implementar formularios básicos de tratamientos  
- [ ] Dashboard único adaptable por rol
- [ ] Sistema de administración simple

### **SEMANA 7-8: TESTING Y REFINAMIENTO**
- [ ] Testing completo de funcionalidades básicas
- [ ] Refinamiento de UX basado en testing
- [ ] Documentación de la versión simplificada
- [ ] Capacitación de usuarios

---

## 🎯 CRITERIOS DE ÉXITO

### **MÉTRICAS TÉCNICAS**
- [ ] **Reducción del 70% en líneas de código**
- [ ] **Tiempo de carga <2 segundos** en todas las páginas
- [ ] **95% menos configuraciones** necesarias
- [ ] **Base de datos <30% del tamaño actual**

### **MÉTRICAS DE USUARIO**
- [ ] **Tiempo de onboarding <30 minutos** para nuevos usuarios
- [ ] **<5 clics** para completar tareas principales
- [ ] **0 capacitación técnica** requerida para uso básico
- [ ] **100% de funcionalidades críticas** preservadas

### **MÉTRICAS DE NEGOCIO**
- [ ] **50% reducción en tiempo de implementación**
- [ ] **40% menos soporte técnico** requerido
- [ ] **Mantenimiento simplificado** con 1 desarrollador
- [ ] **ROI mejorado** por menor complejidad

---

## 📄 CONCLUSIONES Y RECOMENDACIONES

### **ESTADO ACTUAL**
MediTrack es un sistema **sobreespecializado** con funcionalidades avanzadas que exceden las necesidades básicas de la mayoría de usuarios. La alta granularidad en datos y la complejidad de configuración crean barreras de adopción y mantenimiento.

### **RECOMENDACIÓN PRINCIPAL**
**Implementar la simplificación propuesta** manteniendo solo las funcionalidades esenciales:
1. Gestión básica de pacientes
2. Prescripción simple de tratamientos
3. Registro básico de administraciones
4. Dashboard de seguimiento simple

### **FUNCIONALIDADES ESENCIALES A MANTENER**
- ✅ Creación y gestión de pacientes
- ✅ Asignación de un médico y un cuidador principal
- ✅ Prescripción de tratamientos programados simples
- ✅ Medicación PRN básica
- ✅ Registro de administraciones (sí/no/observaciones)
- ✅ Dashboard con estadísticas básicas

### **FUNCIONALIDADES A ELIMINAR**
- ❌ Configuraciones complejas de dosificación
- ❌ Múltiples tipos de personal por paciente
- ❌ Auditoría granular de cambios
- ❌ Permisos temporales
- ❌ Ventanas de tolerancia personalizables
- ❌ Estadísticas calculadas automáticamente
- ❌ Alertas complejas automáticas

### **PRÓXIMOS PASOS INMEDIATOS**
1. **Validar con stakeholders** que funcionalidades son realmente esenciales
2. **Crear prototipo** de la interfaz simplificada
3. **Diseñar esquema de BD** final simplificado
4. **Planificar migración** de datos críticos
5. **Implementar versión MVP** en 8 semanas

---

**📅 Fecha de revisión recomendada**: 30 días  
**👥 Stakeholders a consultar**: Usuarios finales, equipo médico, administradores  
**🎯 Objetivo**: Sistema funcional y simple en 8 semanas

### **SEEDERS**
- [ ] Ejecutar `php artisan db:seed` para actualizar datos existentes