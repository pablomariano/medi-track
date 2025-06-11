-- =====================================================
-- CONSULTAS DE PRUEBA - MEDI-TRACK
-- Sistema de Seguimiento Médico y Administración PRN
-- =====================================================

-- =====================================================
-- 1. CONFIGURACIÓN INICIAL - INSERTAR DATOS BASE
-- =====================================================

-- Crear roles básicos
INSERT INTO roles (nombre, descripcion) VALUES 
('admin', 'Administrador del sistema'),
('medico', 'Personal médico - prescriptor'),
('cuidador', 'Cuidador profesional'),
('apoderado', 'Familiar o tutor legal'),
('paciente', 'Paciente del sistema');

-- Crear géneros
INSERT INTO generos (id, nombre) VALUES 
('M', 'Masculino'),
('F', 'Femenino'),
('O', 'Otro');

-- Crear usuarios base
INSERT INTO usuarios (nombre, email, password, telefono, rol_id) VALUES 
('Dr. Carlos Mendoza', 'carlos.mendoza@hospital.com', 'hash123', '555-0101', 2),
('Ana García', 'ana.garcia@cuidados.com', 'hash456', '555-0102', 3),
('Roberto Silva', 'roberto.silva@familia.com', 'hash789', '555-0103', 4),
('María López', 'maria.lopez@email.com', 'hash321', '555-0104', 5);

-- Crear perfiles específicos
INSERT INTO personal_medico (usuario_id, especialidad, numero_colegiatura, institucion) VALUES 
(1, 'Medicina Interna', 'MED-12345', 'Hospital General');

INSERT INTO cuidadores (usuario_id, certificaciones, experiencia_anos, disponibilidad_horaria) VALUES 
(2, 'Auxiliar de Enfermería, Primeros Auxilios', 5, '24/7 disponible');

INSERT INTO apoderados (usuario_id, relacion_paciente, es_contacto_emergencia) VALUES 
(3, 'Hijo', true);

-- Crear pacientes
INSERT INTO pacientes (usuario_id, nombre, fecha_nacimiento, genero_id, numero_documento, tipo_documento, tipo_sangre, altura) VALUES 
(4, 'María López', '1948-03-15', 'F', '12345678', 'DNI', 'O+', 1.60),
(NULL, 'Pedro Martínez', '1965-07-22', 'M', '87654321', 'DNI', 'A+', 1.75);

-- Establecer relaciones
INSERT INTO paciente_apoderados (paciente_id, apoderado_usuario_id, es_principal) VALUES 
(1, 3, true);

INSERT INTO paciente_cuidadores (paciente_id, cuidador_usuario_id, fecha_asignacion) VALUES 
(1, 2, '2024-01-01'),
(2, 2, '2024-01-01');

INSERT INTO paciente_medicos (paciente_id, medico_usuario_id, es_medico_principal, fecha_asignacion) VALUES 
(1, 1, true, '2024-01-01'),
(2, 1, true, '2024-01-01');

-- Crear medicamentos
INSERT INTO medicamentos (nombre, principio_activo, concentracion, forma_farmaceutica, via_administracion, laboratorio) VALUES 
('Amoxicilina', 'Amoxicilina', '500mg', 'Cápsula', 'Oral', 'LabGenérico'),
('Paracetamol', 'Acetaminofén', '500mg', 'Tableta', 'Oral', 'FarmaPlus'),
('Metformina', 'Metformina HCl', '850mg', 'Tableta', 'Oral', 'DiabeCare'),
('Losartán', 'Losartán Potásico', '50mg', 'Tableta', 'Oral', 'CardioMed'),
('Omeprazol', 'Omeprazol', '20mg', 'Cápsula', 'Oral', 'GastroLab');

-- Crear síntomas para PRN
INSERT INTO sintomas_prn (nombre, categoria) VALUES 
('Fiebre', 'Temperatura'),
('Dolor', 'Analgesia'),
('Acidez', 'Gastrointestinal'),
('Náuseas', 'Gastrointestinal'),
('Ansiedad', 'Psicológico');

-- Crear criterios específicos para síntomas
INSERT INTO criterios_prn (sintoma_id, descripcion, valor_minimo, unidad) VALUES 
(1, 'Fiebre superior a', '38', '°C'),
(2, 'Dolor moderado a severo', '5', 'escala 1-10'),
(3, 'Acidez estomacal molesta', NULL, NULL);

-- =====================================================
-- 2. CREAR TRATAMIENTOS Y MEDICAMENTOS
-- =====================================================

-- Tratamiento programado: Antibiótico
INSERT INTO tratamientos (paciente_id, medico_usuario_id, nombre, diagnostico, tipo, fecha_inicio, fecha_fin_estimada) VALUES 
(1, 1, 'Antibiótico para infección respiratoria', 'Bronquitis aguda', 'Programado', '2024-01-15', '2024-01-22');

-- Medicamento en tratamiento programado
INSERT INTO medicamentos_tratamientos (medicamento_id, tratamiento_id, dosis_cantidad, unidad_dosis, frecuencia_horas, tolerancia_antes_minutos, tolerancia_despues_minutos, duracion_dias, fecha_inicio, indicaciones_uso) VALUES 
(1, 1, 500, 'mg', 8, 30, 60, 7, '2024-01-15', 'Tomar con alimentos para evitar molestias gástricas');

-- Crear horarios programados
INSERT INTO horarios_programados (medicamento_tratamiento_id, paciente_id, hora_programada, dias_semana, fecha_inicio, fecha_fin) VALUES 
(1, 1, '08:00:00', 'Daily', '2024-01-15', '2024-01-22'),
(1, 1, '16:00:00', 'Daily', '2024-01-15', '2024-01-22'),
(1, 1, '00:00:00', 'Daily', '2024-01-15', '2024-01-22');

-- Tratamiento PRN: Paracetamol
INSERT INTO tratamientos (paciente_id, medico_usuario_id, nombre, diagnostico, tipo, fecha_inicio) VALUES 
(1, 1, 'Analgésico PRN', 'Dolor crónico articular', 'PRN', '2024-01-10');

-- Medicamento PRN
INSERT INTO medicamentos_tratamientos (medicamento_id, tratamiento_id, dosis_cantidad, unidad_dosis, intervalo_minimo_horas, dosis_maxima_dia, fecha_inicio, indicaciones_uso) VALUES 
(2, 2, 500, 'mg', 8, 3000, '2024-01-10', 'Administrar según intensidad del dolor. Máximo 6 tabletas/día');

-- Indicaciones PRN específicas
INSERT INTO indicaciones_prn (medicamento_tratamiento_id, sintoma_id, criterio_id, descripcion_personalizada, es_criterio_principal) VALUES 
(2, 1, 1, 'Administrar para fiebre >38°C confirmada con termómetro', true),
(2, 2, 2, 'Para dolor articular evaluar escala 1-10, administrar si intensidad >5', true);

-- =====================================================
-- 3. CONSULTAS DE PRUEBA DE FUNCIONALIDADES
-- =====================================================

-- CONSULTA 1: Dashboard general de un paciente
SELECT 
    p.nombre as paciente,
    COUNT(DISTINCT t.id) as tratamientos_activos,
    COUNT(DISTINCT CASE WHEN t.tipo = 'Programado' THEN t.id END) as tratamientos_programados,
    COUNT(DISTINCT CASE WHEN t.tipo = 'PRN' THEN t.id END) as tratamientos_prn,
    pm.nombre as medico_principal
FROM pacientes p
LEFT JOIN tratamientos t ON p.id = t.paciente_id AND t.estado = 'Activo'
LEFT JOIN paciente_medicos pmed ON p.id = pmed.paciente_id AND pmed.es_medico_principal = true
LEFT JOIN usuarios pm ON pmed.medico_usuario_id = pm.id
WHERE p.id = 1
GROUP BY p.id, p.nombre, pm.nombre;

-- CONSULTA 2: Medicamentos programados con adherencia
SELECT 
    p.nombre as paciente,
    m.nombre as medicamento,
    mt.dosis_cantidad,
    mt.unidad_dosis,
    mt.frecuencia_horas,
    COUNT(hp.id) as dosis_programadas,
    COUNT(a.id) as dosis_administradas,
    COUNT(CASE WHEN a.estado = 'Omitida' THEN 1 END) as dosis_omitidas,
    ROUND((COUNT(a.id) * 100.0 / NULLIF(COUNT(hp.id), 0)), 2) as adherencia_porcentaje
FROM pacientes p
JOIN tratamientos t ON p.id = t.paciente_id
JOIN medicamentos_tratamientos mt ON t.id = mt.tratamiento_id
JOIN medicamentos m ON mt.medicamento_id = m.id
LEFT JOIN horarios_programados hp ON mt.id = hp.medicamento_tratamiento_id
LEFT JOIN administraciones a ON hp.id = a.horario_programado_id
WHERE t.tipo = 'Programado' AND t.estado = 'Activo' AND p.id = 1
GROUP BY p.id, p.nombre, m.nombre, mt.dosis_cantidad, mt.unidad_dosis, mt.frecuencia_horas;

-- CONSULTA 3: Análisis de administraciones PRN
SELECT 
    p.nombre as paciente,
    m.nombre as medicamento,
    s.nombre as sintoma,
    a.intensidad_sintoma,
    a.criterio_cumplido,
    a.fecha_hora_administrada,
    a.dosis_administrada,
    a.observaciones
FROM administraciones a
JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
JOIN tratamientos t ON mt.tratamiento_id = t.id
JOIN medicamentos m ON mt.medicamento_id = m.id
JOIN pacientes p ON t.paciente_id = p.id
LEFT JOIN sintomas_prn s ON a.sintoma_reportado_id = s.id
WHERE a.horario_programado_id IS NULL AND p.id = 1
ORDER BY a.fecha_hora_administrada DESC;

-- CONSULTA 4: Verificación de restricciones PRN
SELECT 
    p.nombre as paciente,
    m.nombre as medicamento,
    mt.intervalo_minimo_horas,
    mt.dosis_maxima_dia,
    MAX(a.fecha_hora_administrada) as ultima_administracion,
    COALESCE(SUM(CASE WHEN DATE(a.fecha_hora_administrada) = CURRENT_DATE THEN a.dosis_administrada ELSE 0 END), 0) as consumo_hoy
FROM pacientes p
JOIN tratamientos t ON p.id = t.paciente_id
JOIN medicamentos_tratamientos mt ON t.id = mt.tratamiento_id
JOIN medicamentos m ON mt.medicamento_id = m.id
LEFT JOIN administraciones a ON mt.id = a.medicamento_tratamiento_id AND a.horario_programado_id IS NULL
WHERE t.tipo = 'PRN' AND t.estado = 'Activo' AND p.id = 1
GROUP BY p.nombre, m.nombre, mt.intervalo_minimo_horas, mt.dosis_maxima_dia;

-- CONSULTA 5: Alertas activas del sistema
SELECT 
    a.tipo,
    a.nivel,
    a.mensaje,
    a.fecha_generada,
    p.nombre as paciente,
    t.nombre as tratamiento
FROM alertas a
LEFT JOIN pacientes p ON a.paciente_id = p.id
LEFT JOIN tratamientos t ON a.tratamiento_id = t.id
WHERE a.revisada = false
ORDER BY CASE a.nivel WHEN 'Critica' THEN 1 WHEN 'Advertencia' THEN 2 ELSE 3 END;

-- CONSULTA 6: Medicamentos pendientes para hoy
SELECT 
    p.nombre as paciente,
    m.nombre as medicamento,
    mt.dosis_cantidad,
    mt.unidad_dosis,
    hp.hora_programada,
    'Pendiente' as estado
FROM pacientes p
JOIN tratamientos t ON p.id = t.paciente_id AND t.estado = 'Activo'
JOIN medicamentos_tratamientos mt ON t.id = mt.tratamiento_id
JOIN medicamentos m ON mt.medicamento_id = m.id
JOIN horarios_programados hp ON mt.id = hp.medicamento_tratamiento_id
WHERE p.id = 1 AND hp.dias_semana = 'Daily'
  AND NOT EXISTS (
    SELECT 1 FROM administraciones a 
    WHERE a.horario_programado_id = hp.id 
    AND DATE(a.fecha_hora_administrada) = CURRENT_DATE
  )
ORDER BY hp.hora_programada;

-- CONSULTA 7: Resumen de síntomas PRN más frecuentes
SELECT 
    s.nombre as sintoma,
    s.categoria,
    COUNT(a.id) as total_administraciones,
    m.nombre as medicamento_mas_usado
FROM sintomas_prn s
JOIN administraciones a ON s.id = a.sintoma_reportado_id
JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
JOIN medicamentos m ON mt.medicamento_id = m.id
GROUP BY s.id, s.nombre, s.categoria, m.nombre
ORDER BY total_administraciones DESC;

-- CONSULTA 8: Resumen ejecutivo por paciente
SELECT 
    p.nombre as paciente,
    EXTRACT(YEAR FROM AGE(p.fecha_nacimiento)) as edad,
    COUNT(DISTINCT t.id) as tratamientos_totales,
    COUNT(DISTINCT CASE WHEN t.estado = 'Activo' THEN t.id END) as tratamientos_activos,
    COUNT(a.id) as total_administraciones,
    COUNT(CASE WHEN a.horario_programado_id IS NOT NULL THEN 1 END) as administraciones_programadas,
    COUNT(CASE WHEN a.horario_programado_id IS NULL THEN 1 END) as administraciones_prn,
    ROUND((COUNT(CASE WHEN a.estado = 'Administrada' THEN 1 END) * 100.0 / NULLIF(COUNT(a.id), 0)), 1) as adherencia_general
FROM pacientes p
LEFT JOIN tratamientos t ON p.id = t.paciente_id
LEFT JOIN medicamentos_tratamientos mt ON t.id = mt.tratamiento_id
LEFT JOIN administraciones a ON mt.id = a.medicamento_tratamiento_id
WHERE p.id = 1
GROUP BY p.id, p.nombre, p.fecha_nacimiento; 