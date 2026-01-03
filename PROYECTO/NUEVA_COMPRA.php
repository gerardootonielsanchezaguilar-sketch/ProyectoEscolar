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

// Obtener productos agrupados por línea
$sql_productos = "SELECT ID, NOMBRE_PRODUCTO, CODIGO, CATEGORIA, LINEA, PRECIO, STOCK 
                  FROM productos 
                  ORDER BY LINEA, NOMBRE_PRODUCTO";
$productos = $conn->query($sql_productos);

// Agrupar productos por línea
$productos_por_linea = [];
$productos->data_seek(0);
while($prod = $productos->fetch_assoc()) {
    $linea = $prod['LINEA'];
    if (!isset($productos_por_linea[$linea])) {
        $productos_por_linea[$linea] = [];
    }
    $productos_por_linea[$linea][] = $prod;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Compra - Agroservicio</title>
    <!-- Usar el tema de Agroservicio (mismo que COMPRAS) -->
    <link rel="stylesheet" href="CSS/COMPRAS.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🛒 Nueva Compra</h1>
            <a href="compras.php" class="btn btn-secondary">← Volver a Compras</a>
        </div>

        <!-- Content -->
        <div class="content">
            <div id="alertMessage" class="alert"></div>

            <form id="formNuevaCompra">
                <!-- Sección 1: Información General -->
                <div class="form-section">
                    <h2>📋 Información General</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Número de Factura <span class="required">*</span></label>
                            <input type="text" name="numero_factura" id="numero_factura" required 
                                   placeholder="Ej: FACT-2025-001">
                        </div>
                        <div class="form-group">
                            <label>Fecha de Compra <span class="required">*</span></label>
                            <input type="date" name="fecha_compra" id="fecha_compra" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Método de Pago <span class="required">*</span></label>
                            <select name="metodo_pago" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="Efectivo">💵 Efectivo</option>
                                <option value="Transferencia">🏦 Transferencia</option>
                                <option value="Cheque">📝 Cheque</option>
                                <option value="Crédito">💳 Crédito</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Información del Proveedor -->
                <div class="form-section">
                    <h2>👤 Información del Proveedor</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre del Proveedor <span class="required">*</span></label>
                            <input type="text" name="nombre_proveedor" required 
                                   placeholder="Ej: Proveedora Ganadera El Roble">
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono_proveedor" 
                                   placeholder="Ej: 7890-1234">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email_proveedor" 
                                   placeholder="Ej: ventas@proveedor.com">
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Productos -->
                <div class="form-section">
                    <h2>📦 Productos de la Compra</h2>
                    
                    <div class="info-box">
                        <strong>ℹ️ Instrucciones:</strong>
                        Primero selecciona la línea, luego el producto. Los productos especiales permiten nombre personalizado.
                    </div>

                    <div class="productos-section">
                        <div class="productos-header">
                            <h3>Lista de Productos</h3>
                            <button type="button" class="btn btn-success" onclick="agregarProducto()">
                                ➕ Agregar Producto
                            </button>
                        </div>

                        <div id="productosContainer">
                            <!-- Los productos se agregarán dinámicamente aquí -->
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="totales-box">
                        <div class="totales-row">
                            <span>Subtotal:</span>
                            <span id="subtotalDisplay">$0.00</span>
                        </div>
                        <div class="totales-row">
                            <span>Impuesto (13%):</span>
                            <span id="impuestoDisplay">$0.00</span>
                        </div>
                        <div class="totales-row">
                            <span>Descuento:</span>
                            <span>
                                $<input type="number" name="descuento_general" id="descuento_general" 
                                        value="0" min="0" step="0.01" 
                                        style="width: 120px; padding: 5px; border: none; border-radius: 5px; text-align: right;"
                                        onchange="calcularTotales()">
                            </span>
                        </div>
                        <div class="totales-row total">
                            <span>TOTAL A PAGAR:</span>
                            <span id="totalDisplay">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Observaciones -->
                <div class="form-section">
                    <h2>📝 Observaciones</h2>
                    <div class="form-group">
                        <label>Observaciones (Opcional)</label>
                        <textarea name="observaciones" rows="4" 
                                  placeholder="Escribe cualquier observación o nota sobre esta compra..."></textarea>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        💾 Guardar Compra
                    </button>
                    <a href="compras.php" class="btn btn-secondary">
                        ❌ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Pasar datos de PHP a JavaScript -->
    <script>
        // Productos agrupados por línea
        const productosPorLinea = <?php echo json_encode($productos_por_linea); ?>;
        
        // Lista de líneas disponibles
        const lineasDisponibles = <?php echo json_encode(array_keys($productos_por_linea)); ?>;
    </script>

    <script src="js/nueva_compra.js"></script>
</body>
</html>

<?php $conn->close(); ?>