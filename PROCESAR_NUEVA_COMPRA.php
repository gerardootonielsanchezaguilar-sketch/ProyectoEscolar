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

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario
$numero_factura = trim($_POST['numero_factura']);
$fecha_compra = $_POST['fecha_compra'];
$metodo_pago = $_POST['metodo_pago'];
$nombre_proveedor = trim($_POST['nombre_proveedor']);
$telefono_proveedor = trim($_POST['telefono_proveedor'] ?? '');
$email_proveedor = trim($_POST['email_proveedor'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$subtotal_general = floatval($_POST['subtotal_general']);
$impuesto_general = floatval($_POST['impuesto_general']);
$descuento_general = floatval($_POST['descuento_general']);
$total_general = floatval($_POST['total_general']);
$productos = $_POST['productos'] ?? [];

// Validaciones básicas
if (empty($numero_factura) || empty($fecha_compra) || empty($metodo_pago) || empty($nombre_proveedor)) {
    echo json_encode(['success' => false, 'mensaje' => 'Todos los campos obligatorios deben estar llenos']);
    exit;
}

if (empty($productos) || !is_array($productos)) {
    echo json_encode(['success' => false, 'mensaje' => 'Debes agregar al menos un producto']);
    exit;
}

// Verificar que el número de factura no exista
$sql_verificar = "SELECT id FROM compras WHERE numero_factura = ?";
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
    // 1. Insertar la compra principal
    $sql_compra = "INSERT INTO compras (
        numero_factura, 
        fecha_compra, 
        nombre_proveedor, 
        telefono_proveedor,
        email_proveedor,
        subtotal_general,
        impuesto_general,
        descuento_general,
        total_general,
        metodo_pago,
        estado_compra,
        observaciones,
        usuario_registro
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?, 'admin')";
    
    $stmt_compra = $conn->prepare($sql_compra);
    $stmt_compra->bind_param(
        "sssssdddsss",
        $numero_factura,
        $fecha_compra,
        $nombre_proveedor,
        $telefono_proveedor,
        $email_proveedor,
        $subtotal_general,
        $impuesto_general,
        $descuento_general,
        $total_general,
        $metodo_pago,
        $observaciones
    );
    
    if (!$stmt_compra->execute()) {
        throw new Exception('Error al insertar la compra: ' . $stmt_compra->error);
    }
    
    $id_compra = $conn->insert_id;
    
    // 2. Procesar cada producto
    foreach ($productos as $producto) {
        if (empty($producto['linea']) || empty($producto['cantidad']) || empty($producto['precio_unitario'])) {
            throw new Exception('Datos de producto incompletos');
        }
        
        $linea = trim($producto['linea']);
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
        
        // Verificar si es producto especial o existente
        if ($linea === 'Especial' && $producto['id'] === 'nuevo') {
            // Producto especial - crear nuevo producto
            $nombre_producto = trim($producto['nombre']);
            $codigo_producto = trim($producto['codigo']) ?: 'ESP-' . time();
            $categoria_producto = 'Especial';
            
            if (empty($nombre_producto)) {
                throw new Exception('El nombre del producto especial es obligatorio');
            }
            
            // Insertar nuevo producto
            $sql_nuevo_prod = "INSERT INTO productos (NOMBRE_PRODUCTO, CODIGO, CATEGORIA, LINEA, PRECIO, STOCK, ESTADO) 
                              VALUES (?, ?, ?, ?, ?, 0, 'Agotado')";
            $stmt_nuevo_prod = $conn->prepare($sql_nuevo_prod);
            $stmt_nuevo_prod->bind_param("ssssd", $nombre_producto, $codigo_producto, $categoria_producto, $linea, $precio_unitario);
            
            if (!$stmt_nuevo_prod->execute()) {
                throw new Exception('Error al crear producto especial: ' . $stmt_nuevo_prod->error);
            }
            
            $id_producto = $conn->insert_id;
            
        } else {
            // Producto existente
            $id_producto = intval($producto['id']);
            $nombre_producto = trim($producto['nombre']);
            $categoria_producto = trim($producto['categoria']);
            $codigo_producto = trim($producto['codigo']);
            
            // Verificar que el producto existe
            $sql_verificar_prod = "SELECT ID FROM productos WHERE ID = ?";
            $stmt_verificar_prod = $conn->prepare($sql_verificar_prod);
            $stmt_verificar_prod->bind_param("i", $id_producto);
            $stmt_verificar_prod->execute();
            $resultado_prod = $stmt_verificar_prod->get_result();
            
            if ($resultado_prod->num_rows === 0) {
                throw new Exception('El producto con ID ' . $id_producto . ' no existe');
            }
        }
        
        // Insertar el detalle de compra
        $sql_detalle = "INSERT INTO detalle_compras (
            id_compra,
            id_producto,
            nombre_producto,
            categoria_producto,
            codigo_producto,
            linea_producto,
            cantidad,
            precio_unitario,
            subtotal,
            descuento_item,
            total_item
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->bind_param(
            "iissssddddd",
            $id_compra,
            $id_producto,
            $nombre_producto,
            $categoria_producto,
            $codigo_producto,
            $linea,
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
    
    // 3. Confirmar la transacción
    $conn->commit();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'mensaje' => 'Compra registrada exitosamente',
        'id_compra' => $id_compra,
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