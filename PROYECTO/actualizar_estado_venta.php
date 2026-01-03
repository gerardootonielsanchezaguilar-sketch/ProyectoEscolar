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
    $id_venta = $_POST['id_venta'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    // Actualizar el estado de la venta
    $sql = "UPDATE ventas SET estado_venta = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id_venta);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>