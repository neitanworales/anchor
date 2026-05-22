-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 23-05-2026 a las 00:03:33
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `anchor`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(128) NOT NULL,
  `token_type` varchar(32) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_id`, `token`, `token_type`, `expires_at`, `created_at`, `revoked_at`) VALUES
(1, 1, '6b514b25e67ed0226796745163b619bbffafa65f85a55d67bd65548e2be5de1b', 'auth', '2026-05-29 21:37:14', '2026-05-22 13:37:14', NULL),
(2, 1, '4d51f011a2f39487581a2a57aeeddfc5e5b6783404b7a11f5d6174463828a4d2', 'auth', '2026-05-29 21:38:20', '2026-05-22 13:38:20', NULL),
(3, 1, '7eec030bb2625965e9c88b35e17fc1850d87f85603b7c586a998fc6a7c492ba7', 'auth', '2026-05-29 21:44:33', '2026-05-22 13:44:33', NULL),
(4, 1, 'ae648a0d2888fbf80520ae71089d7f264c98cb5fc18da2d5d191f92ed07c6d47', 'auth', '2026-05-29 21:45:50', '2026-05-22 13:45:50', NULL),
(5, 1, '812672d4ca98a44967b4926a40201bf915e1bdd12fe37dbb0f8c38d6c50a4de0', 'auth', '2026-05-29 21:54:51', '2026-05-22 13:54:51', NULL),
(6, 1, '6064afcf8d0d39b1b613ec18251a2752c2a631bce8548b93527157d97a5c7b8b', 'auth', '2026-05-29 21:55:52', '2026-05-22 13:55:52', NULL),
(7, 1, '447c6b7e54ca67bce0b77c9886e56c08647a3fa9dadb91dc58f9fb87bc535d74', 'auth', '2026-05-29 21:56:46', '2026-05-22 13:56:46', NULL),
(8, 1, 'ceb3066c6759dbf60f7b9255597bc30919946d09ac9c85e81376edb771ba8159', 'auth', '2026-05-29 21:57:10', '2026-05-22 13:57:10', NULL),
(9, 1, 'cdb9459160505cd16c95e759ab3357a62bce46831cae336deda4fc9155826409', 'auth', '2026-05-29 22:00:02', '2026-05-22 14:00:02', NULL),
(10, 1, '8097da81ae44b06925e274d8e5f3989870c7f66fc4eb642893dba35608e555de', 'auth', '2026-05-29 22:11:45', '2026-05-22 14:11:45', NULL),
(11, 1, '8a8247e5e501ccc8edd0a21d6aa37590422b257591ffc301009851933f0e28ef', 'auth', '2026-05-29 22:20:59', '2026-05-22 14:20:59', NULL),
(12, 1, '9176ce6f5231b57a4ca0866ecc70aa5bd2ca6a6c2d1cbf601c6fe7ad879eeec7', 'auth', '2026-05-29 22:42:31', '2026-05-22 14:42:31', NULL),
(13, 1, '1e6fdf2477c9b48602d981832f2504f13202dc1462b11549182a16744ba8cebd', 'auth', '2026-05-29 22:46:11', '2026-05-22 14:46:11', '2026-05-22 14:48:17'),
(14, 1, 'f4f36d6949922f9a3fdd7826d92e4c0975057c561f2e8abccd72a9c0bd004536', 'auth', '2026-05-29 22:48:21', '2026-05-22 14:48:21', '2026-05-22 14:48:24'),
(15, 1, 'cc360f15a488844c5cf837b8a30ceee140223153aba0b9219a5052166ac4525e', 'auth', '2026-05-29 23:20:16', '2026-05-22 15:20:17', '2026-05-22 15:20:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cfdi_relacionados`
--

CREATE TABLE `cfdi_relacionados` (
  `id` bigint(20) NOT NULL,
  `factura_id` bigint(20) NOT NULL,
  `tipo_relacion` varchar(10) DEFAULT NULL,
  `uuid_relacionado` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` bigint(20) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `version_cfdi` varchar(10) DEFAULT NULL,
  `serie` varchar(50) DEFAULT NULL,
  `folio` varchar(50) DEFAULT NULL,
  `fecha_emision` datetime DEFAULT NULL,
  `fecha_timbrado` datetime DEFAULT NULL,
  `tipo_comprobante` varchar(5) DEFAULT NULL,
  `moneda` varchar(10) DEFAULT NULL,
  `tipo_cambio` decimal(18,6) DEFAULT NULL,
  `subtotal` decimal(18,6) DEFAULT NULL,
  `descuento` decimal(18,6) DEFAULT NULL,
  `total` decimal(18,6) DEFAULT NULL,
  `metodo_pago` varchar(10) DEFAULT NULL,
  `forma_pago` varchar(10) DEFAULT NULL,
  `lugar_expedicion` varchar(10) DEFAULT NULL,
  `exportacion` varchar(10) DEFAULT NULL,
  `rfc_emisor` varchar(13) DEFAULT NULL,
  `nombre_emisor` varchar(255) DEFAULT NULL,
  `regimen_emisor` varchar(10) DEFAULT NULL,
  `rfc_receptor` varchar(13) DEFAULT NULL,
  `nombre_receptor` varchar(255) DEFAULT NULL,
  `domicilio_fiscal_receptor` varchar(10) DEFAULT NULL,
  `regimen_receptor` varchar(10) DEFAULT NULL,
  `uso_cfdi` varchar(10) DEFAULT NULL,
  `sello_cfd` text DEFAULT NULL,
  `no_certificado` varchar(50) DEFAULT NULL,
  `certificado` longtext DEFAULT NULL,
  `sello_sat` text DEFAULT NULL,
  `no_certificado_sat` varchar(50) DEFAULT NULL,
  `rfc_pac` varchar(13) DEFAULT NULL,
  `xml_original` longtext DEFAULT NULL,
  `ruta_xml` varchar(500) DEFAULT NULL,
  `estatus_sat` varchar(50) DEFAULT 'NO_CONSULTADO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_usuarios`
--

CREATE TABLE `factura_usuarios` (
  `id` bigint(20) NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `factura_id` bigint(20) NOT NULL,
  `aprobado` tinyint(1) NOT NULL DEFAULT 0,
  `aprobado_por` int(10) UNSIGNED DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_approved` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_conceptos`
--

CREATE TABLE `factura_conceptos` (
  `id` bigint(20) NOT NULL,
  `factura_id` bigint(20) NOT NULL,
  `clave_prod_serv` varchar(20) DEFAULT NULL,
  `no_identificacion` varchar(100) DEFAULT NULL,
  `cantidad` decimal(18,6) DEFAULT NULL,
  `clave_unidad` varchar(20) DEFAULT NULL,
  `unidad` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `valor_unitario` decimal(18,6) DEFAULT NULL,
  `importe` decimal(18,6) DEFAULT NULL,
  `descuento` decimal(18,6) DEFAULT NULL,
  `objeto_imp` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_impuestos`
--

CREATE TABLE `factura_impuestos` (
  `id` bigint(20) NOT NULL,
  `factura_id` bigint(20) NOT NULL,
  `concepto_id` bigint(20) DEFAULT NULL,
  `tipo` enum('TRASLADO','RETENCION') NOT NULL,
  `impuesto` varchar(10) DEFAULT NULL,
  `tipo_factor` varchar(20) DEFAULT NULL,
  `tasa_cuota` decimal(18,6) DEFAULT NULL,
  `base` decimal(18,6) DEFAULT NULL,
  `importe` decimal(18,6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` bigint(20) NOT NULL,
  `factura_id` bigint(20) NOT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `forma_pago` varchar(10) DEFAULT NULL,
  `moneda_pago` varchar(10) DEFAULT NULL,
  `tipo_cambio_pago` decimal(18,6) DEFAULT NULL,
  `monto` decimal(18,6) DEFAULT NULL,
  `num_operacion` varchar(100) DEFAULT NULL,
  `rfc_banco_emisor` varchar(13) DEFAULT NULL,
  `cuenta_ordenante` varchar(50) DEFAULT NULL,
  `rfc_banco_receptor` varchar(13) DEFAULT NULL,
  `cuenta_beneficiario` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_documentos_relacionados`
--

CREATE TABLE `pagos_documentos_relacionados` (
  `id` bigint(20) NOT NULL,
  `pago_id` bigint(20) NOT NULL,
  `uuid_documento` varchar(36) NOT NULL,
  `serie` varchar(50) DEFAULT NULL,
  `folio` varchar(50) DEFAULT NULL,
  `moneda_dr` varchar(10) DEFAULT NULL,
  `metodo_pago_dr` varchar(10) DEFAULT NULL,
  `num_parcialidad` int(11) DEFAULT NULL,
  `saldo_anterior` decimal(18,6) DEFAULT NULL,
  `importe_pagado` decimal(18,6) DEFAULT NULL,
  `saldo_insoluto` decimal(18,6) DEFAULT NULL,
  `objeto_imp_dr` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'super'),
(2, 'admin'),
(3, 'capturer'),
(4, 'approver');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `second_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `second_last_name` varchar(100) DEFAULT NULL,
  `user_status` varchar(50) DEFAULT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `verification_code` varchar(120) DEFAULT NULL,
  `registered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `second_name`, `last_name`, `second_last_name`, `user_status`, `email`, `password_hash`, `verification_code`, `registered_at`) VALUES
(1, 'Pedro', 'Natán', 'Morales', 'Hernández', 'active', 'neitan.morales@gmail.com', '$2y$12$DSHaOWPg8LfApbMzt2g3Gu1GCGNbT0CWWQuH2jo42e2mx8YdVz0Pm', NULL, '2026-05-22 12:51:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_auth_tokens_token` (`token`),
  ADD KEY `idx_auth_tokens_user_id` (`user_id`),
  ADD KEY `idx_auth_tokens_token_type` (`token_type`);

--
-- Indices de la tabla `cfdi_relacionados`
--
ALTER TABLE `cfdi_relacionados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_relacionados_factura` (`factura_id`),
  ADD KEY `idx_relacionados_uuid` (`uuid_relacionado`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_facturas_uuid` (`uuid`),
  ADD KEY `idx_facturas_rfc_emisor` (`rfc_emisor`),
  ADD KEY `idx_facturas_rfc_receptor` (`rfc_receptor`),
  ADD KEY `idx_facturas_fecha_emision` (`fecha_emision`),
  ADD KEY `idx_facturas_tipo_comprobante` (`tipo_comprobante`);

--
-- Indices de la tabla `factura_usuarios`
--
ALTER TABLE `factura_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_factura_usuarios_usuario_id` (`usuario_id`),
  ADD KEY `idx_factura_usuarios_factura_id` (`factura_id`),
  ADD KEY `idx_factura_usuarios_aprobado_por` (`aprobado_por`),
  ADD KEY `idx_factura_usuarios_aprobado` (`aprobado`);

--
-- Indices de la tabla `factura_conceptos`
--
ALTER TABLE `factura_conceptos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conceptos_factura_id` (`factura_id`);

--
-- Indices de la tabla `factura_impuestos`
--
ALTER TABLE `factura_impuestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_impuestos_factura_id` (`factura_id`),
  ADD KEY `idx_impuestos_concepto_id` (`concepto_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagos_factura_id` (`factura_id`);

--
-- Indices de la tabla `pagos_documentos_relacionados`
--
ALTER TABLE `pagos_documentos_relacionados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_documentos_pago` (`pago_id`),
  ADD KEY `idx_pagos_docs_uuid` (`uuid_documento`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_verification_code` (`verification_code`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `cfdi_relacionados`
--
ALTER TABLE `cfdi_relacionados`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `factura_usuarios`
--
ALTER TABLE `factura_usuarios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `factura_conceptos`
--
ALTER TABLE `factura_conceptos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `factura_impuestos`
--
ALTER TABLE `factura_impuestos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_documentos_relacionados`
--
ALTER TABLE `pagos_documentos_relacionados`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `fk_auth_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cfdi_relacionados`
--
ALTER TABLE `cfdi_relacionados`
  ADD CONSTRAINT `fk_relacionados_factura` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `factura_conceptos`
--
ALTER TABLE `factura_conceptos`
  ADD CONSTRAINT `fk_conceptos_factura` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `factura_impuestos`
--
ALTER TABLE `factura_impuestos`
  ADD CONSTRAINT `fk_impuestos_concepto` FOREIGN KEY (`concepto_id`) REFERENCES `factura_conceptos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_impuestos_factura` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `factura_usuarios`
--
ALTER TABLE `factura_usuarios`
  ADD CONSTRAINT `fk_factura_usuarios_factura` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_factura_usuarios_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_factura_usuarios_aprobador` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_factura` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos_documentos_relacionados`
--
ALTER TABLE `pagos_documentos_relacionados`
  ADD CONSTRAINT `fk_documentos_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
