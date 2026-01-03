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

// Obtener productos de la línea Aves de Postura
$sql_aves = "SELECT 
                NOMBRE_PRODUCTO,
                CODIGO,
                CATEGORIA,
                ESTADO,
                STOCK
             FROM productos 
             WHERE LINEA = 'Aves de Postura'
             ORDER BY NOMBRE_PRODUCTO";

$resultado = $conn->query($sql_aves);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>INVENTARIO - LÍNEA AVES DE POSTURA</title>
  <link rel="stylesheet" href="CSS/TABLAS.CSS">
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body>

  <header>
    <center><h2>INVENTARIO</h2></center>
    <img src="IMG/tipo.png" alt="Logo de empresa" class="logo">
  </header>

  <main>
    <section aria-labelledby="titulo-aves">
      <h2 id="titulo-aves">LÍNEA AVES DE POSTURA</h2>

      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Código</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>Cantidad</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($resultado && $resultado->num_rows > 0): ?>
            <?php while($producto = $resultado->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($producto['NOMBRE_PRODUCTO']); ?></td>
                <td><?php echo htmlspecialchars($producto['CODIGO']); ?></td>
                <td><?php echo htmlspecialchars($producto['CATEGORIA']); ?></td>
                <td class="<?php echo strtolower(str_replace(' ', '-', $producto['ESTADO'])); ?>">
                  <?php echo htmlspecialchars($producto['ESTADO']); ?>
                </td>
                <td><?php echo htmlspecialchars($producto['STOCK']); ?> Unidades</td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 40px;">
                <p>No hay productos registrados en esta línea</p>
                <small>Los productos aparecerán aquí después de realizar compras</small>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <footer>
        <div style="text-align: center; padding: 20px; color: #666;">
          <p>Última actualización: <?php echo date('d/m/Y H:i:s'); ?></p>
          <a href="INVENTARIO.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
            ← Volver
          </a>
        </div>
      </footer>
    </section>
  </main>

</body>
</html>

<?php $conn->close(); ?>