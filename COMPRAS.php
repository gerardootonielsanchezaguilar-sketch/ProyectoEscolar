<?php
// -------------------- CONEXIÓN A LA BASE DE DATOS --------------------
$servidor = "localhost:3309";
$usuario = "root";
$clave = "";
$basededatos = "sistema_de_invenario";

$conn = new mysqli($servidor, $usuario, $clave, $basededatos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// -------------------- CONSULTA PRINCIPAL DE COMPRAS --------------------
$sql_compras = "
    SELECT 
        c.id,
        c.numero_factura,
        c.fecha_compra,
        c.nombre_proveedor,
        c.telefono_proveedor,
        c.estado_compra,
        c.metodo_pago,
        c.total_general,
        COUNT(dc.id) as total_productos
    FROM compras c
    LEFT JOIN detalle_compras dc ON c.id = dc.id_compra
    GROUP BY c.id
    ORDER BY c.fecha_compra DESC, c.id DESC
";

$resultado_compras = $conn->query($sql_compras);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Compras - Agroservicio</title>
    <link rel="stylesheet" href="CSS/COMPRAS.css"> <!-- Estilos externos -->
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <a href="CENTRO.php" class="btn btn-secondary" style="margin-right: auto;">← Centro</a>
            <h1>🛒 Gestión de Compras</h1>
            <p>Sistema de Control de Compras - Agroservicio</p>
        </div>

        <!-- Barra de herramientas -->
        <div class="toolbar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Buscar por factura, proveedor...">
            </div>
            <a href="nueva_compra.php" class="btn btn-primary">➕ Nueva Compra</a>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <!-- Estadísticas -->
            <div class="stats">
                <?php
                $sql_stats = "
                    SELECT 
                        COUNT(*) as total_compras,
                        SUM(CASE WHEN estado_compra = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado_compra = 'Recibida' THEN 1 ELSE 0 END) as recibidas,
                        SUM(CASE WHEN estado_compra = 'Recibida' THEN total_general ELSE 0 END) as total_invertido
                    FROM compras
                ";
                $stats = $conn->query($sql_stats)->fetch_assoc();
                ?>
                <div class="stat-card"><h3><?= $stats['total_compras']; ?></h3><p>Total Compras</p></div>
                <div class="stat-card"><h3><?= $stats['pendientes']; ?></h3><p>Pendientes</p></div>
                <div class="stat-card"><h3><?= $stats['recibidas']; ?></h3><p>Recibidas</p></div>
                <div class="stat-card"><h3>$<?= number_format($stats['total_invertido'], 2); ?></h3><p>Total Invertido</p></div>
            </div>

            <!-- Tabla de compras -->
            <div class="table-container">
                <?php if ($resultado_compras->num_rows > 0): ?>
                <table id="comprasTable">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Método Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($compra = $resultado_compras->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= $compra['numero_factura']; ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($compra['fecha_compra'])); ?></td>
                            <td>
                                <?= $compra['nombre_proveedor']; ?>
                                <?php if($compra['telefono_proveedor']): ?>
                                    <br><small style="color: #666;">📞 <?= $compra['telefono_proveedor']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= $compra['total_productos']; ?> items</td>
                            <td><strong>$<?= number_format($compra['total_general'], 2); ?></strong></td>
                            <td><?= $compra['metodo_pago']; ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($compra['estado_compra']); ?>">
                                    <?= $compra['estado_compra']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-info" onclick="verDetalle(<?= $compra['id']; ?>)">👁️ Ver</button>
                                    <?php if($compra['estado_compra'] != 'Cancelada'): ?>
                                    <button class="btn btn-success" 
                                            onclick="abrirModalEstado(<?= $compra['id']; ?>, '<?= $compra['numero_factura']; ?>', '<?= $compra['estado_compra']; ?>')">
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
                    <h3>📦 No hay compras registradas</h3>
                    <p>Comienza registrando tu primera compra</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detalle -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Detalle de Compra</h2>
                <span class="close" onclick="cerrarModal('modalDetalle')">&times;</span>
            </div>
            <div class="modal-body" id="modalDetalleBody"></div>
        </div>
    </div>

    <!-- Modal Cambiar Estado -->
    <div id="modalEstado" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔄 Cambiar Estado de Compra</h2>
                <span class="close" onclick="cerrarModal('modalEstado')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="info-item"><label>Factura:</label><div class="value" id="modalFactura"></div></div>
                <div class="info-item"><label>Estado Actual:</label><div class="value" id="modalEstadoActual"></div></div>

                <form id="formEstado">
                    <input type="hidden" id="idCompraEstado" name="id_compra">
                    <div class="form-group">
                        <label>Nuevo Estado:</label>
                        <select name="nuevo_estado" id="selectNuevoEstado" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="Pendiente">⏳ Pendiente</option>
                            <option value="Recibida">✅ Recibida</option>
                            <option value="Cancelada">❌ Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observaciones (opcional):</label>
                        <textarea name="observaciones" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">💾 Guardar Cambio</button>
                </form>
            </div>
        </div>
    </div>

    <script src="JS/compras.js"></script> <!-- Script externo -->
</body>
</html>
<?php $conn->close(); ?>
