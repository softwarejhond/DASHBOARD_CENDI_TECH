-- Migración: fecha real de finalización de cursos en notas_estudiantes.
-- La setea el cron (cron_obtener_notas.php) una sola vez, cuando el estudiante
-- completa los tres componentes.

ALTER TABLE `notas_estudiantes`
  ADD COLUMN `fecha_fin_cursos` DATETIME NULL DEFAULT NULL AFTER `nota_final`;
