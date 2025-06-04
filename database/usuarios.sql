CREATE TABLE `roles` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(50) UNIQUE NOT NULL,
  `descripcion` text,
  `activo` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `usuarios` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20),
  `rol_id` int NOT NULL,
  `activo` boolean DEFAULT true,
  `email_verificado` boolean DEFAULT false,
  `ultimo_acceso` timestamp,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `personal_medico` (
  `usuario_id` int PRIMARY KEY,
  `especialidad` varchar(100),
  `numero_colegiatura` varchar(50) UNIQUE,
  `institucion` varchar(100),
  `anos_experiencia` int
);

CREATE TABLE `cuidadores` (
  `usuario_id` int PRIMARY KEY,
  `certificaciones` text,
  `experiencia_anos` int,
  `disponibilidad_horaria` varchar(100),
  `tarifa_hora` decimal(8,2)
);

CREATE TABLE `apoderados` (
  `usuario_id` int PRIMARY KEY,
  `relacion_paciente` varchar(50),
  `es_contacto_emergencia` boolean DEFAULT true
);

CREATE TABLE `pacientes` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `usuario_id` int COMMENT 'Opcional - paciente puede no tener cuenta',
  `nombre` varchar(100) NOT NULL,
  `fecha_nacimiento` date,
  `genero_id` char(1),
  `numero_documento` varchar(20) UNIQUE,
  `tipo_documento` varchar(10),
  `tipo_sangre` varchar(10),
  `altura` decimal(5,2),
  `direccion` text,
  `telefono_emergencia` varchar(20),
  `observaciones_medicas` text,
  `activo` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `generos` (
  `id` char(1) PRIMARY KEY,
  `nombre` varchar(20) UNIQUE NOT NULL
);

CREATE TABLE `paciente_apoderados` (
  `paciente_id` int,
  `apoderado_usuario_id` int,
  `es_principal` boolean DEFAULT false,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP),
  PRIMARY KEY (`paciente_id`, `apoderado_usuario_id`)
);

CREATE TABLE `paciente_cuidadores` (
  `paciente_id` int,
  `cuidador_usuario_id` int,
  `fecha_asignacion` date NOT NULL,
  `fecha_fin` date,
  `activo` boolean DEFAULT true,
  PRIMARY KEY (`paciente_id`, `cuidador_usuario_id`)
);

CREATE TABLE `paciente_medicos` (
  `paciente_id` int,
  `medico_usuario_id` int,
  `es_medico_principal` boolean DEFAULT false,
  `fecha_asignacion` date NOT NULL,
  `fecha_fin` date,
  `especialidad_tratamiento` varchar(100),
  PRIMARY KEY (`paciente_id`, `medico_usuario_id`)
);

CREATE TABLE `permisos` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(50) UNIQUE NOT NULL,
  `descripcion` text,
  `modulo` varchar(50)
);

CREATE TABLE `rol_permisos` (
  `rol_id` int,
  `permiso_id` int,
  PRIMARY KEY (`rol_id`, `permiso_id`)
);

CREATE TABLE `auditoria_usuarios` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `usuario_id` int,
  `accion` varchar(100) NOT NULL,
  `tabla_afectada` varchar(50),
  `registro_id` int,
  `datos_anteriores` json,
  `datos_nuevos` json,
  `ip_address` varchar(45),
  `user_agent` text,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `sesiones_usuario` (
  `id` varchar(255) PRIMARY KEY,
  `usuario_id` int,
  `ip_address` varchar(45),
  `user_agent` text,
  `activa` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP),
  `expires_at` timestamp NOT NULL
);

ALTER TABLE `roles` COMMENT = 'Roles del sistema: admin, medico, cuidador, apoderado, paciente';

ALTER TABLE `usuarios` COMMENT = 'Tabla central de usuarios del sistema';

ALTER TABLE `personal_medico` COMMENT = 'Información específica del personal médico';

ALTER TABLE `cuidadores` COMMENT = 'Información específica de cuidadores';

ALTER TABLE `apoderados` COMMENT = 'Información específica de apoderados/tutores';

ALTER TABLE `pacientes` COMMENT = 'Pacientes del sistema - pueden o no tener cuenta de usuario';

ALTER TABLE `generos` COMMENT = 'M: Masculino, F: Femenino, O: Otro';

ALTER TABLE `paciente_apoderados` COMMENT = 'Un paciente puede tener múltiples apoderados';

ALTER TABLE `paciente_cuidadores` COMMENT = 'Asignación de cuidadores a pacientes';

ALTER TABLE `paciente_medicos` COMMENT = 'Asignación de médicos a pacientes';

ALTER TABLE `permisos` COMMENT = 'Permisos específicos del sistema';

ALTER TABLE `rol_permisos` COMMENT = 'Permisos asignados a cada rol';

ALTER TABLE `auditoria_usuarios` COMMENT = 'Registro de acciones realizadas por usuarios';

ALTER TABLE `sesiones_usuario` COMMENT = 'Gestión de sesiones activas de usuarios';

ALTER TABLE `usuarios` ADD FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

ALTER TABLE `personal_medico` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `cuidadores` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `apoderados` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `pacientes` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `pacientes` ADD FOREIGN KEY (`genero_id`) REFERENCES `generos` (`id`);

ALTER TABLE `paciente_apoderados` ADD FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`);

ALTER TABLE `paciente_apoderados` ADD FOREIGN KEY (`apoderado_usuario_id`) REFERENCES `apoderados` (`usuario_id`);

ALTER TABLE `paciente_cuidadores` ADD FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`);

ALTER TABLE `paciente_cuidadores` ADD FOREIGN KEY (`cuidador_usuario_id`) REFERENCES `cuidadores` (`usuario_id`);

ALTER TABLE `paciente_medicos` ADD FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`);

ALTER TABLE `paciente_medicos` ADD FOREIGN KEY (`medico_usuario_id`) REFERENCES `personal_medico` (`usuario_id`);

ALTER TABLE `rol_permisos` ADD FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

ALTER TABLE `rol_permisos` ADD FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`);

ALTER TABLE `auditoria_usuarios` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `sesiones_usuario` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `paciente_cuidadores` ADD FOREIGN KEY (`fecha_fin`) REFERENCES `paciente_cuidadores` (`paciente_id`);

ALTER TABLE `auditoria_usuarios` ADD FOREIGN KEY (`accion`) REFERENCES `auditoria_usuarios` (`usuario_id`);
