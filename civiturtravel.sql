-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-09-2025 a las 20:38:37
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
-- Base de datos: `civiturtravel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `tipo_documento` enum('dui','pasaporte','nit','otros') DEFAULT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `nacionalidad` varchar(50) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `email`, `telefono`, `direccion`, `tipo_documento`, `numero_documento`, `fecha_nacimiento`, `nacionalidad`, `notas`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Carlos Méndez', 'carlos@email.com', '0987654321', '', 'dui', '171234578', NULL, '', '', '2025-08-27 19:58:21', '2025-09-01 21:49:35'),
(2, 'Laura Sánchez', 'laura@email.com', '0991234567', '', 'dui', '134567489', NULL, '', '', '2025-08-27 19:58:21', '2025-09-01 21:49:55'),
(3, 'Roberto Jiménez', 'roberto@email.com', '0976543210', NULL, 'pasaporte', 'AB123456', NULL, NULL, NULL, '2025-08-27 19:58:21', '2025-08-27 19:58:21'),
(4, 'Ana Gómez', 'ana@email.com', '22521675', 'Col. Satelite, San Salvador', 'dui', '170987654', '1980-05-14', 'El Salvador', '', '2025-08-27 19:58:21', '2025-09-01 21:49:23'),
(5, 'Carlos Méndoza', 'cmendoza@mail.com', '78589582', 'Cd. Juarez', 'nit', '06142510601383', '1960-10-25', 'Mexico', '', '2025-09-01 21:51:00', '2025-09-01 21:51:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL,
  `seccion` varchar(50) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'string',
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `seccion`, `clave`, `valor`, `tipo`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'general', 'nombre_sistema', 'Civitur Travel', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:26'),
(2, 'general', 'moneda', 'USD', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:26'),
(3, 'general', 'formato_fecha', 'd/m/Y', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:26'),
(4, 'general', 'timezone', 'America/El_Salvador', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:26'),
(5, 'general', 'idioma', 'es', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:26'),
(6, 'general', 'registros_pagina', '10', 'number', '', '2025-09-02 00:02:23', '2025-09-02 00:42:26'),
(7, 'empresa', 'nombre', 'Civitur Travel', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:52'),
(8, 'empresa', 'nrc', '12345678900012', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:52'),
(9, 'empresa', 'direccion', '79Av Nte y 3ra Calle Poniente, CC Las Alquerías Local 205, Col Escalón, San Salvador ', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:52'),
(10, 'empresa', 'telefono', '+503 2519 2655', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:52'),
(11, 'empresa', 'email', 'info@civiturtravel.com', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:52'),
(12, 'empresa', 'website', 'https://www.civiturtravel.com', 'string', '', '2025-09-02 00:02:23', '2025-09-02 00:42:52'),
(13, 'correo', 'smtp_host', 'mail.civiturtravel.com', 'string', 'Servidor SMTP', '2025-09-02 00:02:23', '2025-09-02 00:02:23'),
(14, 'correo', 'smtp_usuario', 'notificaciones@civiturtravel.com', 'string', 'Usuario SMTP', '2025-09-02 00:02:23', '2025-09-02 00:02:23'),
(15, 'correo', 'smtp_puerto', '587', 'number', 'Puerto SMTP', '2025-09-02 00:02:23', '2025-09-02 00:02:23'),
(16, 'correo', 'smtp_seguridad', 'tls', 'string', 'Tipo de seguridad SMTP', '2025-09-02 00:02:23', '2025-09-02 00:02:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `codigo` varchar(20) NOT NULL,
  `servicio` varchar(50) DEFAULT NULL,
  `detalles` text DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_vigencia` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  `estado` enum('pendiente','confirmada','rechazada','transferida') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `impuestos` decimal(10,2) DEFAULT 0.00,
  `moneda` varchar(3) DEFAULT 'USD'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cotizaciones`
--

INSERT INTO `cotizaciones` (`id`, `cliente_id`, `usuario_id`, `codigo`, `servicio`, `detalles`, `fecha_emision`, `fecha_vigencia`, `total`, `estado`, `notas`, `fecha_creacion`, `fecha_actualizacion`, `subtotal`, `impuestos`, `moneda`) VALUES
(1, 1, 3, 'CT-2023-001', 'Vuelo NYC + Hotel', NULL, '2023-08-15', '2023-08-22', 1225.00, 'confirmada', '\n[2025-09-01 18:41] Estado cambiado a: rechazada. \n[2025-09-01 18:41] Estado cambiado a: pendiente. \n[2025-09-01 18:42] Estado cambiado a: confirmada. ', '2025-08-27 19:58:21', '2025-09-01 16:42:01', 0.00, 0.00, 'USD'),
(2, 2, 3, 'CT-2023-002', 'Paquete Cancún Todo Incluido', NULL, '2023-08-14', '2023-08-21', 1850.00, 'transferida', NULL, '2025-08-27 19:58:21', '2025-08-27 19:58:21', 0.00, 0.00, 'USD'),
(3, 3, 2, 'CT-2023-003', 'Crucero Caribe', NULL, '2023-08-13', '2023-08-20', 2340.00, 'confirmada', NULL, '2025-08-27 19:58:21', '2025-08-27 19:58:21', 0.00, 0.00, 'USD'),
(4, 4, 2, 'CT-2023-004', 'Vuelo Madrid + Hotel', NULL, '2023-08-12', '2023-08-19', 1725.00, 'pendiente', NULL, '2025-08-27 19:58:21', '2025-08-27 19:58:21', 0.00, 0.00, 'USD'),
(5, 4, 1, 'VUE-20250901-190222', 'Vuelo SAL-LAX', NULL, '2025-09-01', '2025-09-08', 300.00, 'confirmada', '\n[2025-09-01 19:02] Estado cambiado a: rechazada. \n[2025-09-02 00:35] Estado cambiado a: pendiente. \n[2025-09-02 00:59] Estado cambiado a: confirmada. ', '2025-09-01 17:02:22', '2025-09-01 22:59:24', 0.00, 0.00, 'USD'),
(6, 2, 1, 'VUE-20250902-015404', 'Vuelo SAL-CDMX', NULL, '2025-09-01', '2025-09-08', 436.00, 'pendiente', '\n[2025-09-02 01:54] Estado cambiado a: rechazada. \n[2025-09-02 14:21] Estado cambiado a: pendiente. ', '2025-09-01 23:54:04', '2025-09-02 12:21:07', 0.00, 0.00, 'USD');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion_servicios`
--

CREATE TABLE `cotizacion_servicios` (
  `id` int(11) NOT NULL,
  `cotizacion_id` int(11) DEFAULT NULL,
  `tipo_servicio` enum('vuelo','hotel','paquete','transporte','tour','otros') NOT NULL,
  `detalles` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad` int(11) DEFAULT 1,
  `subtotal` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cotizacion_servicios`
--

INSERT INTO `cotizacion_servicios` (`id`, `cotizacion_id`, `tipo_servicio`, `detalles`, `precio`, `fecha_creacion`, `cantidad`, `subtotal`) VALUES
(1, 1, 'vuelo', 'Vuelo NYC ida y vuelta', 625.00, '2025-08-27 19:58:21', 1, 625.00),
(2, 1, 'hotel', 'Hotel 3 noches en NYC', 600.00, '2025-08-27 19:58:21', 1, 600.00),
(3, 2, 'paquete', 'Paquete todo incluido 7 días', 1850.00, '2025-08-27 19:58:21', 1, 1850.00),
(4, 3, 'tour', 'Crucero Caribe 5 días', 2340.00, '2025-08-27 19:58:21', 1, 2340.00),
(5, 4, 'vuelo', 'Vuelo Madrid ida y vuelta', 900.00, '2025-08-27 19:58:21', 1, 900.00),
(6, 4, 'hotel', 'Hotel 5 noches en Madrid', 825.00, '2025-08-27 19:58:21', 1, 825.00),
(7, 5, 'vuelo', 'Vuelo Avianca - SAL a LAX', 300.00, '2025-09-01 17:02:22', 1, 300.00),
(8, 6, 'vuelo', 'Vuelo LATAM - SAL a CDMX', 436.00, '2025-09-01 23:54:04', 1, 436.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidad_habitaciones`
--

CREATE TABLE `disponibilidad_habitaciones` (
  `id` int(11) NOT NULL,
  `habitacion_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `precio_especial` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `disponibilidad_habitaciones`
--

INSERT INTO `disponibilidad_habitaciones` (`id`, `habitacion_id`, `fecha`, `disponible`, `precio_especial`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-09-03', 1, NULL, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(2, 1, '2025-09-04', 1, 85.99, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(3, 1, '2025-09-05', 0, NULL, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(4, 2, '2025-09-03', 1, NULL, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(5, 2, '2025-09-04', 1, NULL, '2025-09-02 07:31:27', '2025-09-02 07:31:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos_hoteles`
--

CREATE TABLE `fotos_hoteles` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `habitacion_id` int(11) DEFAULT NULL,
  `url_foto` varchar(500) NOT NULL,
  `tipo` enum('exterior','lobby','habitacion','restaurante','piscina','otro') DEFAULT 'otro',
  `descripcion` text DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `tipo_habitacion` varchar(100) NOT NULL,
  `numero_habitacion` varchar(20) DEFAULT NULL,
  `capacidad` int(11) DEFAULT 2,
  `camas` int(11) DEFAULT 1,
  `precio_noche` decimal(10,2) NOT NULL,
  `moneda` varchar(3) DEFAULT 'USD',
  `descripcion` text DEFAULT NULL,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `hotel_id`, `tipo_habitacion`, `numero_habitacion`, `capacidad`, `camas`, `precio_noche`, `moneda`, `descripcion`, `amenities`, `disponible`, `created_at`, `updated_at`) VALUES
(1, 1, 'Habitación Superior', '201', 2, 1, 120.00, 'EUR', 'Habitación espaciosa con vista a la Gran Vía', '[\"wifi\", \"tv\", \"ac\", \"minibar\", \"baño_privado\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(2, 1, 'Suite Deluxe', '501', 3, 2, 220.00, 'EUR', 'Suite con sala independiente y terraza', '[\"wifi\", \"tv\", \"ac\", \"minibar\", \"jacuzzi\", \"terraza\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(3, 2, 'Suite Luxury', 'Ven-102', 4, 2, 350.00, 'USD', 'Suite temática veneciana con amenities de lujo', '[\"wifi\", \"tv\", \"ac\", \"minibar\", \"jacuzzi\", \"vista_strip\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(4, 3, 'Habitación City View', 'MBS-205', 2, 1, 450.00, 'SGD', 'Habitación con vista al skyline de Singapur', '[\"wifi\", \"tv\", \"ac\", \"minibar\", \"vista_ciudad\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(5, 4, 'Suite Valle Sagrado', 'Tambo-301', 2, 1, 280.00, 'USD', 'Suite con vista al Valle Sagrado de los Incas', '[\"wifi\", \"tv\", \"ac\", \"chimenea\", \"vista_valle\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(6, 5, 'Villa Overwater', 'OWV-12', 2, 1, 850.00, 'USD', 'Villa sobre el agua con acceso directo al mar', '[\"wifi\", \"tv\", \"ac\", \"terraza_privada\", \"escalera_mar\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(7, 6, 'Fairmont Room', 'BNF-107', 2, 1, 320.00, 'CAD', 'Habitación con vista a las montañas canadienses', '[\"wifi\", \"tv\", \"ac\", \"chimenea\", \"vista_montanas\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(8, 7, 'Deluxe Room', 'ANT-209', 2, 1, 180.00, 'THB', 'Habitación con decoración tailandesa tradicional', '[\"wifi\", \"tv\", \"ac\", \"minibar\", \"balcon_rio\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(9, 8, 'Opera View Room', 'PHS-301', 2, 1, 520.00, 'AUD', 'Habitación con vista directa a la Ópera de Sídney', '[\"wifi\", \"tv\", \"ac\", \"minibar\", \"vista_opera\"]', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hoteles`
--

CREATE TABLE `hoteles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cadena_hotelera` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'Ecuador',
  `moneda` varchar(3) DEFAULT 'USD',
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `categoria` int(11) DEFAULT 3 CHECK (`categoria` between 1 and 5),
  `descripcion` text DEFAULT NULL,
  `check_in` time DEFAULT '14:00:00',
  `check_out` time DEFAULT '12:00:00',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `hoteles`
--

INSERT INTO `hoteles` (`id`, `nombre`, `cadena_hotelera`, `direccion`, `ciudad`, `pais`, `moneda`, `telefono`, `email`, `sitio_web`, `categoria`, `descripcion`, `check_in`, `check_out`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Hotel Riu Plaza España', 'Riu Hotels', 'C/ Gran Vía, 84', 'Madrid', 'España', 'USD', '+34 91 523 52 00', 'reservas@riuplaza.com', NULL, 4, 'Hotel urbano en el centro de Madrid cerca de puntos de interés', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(2, 'The Venetian Resort', 'Venetian', '3355 Las Vegas Blvd South', 'Las Vegas', 'Estados Unidos', 'USD', '+1 702-414-1000', 'reservations@venetian.com', NULL, 5, 'Resort temático italiano con canal interior y suites lujosas', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(3, 'Marina Bay Sands', 'Sands', '10 Bayfront Avenue', 'Singapur', 'Singapur', 'USD', '+65 6688 8868', 'inquiry@marinabaysands.com', NULL, 5, 'Iconico hotel con infinity pool y vista panorámica de Singapur', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(4, 'Tambo del Inka Resort', 'Libertador', 'Av. Ferrocarril 101', 'Urubamba', 'Perú', 'USD', '+51 84 581777', 'tambodelinka@libertador.com.pe', NULL, 5, 'Resort de lujo en Valle Sagrado, acceso a Machu Picchu', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(5, 'Conrad Maldives Rangali Island', 'Hilton', 'Rangali Island', 'Alifu Dhaalu', 'Maldivas', 'USD', '+960 668-0629', 'conradmaldives@hilton.com', NULL, 5, 'Hotel con villas sobre el agua en paraíso tropical', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(6, 'Fairmont Banff Springs', 'Fairmont', '405 Spray Avenue', 'Banff', 'Canadá', 'USD', '+1 403-762-2211', 'banffsprings@fairmont.com', NULL, 5, 'Castillo hotelero en las Montañas Rocosas canadienses', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(7, 'Anantara Riverside Bangkok', 'Anantara', '257/1-3 Charoennakorn Road', 'Bangkok', 'Tailandia', 'USD', '+66 2 476 0022', 'riversidebangkok@anantara.com', NULL, 5, 'Resort junto al río Chao Phraya con spa tradicional tailandés', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(8, 'Park Hyatt Sydney', 'Hyatt', '7 Hickson Road', 'Sídney', 'Australia', 'USD', '+61 2 9256 1234', 'sydney.park@hyatt.com', NULL, 5, 'Hotel frente a la Ópera de Sídney con vistas espectaculares', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(9, 'Hotel Real Intercontinental San Salvador', 'Intercontinental', 'Blvd. de los Héroes y Ave. Sisimiles', 'San Salvador', 'El Salvador', 'USD', '+503 2211-3333', 'reservas@intercontinentalsansalvador.com', NULL, 5, 'Hotel de lujo en San Salvador con amenities de primera clase', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27'),
(10, 'Sheraton Presidente San Salvador', 'Sheraton', 'Ave. La Revolución, Colonia San Benito', 'San Salvador', 'El Salvador', 'USD', '+503 2243-4000', 'reservas@sheratonsansalvador.com', NULL, 5, 'Hotel ejecutivo en zona comercial de San Salvador', '14:00:00', '12:00:00', 1, '2025-09-02 07:31:27', '2025-09-02 07:31:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('superadmin','admin','vendedor') DEFAULT 'vendedor',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Administrador Principal', 'admin@civiturtravel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 1, '2025-08-27 19:58:21', '2025-08-27 19:58:21'),
(2, 'Juan Pérez', 'juan@civiturtravel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2025-08-27 19:58:21', '2025-08-27 19:58:21'),
(3, 'Maria Rodriguez', 'maria@civiturtravel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor', 1, '2025-08-27 19:58:21', '2025-08-27 19:58:21'),
(4, 'Malena Portillo', 'mportillo@mail.com', '$2y$10$vNXGJxDQCPNubBL6uU/wDeEv0KAW81ca3zmCXx61Mp.3HcnAX03Uy', 'vendedor', 1, '2025-09-01 22:00:14', '2025-09-01 22:00:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vuelos`
--

CREATE TABLE `vuelos` (
  `id` int(11) NOT NULL,
  `cotizacion_id` int(11) DEFAULT NULL,
  `aerolinea` varchar(100) DEFAULT NULL,
  `tipo_vuelo` enum('sencillo','redondo','multidestino') DEFAULT NULL,
  `origen` varchar(100) DEFAULT NULL,
  `destino` varchar(100) DEFAULT NULL,
  `fecha_salida` date DEFAULT NULL,
  `fecha_regreso` date DEFAULT NULL,
  `clase` enum('economica','premium','business','primera') DEFAULT NULL,
  `pasajeros_adultos` int(11) DEFAULT 1,
  `pasajeros_ninos` int(11) DEFAULT 0,
  `pasajeros_bebes` int(11) DEFAULT 0,
  `precio` decimal(10,2) DEFAULT NULL,
  `numero_vuelo` varchar(50) DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `hora_llegada` time DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `vuelos`
--

INSERT INTO `vuelos` (`id`, `cotizacion_id`, `aerolinea`, `tipo_vuelo`, `origen`, `destino`, `fecha_salida`, `fecha_regreso`, `clase`, `pasajeros_adultos`, `pasajeros_ninos`, `pasajeros_bebes`, `precio`, `numero_vuelo`, `hora_salida`, `hora_llegada`, `notas`, `fecha_creacion`) VALUES
(1, 1, 'American Airlines', 'redondo', 'Quito', 'Nueva York', '2023-09-15', '2023-09-22', 'economica', 2, 0, 0, 625.00, NULL, NULL, NULL, NULL, '2025-08-27 19:58:21'),
(2, 4, 'Iberia', 'redondo', 'Quito', 'Madrid', '2023-10-10', '2023-10-20', 'economica', 1, 0, 0, 900.00, NULL, NULL, NULL, NULL, '2025-08-27 19:58:21'),
(3, 5, 'Avianca', 'redondo', 'SAL', 'LAX', '2025-09-18', '2025-09-22', 'economica', 1, 0, 0, 300.00, NULL, NULL, NULL, '', '2025-09-01 17:02:22'),
(4, 6, 'LATAM', 'sencillo', 'SAL', 'CDMX', '2025-10-07', NULL, 'economica', 1, 0, 0, 436.00, NULL, NULL, NULL, '', '2025-09-01 23:54:04'),
(5, NULL, 'Avianca', 'redondo', 'BOG', 'WIA', '2024-01-15', '2024-01-20', 'economica', 1, 0, 0, 250.00, 'AV101', '08:00:00', '12:00:00', NULL, '2025-09-08 18:35:16'),
(6, NULL, 'United', 'sencillo', 'WIA', '500', '2024-01-16', NULL, 'economica', 1, 0, 0, 180.00, 'UA456', '14:00:00', '16:00:00', NULL, '2025-09-08 18:35:16'),
(7, NULL, 'Copa', 'redondo', 'PTY', 'BOG', '2024-01-17', '2024-01-22', 'business', 1, 0, 0, 350.00, 'CU789', '10:00:00', '12:00:00', NULL, '2025-09-08 18:35:16');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_config` (`seccion`,`clave`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `cotizacion_servicios`
--
ALTER TABLE `cotizacion_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`);

--
-- Indices de la tabla `disponibilidad_habitaciones`
--
ALTER TABLE `disponibilidad_habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_disponibilidad` (`habitacion_id`,`fecha`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `fotos_hoteles`
--
ALTER TABLE `fotos_hoteles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `habitacion_id` (`habitacion_id`),
  ADD KEY `idx_hotel_id` (`hotel_id`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hotel_id` (`hotel_id`),
  ADD KEY `idx_tipo` (`tipo_habitacion`),
  ADD KEY `idx_disponible` (`disponible`);

--
-- Indices de la tabla `hoteles`
--
ALTER TABLE `hoteles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ciudad` (`ciudad`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vuelos`
--
ALTER TABLE `vuelos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cotizacion_servicios`
--
ALTER TABLE `cotizacion_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `disponibilidad_habitaciones`
--
ALTER TABLE `disponibilidad_habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `fotos_hoteles`
--
ALTER TABLE `fotos_hoteles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `hoteles`
--
ALTER TABLE `hoteles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vuelos`
--
ALTER TABLE `vuelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cotizaciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cotizacion_servicios`
--
ALTER TABLE `cotizacion_servicios`
  ADD CONSTRAINT `cotizacion_servicios_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cotizacion_servicios_ibfk_2` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `disponibilidad_habitaciones`
--
ALTER TABLE `disponibilidad_habitaciones`
  ADD CONSTRAINT `disponibilidad_habitaciones_ibfk_1` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fotos_hoteles`
--
ALTER TABLE `fotos_hoteles`
  ADD CONSTRAINT `fotos_hoteles_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hoteles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fotos_hoteles_ibfk_2` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD CONSTRAINT `habitaciones_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hoteles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vuelos`
--
ALTER TABLE `vuelos`
  ADD CONSTRAINT `vuelos_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
