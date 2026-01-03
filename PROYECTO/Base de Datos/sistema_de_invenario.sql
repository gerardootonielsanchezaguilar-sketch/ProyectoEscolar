-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3309
-- Tiempo de generación: 03-01-2026 a las 19:41:40
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
-- Base de datos: `sistema_de_invenario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `edad` int(11) NOT NULL,
  `dui` varchar(10) NOT NULL,
  `telefono` varchar(10) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre_completo`, `edad`, `dui`, `telefono`, `fecha_registro`) VALUES
(1, 'Sergio  Efrain Ramos Rodriguez', 18, '92442923-4', '6594-7428', '2025-10-27 19:49:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `fecha_compra` date NOT NULL,
  `nombre_proveedor` varchar(255) NOT NULL,
  `telefono_proveedor` varchar(20) DEFAULT NULL,
  `email_proveedor` varchar(100) DEFAULT NULL,
  `subtotal_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `impuesto_general` decimal(10,2) DEFAULT 0.00,
  `descuento_general` decimal(10,2) DEFAULT 0.00,
  `total_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` enum('Efectivo','Transferencia','Cheque','Crédito') NOT NULL,
  `estado_compra` enum('Pendiente','Recibida','Cancelada') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `numero_factura`, `fecha_compra`, `nombre_proveedor`, `telefono_proveedor`, `email_proveedor`, `subtotal_general`, `impuesto_general`, `descuento_general`, `total_general`, `metodo_pago`, `estado_compra`, `observaciones`, `usuario_registro`, `fecha_registro`, `fecha_actualizacion`) VALUES
(2, 'FACT-2025-001', '2025-10-25', 'MARVINSIN', '7387-3789', 'mrvin@gmail.com', 90.00, 11.70, 0.00, 101.70, 'Efectivo', 'Cancelada', '', 'admin', '2025-10-25 21:22:02', '2025-10-27 19:54:11'),
(3, 'FACT-2025-002', '2025-10-27', 'AGROAMIGO', '4749-4363', 'agromigo@gmail.com', 1400.00, 182.00, 0.00, 1582.00, 'Efectivo', 'Recibida', 'Primera compra del mes,', 'admin', '2025-10-27 19:56:38', '2025-10-27 20:03:35'),
(8, 'fact', '2025-10-30', 'ryrr', '7387-3789', 'mrvin@gmail.com', 34.00, 4.42, 0.00, 38.42, 'Efectivo', 'Pendiente', '', 'admin', '2025-10-30 02:59:37', '2025-10-30 02:59:37');

--
-- Disparadores `compras`
--
DELIMITER $$
CREATE TRIGGER `actualizar_stock_estado_compra_multiple` AFTER UPDATE ON `compras` FOR EACH ROW BEGIN
    -- Si el estado cambió de NO recibida a Recibida
    IF OLD.estado_compra != 'Recibida' AND NEW.estado_compra = 'Recibida' THEN
        -- Actualizar stock de TODOS los productos de esta compra
        UPDATE productos p
        INNER JOIN detalle_compras dc ON p.id = dc.id_producto
        SET p.stock = p.stock + dc.cantidad
        WHERE dc.id_compra = NEW.id;
    END IF;
    
    -- Si se canceló una compra que ya estaba recibida
    IF OLD.estado_compra = 'Recibida' AND NEW.estado_compra = 'Cancelada' THEN
        -- Restar el stock de TODOS los productos
        UPDATE productos p
        INNER JOIN detalle_compras dc ON p.id = dc.id_producto
        SET p.stock = p.stock - dc.cantidad
        WHERE dc.id_compra = NEW.id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compras`
--

CREATE TABLE `detalle_compras` (
  `id` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `categoria_producto` varchar(100) DEFAULT NULL,
  `linea_producto` varchar(50) DEFAULT NULL,
  `codigo_producto` varchar(50) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento_item` decimal(10,2) DEFAULT 0.00,
  `total_item` decimal(10,2) NOT NULL,
  `observaciones_item` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_compras`
--

INSERT INTO `detalle_compras` (`id`, `id_compra`, `id_producto`, `nombre_producto`, `categoria_producto`, `linea_producto`, `codigo_producto`, `cantidad`, `precio_unitario`, `subtotal`, `descuento_item`, `total_item`, `observaciones_item`) VALUES
(2, 2, 5, 'Aves de Traspatio', 'Alimento concentrado', 'Aves de Postura', '0-7485-AP', 4, 22.50, 90.00, 0.00, 90.00, NULL),
(3, 3, 3, 'Desarrollo Ponedora', 'Alimento concentrado', 'Aves de Postura', '0-1985', 50, 28.00, 1400.00, 0.00, 1400.00, NULL),
(4, 8, 35, 'Desarrollo ternera', 'Alimento concentrado', 'Linea de Ganado', '0-8128', 1, 34.00, 34.00, 0.00, 34.00, NULL);

--
-- Disparadores `detalle_compras`
--
DELIMITER $$
CREATE TRIGGER `actualizar_linea_detalle` BEFORE INSERT ON `detalle_compras` FOR EACH ROW BEGIN
    DECLARE linea_prod VARCHAR(50);
    
    -- Obtener la línea del producto
    SELECT LINEA INTO linea_prod
    FROM productos
    WHERE ID = NEW.id_producto;
    
    -- Asignar la línea al detalle
    IF linea_prod IS NOT NULL THEN
        SET NEW.linea_producto = linea_prod;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizar_stock_detalle_compra` AFTER INSERT ON `detalle_compras` FOR EACH ROW BEGIN
    DECLARE estado_actual VARCHAR(20);
    
    -- Obtener el estado de la compra
    SELECT estado_compra INTO estado_actual 
    FROM compras 
    WHERE id = NEW.id_compra;
    
    -- Solo actualizar stock si la compra está recibida
    IF estado_actual = 'Recibida' THEN
        UPDATE productos 
        SET stock = stock + NEW.cantidad
        WHERE id = NEW.id_producto;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizar_total_item` BEFORE UPDATE ON `detalle_compras` FOR EACH ROW BEGIN
    -- Recalcular si cambió cantidad o precio
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario;
    SET NEW.total_item = NEW.subtotal - NEW.descuento_item;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calcular_total_item` BEFORE INSERT ON `detalle_compras` FOR EACH ROW BEGIN
    -- Calcular subtotal del item
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario;
    
    -- Calcular total del item (subtotal - descuento)
    SET NEW.total_item = NEW.subtotal - NEW.descuento_item;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `codigo_producto` varchar(50) DEFAULT NULL,
  `categoria_producto` varchar(100) DEFAULT NULL,
  `linea_producto` varchar(50) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento_item` decimal(10,2) DEFAULT 0.00,
  `total_item` decimal(10,2) NOT NULL,
  `observaciones_item` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id`, `id_venta`, `id_producto`, `nombre_producto`, `codigo_producto`, `categoria_producto`, `linea_producto`, `cantidad`, `precio_unitario`, `subtotal`, `descuento_item`, `total_item`, `observaciones_item`) VALUES
(1, 1, 5, 'Aves de Traspatio', '0-7485', 'Alimento concentrado', 'Aves de Postura', 3, 22.50, 67.50, 0.00, 67.50, NULL),
(2, 2, 3, 'Desarrollo Ponedora', '0-1985', 'Alimento concentrado', 'Aves de Postura', 4, 28.00, 112.00, 0.00, 112.00, NULL),
(3, 3, 3, 'Desarrollo Ponedora', '0-1985', 'Alimento concentrado', 'Aves de Postura', 4, 28.00, 112.00, 0.00, 112.00, NULL),
(4, 4, 3, 'Desarrollo Ponedora', '0-1985', 'Alimento concentrado', 'Aves de Postura', 1, 28.00, 28.00, 0.00, 28.00, NULL),
(5, 5, 3, 'Desarrollo Ponedora', '0-1985', 'Alimento concentrado', 'Aves de Postura', 1, 28.00, 28.00, 0.00, 28.00, NULL),
(6, 6, 3, 'Desarrollo Ponedora', '0-1985', 'Alimento concentrado', 'Aves de Postura', 6, 28.00, 168.00, 0.00, 168.00, NULL);

--
-- Disparadores `detalle_ventas`
--
DELIMITER $$
CREATE TRIGGER `actualizar_linea_detalle_venta` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
    DECLARE linea_prod VARCHAR(50);
    SELECT LINEA INTO linea_prod FROM productos WHERE ID = NEW.id_producto;
    IF linea_prod IS NOT NULL THEN
        SET NEW.linea_producto = linea_prod;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizar_total_item_venta` BEFORE UPDATE ON `detalle_ventas` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario;
    SET NEW.total_item = NEW.subtotal - NEW.descuento_item;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calcular_total_item_venta` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario;
    SET NEW.total_item = NEW.subtotal - NEW.descuento_item;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `ID` int(50) NOT NULL,
  `NOMBRE_APELLIDO` varchar(50) NOT NULL,
  `DUI` varchar(50) NOT NULL,
  `EDAD` int(50) NOT NULL,
  `TELEFONO_CORREO` varchar(50) NOT NULL,
  `FECHA_DE_INGRESO` date NOT NULL,
  `ROL` varchar(50) NOT NULL DEFAULT 'ADMINISTRADOR',
  `CONTRASEÑA` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`ID`, `NOMBRE_APELLIDO`, `DUI`, `EDAD`, `TELEFONO_CORREO`, `FECHA_DE_INGRESO`, `ROL`, `CONTRASEÑA`) VALUES
(1, 'Gerardo Sanchez', '19930610-9', 18, '7511-5319', '2007-09-30', 'Administrador de inventario', 'electriclove'),
(3, 'Daniel Isaac', '93276438-9', 19, '8644-4482', '2025-10-21', 'Administrador', 'chinchin'),
(4, 'pablo', '94442744-9', 56, 'primo@gmail.com', '2025-10-30', '', 'hola'),
(5, 'Rudy López', '1234567-8', 18, '79591834', '2025-03-08', '', 'alex123');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `ID` int(50) NOT NULL,
  `NOMBRE_PRODUCTO` varchar(50) NOT NULL,
  `CODIGO` varchar(50) NOT NULL,
  `CATEGORIA` varchar(50) NOT NULL,
  `LINEA` enum('Linea de Ganado','Aves de Postura','Línea de Cerdos','Línea de Equinos','Línea de Tilapia') DEFAULT NULL,
  `PRECIO` decimal(10,2) NOT NULL,
  `STOCK` int(50) DEFAULT 0,
  `FECHA_INGRESO` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `DESCRIPCION` varchar(500) NOT NULL,
  `ESTADO` enum('Agotado','Producto Bajo','Disponible') NOT NULL DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`ID`, `NOMBRE_PRODUCTO`, `CODIGO`, `CATEGORIA`, `LINEA`, `PRECIO`, `STOCK`, `FECHA_INGRESO`, `DESCRIPCION`, `ESTADO`) VALUES
(2, 'Iniciador Ponedora', '0-2585', 'Alimento concentrado', 'Aves de Postura', 25.50, 0, '2025-10-26 01:20:55', 'Alimento para aves ponedoras en etapa inicial', 'Agotado'),
(3, 'Desarrollo Ponedora', '0-1985', 'Alimento concentrado', 'Aves de Postura', 28.00, 49, '2025-10-30 16:09:09', 'Alimento para desarrollo de ponedoras', 'Disponible'),
(4, 'Preinicio I', '0-4565', 'Alimento concentrado', 'Aves de Postura', 30.00, 0, '2025-10-26 01:21:19', 'Alimento preinicio para aves', 'Agotado'),
(5, 'Aves de Traspatio', '0-7485', 'Alimento concentrado', 'Aves de Postura', 22.50, 0, '2025-10-27 19:54:11', 'Alimento para aves de traspatio', 'Agotado'),
(6, 'Pollo Inicio', '0-5483', 'Alimento concentrado', 'Aves de Postura', 26.00, 0, '2025-10-26 01:21:26', 'Alimento para pollos en etapa inicial', 'Agotado'),
(7, 'Pollo Final', '0-0453', 'Alimento concentrado', 'Aves de Postura', 27.50, 0, '2025-10-26 01:21:31', 'Alimento para pollos en etapa final', 'Agotado'),
(13, 'Iniciador Cerdos (Cerdos destetados)', '0-9285', 'Alimento concentrado', 'Línea de Cerdos', 25.00, 0, '2025-10-26 01:40:07', 'Alimento para cerdos recién destetados, rico en proteínas y energía.', 'Agotado'),
(14, 'Crecimiento Cerdo', '0-9185', 'Alimento concentrado', 'Línea de Cerdos', 27.50, 0, '2025-10-26 01:40:07', 'Alimento para cerdos en etapa de crecimiento, favorece desarrollo muscular.', 'Agotado'),
(15, 'Finalizador Cerdo', '0-9465', 'Alimento concentrado', 'Línea de Cerdos', 28.00, 0, '2025-10-26 01:40:07', 'Alimento para cerdos en fase final antes de la venta o sacrificio.', 'Agotado'),
(16, 'Gestante Reproductora (Cerdas preñadas)', '0-9485', 'Alimento concentrado', 'Línea de Cerdos', 26.00, 0, '2025-10-26 01:40:07', 'Alimento formulado para cerdas gestantes, mejora salud y parto.', 'Agotado'),
(17, 'Lactante Reproductora', '0-9528', 'Alimento concentrado', 'Línea de Cerdos', 27.00, 0, '2025-10-26 01:40:07', 'Alimento para cerdas lactantes, favorece la producción de leche.', 'Agotado'),
(18, 'Tilapia Agroamigo 45% (Pre Inicio)', '0-6285', 'Alimento concentrado', 'Línea de Tilapia', 32.00, 0, '2025-10-26 01:40:07', 'Alimento con 45% de proteína, ideal para alevines de tilapia en etapa inicial.', 'Agotado'),
(19, 'Tilapia Agroamigo 38% (Iniciador)', '0-6185', 'Alimento concentrado', 'Línea de Tilapia', 30.50, 0, '2025-10-26 01:40:07', 'Alimento para tilapia joven, favorece crecimiento temprano.', 'Agotado'),
(20, 'Tilapia Agroamigo 32% (Desarrollo)', '0-6465', 'Alimento concentrado', 'Línea de Tilapia', 29.00, 0, '2025-10-26 01:40:07', 'Alimento balanceado para tilapia en fase de desarrollo.', 'Agotado'),
(21, 'Tilapia Agroamigo 28% (Finalizador)', '0-6485', 'Alimento concentrado', 'Línea de Tilapia', 27.50, 0, '2025-10-26 01:40:07', 'Alimento formulado para tilapia adulta en etapa final de engorde.', 'Agotado'),
(22, 'Concentrado Equino', '0-8424', 'Alimento Concentrado', 'Línea de Equinos', 50.00, 0, '2025-10-26 01:34:00', 'Alimento para caballos, equilibro ideal de nutrientes', 'Agotado'),
(30, 'Vaca lechera plus 24%', '0-8123', 'Alimento concentrado', 'Linea de Ganado', 30.00, 0, '2025-10-30 02:56:34', 'Alimento concentrado formulado para vacas lecheras de alta exigencia energética, con 24% de proteína, vitaminas y minerales esenciales que favorecen una óptima producción y salud general. Ideal para hatos de alto rendimiento.', 'Agotado'),
(31, 'Vaca lechera 22% o alta producción', '0-8124', 'Alimento concentrado', 'Linea de Ganado', 40.00, 0, '2025-10-30 02:58:38', 'Concentrado diseñado para vacas lecheras en etapas de alta producción, con 22% de proteína y una mezcla equilibrada de nutrientes que promueven la eficiencia digestiva y el mantenimiento del peso corporal.', 'Agotado'),
(32, 'Vaca lechera 18% o vacas de mediana producción', '0-8125', 'Alimento concentrado', 'Linea de Ganado', 24.00, 0, '2025-10-30 02:58:38', 'Alimento balanceado para vacas lecheras de producción media, con 18% de proteína, elaborado con granos seleccionados y minerales que contribuyen a la salud del rumen y una producción constante de leche.', 'Agotado'),
(33, 'Vaca lechera 15% o vacas horas (preñadas)', '0-8126', 'Alimento concentrado', 'Linea de Ganado', 62.00, 0, '2025-10-30 02:58:38', 'Concentrado para vacas gestantes o en etapa de descanso productivo, con 15% de proteína, diseñado para mantener la condición corporal y asegurar un buen desarrollo fetal.', 'Agotado'),
(34, 'Ganado de engorde', '0-8127', 'Alimento concentrado', 'Linea de Ganado', 62.00, 0, '2025-10-30 02:58:38', 'Alimento concentrado especializado para ganado de engorde, formulado con alto contenido energético y proteico que favorece el aumento rápido y saludable de peso, optimizando los resultados de conversión alimenticia.', 'Agotado'),
(35, 'Desarrollo ternera', '0-8128', 'Alimento concentrado', 'Linea de Ganado', 34.00, 0, '2025-10-30 02:58:38', 'Concentrado diseñado para terneras en desarrollo, con balance adecuado de proteínas, energía, vitaminas y minerales que estimulan el crecimiento, fortalecen el sistema inmunológico y preparan al animal para su futura producción.', 'Agotado');

--
-- Disparadores `productos`
--
DELIMITER $$
CREATE TRIGGER `ESTADO` BEFORE INSERT ON `productos` FOR EACH ROW BEGIN
    IF NEW.stock = 0 THEN
        SET NEW.estado = 'Agotado';
    ELSEIF NEW.stock > 0 AND NEW.stock <= 40 THEN
        SET NEW.estado = 'Producto Bajo';
    ELSE
        SET NEW.estado = 'Disponible';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actu_de_estado` BEFORE UPDATE ON `productos` FOR EACH ROW BEGIN
    IF NEW.stock != OLD.stock THEN
        IF NEW.stock = 0 THEN
            SET NEW.estado = 'Agotado';
        ELSEIF NEW.stock > 0 AND NEW.stock <= 40 THEN
            SET NEW.estado = 'Producto Bajo';
        ELSE
            SET NEW.estado = 'Disponible';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizar_estado_producto_insert` BEFORE INSERT ON `productos` FOR EACH ROW BEGIN
    IF NEW.stock = 0 THEN
        SET NEW.estado = 'Agotado';
    ELSEIF NEW.stock > 0 AND NEW.stock <= 40 THEN
        SET NEW.estado = 'Producto Bajo';
    ELSE
        SET NEW.estado = 'Disponible';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizar_estado_producto_update` BEFORE UPDATE ON `productos` FOR EACH ROW BEGIN
    IF NEW.stock != OLD.stock THEN
        IF NEW.stock = 0 THEN
            SET NEW.estado = 'Agotado';
        ELSEIF NEW.stock > 0 AND NEW.stock <= 40 THEN
            SET NEW.estado = 'Producto Bajo';
        ELSE
            SET NEW.estado = 'Disponible';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `asignar_categoria_por_linea` BEFORE INSERT ON `productos` FOR EACH ROW BEGIN
    -- Si la línea es Especial y no tiene categoría, asignarla
    IF NEW.LINEA = 'Especial' AND (NEW.CATEGORIA IS NULL OR NEW.CATEGORIA = '') THEN
        SET NEW.CATEGORIA = 'Especial';
    END IF;
    
    -- Si la línea es Aves de Postura y no tiene categoría
    IF NEW.LINEA = 'Aves de Postura' AND (NEW.CATEGORIA IS NULL OR NEW.CATEGORIA = '') THEN
        SET NEW.CATEGORIA = 'Alimento concentrado';
    END IF;
    
    -- Si la línea es Ganado Lechero y no tiene categoría
    IF NEW.LINEA = 'Ganado Lechero' AND (NEW.CATEGORIA IS NULL OR NEW.CATEGORIA = '') THEN
        SET NEW.CATEGORIA = 'Alimento concentrado';
    END IF;
    
    -- Generar código automático si está vacío
    IF NEW.CODIGO IS NULL OR NEW.CODIGO = '' THEN
        SET NEW.CODIGO = CONCAT('AUTO-', LPAD(NEW.ID, 5, '0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `nombre_cliente` varchar(255) NOT NULL,
  `dui_cliente` varchar(10) NOT NULL,
  `telefono_cliente` varchar(10) DEFAULT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `fecha_venta` date NOT NULL,
  `subtotal_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `impuesto_general` decimal(10,2) DEFAULT 0.00,
  `descuento_general` decimal(10,2) DEFAULT 0.00,
  `total_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` enum('Efectivo','Transferencia','Tarjeta','Crédito') NOT NULL,
  `estado_venta` enum('Pendiente','Completada','Cancelada') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `id_cliente`, `nombre_cliente`, `dui_cliente`, `telefono_cliente`, `numero_factura`, `fecha_venta`, `subtotal_general`, `impuesto_general`, `descuento_general`, `total_general`, `metodo_pago`, `estado_venta`, `observaciones`, `usuario_registro`, `fecha_registro`, `fecha_actualizacion`) VALUES
(1, 1, 'Sergio  Efrain Ramos Rodriguez', '92442923-4', '6594-7428', 'VTA-2025-11', '2025-10-27', 67.50, 8.78, 0.00, 76.28, 'Crédito', 'Pendiente', '', 'admin', '2025-10-27 19:49:32', '2025-10-27 19:49:32'),
(2, 1, 'sergio', '92442923-4', '6594-7428', 'VTA-2025-12', '2025-10-30', 112.00, 14.56, 0.00, 126.56, 'Efectivo', 'Pendiente', '', 'admin', '2025-10-30 13:27:53', '2025-10-30 13:27:53'),
(3, 1, 'sergio', '92442923-4', '6594-7428', 'VTA-2025-13', '2025-10-30', 112.00, 14.56, 0.00, 126.56, 'Efectivo', 'Pendiente', '', 'admin', '2025-10-30 13:30:30', '2025-10-30 13:30:30'),
(4, 1, 'ewe', '92442923-4', '6479-9647', 'VTA-2025-|4', '2025-10-30', 28.00, 3.64, 0.00, 31.64, 'Transferencia', 'Pendiente', '', 'admin', '2025-10-30 13:31:16', '2025-10-30 13:31:16'),
(5, 1, 'ewe', '92442923-4', '6479-9647', 'VTA-2025-7', '2025-10-30', 28.00, 3.64, 0.00, 31.64, 'Transferencia', 'Completada', '', 'admin', '2025-10-30 13:32:17', '2025-10-30 16:09:09'),
(6, 1, 'Sergio  Efrain Ramos Rodriguez', '92442923-4', '6594-7428', 'VTA-2025-2', '2025-10-30', 28.00, 3.64, 0.00, 168.00, 'Efectivo', 'Cancelada', '', 'admin', '2025-10-30 13:34:09', '2025-10-30 14:56:55');

--
-- Disparadores `ventas`
--
DELIMITER $$
CREATE TRIGGER `actualizar_stock_venta_completada` AFTER UPDATE ON `ventas` FOR EACH ROW BEGIN
    -- Si pasa a completada: restar stock
    IF OLD.estado_venta != 'Completada' AND NEW.estado_venta = 'Completada' THEN
        UPDATE productos p
        INNER JOIN detalle_ventas dv ON p.id = dv.id_producto
        SET p.stock = p.stock - dv.cantidad
        WHERE dv.id_venta = NEW.id;
    END IF;

    -- Si pasa de completada a cancelada: devolver stock
    IF OLD.estado_venta = 'Completada' AND NEW.estado_venta = 'Cancelada' THEN
        UPDATE productos p
        INNER JOIN detalle_ventas dv ON p.id = dv.id_producto
        SET p.stock = p.stock + dv.cantidad
        WHERE dv.id_venta = NEW.id;
    END IF;
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dui` (`dui`),
  ADD KEY `idx_dui` (`dui`),
  ADD KEY `idx_nombre` (`nombre_completo`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`),
  ADD KEY `idx_fecha` (`fecha_compra`),
  ADD KEY `idx_proveedor` (`nombre_proveedor`),
  ADD KEY `idx_estado` (`estado_compra`),
  ADD KEY `idx_factura` (`numero_factura`);

--
-- Indices de la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compra` (`id_compra`),
  ADD KEY `idx_producto` (`id_producto`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venta` (`id_venta`),
  ADD KEY `idx_producto` (`id_producto`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `DUI` (`DUI`,`TELEFONO_CORREO`),
  ADD UNIQUE KEY `TELEFONO_CORREO` (`TELEFONO_CORREO`),
  ADD UNIQUE KEY `CONTRASEÑA` (`CONTRASEÑA`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `CODIGO` (`CODIGO`),
  ADD KEY `idx_linea` (`LINEA`),
  ADD KEY `idx_codigo` (`CODIGO`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`),
  ADD KEY `idx_fecha` (`fecha_venta`),
  ADD KEY `idx_cliente` (`id_cliente`),
  ADD KEY `idx_estado` (`estado_venta`),
  ADD KEY `idx_factura` (`numero_factura`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `ID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `ID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD CONSTRAINT `detalle_compras_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_compras_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`ID`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`ID`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
