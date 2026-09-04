-- FASE 8: eventos importantes configurables por ciclo.
-- Migración aditiva; no modifica ni elimina información existente.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `discipulado_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ciclo_id` int NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NULL,
  `descripcion` text NULL,
  `creado_por` int NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_discipulado_eventos_ciclo_fecha` (`ciclo_id`, `fecha`),
  CONSTRAINT `fk_discipulado_eventos_ciclo` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos_discipulado` (`id`),
  CONSTRAINT `fk_discipulado_eventos_usuario` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
