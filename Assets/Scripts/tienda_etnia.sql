-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 17-12-2025 a las 02:55:29
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

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
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_zapato`
--

CREATE TABLE `carrito_zapato` (
  `id` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `id_zapato` int(11) NOT NULL,
  `id_talla` int(11) NOT NULL,
  `id_color` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `id_usuario` int(11) NOT NULL,
  `id_zapato` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_zapato`
--

CREATE TABLE `imagenes_zapato` (
  `id_imagen` int(11) NOT NULL,
  `id_zapato` int(11) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `imagenes_zapato`
--

INSERT INTO `imagenes_zapato` (`id_imagen`, `id_zapato`, `ruta`, `orden`) VALUES
(1, 1, 'Assets/Imagenes/Items/Botines/botin_chelse_cafe.jpg', 1),
(2, 2, 'Assets/Imagenes/Items/Botines/botin_con_hebilla_cafe.jpg', 1),
(3, 2, 'Assets/Imagenes/Items/Botines/botin_con_hebilla_negro.jpg', 2);

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
(2, 'Botín prueba', 510.00, 3);

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
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `carrito_zapato`
--
ALTER TABLE `carrito_zapato`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_carrito` (`id_carrito`),
  ADD KEY `id_zapato` (`id_zapato`),
  ADD KEY `id_talla` (`id_talla`),
  ADD KEY `id_color` (`id_color`);

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
  ADD PRIMARY KEY (`id_usuario`,`id_zapato`),
  ADD KEY `id_zapato` (`id_zapato`);

--
-- Indices de la tabla `imagenes_zapato`
--
ALTER TABLE `imagenes_zapato`
  ADD PRIMARY KEY (`id_imagen`),
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
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carrito_zapato`
--
ALTER TABLE `carrito_zapato`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `imagenes_zapato`
--
ALTER TABLE `imagenes_zapato`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tallas`
--
ALTER TABLE `tallas`
  MODIFY `id_talla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `zapatos`
--
ALTER TABLE `zapatos`
  MODIFY `id_zapato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carrito_zapato`
--
ALTER TABLE `carrito_zapato`
  ADD CONSTRAINT `carrito_zapato_ibfk_1` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_zapato_ibfk_2` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`),
  ADD CONSTRAINT `carrito_zapato_ibfk_3` FOREIGN KEY (`id_talla`) REFERENCES `tallas` (`id_talla`),
  ADD CONSTRAINT `carrito_zapato_ibfk_4` FOREIGN KEY (`id_color`) REFERENCES `colores_zapato` (`id_color`);

--
-- Filtros para la tabla `colores_zapato`
--
ALTER TABLE `colores_zapato`
  ADD CONSTRAINT `colores_zapato_ibfk_1` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`) ON DELETE CASCADE;

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_zapato`
--
ALTER TABLE `imagenes_zapato`
  ADD CONSTRAINT `imagenes_zapato_ibfk_1` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`) ON DELETE CASCADE;

--
-- Filtros para la tabla `zapatos`
--
ALTER TABLE `zapatos`
  ADD CONSTRAINT `zapatos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `zapato_talla`
--
ALTER TABLE `zapato_talla`
  ADD CONSTRAINT `zapato_talla_ibfk_1` FOREIGN KEY (`id_zapato`) REFERENCES `zapatos` (`id_zapato`) ON DELETE CASCADE,
  ADD CONSTRAINT `zapato_talla_ibfk_2` FOREIGN KEY (`id_talla`) REFERENCES `tallas` (`id_talla`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
