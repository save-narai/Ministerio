-- Catálogo reutilizable de clases oficiales de discipulado.
-- Ejecutar una sola vez sobre la base de datos ministerio_jovenes.

START TRANSACTION;

CREATE TABLE clases_base_discipulado (
    id INT NOT NULL AUTO_INCREMENT,
    numero_orden INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    modalidad_programada ENUM('PRESENCIAL', 'VIRTUAL') NULL,
    repasos_requeridos TINYINT UNSIGNED NOT NULL DEFAULT 2,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clase_base_orden (numero_orden),
    KEY idx_clase_base_activa_orden (activo, numero_orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE clases_discipulado
    ADD COLUMN clase_base_id INT NULL AFTER ciclo_id,
    ADD COLUMN repasos_requeridos TINYINT UNSIGNED NOT NULL DEFAULT 2 AFTER modalidad_programada,
    ADD KEY idx_clase_base (clase_base_id),
    ADD CONSTRAINT fk_clase_ciclo_base
        FOREIGN KEY (clase_base_id) REFERENCES clases_base_discipulado(id)
        ON UPDATE CASCADE ON DELETE SET NULL;

-- Conserva las clases ya creadas como catálogo oficial, sin borrar ni reemplazar datos.
INSERT INTO clases_base_discipulado (numero_orden, nombre, descripcion, modalidad_programada, repasos_requeridos)
SELECT cd.numero_orden, MIN(cd.nombre), MAX(cd.descripcion), MAX(cd.modalidad_programada), 2
FROM clases_discipulado cd
GROUP BY cd.numero_orden
ORDER BY cd.numero_orden
ON DUPLICATE KEY UPDATE id = id;

-- En instalaciones sin clases históricas se entrega el catálogo inicial de la cartilla.
INSERT INTO clases_base_discipulado (numero_orden, nombre, repasos_requeridos)
VALUES
    (1, 'Lección 1', 2), (2, 'Lección 2', 2), (3, 'Lección 3', 2),
    (4, 'Lección 4', 2), (5, 'Lección 5', 2), (6, 'Lección 6', 2),
    (7, 'Lección 7', 2), (8, 'Lección 8', 2), (9, 'Lección 9', 2)
ON DUPLICATE KEY UPDATE id = id;

UPDATE clases_discipulado cd
INNER JOIN clases_base_discipulado cb
    ON cb.numero_orden = cd.numero_orden
    AND cb.nombre = cd.nombre
SET cd.clase_base_id = cb.id,
    cd.repasos_requeridos = cb.repasos_requeridos
WHERE cd.clase_base_id IS NULL;

COMMIT;
