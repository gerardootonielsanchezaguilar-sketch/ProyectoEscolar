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
$sql_productos = "SELECT ID, NOMBRE_PRODUCTO, CODIGO, CATEGORIA, LINEA, PRECIO, STOCK, ESTADO
                  FROM productos 
                  ORDER BY LINEA, NOMBRE_PRODUCTO";
$productos = $conn->query($sql_productos);

// Agrupar productos por línea (TODOS, incluso sin stock)
$productos_por_linea = [];
$productos_con_stock = [];
$productos->data_seek(0);
while($prod = $productos->fetch_assoc()) {
    $linea = $prod['LINEA'];
    if (!isset($productos_por_linea[$linea])) {
        $productos_por_linea[$linea] = [];
    }
    $productos_por_linea[$linea][] = $prod;
    
    // Contar productos con stock
    if ($prod['STOCK'] > 0) {
        $productos_con_stock[] = $prod;
    }
}

// Obtener clientes para autocompletar
$sql_clientes = "SELECT id, nombre_completo, dui, telefono, edad FROM clientes ORDER BY nombre_completo";
$clientes = $conn->query($sql_clientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva Venta - Agroservicio</title>
  <!-- Usar tema verde unificado -->
  <link rel="stylesheet" href="CSS/COMPRAS.css">
</head>
<body>

  <div class="container">
    <!-- Header -->
    <div class="header">
      <h1>🛍️ Nueva Venta</h1>
      <a href="gestion_venta.php" class="btn btn-secondary">← Volver a Ventas</a>
    </div>

    <!-- Content -->
    <div class="content">
      <div id="alertMessage" class="alert"></div>

      <?php if (count($productos_con_stock) == 0): ?>
      <!-- Alerta de sin stock -->
      <div class="alert-warning-box">
        <h3>⚠️ Sin Productos Disponibles</h3>
        <p>Actualmente no hay productos con stock disponible para vender.</p>
        <p><strong>Sugerencia:</strong> Primero realiza una <a href="nueva_compra.php" style="color: #667eea; font-weight: bold;">compra de productos</a> para abastecer tu inventario.</p>
      </div>
      <?php endif; ?>

      <form id="formVenta">
        <!-- Sección 1: Información del Cliente -->
        <div class="form-section">
          <h2>👤 Información del Cliente</h2>
          
          <div class="cliente-busqueda">
            <div class="form-group" style="flex: 2;">
              <label>Buscar Cliente por DUI o Nombre</label>
              <input type="text" id="buscar_cliente" placeholder="Escribe DUI o nombre del cliente..." 
                     onkeyup="buscarCliente()" autocomplete="off">
              <div id="resultados_clientes" class="resultados-busqueda"></div>
            </div>
            <div class="form-group">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-info" onclick="nuevoCliente()">
                ➕ Nuevo Cliente
              </button>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Nombre Completo <span class="required">*</span></label>
              <input type="text" name="nombre_cliente" id="nombre_cliente" required 
                     placeholder="Nombre completo del cliente">
              <input type="hidden" name="id_cliente" id="id_cliente">
            </div>
            <div class="form-group">
              <label>DUI <span class="required">*</span></label>
              <input type="text" name="dui_cliente" id="dui_cliente" required 
                     placeholder="00000000-0" pattern="[0-9]{8}-[0-9]{1}">
            </div>
            <div class="form-group">
              <label>Edad <span class="required">*</span></label>
              <input type="number" name="edad_cliente" id="edad_cliente" required 
                     min="1" max="120" placeholder="Edad">
            </div>
            <div class="form-group">
              <label>Teléfono <span class="required">*</span></label>
              <input type="tel" name="telefono_cliente" id="telefono_cliente" required 
                     placeholder="0000-0000" pattern="[0-9]{4}-[0-9]{4}">
            </div>
          </div>
        </div>

        <!-- Sección 2: Información de la Venta -->
        <div class="form-section">
          <h2>📋 Información de la Venta</h2>
          <div class="form-grid">
            <div class="form-group">
              <label>Número de Factura <span class="required">*</span></label>
              <input type="text" name="numero_factura" id="numero_factura" required 
                     placeholder="Ej: VTA-2025-001" value="VTA-<?php echo date('Y'); ?>-">
            </div>
            <div class="form-group">
              <label>Fecha de Venta <span class="required">*</span></label>
              <input type="date" name="fecha_venta" id="fecha_venta" required 
                     value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
              <label>Método de Pago <span class="required">*</span></label>
              <select name="metodo_pago" required>
                <option value="">-- Seleccionar --</option>
                <option value="Efectivo">💵 Efectivo</option>
                <option value="Transferencia">🏦 Transferencia</option>
                <option value="Tarjeta">💳 Tarjeta</option>
                <option value="Crédito">📝 Crédito</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Sección 3: Productos -->
        <div class="form-section">
          <h2>📦 Productos de la Venta</h2>
          
          <div class="info-box">
            <strong>ℹ️ Instrucciones:</strong>
            Selecciona la línea y luego el producto. 
            <?php if (count($productos_con_stock) > 0): ?>
            <span style="color: #28a745;">✓ Hay <?php echo count($productos_con_stock); ?> productos disponibles</span>
            <?php else: ?>
            <span style="color: #dc3545;">✗ No hay productos con stock disponible</span>
            <?php endif; ?>
          </div>

          <div class="productos-section">
            <div class="productos-header">
              <h3>Lista de Productos</h3>
              <button type="button" class="btn btn-success" onclick="agregarProducto()" 
                      <?php echo count($productos_con_stock) == 0 ? 'disabled title="No hay productos disponibles"' : ''; ?>>
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
                      placeholder="Escribe cualquier observación o nota sobre esta venta..."></textarea>
          </div>
        </div>

        <!-- Acciones -->
        <div class="form-actions">
          <button type="submit" class="btn btn-primary" 
                  <?php echo count($productos_con_stock) == 0 ? 'disabled title="No hay productos disponibles"' : ''; ?>>
            💾 Guardar Venta
          </button>
          <button type="button" class="btn btn-warning" onclick="restablecerFormulario()">
            🔄 Restablecer
          </button>
          <a href="gestion_venta.php" class="btn btn-secondary">
            ❌ Cancelar
          </a>
        </div>
      </form>
    </div>

  
  </div>

  <!-- Pasar datos de PHP a JavaScript -->
  <script>
    const productosPorLinea = <?php echo json_encode($productos_por_linea); ?>;
    const lineasDisponibles = <?php echo json_encode(array_keys($productos_por_linea)); ?>;
    const clientesDisponibles = [
      <?php 
      $clientes->data_seek(0);
      while($cliente = $clientes->fetch_assoc()): 
      ?>
      {
        id: <?php echo $cliente['id']; ?>,
        nombre: "<?php echo addslashes($cliente['nombre_completo']); ?>",
        dui: "<?php echo $cliente['dui']; ?>",
        telefono: "<?php echo $cliente['telefono']; ?>",
        edad: <?php echo $cliente['edad']; ?>
      },
      <?php endwhile; ?>
    ];
  </script>

  <script src="js/venta.js"></script>
</body>
</html>

<?php $conn->close(); ?>