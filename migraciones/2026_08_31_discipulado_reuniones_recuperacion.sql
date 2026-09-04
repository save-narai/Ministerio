-- ==========================================================
-- MIGRACIÓN: FASE 7 — INTEGRACIÓN CON REUNIONES Y ASISTENCIA
-- SIG REMANENTE
-- Fecha: 2026-08-31
--
-- NATURALEZA: ADITIVA. Agrega UNA sola columna a una tabla
-- que la Fase 2 dejó creada pero todavía sin usar
-- (`discipulado_reuniones` estaba vacía hasta esta fase), por
-- lo que no hay ninguna fila existente que se vea afectada.
--
-- MOTIVO (ver Fase 7, sección 12):
-- "es_recuperacion = modalidad VIRTUAL" sería una regla
-- incorrecta (puede existir una clase virtual normal que NO
-- es recuperación). Se necesita un indicador EXPLÍCITO en la
-- reunión para saber si esa reunión puntual es una
-- recuperación, sin inventar una tabla nueva.
-- ==========================================================

SET NAMES utf8mb4;

ALTER TABLE `discipulado_reuniones`
  ADD COLUMN `es_recuperacion` tinyint(1) NOT NULL DEFAULT 0 AFTER `modalidad`;
