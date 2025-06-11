-- =====================================================
-- SCRIPT COMPLETO DE PRUEBA - MEDI-TRACK
-- Sistema de Seguimiento Médico y Administración PRN
-- =====================================================

-- 1. SETUP INICIAL - Crear datos base
INSERT INTO roles (nombre, descripcion) VALUES 
('medico', 'Personal médico'), ('cuidador', 'Cuidador profesional'), 
('apoderado', 'Familiar responsable'), ('paciente', 'Paciente');

INSERT INTO generos (id, nombre) VALUES ('M', 'Masculino'), ('F', 'Femenino');

INSERT INTO usuarios (nombre, email, password, rol_id) VALUES 
('Dr. Carlos Mendoza', 'carlos@hospital.com', 'hash123', 1),
('Ana García Cuidadora', 'ana@cuidados.com', 'hash456', 2),
('Roberto Silva Familiar', 'roberto@familia.com', 'hash789', 3);

INSERT INTO personal_medico (usuario_id, especialidad, numero_colegiatura) VALUES 
(1, 'Medicina Interna', 'MED-12345');

INSERT INTO cuidadores (usuario_id, experiencia_anos) VALUES (2, 5);
INSERT INTO apoderados (usuario_id, relacion_paciente) VALUES (3, 'Hijo');

INSERT INTO pacientes (nombre, fecha_nacimiento, genero_id, numero_documento) VALUES 
('María López', '1948-03-15', 'F', '12345678'),
('Pedro Martínez', '1965-07-22', 'M', '87654321');

-- Establecer relaciones
INSERT INTO paciente_cuidadores (paciente_id, cuidador_usuario_id, fecha_asignacion) VALUES 
(1, 2, '2024-01-01'), (2, 2, '2024-01-01');

INSERT INTO paciente_medicos (paciente_id, medico_usuario_id, es_medico_principal, fecha_asignacion) VALUES 
(1, 1, true, '2024-01-01'), (2, 1, true, '2024-01-01');

INSERT INTO paciente_apoderados (paciente_id, apoderado_usuario_id, es_principal) VALUES (1, 3, true);

-- 2. MEDICAMENTOS Y SÍNTOMAS
INSERT INTO medicamentos (nombre, principio_activo, concentracion, forma_farmaceutica) VALUES 
('Amoxicilina', 'Amoxicilina', '500mg', 'Cápsula'),
('Paracetamol', 'Acetaminofén', '500mg', 'Tableta'),
('Metformina', 'Metformina HCl', '850mg', 'Tableta'),
('Omeprazol', 'Omeprazol', '20mg', 'Cápsula');

INSERT INTO sintomas_prn (nombre, categoria) VALUES 
('Fiebre', 'Temperatura'), ('Dolor', 'Analgesia'), ('Acidez', 'Gastrointestinal');

INSERT INTO criterios_prn (sintoma_id, descripcion, valor_minimo, unidad) VALUES 
(1, 'Fiebre superior a', '38', '°C'),
(2, 'Dolor moderado a severo', '5', 'escala 1-10');

-- 3. TRATAMIENTOS DE PRUEBA
-- Tratamiento programado: Antibiótico
INSERT INTO tratamientos (paciente_id, medico_usuario_id, nombre, tipo, fecha_inicio) VALUES 
(1, 1, 'Antibiótico infección respiratoria', 'Programado', '2024-01-15');

INSERT INTO medicamentos_tratamientos (medicamento_id, tratamiento_id, dosis_cantidad, unidad_dosis, frecuencia_horas, tolerancia_antes_minutos, tolerancia_despues_minutos, fecha_inicio) VALUES 
(1, 1, 500, 'mg', 8, 30, 60, '2024-01-15');

INSERT INTO horarios_programados (medicamento_tratamiento_id, paciente_id, hora_programada, dias_semana, fecha_inicio) VALUES 
(1, 1, '08:00:00', 'Daily', '2024-01-15'),
(1, 1, '16:00:00', 'Daily', '2024-01-15'),
(1, 1, '00:00:00', 'Daily', '2024-01-15');

-- Tratamiento PRN: Paracetamol
INSERT INTO tratamientos (paciente_id, medico_usuario_id, nombre, tipo, fecha_inicio) VALUES 
(1, 1, 'Analgésico PRN', 'PRN', '2024-01-10');

INSERT INTO medicamentos_tratamientos (medicamento_id, tratamiento_id, dosis_cantidad, unidad_dosis, intervalo_minimo_horas, dosis_maxima_dia, fecha_inicio) VALUES 
(2, 2, 500, 'mg', 8, 3000, '2024-01-10');

INSERT INTO indicaciones_prn (medicamento_tratamiento_id, sintoma_id, criterio_id, es_criterio_principal) VALUES 
(2, 1, 1, true), (2, 2, 2, true);

-- Tratamiento crónico: Diabetes
INSERT INTO tratamientos (paciente_id, medico_usuario_id, nombre, tipo, fecha_inicio) VALUES 
(1, 1, 'Control Diabetes', 'Programado', '2024-01-01');

INSERT INTO medicamentos_tratamientos (medicamento_id, tratamiento_id, dosis_cantidad, unidad_dosis, frecuencia_horas, fecha_inicio) VALUES 
(3, 3, 850, 'mg', 12, '2024-01-01');

INSERT INTO horarios_programados (medicamento_tratamiento_id, paciente_id, hora_programada, dias_semana, fecha_inicio) VALUES 
(3, 1, '08:00:00', 'Daily', '2024-01-01'),
(3, 1, '20:00:00', 'Daily', '2024-01-01');

-- 4. SIMULAR ADMINISTRACIONES
-- Administraciones programadas
INSERT INTO administraciones (medicamento_tratamiento_id, horario_programado_id, paciente_id, cuidador_usuario_id, fecha_hora_programada, fecha_hora_administrada, dosis_administrada, estado, es_dentro_ventana_tolerancia, minutos_diferencia, observaciones) VALUES 
(1, 1, 1, 2, '2024-01-15 08:00:00', '2024-01-15 08:15:00', 500, 'Administrada', true, 15, 'Con desayuno'),
(1, 2, 1, 2, '2024-01-15 16:00:00', '2024-01-15 17:30:00', 500, 'Tardía', false, 90, 'Paciente dormía'),
(3, 4, 1, 2, '2024-01-15 08:00:00', '2024-01-15 08:15:00', 850, 'Administrada', true, 15, 'Metformina con desayuno');

-- Administraciones PRN
INSERT INTO administraciones (medicamento_tratamiento_id, paciente_id, cuidador_usuario_id, fecha_hora_administrada, dosis_administrada, estado, sintoma_reportado_id, intensidad_sintoma, criterio_cumplido, observaciones) VALUES 
(2, 1, 2, '2024-01-15 14:30:00', 500, 'Administrada', 2, '7/10', 'Dolor rodillas 7>5', 'Dolor articular severo'),
(2, 1, 2, '2024-01-16 10:15:00', 500, 'Administrada', 1, '38.5°C', 'Fiebre 38.5>38°C', 'Fiebre matutina');

-- 5. ALERTAS DE PRUEBA
INSERT INTO alertas (paciente_id, tratamiento_id, tipo, nivel, mensaje) VALUES 
(1, 1, 'Fuera_Ventana', 'Info', 'Dosis Amoxicilina 16:00 tardía - 90 min'),
(1, 2, 'Intervalo_Corto_PRN', 'Advertencia', 'Paracetamol solicitado - Intervalo <8h');

-- =====================================================
-- CONSULTAS DE VALIDACIÓN Y TESTING
-- =====================================================

-- CONSULTA 1: Dashboard del paciente
SELECT 
    'DASHBOARD PACIENTE' as consulta,
    p.nombre as paciente,
    COUNT(DISTINCT t.id) as tratamientos_activos,
    COUNT(DISTINCT CASE WHEN t.tipo = 'Programado' THEN t.id END) as programados,
    COUNT(DISTINCT CASE WHEN t.tipo = 'PRN' THEN t.id END) as prn
FROM pacientes p
LEFT JOIN tratamientos t ON p.id = t.paciente_id AND t.estado = 'Activo'
WHERE p.id = 1
GROUP BY p.nombre;

-- CONSULTA 2: Adherencia por medicamento
SELECT 
    'ADHERENCIA MEDICAMENTOS' as consulta,
    m.nombre as medicamento,
    mt.dosis_cantidad || ' ' || mt.unidad_dosis as dosis,
    COUNT(hp.id) as dosis_programadas,
    COUNT(a.id) as dosis_administradas,
    ROUND((COUNT(a.id) * 100.0 / NULLIF(COUNT(hp.id), 0)), 1) as adherencia_pct
FROM medicamentos m
JOIN medicamentos_tratamientos mt ON m.id = mt.medicamento_id
JOIN tratamientos t ON mt.tratamiento_id = t.id
LEFT JOIN horarios_programados hp ON mt.id = hp.medicamento_tratamiento_id
LEFT JOIN administraciones a ON hp.id = a.horario_programado_id
WHERE t.paciente_id = 1 AND t.tipo = 'Programado'
GROUP BY m.nombre, mt.dosis_cantidad, mt.unidad_dosis;

-- CONSULTA 3: Historial PRN
SELECT 
    'HISTORIAL PRN' as consulta,
    DATE(a.fecha_hora_administrada) as fecha,
    m.nombre as medicamento,
    s.nombre as sintoma,
    a.intensidad_sintoma,
    a.observaciones
FROM administraciones a
JOIN medicamentos_tratamientos mt ON a.medicamento_tratamiento_id = mt.id
JOIN medicamentos m ON mt.medicamento_id = m.id
LEFT JOIN sintomas_prn s ON a.sintoma_reportado_id = s.id
WHERE a.horario_programado_id IS NULL
ORDER BY a.fecha_hora_administrada DESC;

-- CONSULTA 4: Verificación PRN actual
SELECT 
    'VERIFICACION PRN' as consulta,
    m.nombre as medicamento,
    mt.intervalo_minimo_horas as intervalo_min,
    mt.dosis_maxima_dia as dosis_max_dia,
    MAX(a.fecha_hora_administrada) as ultima_dosis,
    COALESCE(SUM(CASE WHEN DATE(a.fecha_hora_administrada) = CURRENT_DATE THEN a.dosis_administrada ELSE 0 END), 0) as consumo_hoy
FROM medicamentos m
JOIN medicamentos_tratamientos mt ON m.id = mt.medicamento_id
JOIN tratamientos t ON mt.tratamiento_id = t.id
LEFT JOIN administraciones a ON mt.id = a.medicamento_tratamiento_id AND a.horario_programado_id IS NULL
WHERE t.tipo = 'PRN' AND t.paciente_id = 1
GROUP BY m.nombre, mt.intervalo_minimo_horas, mt.dosis_maxima_dia;

-- CONSULTA 5: Alertas pendientes
SELECT 
    'ALERTAS PENDIENTES' as consulta,
    a.tipo,
    a.nivel,
    a.mensaje,
    p.nombre as paciente
FROM alertas a
LEFT JOIN pacientes p ON a.paciente_id = p.id
WHERE a.revisada = false
ORDER BY CASE a.nivel WHEN 'Critica' THEN 1 WHEN 'Advertencia' THEN 2 ELSE 3 END;

-- CONSULTA 6: Resumen estadístico
SELECT 
    'RESUMEN ESTADISTICO' as consulta,
    COUNT(DISTINCT p.id) as total_pacientes,
    COUNT(DISTINCT t.id) as total_tratamientos,
    COUNT(DISTINCT m.id) as total_medicamentos,
    COUNT(a.id) as total_administraciones,
    COUNT(CASE WHEN a.horario_programado_id IS NOT NULL THEN 1 END) as admin_programadas,
    COUNT(CASE WHEN a.horario_programado_id IS NULL THEN 1 END) as admin_prn
FROM pacientes p
LEFT JOIN tratamientos t ON p.id = t.paciente_id
LEFT JOIN medicamentos_tratamientos mt ON t.id = mt.tratamiento_id
LEFT JOIN medicamentos m ON mt.medicamento_id = m.id
LEFT JOIN administraciones a ON mt.id = a.medicamento_tratamiento_id;

SELECT 'SCRIPT COMPLETADO - BASE DE DATOS POBLADA CON DATOS DE PRUEBA' as resultado; 