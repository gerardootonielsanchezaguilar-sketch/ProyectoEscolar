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

// Obtener todas las ventas con sus totales
$sql_ventas = "
    SELECT 
        v.id,
        v.numero_factura,
        v.fecha_venta,
        v.nombre_cliente,
        v.dui_cliente,
        v.telefono_cliente,
        v.estado_venta,
        v.metodo_pago,
        v.total_general,
        COUNT(dv.id) as total_productos
    FROM ventas v
    LEFT JOIN detalle_ventas dv ON v.id = dv.id_venta
    GROUP BY v.id
    ORDER BY v.fecha_venta DESC, v.id DESC
";

$resultado_ventas = $conn->query($sql_ventas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas - Agroservicio</title>
    <link rel="stylesheet" href="CSS/COMPRAS.css">
</head>
<body>
    <!-- Modal para cambiar estado -->
    <div id="estadoModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Cambiar Estado de Venta</h2>
            <p>Factura: <span id="modal-factura"></span></p>
            <p>Estado actual: <span id="modal-estado-actual"></span></p>
            <select id="nuevo-estado" class="form-control">
                <option value="Pendiente">Pendiente</option>
                <option value="Completada">Completada</option>
                <option value="Cancelada">Cancelada</option>
            </select>
            <button onclick="guardarCambioEstado()" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="CENTRO.php" class="btn btn-secondary" style="margin-right: auto;">← Centro</a>
            <h1>🛍️ Gestión de Ventas</h1>
            <p>Sistema de Control de Ventas - Agroservicio</p>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Buscar por factura, cliente, DUI...">
            </div>
            <a href="venta.php" class="btn btn-primary">➕ Nueva Venta</a>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Statistics -->
            <div class="stats">
                <?php
                $sql_stats = "
                    SELECT 
                        COUNT(*) as total_ventas,
                        SUM(CASE WHEN estado_venta = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado_venta = 'Completada' THEN 1 ELSE 0 END) as completadas,
                        SUM(CASE WHEN estado_venta = 'Completada' THEN total_general ELSE 0 END) as total_vendido
                    FROM ventas
                ";
                $stats = $conn->query($sql_stats)->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3><?php echo $stats['total_ventas']; ?></h3>
                    <p>Total Ventas</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['pendientes']; ?></h3>
                    <p>Pendientes</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['completadas']; ?></h3>
                    <p>Completadas</p>
                </div>
                <div class="stat-card">
                    <h3>$<?php echo number_format($stats['total_vendido'], 2); ?></h3>
                    <p>Total Vendido</p>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <?php if ($resultado_ventas->num_rows > 0): ?>
                <table id="ventasTable">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>DUI</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Método Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($venta = $resultado_ventas->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $venta['numero_factura']; ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($venta['fecha_venta'])); ?></td>
                            <td>
                                <?php echo $venta['nombre_cliente']; ?>
                                <?php if($venta['telefono_cliente']): ?>
                                <br><small style="color: #666;">📞 <?php echo $venta['telefono_cliente']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $venta['dui_cliente']; ?></td>
                            <td><?php echo $venta['total_productos']; ?> items</td>
                            <td><strong>$<?php echo number_format($venta['total_general'], 2); ?></strong></td>
                            <td><?php echo $venta['metodo_pago']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($venta['estado_venta']); ?>">
                                    <?php echo $venta['estado_venta']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-info" onclick="verDetalle(<?php echo $venta['id']; ?>)">
                                        👁️ Ver
                                    </button>
                                    <?php if($venta['estado_venta'] != 'Cancelada'): ?>
                                    <button class="btn btn-success" onclick="cambiarEstado(<?php echo $venta['id']; ?>, '<?php echo $venta['numero_factura']; ?>', '<?php echo $venta['estado_venta']; ?>')">
                                        🔄 Estado
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <h3>📦 No hay ventas registradas</h3>
                    <p>Comienza registrando tu primera venta</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#ventasTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Ver detalle de venta
        function verDetalle(idVenta) {
            window.location.href = `detalle_venta.php?id=${idVenta}`;
        }

        let ventaIdActual = null;

        // Cambiar estado
        function cambiarEstado(id, factura, estadoActual) {
            ventaIdActual = id;
            document.getElementById('modal-factura').textContent = factura;
            document.getElementById('modal-estado-actual').textContent = estadoActual;
            document.getElementById('nuevo-estado').value = estadoActual;
            document.getElementById('estadoModal').style.display = 'block';
        }

        // Guardar cambio de estado
        function guardarCambioEstado() {
            const nuevoEstado = document.getElementById('nuevo-estado').value;
            
            fetch('actualizar_estado_venta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_venta=${ventaIdActual}&nuevo_estado=${nuevoEstado}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Estado actualizado correctamente');
                    location.reload(); // Recargar para ver los cambios
                } else {
                    alert('Error al actualizar el estado: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error al procesar la solicitud');
                console.error('Error:', error);
            });

            document.getElementById('estadoModal').style.display = 'none';
        }

        // Cerrar modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('estadoModal').style.display = 'none';
        }

        // Cerrar modal si se hace clic fuera de él
        window.onclick = function(event) {
            if (event.target == document.getElementById('estadoModal')) {
                document.getElementById('estadoModal').style.display = 'none';
            }
        }
    </script>

    <style>
        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        #nuevo-estado {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</body>
</html>

<?php $conn->close(); ?>