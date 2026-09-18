-- Migración: control de diplomas emitidos (idempotencia y envío de correo).

CREATE TABLE IF NOT EXISTS `diplomas_emitidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number_id` varchar(20) NOT NULL,
  `programa` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `email_destino` varchar(255) NOT NULL,
  `estado` enum('generado','enviado','error') NOT NULL DEFAULT 'generado',
  `intentos` int(11) NOT NULL DEFAULT 0,
  `error_msg` varchar(255) DEFAULT NULL,
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_envio` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_number_programa` (`number_id`, `programa`),
  UNIQUE KEY `uq_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
