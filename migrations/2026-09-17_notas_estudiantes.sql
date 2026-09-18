-- Migración: notas_estudiantes
-- Reemplaza code/nota1/nota2 por columnas por tipo de curso (técnico, inglés, habilidades)
-- más una nota final. Una fila por estudiante (number_id sigue UNIQUE).

ALTER TABLE `notas_estudiantes`
  ADD COLUMN `id_tecnico` INT(11) NULL DEFAULT NULL AFTER `number_id`,
  ADD COLUMN `nota_tecnico` DECIMAL(5,2) NULL DEFAULT NULL AFTER `id_tecnico`,
  ADD COLUMN `id_ingles` INT(11) NULL DEFAULT NULL AFTER `nota_tecnico`,
  ADD COLUMN `nota_ingles` DECIMAL(5,2) NULL DEFAULT NULL AFTER `id_ingles`,
  ADD COLUMN `id_habilidades` INT(11) NULL DEFAULT NULL AFTER `nota_ingles`,
  ADD COLUMN `nota_habilidades` DECIMAL(5,2) NULL DEFAULT NULL AFTER `id_habilidades`,
  ADD COLUMN `nota_final` DECIMAL(5,2) NULL DEFAULT NULL AFTER `nota_habilidades`;

UPDATE `notas_estudiantes` SET `id_tecnico` = `code`;

ALTER TABLE `notas_estudiantes`
  DROP COLUMN `code`,
  DROP COLUMN `nota1`,
  DROP COLUMN `nota2`;
