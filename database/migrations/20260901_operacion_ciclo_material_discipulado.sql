START TRANSACTION;

ALTER TABLE ciclos_discipulado
    ADD COLUMN monitor_id INT NULL AFTER creado_por,
    ADD COLUMN encargado_principal_id INT NULL AFTER monitor_id,
    ADD KEY idx_ciclo_monitor (monitor_id),
    ADD KEY idx_ciclo_encargado (encargado_principal_id),
    ADD CONSTRAINT fk_ciclo_monitor FOREIGN KEY (monitor_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_ciclo_encargado FOREIGN KEY (encargado_principal_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE clases_discipulado
    ADD COLUMN profesor_id INT NULL AFTER clase_base_id,
    ADD KEY idx_clase_profesor (profesor_id),
    ADD CONSTRAINT fk_clase_profesor FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE materiales_discipulado (
    id INT NOT NULL AUTO_INCREMENT,
    clase_base_id INT NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    archivo_generado VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    tamano_bytes INT UNSIGNED NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_material_clase_base (clase_base_id),
    UNIQUE KEY uq_material_archivo (archivo_generado),
    CONSTRAINT fk_material_clase_base FOREIGN KEY (clase_base_id) REFERENCES clases_base_discipulado(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

UPDATE clases_base_discipulado
SET nombre = CASE numero_orden
    WHEN 1 THEN 'Ciencia versus Dios'
    WHEN 2 THEN 'Fe ciega'
    WHEN 3 THEN 'Quién necesita la salvación'
    WHEN 4 THEN 'Jesucristo, regalo de Dios'
    WHEN 5 THEN '¿Qué es nacer de nuevo?'
    WHEN 6 THEN 'Libro de libros'
    WHEN 7 THEN 'Cristianismo, un culto irracional'
    WHEN 8 THEN '¿Cómo debo manejar mi vida?'
    WHEN 9 THEN 'Mentiras, verdad'
END
WHERE numero_orden BETWEEN 1 AND 9;

COMMIT;
