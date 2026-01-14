-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 14-01-2026 a las 06:41:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tienda_etnia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_zapato`
--

CREATE TABLE `carrito_zapato` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_grupo` varchar(50) DEFAULT NULL,
  `id_zapato` int(11) NOT NULL,
  `id_talla` int(11) NOT NULL,
  `id_color` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `tipo_paquete` enum('seis','doce') NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito_zapato`
--

INSERT INTO `carrito_zapato` (`id`, `id_usuario`, `id_grupo`, `id_zapato`, `id_talla`, `id_color`, `cantidad`, `categoria_id`, `tipo_paquete`, `subtotal`) VALUES
(104, 1, 'GRP-1768368507268', 2, 4, 2, 6, 3, 'seis', 2065.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`) VALUES
(2, 'Balerina'),
(3, 'Botines'),
(1, 'Casual'),
(5, 'Confort Sandalia'),
(4, 'Guante'),
(6, 'Sandalia'),
(7, 'Sandalia de Tacón'),
(8, 'Tacón Cerrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colores_zapato`
--

CREATE TABLE `colores_zapato` (
  `id_color` int(11) NOT NULL,
  `id_zapato` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `hex` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `colores_zapato`
--

INSERT INTO `colores_zapato` (`id_color`, `id_zapato`, `nombre`, `hex`) VALUES
(1, 2, 'Café', '#6F4E37'),
(2, 2, 'Negro', '#000000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `id_favorito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_zapato` int(11) NOT NULL,
  `fecha_agregado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `favoritos`
--

INSERT INTO `favoritos` (`id_favorito`, `id_usuario`, `id_zapato`, `fecha_agregado`) VALUES
(38, 1, 1, '2026-01-13 22:52:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_zapato`
--

CREATE TABLE `imagenes_zapato` (
  `id_imagen` int(11) NOT NULL,
  `id_zapato` int(11) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 1,
  `id_color` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `imagenes_zapato`
--

INSERT INTO `imagenes_zapato` (`id_imagen`, `id_zapato`, `ruta`, `orden`, `id_color`) VALUES
(1, 1, 'Assets/Imagenes/Items/Botines/botin_chelse_cafe.jpg', 1, NULL),
(2, 2, 'Assets/Imagenes/Items/Botines/botin_con_hebilla_cafe.jpg', 1, 1),
(3, 2, 'Assets/Imagenes/Items/Botines/botin_con_hebilla_negro.jpg', 1, 2),
(4, 2, 'Assets\\Imagenes\\Items\\Botines\\botin_con_hebilla_negro_2.jpg', 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas`
--

CREATE TABLE `ofertas` (
  `id_oferta` int(11) NOT NULL,
  `id_zapato` int(11) NOT NULL,
  `porcentaje` int(11) NOT NULL,
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ofertas`
--

INSERT INTO `ofertas` (`id_oferta`, `id_zapato`, `porcentaje`, `estado`) VALUES
(1, 1, 15, 1),
(2, 2, 25, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `populares`
--

CREATE TABLE `populares` (
  `id_popular` int(11) NOT NULL,
  `id_zapato` int(11) NOT NULL,
  `orden` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `populares`
--

INSERT INTO `populares` (`id_popular`, `id_zapato`, `orden`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tallas`
--

CREATE TABLE `tallas` (
  `id_talla` int(11) NOT NULL,
  `valor` decimal(3,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tallas`
--

INSERT INTO `tallas` (`id_talla`, `valor`) VALUES
(1, 22.5),
(2, 23.0),
(3, 23.5),
(4, 24.0),
(5, 24.5),
(6, 25.0),
(7, 25.5),
(8, 26.0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password`, `fecha_registro`) VALUES
(1, 'Angel Rizo', 'correoej@gmail.com', '$2y$10$wFvS1vPmjUoT/n1IH6fx8eB0D3Uctk3/7q.2.jzlYEf5bp2tXCd5i', '2026-01-13 08:47:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zapatos`
--

CREATE TABLE `zapatos` (
  `id_zapato` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `zapatos`
--

INSERT INTO `zapatos` (`id_zapato`, `nombre`, `precio`, `id_categoria`) VALUES
(1, 'Botín Chelse', 200.00, 3),
(2, 'Botín con hebilla', 510.00, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zapato_talla`
--

CREATE TABLE `zapato_talla` (
  `id_zapato` int(11) NOT NULL,
  `id_talla` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `zapato_talla`
--

INSERT INTO `zapato_talla` (`id_zapato`, `id_talla`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito_zapato`
--
ALTER TABLE `carrito_zapato`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `colores_zapato`
--
ALTER TABLE `colores_zapato`
  ADD PRIMARY KEY (`id_color`),
  ADD KEY `id_zapato` (`id_zapato`);

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id_favorito`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_zapato` (`id_zapato`);

--
-- Indices de la tabla `imagenes_zapato`
--
ALTER TABLE `imagenes_zapato`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_zapato` (`id_zapato`),
  ADD KEY `fk_imagen_color` (`id_color`);

--
-- Indices de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD PRIMARY KEY (`id_oferta`),
  ADD KEY `id_zapato` (`id_zapato`);

--
-- Indices de la tabla `populares`
--
ALTER TABLE `populares`
  ADD PRIMARY KEY (`id_popular`),
  ADD KEY `id_zapato` (`id_zapato`);

--
-- Indices de la tabla `tallas`
--
ALTER TABLE `tallas`
  ADD PRIMARY KEY (`id_talla`),
  ADD UNIQUE KEY `valor` (`valor`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `zapatos`
--
ALTER TABLE `zapatos`
  ADD PRIMARY KEY (`id_zapato`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `zapato_talla`
--
ALTER TABLE `zapato_talla`
  ADD PRIMARY KEY (`id_zapato`,`id_talla`),
  ADD KEY `id_talla` (`id_talla`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito_zapato`
--
ALTER TABLE `carrito_zapato`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `colores_zapato`
--
ALTER TABLE `colores_zapato`
  MODIFY `id_color` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id_favorito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `imagenes_zapato`
--
ALTER TABLE `imagenes_zapato`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  MODIFY `id_oferta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `populares`
--
ALTER TABLE `populares`
  MODIFY `id_popular` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tallas`
--
ALTER TABLE `tallas`
  MODIFY `id_talla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `zapatos`
--
ALTER TABLE `zapatos`
  MODIFY `id_zapato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `colores_zapato`
--
ALTER TABLE `colores_zapato`
  ADD CONSTRAINT `fk_colores_zapato` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`) ON DELETE CASCADE;

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`);

--
-- Filtros para la tabla `imagenes_zapato`
--
ALTER TABLE `imagenes_zapato`
  ADD CONSTRAINT `fk_imagen_color` FOREIGN KEY (`id_color`) REFERENCES `colores_zapato` (`id_color`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_img_zapato` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD CONSTRAINT `ofertas_ibfk_1` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`);

--
-- Filtros para la tabla `populares`
--
ALTER TABLE `populares`
  ADD CONSTRAINT `populares_ibfk_1` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`) ON DELETE CASCADE;

--
-- Filtros para la tabla `zapatos`
--
ALTER TABLE `zapatos`
  ADD CONSTRAINT `fk_zapato_cat` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `zapato_talla`
--
ALTER TABLE `zapato_talla`
  ADD CONSTRAINT `fk_zt_talla` FOREIGN KEY (`id_talla`) REFERENCES `tallas` (`id_talla`),
  ADD CONSTRAINT `fk_zt_zapato` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
