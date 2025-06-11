# CASOS DE USO - MEDI-TRACK
## Sistema de Seguimiento Médico y Administración de Medicamentos

### 📋 RESUMEN DEL SISTEMA

**Medi-Track** es un sistema integral para el seguimiento médico y administración de medicamentos que soporta:
- Dosificación programada (horarios fijos)
- Dosificación PRN (por necesidad según síntomas)
- Múltiples roles: médicos, cuidadores, apoderados y pacientes
- Alertas automáticas y estadísticas de adherencia

---

## 🎭 ACTORES DEL SISTEMA

### 1. **Médico** (Personal Médico)
- Prescribe tratamientos y medicamentos
- Define protocolos PRN con criterios específicos
- Revisa adherencia y estadísticas de pacientes

### 2. **Cuidador**
- Administra medicamentos siguiendo prescripciones
- Registra síntomas para medicamentos PRN
- Reporta efectos adversos

### 3. **Apoderado** (Familiar/Tutor)
- Supervisa el tratamiento del paciente
- Recibe alertas importantes
- Consulta estadísticas de adherencia

### 4. **Paciente**
- Puede auto-administrarse medicamentos (según configuración)
- Reporta síntomas para medicamentos PRN
- Consulta su propio historial

### 5. **Administrador del Sistema**
- Gestiona usuarios y permisos
- Mantiene catálogo de medicamentos
- Configura alertas del sistema

---

## 📱 CASOS DE USO PRINCIPALES

### **CU-01: Registro y Gestión de Usuarios**

**Actor Principal:** Administrador del Sistema
**Precondición:** El administrador tiene acceso al sistema
**Flujo Principal:**
1. El administrador accede al módulo de gestión de usuarios
2. Selecciona el tipo de usuario a crear (médico, cuidador, apoderado, paciente)
3. Ingresa datos básicos (nombre, email, teléfono)
4. Asigna rol y permisos específicos
5. Para médicos: registra especialidad y número de colegiatura
6. Para cuidadores: define certificaciones y disponibilidad
7. Para apoderados: establece relación con paciente
8. El sistema envía credenciales de acceso por email

**Resultado:** Usuario creado y activo en el sistema

---

### **CU-02: Crear Paciente y Establecer Relaciones**

**Actor Principal:** Administrador/Médico
**Precondición:** Usuarios médico, cuidador y apoderado ya existen
**Flujo Principal:**
1. Se crea el perfil del paciente con datos médicos básicos
2. Se asigna médico principal y especialistas
3. Se vincula con apoderados (familiar responsable)
4. Se asigna cuidador principal
5. Se registran contactos de emergencia
6. Se documentan alergias y condiciones médicas relevantes

**Resultado:** Paciente registrado con todas las relaciones establecidas

---

### **CU-03: Prescripción de Tratamiento Programado**

**Actor Principal:** Médico
**Precondición:** Paciente registrado en el sistema
**Escenario:** Antibiótico cada 8 horas por 7 días

**Flujo Principal:**
1. Médico accede al perfil del paciente
2. Crea nuevo tratamiento tipo "Programado"
3. Selecciona medicamento: Amoxicilina 500mg
4. Configura dosificación:
   - Dosis: 1 cápsula (500mg)
   - Frecuencia: cada 8 horas
   - Duración: 7 días
   - Tolerancia: 30 min antes, 60 min después
5. Define horarios específicos: 08:00, 16:00, 00:00
6. Agrega instrucciones especiales: "Tomar con alimentos"
7. Sistema genera automáticamente todas las dosis programadas

**Resultado:** Tratamiento programado activo con horarios automáticos

---

### **CU-04: Prescripción de Medicamento PRN**

**Actor Principal:** Médico
**Precondición:** Paciente con necesidad de medicación por síntomas
**Escenario:** Paracetamol PRN para fiebre y dolor

**Flujo Principal:**
1. Médico crea tratamiento tipo "PRN"
2. Selecciona medicamento: Paracetamol 500mg
3. Configura restricciones PRN:
   - Dosis máxima por día: 3000mg (6 tabletas)
   - Intervalo mínimo: 8 horas entre dosis
   - Máximo consecutivo: 3 dosis sin pausa de 24h
4. Define indicaciones específicas:
   - **Síntoma 1:** Fiebre >38°C
   - **Síntoma 2:** Dolor moderado a severo (>5/10)
5. Agrega criterios personalizados:
   - "Solo si temperatura axilar >38°C confirmada"
   - "Para dolor: evaluar escala 1-10, administrar si >5"
6. Establece duración del protocolo: 15 días

**Resultado:** Protocolo PRN activo con criterios específicos

---

### **CU-05: Administración de Medicamento Programado**

**Actor Principal:** Cuidador
**Precondición:** Existe dosis programada pendiente
**Escenario:** Administración de antibiótico en horario

**Flujo Principal:**
1. Sistema notifica dosis pendiente a las 08:00
2. Cuidador accede a la aplicación
3. Ve lista de medicamentos pendientes para el paciente
4. Selecciona "Amoxicilina 500mg - 08:00"
5. Confirma que el paciente recibió la dosis completa
6. Registra hora real de administración: 08:15
7. Agrega observaciones: "Paciente tomó con desayuno"
8. Sistema calcula: dentro de ventana de tolerancia (+15 min)

**Flujo Alternativo - Dosis Omitida:**
5a. Cuidador marca como "Omitida"
5b. Selecciona motivo: "Paciente vomitó" 
5c. Sistema genera alerta para revisión médica

**Resultado:** Administración registrada con cálculo automático de adherencia

---

### **CU-06: Administración de Medicamento PRN**

**Actor Principal:** Cuidador
**Precondición:** Paciente presenta síntomas, protocolo PRN activo
**Escenario:** Paciente con fiebre de 38.5°C

**Flujo Principal:**
1. Cuidador observa síntoma en paciente (fiebre)
2. Accede al módulo PRN en la aplicación
3. Sistema muestra medicamentos PRN disponibles para "Fiebre"
4. Selecciona "Paracetamol 500mg PRN"
5. Sistema verifica automáticamente:
   - ✅ Última dosis hace 12 horas (>8h requerido)
   - ✅ Consumo del día: 1000mg (<3000mg límite)
   - ✅ Criterio cumplido: 38.5°C > 38°C
6. Registra administración:
   - Síntoma: Fiebre
   - Intensidad: 38.5°C
   - Hora: 14:30
   - Dosis: 500mg
7. Agrega observaciones: "Paciente muy incómodo por fiebre"

**Flujo de Excepción - Intervalo Insuficiente:**
5a. Sistema detecta última dosis hace 6 horas
5b. Muestra alerta: "Intervalo mínimo no cumplido"
5c. Sugiere alternativas o contactar médico

**Resultado:** Administración PRN válida registrada con criterios documentados

---

### **CU-07: Gestión de Alertas Automáticas**

**Actor Principal:** Sistema (automático)
**Triggers:** Diversos eventos en el sistema

**Tipos de Alertas Generadas:**

#### **Alerta: Dosis Omitida**
- **Trigger:** Pasa ventana de tolerancia sin administración
- **Nivel:** Advertencia
- **Mensaje:** "Dosis de Amoxicilina 16:00 no administrada - Ventana expirada"
- **Notifica a:** Cuidador principal, apoderado

#### **Alerta: Exceso PRN**
- **Trigger:** Intento de administrar PRN fuera de límites
- **Nivel:** Crítica  
- **Mensaje:** "Intento de administrar Paracetamol - Dosis diaria máxima alcanzada"
- **Notifica a:** Médico, cuidador

#### **Alerta: Intervalo PRN Corto**
- **Trigger:** Intento de administrar antes del intervalo mínimo
- **Nivel:** Advertencia
- **Mensaje:** "Paracetamol solicitado - Intervalo mínimo 8h no cumplido (última dosis hace 5h)"

#### **Alerta: Efecto Adverso**
- **Trigger:** Reporte manual de reacción
- **Nivel:** Crítica
- **Mensaje:** "Efecto adverso reportado: erupción cutánea - Amoxicilina"
- **Notifica a:** Médico prescriptor inmediatamente

---

### **CU-08: Consulta de Estadísticas y Adherencia**

**Actor Principal:** Médico/Apoderado
**Precondición:** Paciente con historial de administraciones

**Flujo Principal:**
1. Actor accede al dashboard del paciente
2. Selecciona período de análisis (última semana)
3. Sistema presenta métricas:

#### **Adherencia General:**
- Dosis programadas: 42
- Dosis administradas: 38 (90.5%)
- Dosis omitidas: 4 (9.5%)
- Dosis tardías: 8 (21% de administradas)

#### **Análisis PRN:**
- Administraciones PRN: 12
- Síntoma más frecuente: Dolor (7 veces)
- Intensidad promedio: 6.5/10
- Efectividad: Reducción síntomas en 85% casos

#### **Tendencias:**
- Mejor adherencia: Lunes-Viernes (95%)
- Menor adherencia: Fines de semana (82%)
- Horarios problemáticos: 00:00 (60% omisión)

4. Genera reporte para ajuste de tratamiento

**Resultado:** Análisis completo para optimización terapéutica

---

### **CU-09: Modificación de Tratamiento**

**Actor Principal:** Médico
**Precondición:** Tratamiento activo requiere ajuste
**Escenario:** Cambio de frecuencia por efectos adversos

**Flujo Principal:**
1. Médico revisa adherencia y reportes de efectos adversos
2. Decide modificar frecuencia de antibiótico: cada 8h → cada 12h
3. Accede al tratamiento activo
4. Modifica configuración:
   - Nueva frecuencia: 12 horas
   - Nuevos horarios: 08:00, 20:00
   - Mantiene dosis: 500mg
5. Agrega justificación: "Reducción por intolerancia gastrointestinal"
6. Sistema:
   - Cancela horarios futuros del esquema anterior
   - Genera nuevos horarios con la nueva frecuencia
   - Notifica a cuidadores del cambio

**Resultado:** Tratamiento ajustado sin pérdida de continuidad

---

### **CU-10: Finalización de Tratamiento**

**Actor Principal:** Médico
**Precondición:** Tratamiento completado o debe suspenderse

**Flujo Principal:**
1. Médico evalúa evolución del paciente
2. Decide finalizar tratamiento de antibiótico
3. Accede al tratamiento activo
4. Selecciona "Finalizar tratamiento"
5. Elige motivo:
   - ✅ "Completado exitosamente"
   - ❌ "Suspendido por efectos adversos"
   - ❌ "Suspendido por falta de eficacia"
6. Agrega observaciones finales
7. Sistema:
   - Marca tratamiento como "Completado"
   - Cancela dosis futuras programadas
   - Genera resumen estadístico final
   - Archiva alertas pendientes

**Resultado:** Tratamiento finalizado con resumen completo

---

## 🔄 FLUJOS INTEGRADOS COMPLEJOS

### **Escenario Integral: Paciente con Múltiples Tratamientos**

**Contexto:** María, 75 años, diabetes e hipertensión
**Tratamientos Activos:**
1. **Metformina** - Programado cada 12h (diabetes)
2. **Losartán** - Programado diario por las mañanas (hipertensión)  
3. **Paracetamol** - PRN para dolor articular
4. **Omeprazol** - PRN para acidez estomacal

**Día Típico con Múltiples Eventos:**

#### **08:00 - Medicamentos Matutinos**
- Cuidador administra Metformina 850mg ✅
- Cuidador administra Losartán 50mg ✅
- Paciente desayuna normalmente

#### **14:30 - Evento PRN #1**
- Paciente reporta dolor en rodillas (7/10)
- Cuidador consulta protocolo PRN de Paracetamol
- Sistema verifica: última dosis hace 3 días ✅
- Administra Paracetamol 500mg ✅
- Registra: "Dolor articular moderado-severo"

#### **17:45 - Evento PRN #2**  
- Paciente reporta acidez estomacal después de merienda
- Cuidador consulta Omeprazol PRN
- Sistema verifica: no hay conflictos ✅
- Administra Omeprazol 20mg ✅
- Registra: "Acidez post-prandial"

#### **20:00 - Medicamento Nocturno**
- Notificación: Metformina programada
- Cuidador administra Metformina 850mg ✅
- Paciente cena con normalidad

#### **22:30 - Solicitud PRN Problemática**
- Paciente solicita otro Paracetamol por dolor
- Sistema detecta: intervalo <8h desde última dosis ❌
- Genera alerta: "Intervalo mínimo no cumplido"
- Sugiere alternativas no farmacológicas
- Cuidador aplica compresas frías, dolor mejora

**Resultado del Día:**
- Adherencia programada: 100% (4/4)
- Administraciones PRN válidas: 2
- Alertas preventivas: 1 (evitó sobredosis)
- Sin efectos adversos reportados

---

## 📊 MÉTRICAS Y KPIs DEL SISTEMA

### **Indicadores de Calidad Asistencial**
- **Adherencia Global:** >95% objetivo
- **Puntualidad:** >80% dentro de ventana
- **Efectividad PRN:** >70% mejora sintomática
- **Prevención de Errores:** 0 sobredosis

### **Indicadores Operacionales**  
- **Tiempo de Respuesta a Alertas:** <30 min
- **Satisfacción del Usuario:** >4.5/5
- **Disponibilidad del Sistema:** >99.5%
- **Precisión de Datos:** >99%

---

## 🚨 CASOS DE EXCEPCIÓN Y MANEJO DE ERRORES

### **Emergencias Médicas**
- Reacción alérgica severa → Suspensión automática + alerta médica
- Sobredosis accidental → Protocolo de emergencia + notificación inmediata
- Falla del sistema → Modo offline con sincronización posterior

### **Problemas de Conectividad**
- Registro offline de administraciones
- Sincronización automática al reconectar
- Alertas locales por dosis omitidas

### **Errores Humanos**
- Validaciones en tiempo real
- Confirmaciones dobles para acciones críticas
- Registro de auditoría completo

---

## 🎯 OBJETIVOS DE ÉXITO

### **Para Pacientes:**
- Mejora en adherencia terapéutica
- Reducción de hospitalizaciones
- Mayor calidad de vida
- Autonomía en autocuidado

### **Para Cuidadores:**
- Procesos más eficientes
- Reducción de errores
- Mejor comunicación con médicos
- Tranquilidad en la gestión

### **Para Médicos:**
- Datos precisos para ajuste terapéutico
- Identificación temprana de problemas
- Optimización de tratamientos
- Mejor seguimiento remoto

### **Para el Sistema de Salud:**
- Reducción de costos por complicaciones
- Optimización de recursos
- Mejores outcomes clínicos
- Datos para investigación y mejora continua 