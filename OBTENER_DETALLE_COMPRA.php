<?php
// Conexión a la base de datos
$servidor = "localhost:3309";
$usuario = "root";
$clave = "";
$basededatos = "sistema_de_invenario";

$conn = new mysqli($servidor, $usuario, $clave, $basededatos);

if ($conn->connect_error) {
    die("Error de conexión");
}

$id_compra = intval($_GET['id']);

// Obtener datos generales de la compra
$sql_compra = "SELECT * FROM compras WHERE id = $id_compra";
$compra = $conn->query($sql_compra)->fetch_assoc();

// Obtener productos de la compra
$sql_detalle = "SELECT * FROM detalle_compras WHERE id_compra = $id_compra";
$productos = $conn->query($sql_detalle);

if (!$compra) {
    echo '<p style="color:red; text-align:center;">Compra no encontrada</p>';
    exit;
}
?>

<div class="info-grid">
    <div class="info-item">
        <label>Número de Factura</label>
        <div class="value"><?php echo $compra['numero_factura']; ?></div>
    </div>
    <div class="info-item">
        <label>Fecha de Compra</label>
        <div class="value"><?php echo date('d/m/Y', strtotime($compra['fecha_compra'])); ?></div>
    </div>
    <div class="info-item">
        <label>Proveedor</label>
        <div class="value"><?php echo $compra['nombre_proveedor']; ?></div>
    </div>
    <div class="info-item">
        <label>Teléfono</label>
        <div class="value"><?php echo $compra['telefono_proveedor'] ?: 'N/A'; ?></div>
    </div>
    <div class="info-item">
        <label>Método de Pago</label>
        <div class="value"><?php echo $compra['metodo_pago']; ?></div>
    </div>
    <div class="info-item">
        <label>Estado</label>
        <div class="value">
            <span class="badge badge-<?php echo strtolower($compra['estado_compra']); ?>">
                <?php echo $compra['estado_compra']; ?>
            </span>
        </div>
    </div>
</div>

<?php if ($compra['observaciones']): ?>
<div class="info-item" style="margin: 20px 0;">
    <label>Observaciones</label>
    <div class="value"><?php echo nl2br($compra['observaciones']); ?></div>
</div>
<?php endif; ?>

<div class="productos-table">
    <h3>📦 Productos Comprados</h3>
    <table style="margin: 0;">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
                <th>Descuento</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal_total = 0;
            $descuento_total = 0;
            while($item = $productos->fetch_assoc()): 
                $subtotal_total += $item['subtotal'];
                $descuento_total += $item['descuento_item'];
            ?>
            <tr>
                <td><strong><?php echo $item['nombre_producto']; ?></strong></td>
                <td><?php echo $item['categoria_producto']; ?></td>
                <td><?php echo $item['cantidad']; ?> unidades</td>
                <td>$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                <td>$<?php echo number_format($item['descuento_item'], 2); ?></td>
                <td><strong>$<?php echo number_format($item['total_item'], 2); ?></strong></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <strong>Subtotal:</strong>
        <span>$<?php echo number_format($compra['subtotal_general'], 2); ?></span>
    </div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <strong>Impuesto:</strong>
        <span>$<?php echo number_format($compra['impuesto_general'], 2); ?></span>
    </div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <strong>Descuento:</strong>
        <span style="color: #dc3545;">-$<?php echo number_format($compra['descuento_general'], 2); ?></span>
    </div>
    <hr style="margin: 15px 0; border: 0; border-top: 2px solid #dee2e6;">
    <div style="display: flex; justify-content: space-between; font-size: 1.3em;">
        <strong>TOTAL:</strong>
        <strong style="color: #667eea;">$<?php echo number_format($compra['total_general'], 2); ?></strong>
    </div>
</div>

<div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 4px;">
    <strong>ℹ️ Información:</strong><br>
    <small>Fecha de registro: <?php echo date('d/m/Y H:i:s', strtotime($compra['fecha_registro'])); ?></small>
    <?php if($compra['usuario_registro']): ?>
    <br><small>Registrado por: <?php echo $compra['usuario_registro']; ?></small>
    <?php endif; ?>
</div>

<?php $conn->close(); ?>