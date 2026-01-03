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
$id_compra = intval($_POST['id_compra']);
$nuevo_estado = $_POST['nuevo_estado'];
$observaciones = $_POST['observaciones'] ?? '';

// Validar que el estado sea válido
$estados_validos = ['Pendiente', 'Recibida', 'Cancelada'];
if (!in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['success' => false, 'mensaje' => 'Estado no válido']);
    exit;
}

// Obtener el estado actual de la compra
$sql_estado_actual = "SELECT estado_compra FROM compras WHERE id = ?";
$stmt = $conn->prepare($sql_estado_actual);
$stmt->bind_param("i", $id_compra);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Compra no encontrada']);
    exit;
}

$compra = $resultado->fetch_assoc();
$estado_actual = $compra['estado_compra'];

// Validaciones de cambio de estado
if ($estado_actual === 'Cancelada') {
    echo json_encode(['success' => false, 'mensaje' => 'No se puede modificar una compra cancelada']);
    exit;
}

if ($estado_actual === 'Recibida' && $nuevo_estado === 'Pendiente') {
    echo json_encode(['success' => false, 'mensaje' => 'No se puede regresar una compra recibida a pendiente']);
    exit;
}

if ($estado_actual === $nuevo_estado) {
    echo json_encode(['success' => false, 'mensaje' => 'La compra ya tiene ese estado']);
    exit;
}

// Iniciar transacción para asegurar integridad
$conn->begin_transaction();

try {
    // Actualizar el estado de la compra
    $sql_update = "UPDATE compras SET estado_compra = ?";
    
    // Agregar observaciones si se proporcionaron
    if (!empty($observaciones)) {
        $sql_update .= ", observaciones = CONCAT(COALESCE(observaciones, ''), '\n[', NOW(), '] Cambio a " . $nuevo_estado . ": ', ?)";
    }
    
    $sql_update .= " WHERE id = ?";
    
    $stmt_update = $conn->prepare($sql_update);
    
    if (!empty($observaciones)) {
        $stmt_update->bind_param("ssi", $nuevo_estado, $observaciones, $id_compra);
    } else {
        $stmt_update->bind_param("si", $nuevo_estado, $id_compra);
    }
    
    if (!$stmt_update->execute()) {
        throw new Exception('Error al actualizar el estado');
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true, 
        'mensaje' => 'Estado actualizado correctamente',
        'estado_anterior' => $estado_actual,
        'estado_nuevo' => $nuevo_estado
    ]);
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollback();
    echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
}

$conn->close();
?>