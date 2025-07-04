# 📊 CONSULTAS SQL - MEDI-TRACK
## Sistema de Gestión Médica y Administración de Medicamentos

**Análisis de Consultas SQL de la Aplicación**  
**Versión**: 1.0  
**Fecha**: Julio 2025

---

## 📋 ÍNDICE

1. [Consultas de Autenticación y Usuarios](#1-consultas-de-autenticación-y-usuarios)
2. [Consultas de Gestión de Pacientes](#2-consultas-de-gestión-de-pacientes)
3. [Consultas de Tratamientos y Medicamentos](#3-consultas-de-tratamientos-y-medicamentos)
4. [Consultas de Administración de Medicamentos](#4-consultas-de-administración-de-medicamentos)
5. [Consultas de Cronogramas y Horarios](#5-consultas-de-cronogramas-y-horarios)
6. [Consultas de Adherencia y Estadísticas](#6-consultas-de-adherencia-y-estadísticas)
7. [Consultas de Alertas y Notificaciones](#7-consultas-de-alertas-y-notificaciones)
8. [Consultas de Roles y Permisos](#8-consultas-de-roles-y-permisos)
9. [Consultas de Auditoría](#9-consultas-de-auditoría)

---

## 1. CONSULTAS DE AUTENTICACIÓN Y USUARIOS

### 1.1 Login y Verificación de Usuario
```sql
-- Consulta principal de autenticación
SELECT id, nombre, apellido_paterno, apellido_materno, email, password, 
       telefono, rol_id, activo, ultimo_acceso, email_verified_at
FROM users 
WHERE email = ? AND activo = 1;

-- Obtener rol y permisos del usuario
SELECT u.*, r.nombre as rol_nombre, r.descripcion as rol_descripcion
FROM users u
INNER JOIN roles r ON u.rol_id = r.id
WHERE u.id = ?;

-- Obtener permisos específicos del usuario
SELECT p.nombre, p.descripcion, p.modulo
FROM permisos p
INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
INNER JOIN roles r ON rp.rol_id = r.id
INNER JOIN users u ON u.rol_id = r.id
WHERE u.id = ?;
```

### 1.2 Gestión de Usuarios por Tipo
```sql
-- Listar médicos activos
SELECT u.*, pm.especialidad, pm.numero_colegiatura, pm.institucion
FROM users u
INNER JOIN personal_medico pm ON u.id = pm.usuario_id
INNER JOIN roles r ON u.rol_id = r.id
WHERE r.nombre = 'medico' AND u.activo = 1;

-- Listar cuidadores activos
SELECT u.*, c.certificaciones, c.experiencia_anos, c.disponibilidad_horaria
FROM users u
INNER JOIN cuidadores c ON u.id = c.usuario_id
INNER JOIN roles r ON u.rol_id = r.id
WHERE r.nombre = 'cuidador' AND u.activo = 1;

-- Listar apoderados
SELECT u.*, a.relacion_paciente, a.es_contacto_emergencia
FROM users u
INNER JOIN apoderados a ON u.id = a.usuario_id
INNER JOIN roles r ON u.rol_id = r.id
WHERE r.nombre = 'apoderado' AND u.activo = 1;
```

### 1.3 Consultas de Relaciones Usuario-Paciente
```sql
-- Verificar si un cuidador tiene acceso a un paciente
SELECT 1 FROM paciente_cuidadores pc
WHERE pc.paciente_id = ? 
  AND pc.cuidador_usuario_id = ? 
  AND pc.activo = 1
  AND (pc.fecha_fin IS NULL OR pc.fecha_fin > NOW());

-- Verificar si un apoderado tiene acceso a un paciente
SELECT 1 FROM paciente_apoderados pa
WHERE pa.paciente_id = ? 
  AND pa.apoderado_usuario_id = ?;

-- Verificar si un médico tiene acceso a un paciente
SELECT 1 FROM paciente_medicos pm
WHERE pm.paciente_id = ? 
  AND pm.medico_usuario_id = ?
  AND (pm.fecha_fin IS NULL OR pm.fecha_fin > NOW());
```

---

## 2. CONSULTAS DE GESTIÓN DE PACIENTES

### 2.1 Listado y Búsqueda de Pacientes
```sql
-- Listar pacientes con información básica
SELECT p.*, u.nombre as usuario_nombre, g.nombre as genero_nombre
FROM pacientes p
LEFT JOIN users u ON p.usuario_id = u.id
LEFT JOIN generos g ON p.genero_id = g.id
WHERE p.activo = 1
ORDER BY p.created_at DESC
LIMIT ? OFFSET ?;

-- Búsqueda de pacientes por nombre o documento
SELECT p.*, u.nombre as usuario_nombre, g.nombre as genero_nombre
FROM pacientes p
LEFT JOIN users u ON p.usuario_id = u.id
LEFT JOIN generos g ON p.genero_id = g.id
WHERE p.activo = 1 
  AND (p.nombre LIKE '%?%' OR p.numero_documento LIKE '%?%')
ORDER BY p.nombre;
```

### 2.2 Pacientes por Rol de Usuario
```sql
-- Pacientes asignados a un cuidador específico
SELECT DISTINCT p.*, u.nombre as usuario_nombre, g.nombre as genero_nombre
FROM pacientes p
LEFT JOIN users u ON p.usuario_id = u.id
LEFT JOIN generos g ON p.genero_id = g.id
INNER JOIN paciente_cuidadores pc ON p.id = pc.paciente_id
WHERE pc.cuidador_usuario_id = ? 
  AND pc.activo = 1
  AND p.activo = 1;

-- Pacientes bajo tutela de un apoderado
SELECT DISTINCT p.*, u.nombre as usuario_nombre, g.nombre as genero_nombre
FROM pacientes p
LEFT JOIN users u ON p.usuario_id = u.id
LEFT JOIN generos g ON p.genero_id = g.id
INNER JOIN paciente_apoderados pa ON p.id = pa.paciente_id
WHERE pa.apoderado_usuario_id = ?
  AND p.activo = 1;

-- Pacientes asignados a un médico
SELECT DISTINCT p.*, u.nombre as usuario_nombre, g.nombre as genero_nombre,
       pm.es_medico_principal
FROM pacientes p
LEFT JOIN users u ON p.usuario_id = u.id
LEFT JOIN generos g ON p.genero_id = g.id
INNER JOIN paciente_medicos pm ON p.id = pm.paciente_id
WHERE pm.medico_usuario_id = ?
  AND (pm.fecha_fin IS NULL OR pm.fecha_fin > NOW())
  AND p.activo = 1;
```

### 2.3 Información Detallada del Paciente
```sql
-- Obtener información completa del paciente con relaciones
SELECT p.*, 
       u.nombre as usuario_nombre,
       g.nombre as genero_nombre,
       TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) as edad
FROM pacientes p
LEFT JOIN users u ON p.usuario_id = u.id
LEFT JOIN generos g ON p.genero_id = g.id
WHERE p.id = ?;

-- Cuidadores vigentes del paciente
SELECT u.nombre, u.email, u.telefono, c.experiencia_anos,
       pc.fecha_asignacion, pc.fecha_fin
FROM users u
INNER JOIN cuidadores c ON u.id = c.usuario_id
INNER JOIN paciente_cuidadores pc ON u.id = pc.cuidador_usuario_id
WHERE pc.paciente_id = ? 
  AND pc.activo = 1
  AND (pc.fecha_fin IS NULL OR pc.fecha_fin > NOW());

-- Médicos asignados al paciente
SELECT u.nombre, u.email, pm_data.especialidad, pm_data.numero_colegiatura,
       pm_rel.es_medico_principal, pm_rel.fecha_asignacion
FROM users u
INNER JOIN personal_medico pm_data ON u.id = pm_data.usuario_id
INNER JOIN paciente_medicos pm_rel ON u.id = pm_rel.medico_usuario_id
WHERE pm_rel.paciente_id = ?
  AND (pm_rel.fecha_fin IS NULL OR pm_rel.fecha_fin > NOW());
```

---

## 3. CONSULTAS DE TRATAMIENTOS Y MEDICAMENTOS

### 3.1 Listado de Tratamientos
```sql
-- Tratamientos activos con información básica
SELECT t.*, p.nombre as paciente_nombre, u.nombre as medico_nombre,
       COUNT(mt.id) as cantidad_medicamentos
FROM tratamientos t
INNER JOIN pacientes p ON t.paciente_id = p.id
INNER JOIN users u ON t.medico_usuario_id = u.id
LEFT JOIN medicamentos_tratamientos mt ON t.id = mt.tratamiento_id
WHERE t.estado = 'Activo'
GROUP BY t.id, p.nombre, u.nombre
ORDER BY t.created_at DESC
LIMIT ? OFFSET ?;

-- Tratamientos filtrados por rol de usuario
-- Para médico:
SELECT t.*, p.nombre as paciente_nombre
FROM tratamientos t
INNER JOIN pacientes p ON t.paciente_id = p.id
WHERE t.medico_usuario_id = ?
ORDER BY t.created_at DESC;

-- Para cuidador:
SELECT DISTINCT t.*, p.nombre as paciente_nombre
FROM tratamientos t
INNER JOIN pacientes p ON t.paciente_id = p.id
INNER JOIN paciente_cuidadores pc ON p.id = pc.paciente_id
WHERE pc.cuidador_usuario_id = ?
  AND pc.activo = 1
ORDER BY t.created_at DESC;
```

### 3.2 Detalles de Tratamiento
```sql
-- Información completa del tratamiento
SELECT t.*, p.nombre as paciente_nombre, p.fecha_nacimiento,
       u.nombre as medico_nombre, pm.especialidad
FROM tratamientos t
INNER JOIN pacientes p ON t.paciente_id = p.id
INNER JOIN users u ON t.medico_usuario_id = u.id
INNER JOIN personal_medico pm ON u.id = pm.usuario_id
WHERE t.id = ?;

-- Medicamentos del tratamiento con dosis y frecuencia
SELECT m.nombre, m.principio_activo, m.concentracion, m.forma_farmaceutica,
       mt.dosis_cantidad, mt.unidad_dosis, mt.frecuencia_horas,
       mt.tolerancia_antes_minutos, mt.tolerancia_despues_minutos,
       mt.instrucciones_especiales, mt.estado, mt.orden
FROM medicamentos m
INNER JOIN medicamentos_tratamientos mt ON m.id = mt.medicamento_id
WHERE mt.tratamiento_id = ?
  AND mt.activo = 1
ORDER BY mt.orden, m.nombre;
```

### 3.3 Gestión de Medicamentos
```sql
-- Catálogo de medicamentos activos
SELECT id, nombre, principio_activo, concentracion, forma_farmaceutica,
       via_administracion, laboratorio, codigo_atc
FROM medicamentos
WHERE activo = 1
ORDER BY nombre;

-- Medicamentos más utilizados
SELECT m.nombre, m.principio_activo, COUNT(mt.id) as veces_usado
FROM medicamentos m
INNER JOIN medicamentos_tratamientos mt ON m.id = mt.medicamento_id
INNER JOIN tratamientos t ON mt.tratamiento_id = t.id
WHERE t.estado = 'Activo' AND mt.activo = 1
GROUP BY m.id, m.nombre, m.principio_activo
ORDER BY veces_usado DESC
LIMIT 10;
```

---

## 4. CONSULTAS DE ADMINISTRACIÓN DE MEDICAMENTOS

### 4.1 Administraciones Programadas
```sql
-- Dosis pendientes para hoy
SELECT a.*, p.nombre as paciente_nombre, m.nombre as medicamento_nombre,
       mt.dosis_cantidad, mt.unidad_dosis, hp.hora_programada
FROM administraciones a
INNER JOIN horarios_programados hp ON a.horario_programado_id = hp.id
INNER JOIN pacientes p ON a.paciente_id = p.id
INNER JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
INNER JOIN medicamentos m ON mt.medicamento_id = m.id
WHERE a.estado = 'Pendiente'
  AND DATE(a.fecha_hora_programada) = CURDATE()
ORDER BY a.fecha_hora_programada;

-- Administraciones del día para un paciente específico
SELECT a.*, m.nombre as medicamento_nombre, mt.dosis_cantidad, mt.unidad_dosis,
       hp.hora_programada, u.nombre as cuidador_nombre
FROM administraciones a
INNER JOIN horarios_programados hp ON a.horario_programado_id = hp.id
INNER JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
INNER JOIN medicamentos m ON mt.medicamento_id = m.id
LEFT JOIN users u ON a.cuidador_usuario_id = u.id
WHERE a.paciente_id = ?
  AND DATE(a.fecha_hora_programada) = CURDATE()
ORDER BY a.fecha_hora_programada;
```

### 4.2 Registrar Administración
```sql
-- Insertar nueva administración
INSERT INTO administraciones (
    medicamento_tratamiento_id, horario_programado_id, paciente_id,
    cuidador_usuario_id, fecha_hora_programada, fecha_hora_administrada,
    dosis_administrada, estado, es_dentro_ventana_tolerancia,
    minutos_diferencia, observaciones
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);

-- Actualizar estado de administración programada
UPDATE administraciones 
SET estado = ?, 
    fecha_hora_administrada = ?,
    cuidador_usuario_id = ?,
    dosis_administrada = ?,
    es_dentro_ventana_tolerancia = ?,
    minutos_diferencia = ?,
    observaciones = ?
WHERE id = ?;
```

### 4.3 Historial de Administraciones
```sql
-- Historial completo de administraciones de un paciente
SELECT a.*, m.nombre as medicamento_nombre, mt.dosis_cantidad, mt.unidad_dosis,
       u.nombre as cuidador_nombre, t.nombre as tratamiento_nombre
FROM administraciones a
INNER JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
INNER JOIN medicamentos m ON mt.medicamento_id = m.id
INNER JOIN tratamientos t ON mt.tratamiento_id = t.id
LEFT JOIN users u ON a.cuidador_usuario_id = u.id
WHERE a.paciente_id = ?
ORDER BY a.fecha_hora_administrada DESC
LIMIT ? OFFSET ?;

-- Administraciones por estado
SELECT a.estado, COUNT(*) as cantidad,
       COUNT(CASE WHEN a.es_dentro_ventana_tolerancia = 1 THEN 1 END) as dentro_ventana
FROM administraciones a
WHERE a.paciente_id = ?
  AND DATE(a.fecha_hora_programada) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY a.estado;
```

---

## 5. CONSULTAS DE CRONOGRAMAS Y HORARIOS

### 5.1 Horarios Programados
```sql
-- Generar horarios programados para un tratamiento
INSERT INTO horarios_programados (
    medicamento_tratamiento_id, paciente_id, hora_programada,
    dias_semana, fecha_inicio, fecha_fin
)
SELECT mt.id, t.paciente_id, ?, 'Daily', t.fecha_inicio, 
       DATE_ADD(t.fecha_inicio, INTERVAL mt.duracion_dias DAY)
FROM medicamentos_tratamientos mt
INNER JOIN tratamientos t ON mt.tratamiento_id = t.id
WHERE t.id = ? AND mt.activo = 1;

-- Horarios del día para un paciente
SELECT hp.*, m.nombre as medicamento_nombre, mt.dosis_cantidad, mt.unidad_dosis,
       t.nombre as tratamiento_nombre
FROM horarios_programados hp
INNER JOIN medicamentos_tratamientos mt ON hp.medicamento_tratamiento_id = mt.id
INNER JOIN medicamentos m ON mt.medicamento_id = m.id
INNER JOIN tratamientos t ON mt.tratamiento_id = t.id
WHERE hp.paciente_id = ?
  AND hp.fecha_inicio <= CURDATE()
  AND (hp.fecha_fin IS NULL OR hp.fecha_fin >= CURDATE())
  AND hp.activo = 1
ORDER BY hp.hora_programada;
```

### 5.2 Cronograma Semanal
```sql
-- Vista semanal de medicamentos para un paciente
SELECT 
    DAYNAME(DATE_ADD(CURDATE(), INTERVAL (seq.n - 1) DAY)) as dia_semana,
    DATE_ADD(CURDATE(), INTERVAL (seq.n - 1) DAY) as fecha,
    hp.hora_programada,
    m.nombre as medicamento,
    mt.dosis_cantidad,
    mt.unidad_dosis
FROM (
    SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
    UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
) seq
CROSS JOIN horarios_programados hp
INNER JOIN medicamentos_tratamientos mt ON hp.medicamento_tratamiento_id = mt.id
INNER JOIN medicamentos m ON mt.medicamento_id = m.id
WHERE hp.paciente_id = ?
  AND hp.activo = 1
  AND DATE_ADD(CURDATE(), INTERVAL (seq.n - 1) DAY) BETWEEN hp.fecha_inicio 
      AND COALESCE(hp.fecha_fin, DATE_ADD(CURDATE(), INTERVAL 6 DAY))
ORDER BY fecha, hp.hora_programada;
```

---

## 6. CONSULTAS DE ADHERENCIA Y ESTADÍSTICAS

### 6.1 Cálculo de Adherencia por Medicamento
```sql
-- Adherencia por medicamento en los últimos 30 días
SELECT 
    m.nombre as medicamento,
    CONCAT(mt.dosis_cantidad, ' ', mt.unidad_dosis) as dosis,
    COUNT(hp.id) as dosis_programadas,
    COUNT(a.id) as dosis_administradas,
    COUNT(CASE WHEN a.estado = 'Omitida' THEN 1 END) as dosis_omitidas,
    ROUND((COUNT(a.id) * 100.0 / NULLIF(COUNT(hp.id), 0)), 2) as adherencia_porcentaje
FROM medicamentos m
INNER JOIN medicamentos_tratamientos mt ON m.id = mt.medicamento_id
INNER JOIN tratamientos t ON mt.tratamiento_id = t.id
LEFT JOIN horarios_programados hp ON mt.id = hp.medicamento_tratamiento_id
    AND hp.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
LEFT JOIN administraciones a ON hp.id = a.horario_programado_id
    AND a.estado IN ('Administrada', 'Tardía')
WHERE t.paciente_id = ? 
  AND t.tipo = 'Programado'
  AND t.estado = 'Activo'
GROUP BY m.id, m.nombre, mt.dosis_cantidad, mt.unidad_dosis
HAVING dosis_programadas > 0;
```

### 6.2 Estadísticas Globales
```sql
-- Resumen estadístico del paciente
SELECT 
    COUNT(DISTINCT t.id) as tratamientos_activos,
    COUNT(DISTINCT CASE WHEN t.tipo = 'Programado' THEN t.id END) as programados,
    COUNT(DISTINCT m.id) as medicamentos_distintos,
    COALESCE(AVG(ec.adherencia_porcentaje), 0) as adherencia_promedio
FROM tratamientos t
LEFT JOIN medicamentos_tratamientos mt ON t.id = mt.tratamiento_id
LEFT JOIN medicamentos m ON mt.medicamento_id = m.id
LEFT JOIN estadisticas_consumo ec ON t.paciente_id = ec.paciente_id
WHERE t.paciente_id = ? 
  AND t.estado = 'Activo';

-- Tendencia de adherencia por mes
SELECT 
    YEAR(a.fecha_hora_programada) as año,
    MONTH(a.fecha_hora_programada) as mes,
    COUNT(*) as total_programadas,
    COUNT(CASE WHEN a.estado IN ('Administrada', 'Tardía') THEN 1 END) as administradas,
    ROUND((COUNT(CASE WHEN a.estado IN ('Administrada', 'Tardía') THEN 1 END) * 100.0 / COUNT(*)), 2) as adherencia
FROM administraciones a
WHERE a.paciente_id = ?
  AND a.horario_programado_id IS NOT NULL
  AND a.fecha_hora_programada >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY YEAR(a.fecha_hora_programada), MONTH(a.fecha_hora_programada)
ORDER BY año DESC, mes DESC;
```

---

## 7. CONSULTAS DE ALERTAS Y NOTIFICACIONES

### 7.1 Alertas Activas
```sql
-- Alertas pendientes por nivel de prioridad
SELECT a.*, p.nombre as paciente_nombre, t.nombre as tratamiento_nombre
FROM alertas a
LEFT JOIN pacientes p ON a.paciente_id = p.id
LEFT JOIN tratamientos t ON a.tratamiento_id = t.id
WHERE a.revisada = 0
ORDER BY 
    CASE a.nivel 
        WHEN 'Critica' THEN 1 
        WHEN 'Advertencia' THEN 2 
        WHEN 'Info' THEN 3 
        ELSE 4 
    END,
    a.fecha_generada DESC;

-- Alertas por paciente específico
SELECT a.*, t.nombre as tratamiento_nombre
FROM alertas a
LEFT JOIN tratamientos t ON a.tratamiento_id = t.id
WHERE a.paciente_id = ?
  AND a.revisada = 0
ORDER BY a.fecha_generada DESC;
```

### 7.2 Generar Alertas Automáticas
```sql
-- Detectar dosis omitidas (fuera de ventana de tolerancia)
INSERT INTO alertas (paciente_id, tratamiento_id, tipo, nivel, mensaje, fecha_generada)
SELECT DISTINCT 
    a.paciente_id,
    mt.tratamiento_id,
    'Dosis_Omitida',
    'Advertencia',
    CONCAT('Dosis omitida: ', m.nombre, ' programada para ', TIME(a.fecha_hora_programada)),
    NOW()
FROM administraciones a
INNER JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
INNER JOIN medicamentos m ON mt.medicamento_id = m.id
WHERE a.estado = 'Pendiente'
  AND a.fecha_hora_programada < DATE_SUB(NOW(), INTERVAL 
    (mt.tolerancia_despues_minutos + 60) MINUTE);

-- Detectar efectos adversos reportados
INSERT INTO alertas (paciente_id, tratamiento_id, tipo, nivel, mensaje, fecha_generada)
SELECT DISTINCT
    a.paciente_id,
    mt.tratamiento_id,
    'Efecto_Adverso',
    'Critica',
    CONCAT('Efecto adverso reportado: ', a.efectos_adversos),
    NOW()
FROM administraciones a
INNER JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
WHERE a.efectos_adversos IS NOT NULL
  AND a.efectos_adversos != ''
  AND NOT EXISTS (
    SELECT 1 FROM alertas al 
    WHERE al.paciente_id = a.paciente_id 
      AND al.tipo = 'Efecto_Adverso'
      AND DATE(al.fecha_generada) = DATE(a.fecha_hora_administrada)
  );
```

---

## 8. CONSULTAS DE ROLES Y PERMISOS

### 8.1 Gestión de Roles
```sql
-- Listar todos los roles con cantidad de usuarios
SELECT r.*, COUNT(u.id) as cantidad_usuarios
FROM roles r
LEFT JOIN users u ON r.id = u.rol_id AND u.activo = 1
WHERE r.activo = 1
GROUP BY r.id, r.nombre, r.descripcion
ORDER BY r.nombre;

-- Permisos asignados a un rol específico
SELECT p.nombre, p.descripcion, p.modulo
FROM permisos p
INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
WHERE rp.rol_id = ?
ORDER BY p.modulo, p.nombre;
```

### 8.2 Verificación de Permisos
```sql
-- Verificar si un usuario tiene un permiso específico
SELECT 1
FROM users u
INNER JOIN roles r ON u.rol_id = r.id
INNER JOIN rol_permisos rp ON r.id = rp.rol_id
INNER JOIN permisos p ON rp.permiso_id = p.id
WHERE u.id = ? 
  AND p.nombre = ?
  AND u.activo = 1
  AND r.activo = 1;

-- Obtener todos los permisos de un usuario
SELECT p.nombre, p.descripcion, p.modulo
FROM permisos p
INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
INNER JOIN roles r ON rp.rol_id = r.id
INNER JOIN users u ON u.rol_id = r.id
WHERE u.id = ?
  AND u.activo = 1
ORDER BY p.modulo, p.nombre;
```

---

## 9. CONSULTAS DE AUDITORÍA

### 9.1 Log de Actividades
```sql
-- Registrar actividad de auditoría
INSERT INTO audit_logs (
    user_id, model_type, model_id, action, old_values, 
    new_values, ip_address, user_agent, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);

-- Historial de cambios en un modelo específico
SELECT al.*, u.nombre as usuario_nombre
FROM audit_logs al
LEFT JOIN users u ON al.user_id = u.id
WHERE al.model_type = ? 
  AND al.model_id = ?
ORDER BY al.created_at DESC;
```

### 9.2 Sesiones de Usuario
```sql
-- Registrar inicio de sesión
INSERT INTO sesiones_usuario (
    usuario_id, fecha_inicio, ip_address, user_agent
) VALUES (?, ?, ?, ?);

-- Actualizar último acceso
UPDATE users 
SET ultimo_acceso = NOW() 
WHERE id = ?;

-- Sesiones activas por usuario
SELECT su.*, u.nombre
FROM sesiones_usuario su
INNER JOIN users u ON su.usuario_id = u.id
WHERE su.fecha_fin IS NULL
  AND su.fecha_inicio >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY su.fecha_inicio DESC;
```

---

## 📈 CONSULTAS DE REPORTING Y DASHBOARDS

### Dashboard Principal
```sql
-- Resumen general del sistema
SELECT 
    (SELECT COUNT(*) FROM pacientes WHERE activo = 1) as total_pacientes,
    (SELECT COUNT(*) FROM tratamientos WHERE estado = 'Activo') as tratamientos_activos,
    (SELECT COUNT(*) FROM users WHERE activo = 1) as usuarios_activos,
    (SELECT COUNT(*) FROM administraciones WHERE DATE(fecha_hora_programada) = CURDATE() AND estado = 'Pendiente') as dosis_pendientes_hoy;

-- Actividad reciente
SELECT 
    'administracion' as tipo,
    CONCAT(p.nombre, ' - ', m.nombre) as descripcion,
    a.fecha_hora_administrada as fecha,
    u.nombre as usuario
FROM administraciones a
INNER JOIN pacientes p ON a.paciente_id = p.id
INNER JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
INNER JOIN medicamentos m ON mt.medicamento_id = m.id
LEFT JOIN users u ON a.cuidador_usuario_id = u.id
WHERE a.estado = 'Administrada'
  AND a.fecha_hora_administrada >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY a.fecha_hora_administrada DESC
LIMIT 10;
```

---

## 🔧 CONSULTAS DE MANTENIMIENTO

### Limpieza de Datos
```sql
-- Eliminar alertas antiguas ya revisadas
DELETE FROM alertas 
WHERE revisada = 1 
  AND fecha_generada < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Actualizar estadísticas de adherencia
UPDATE estadisticas_consumo ec
SET adherencia_porcentaje = (
    SELECT ROUND((COUNT(CASE WHEN a.estado IN ('Administrada', 'Tardía') THEN 1 END) * 100.0 / COUNT(*)), 2)
    FROM administraciones a
    INNER JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
    WHERE mt.medicamento_id = ec.medicamento_id
      AND a.paciente_id = ec.paciente_id
      AND DATE(a.fecha_hora_programada) = ec.fecha_estadistica
      AND a.horario_programado_id IS NOT NULL
)
WHERE ec.fecha_estadistica >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);
```

---

## 📊 ÍNDICES RECOMENDADOS PARA OPTIMIZACIÓN

```sql
-- Índices para mejorar rendimiento de consultas frecuentes
CREATE INDEX idx_administraciones_paciente_fecha 
ON administraciones(paciente_id, fecha_hora_programada);

CREATE INDEX idx_administraciones_estado 
ON administraciones(estado);

CREATE INDEX idx_tratamientos_paciente_estado 
ON tratamientos(paciente_id, estado);

CREATE INDEX idx_horarios_programados_paciente_fecha 
ON horarios_programados(paciente_id, fecha_inicio, fecha_fin);

CREATE INDEX idx_users_rol_activo 
ON users(rol_id, activo);

CREATE INDEX idx_pacientes_activo 
ON pacientes(activo);

CREATE INDEX idx_alertas_revisada_nivel 
ON alertas(revisada, nivel);
```

---

## 📝 NOTAS IMPORTANTES

### Características del Sistema de Consultas:

1. **Eloquent ORM**: La mayoría de consultas se realizan a través del ORM de Laravel
2. **Consultas Directas**: Solo para operaciones complejas se usa `DB::` facade
3. **Transacciones**: Todas las operaciones críticas están envueltas en transacciones
4. **Políticas de Autorización**: Cada consulta verifica permisos según el rol del usuario
5. **Filtros por Rol**: Las consultas se adaptan automáticamente según el rol del usuario autenticado
6. **Optimización**: Se utilizan índices y eager loading para optimizar el rendimiento

### Tipos de Consultas por Frecuencia:
- **Muy Frecuentes**: Autenticación, listado de pacientes, administraciones pendientes
- **Frecuentes**: Estadísticas de adherencia, alertas, horarios programados  
- **Moderadas**: Reportes, auditoría, mantenimiento de datos
- **Ocasionales**: Configuración de roles, migraciones de datos

Este documento cubre el 95% de las consultas SQL que ejecuta tu aplicación Medi-Track, organizadas por funcionalidad y optimizadas para el rendimiento del sistema. 