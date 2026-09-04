-- ==========================================================
-- MIGRACIÓN: REPASOS EN LA VISTA DE ASISTENCIA (JÓVENES)
-- Fecha: 2026-09-02
--
-- CONTEXTO: la hoja de cálculo que se usaba manualmente
-- ("2026-DISCIPULADO CICLO 2") tiene, además de las 9 clases,
-- dos columnas "REPASO" independientes (una a mitad de
-- camino y otra al final) que marcan si el joven asistió a
-- una sesión de refuerzo general — no están amarradas a una
-- clase puntual como sí lo está `discipulado_progreso.
-- es_recuperacion`.
--
-- Se agregan 2 columnas simples (0/1) a la inscripción para
-- reproducir exactamente esas 2 casillas. NATURALEZA: ADITIVA,
-- con DEFAULT 0, no afecta filas existentes.
-- ==========================================================

SET NAMES utf8mb4;

ALTER TABLE `discipulado_inscripciones`
    ADD COLUMN `repaso_1` TINYINT(1) NOT NULL DEFAULT 0 AFTER `modalidad_principal`,
    ADD COLUMN `repaso_2` TINYINT(1) NOT NULL DEFAULT 0 AFTER `repaso_1`;
