-- Migración: nota mínima para aprobar en notas_pesos.
-- Editable desde el modal de configuración de notas en el dashboard.

ALTER TABLE `notas_pesos`
  ADD COLUMN `nota_minima_aprobacion` DECIMAL(3,2) NOT NULL DEFAULT 3.00 AFTER `peso_habilidades`;
