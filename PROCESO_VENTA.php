<?php
header('Content-Type: application/json');

// Conexión a la base de datos
$servidor = "localhost:3309";
$usuario = "root";
$clave = "";
$basededatos = "sistema_de_invenario";

$conn = new mysqli($servidor, $usuario, $clave, $basededatos);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión a la base de datos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario - Cliente
$id_cliente = isset($_POST['id_cliente']) && !empty($_POST['id_cliente']) ? intval($_POST['id_cliente']) : null;
$nombre_cliente = trim($_POST['nombre_cliente']);
$dui_cliente = trim($_POST['dui_cliente']);
$edad_cliente = intval($_POST['edad_cliente']);
$telefono_cliente = trim($_POST['telefono_cliente']);

// Obtener datos del formulario - Venta
$numero_factura = trim($_POST['numero_factura']);
$fecha_venta = $_POST['fecha_venta'];
$metodo_pago = $_POST['metodo_pago'];
$observaciones = trim($_POST['observaciones'] ?? '');
$subtotal_general = floatval($_POST['subtotal_general']);
$impuesto_general = floatval($_POST['impuesto_general']);
$descuento_general = floatval($_POST['descuento_general']);
$total_general = floatval($_POST['total_general']);
$productos = $_POST['productos'] ?? [];

// Validaciones básicas
if (empty($nombre_cliente) || empty($dui_cliente) || empty($telefono_cliente)) {
    echo json_encode(['success' => false, 'mensaje' => 'Todos los campos del cliente son obligatorios']);
    exit;
}

if (empty($numero_factura) || empty($fecha_venta) || empty($metodo_pago)) {
    echo json_encode(['success' => false, 'mensaje' => 'Todos los campos de la venta son obligatorios']);
    exit;
}

if (empty($productos) || !is_array($productos)) {
    echo json_encode(['success' => false, 'mensaje' => 'Debes agregar al menos un producto']);
    exit;
}

// Verificar que el número de factura no exista
$sql_verificar = "SELECT id FROM ventas WHERE numero_factura = ?";
$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param("s", $numero_factura);
$stmt_verificar->execute();
$resultado = $stmt_verificar->get_result();

if ($resultado->num_rows > 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El número de factura ya existe']);
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Verificar o crear cliente
    if ($id_cliente === null) {
        // Verificar si el cliente existe por DUI
        $sql_buscar_cliente = "SELECT id FROM clientes WHERE dui = ?";
        $stmt_buscar = $conn->prepare($sql_buscar_cliente);
        $stmt_buscar->bind_param("s", $dui_cliente);
        $stmt_buscar->execute();
        $resultado_cliente = $stmt_buscar->get_result();
        
        if ($resultado_cliente->num_rows > 0) {
            // Cliente ya existe
            $cliente_existente = $resultado_cliente->fetch_assoc();
            $id_cliente = $cliente_existente['id'];
        } else {
            // Crear nuevo cliente
            $sql_nuevo_cliente = "INSERT INTO clientes (nombre_completo, edad, dui, telefono) VALUES (?, ?, ?, ?)";
            $stmt_nuevo_cliente = $conn->prepare($sql_nuevo_cliente);
            $stmt_nuevo_cliente->bind_param("siss", $nombre_cliente, $edad_cliente, $dui_cliente, $telefono_cliente);
            
            if (!$stmt_nuevo_cliente->execute()) {
                throw new Exception('Error al crear el cliente: ' . $stmt_nuevo_cliente->error);
            }
            
            $id_cliente = $conn->insert_id;
        }
    }
    
    // 2. Insertar la venta principal
    $sql_venta = "INSERT INTO ventas (
        id_cliente,
        nombre_cliente,
        dui_cliente,
        telefono_cliente,
        numero_factura,
        fecha_venta,
        subtotal_general,
        impuesto_general,
        descuento_general,
        total_general,
        metodo_pago,
        estado_venta,
        observaciones,
        usuario_registro
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?, 'admin')";
    
    $stmt_venta = $conn->prepare($sql_venta);
    // Tipos: i (int) para id_cliente, luego s (string) x5, luego d (double) x4, luego s (string) x2 => 12 parámetros
    $stmt_venta->bind_param(
        "isssssddddss",
        $id_cliente,
        $nombre_cliente,
        $dui_cliente,
        $telefono_cliente,
        $numero_factura,
        $fecha_venta,
        $subtotal_general,
        $impuesto_general,
        $descuento_general,
        $total_general,
        $metodo_pago,
        $observaciones
    );
    
    if (!$stmt_venta->execute()) {
        throw new Exception('Error al insertar la venta: ' . $stmt_venta->error);
    }
    
    $id_venta = $conn->insert_id;
    
    // 3. Procesar cada producto
    foreach ($productos as $producto) {
        if (empty($producto['id']) || empty($producto['cantidad']) || empty($producto['precio_unitario'])) {
            throw new Exception('Datos de producto incompletos');
        }
        
        $id_producto = intval($producto['id']);
        $nombre_producto = trim($producto['nombre']);
        $categoria_producto = trim($producto['categoria']);
        $codigo_producto = trim($producto['codigo']);
        $cantidad = intval($producto['cantidad']);
        $precio_unitario = floatval($producto['precio_unitario']);
        $descuento_item = floatval($producto['descuento'] ?? 0);
        
        // Calcular totales
        $subtotal_item = $cantidad * $precio_unitario;
        $total_item = $subtotal_item - $descuento_item;
        
        // Validaciones
        if ($cantidad <= 0 || $precio_unitario <= 0) {
            throw new Exception('Cantidad y precio deben ser mayores a 0');
        }
        
        // Verificar que el producto existe y tiene stock suficiente
        $sql_verificar_prod = "SELECT ID, STOCK, NOMBRE_PRODUCTO FROM productos WHERE ID = ?";
        $stmt_verificar_prod = $conn->prepare($sql_verificar_prod);
        $stmt_verificar_prod->bind_param("i", $id_producto);
        $stmt_verificar_prod->execute();
        $resultado_prod = $stmt_verificar_prod->get_result();
        
        if ($resultado_prod->num_rows === 0) {
            throw new Exception('El producto con ID ' . $id_producto . ' no existe');
        }
        
        $producto_bd = $resultado_prod->fetch_assoc();
        
        if ($producto_bd['STOCK'] < $cantidad) {
            throw new Exception('Stock insuficiente para ' . $producto_bd['NOMBRE_PRODUCTO'] . '. Disponible: ' . $producto_bd['STOCK']);
        }
        
        // Insertar el detalle de venta
        $sql_detalle = "INSERT INTO detalle_ventas (
            id_venta,
            id_producto,
            nombre_producto,
            codigo_producto,
            categoria_producto,
            cantidad,
            precio_unitario,
            subtotal,
            descuento_item,
            total_item
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->bind_param(
            "iisssidddd",
            $id_venta,
            $id_producto,
            $nombre_producto,
            $codigo_producto,
            $categoria_producto,
            $cantidad,
            $precio_unitario,
            $subtotal_item,
            $descuento_item,
            $total_item
        );
        
        if (!$stmt_detalle->execute()) {
            throw new Exception('Error al insertar detalle de producto: ' . $stmt_detalle->error);
        }
    }
    
    // 4. Confirmar la transacción
    $conn->commit();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'mensaje' => 'Venta registrada exitosamente',
        'id_venta' => $id_venta,
        'id_cliente' => $id_cliente,
        'numero_factura' => $numero_factura,
        'total_productos' => count($productos),
        'total_general' => $total_general
    ]);
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}

$conn->close();
?>