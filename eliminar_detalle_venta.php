<?php
// Conexión a la base de datos
$servidor = "localhost:3309";
$usuario = "root";
$clave = "";
$basededatos = "sistema_de_invenario";

$conn = new mysqli($servidor, $usuario, $clave, $basededatos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_detalle = $_POST['id'];
    
    // Primero, obtener la información actual del detalle
    $sql = "SELECT id_venta, id_producto, cantidad FROM detalle_ventas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_detalle);
    $stmt->execute();
    $detalle = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($detalle) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Eliminar el detalle
            $sql = "DELETE FROM detalle_ventas WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_detalle);
            $stmt->execute();
            $stmt->close();
            
            // Recalcular el total de la venta
            $sql = "UPDATE ventas v 
                   SET total_general = COALESCE(
                       (SELECT SUM(cantidad * precio_unitario) 
                        FROM detalle_ventas 
                        WHERE id_venta = v.id), 0)
                   WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $detalle['id_venta']);
            $stmt->execute();
            $stmt->close();
            
            // Confirmar cambios
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Detalle eliminado correctamente']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Detalle no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>