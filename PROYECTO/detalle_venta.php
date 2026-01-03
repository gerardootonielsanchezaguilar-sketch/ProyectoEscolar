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

// Obtener ID de la venta
$id_venta = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_venta === 0) {
    die("ID de venta no válido");
}

// Obtener información de la venta
$sql_venta = "SELECT * FROM ventas WHERE id = ?";
$stmt = $conn->prepare($sql_venta);
$stmt->bind_param("i", $id_venta);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener detalles de la venta
$sql_detalles = "
    SELECT dv.*, p.NOMBRE_PRODUCTO as nombre_producto
    FROM detalle_ventas dv
    JOIN productos p ON dv.id_producto = p.id
    WHERE dv.id_venta = ?
";
$stmt = $conn->prepare($sql_detalles);
$stmt->bind_param("i", $id_venta);
$stmt->execute();
$detalles = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta - Agroservicio</title>
    <link rel="stylesheet" href="CSS/COMPRAS.css">
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="GESTION_VENTA.php" class="btn btn-secondary" style="margin-right: auto;">← Volver</a>
            <h1 style="margin:0;">🧾 Detalle de Venta</h1>
            <span style="margin-left:auto;font-size:1.1em;opacity:0.85;">Factura: <?php echo $venta['numero_factura']; ?></span>
        </div>

        <!-- Información de la venta -->
        <div class="form-section" style="margin-top:18px;">
            <div class="form-grid">
                <div class="form-group">
                    <label><b>Nombre del Cliente</b></label>
                    <div class="info-item"><?php echo $venta['nombre_cliente']; ?></div>
                </div>
                <div class="form-group">
                    <label><b>DUI</b></label>
                    <div class="info-item"><?php echo $venta['dui_cliente']; ?></div>
                </div>
                <div class="form-group">
                    <label><b>Teléfono</b></label>
                    <div class="info-item"><?php echo $venta['telefono_cliente']; ?></div>
                </div>
                <div class="form-group">
                    <label><b>Fecha</b></label>
                    <div class="info-item"><?php echo date('d/m/Y', strtotime($venta['fecha_venta'])); ?></div>
                </div>
                <div class="form-group">
                    <label><b>Método de Pago</b></label>
                    <div class="info-item"><?php echo $venta['metodo_pago']; ?></div>
                </div>
                <div class="form-group">
                    <label><b>Estado</b></label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span class="badge badge-<?php echo strtolower($venta['estado_venta']); ?>"><?php echo $venta['estado_venta']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de productos -->
        <div class="table-container" style="margin-top:18px;">
            <h3 style="margin:0 0 10px 0;">Productos Vendidos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($detalle = $detalles->fetch_assoc()): ?>
                    <tr id="row-<?php echo $detalle['id']; ?>">
                        <td><?php echo $detalle['nombre_producto']; ?></td>
                        <td>
                            <span class="display-value"><?php echo $detalle['cantidad']; ?></span>
                            <input type="number" class="edit-input" style="display: none;" value="<?php echo $detalle['cantidad']; ?>">
                        </td>
                        <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                        <td>$<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2); ?></td>
                        <td class="action-buttons">
                            <button class="btn btn-warning btn-sm edit-btn" onclick="toggleEdit(<?php echo $detalle['id']; ?>)">✏️ Editar</button>
                            <button class="btn btn-success btn-sm save-btn" style="display: none;" onclick="guardarCambios(<?php echo $detalle['id']; ?>)">💾 Guardar</button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarDetalle(<?php echo $detalle['id']; ?>)">🗑️ Eliminar</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total General:</strong></td>
                        <td colspan="2"><strong>$<?php echo number_format($venta['total_general'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        function toggleEdit(id) {
            const row = document.getElementById(`row-${id}`);
            const displayValue = row.querySelector('.display-value');
            const editInput = row.querySelector('.edit-input');
            const editBtn = row.querySelector('.edit-btn');
            const saveBtn = row.querySelector('.save-btn');

            displayValue.style.display = 'none';
            editInput.style.display = 'inline-block';
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        }

        function guardarCambios(id) {
            const row = document.getElementById(`row-${id}`);
            const nuevaCantidad = row.querySelector('.edit-input').value;

            fetch('actualizar_detalle_venta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&cantidad=${nuevaCantidad}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Recargar para ver los cambios actualizados
                } else {
                    alert('Error al actualizar: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error al procesar la solicitud');
                console.error('Error:', error);
            });
        }

        function eliminarDetalle(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto de la venta?')) {
                fetch('eliminar_detalle_venta.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Recargar para ver los cambios
                    } else {
                        alert('Error al eliminar: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error al procesar la solicitud');
                    console.error('Error:', error);
                });
            }
        }
    </script>

    <style>
        .edit-input {
            width: 80px;
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.9em;
        }
    </style>
</body>
</html>

<?php $conn->close(); ?>