<?php
// Conexión a la base de datos y obtención de productos de la línea Equinos
$servidor = "localhost:3309";
$usuario = "root";
$clave = "";
$basededatos = "sistema_de_invenario";

$conn = new mysqli($servidor, $usuario, $clave, $basededatos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql_equinos = "SELECT NOMBRE_PRODUCTO, CODIGO, CATEGORIA, ESTADO, STOCK
                FROM productos
                WHERE LOWER(LINEA) LIKE '%equino%'
                ORDER BY NOMBRE_PRODUCTO";
$resultado = $conn->query($sql_equinos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVENTARIO - LINEA DE EQUINOS</title>
    <link rel="stylesheet" href="CSS/TABLAS.CSS">
    <script src="JS/VISTA3.JS"></script>
</head>
<body>
<header>
<center><h2>INVENTARIO</h2></center>
<img src="IMG/tipo.png" alt="logo" class="logo">
</header>

<main>
<section aria-labelledby="tabla-contenedor">
<h3>LINEA DE EQUINOS</h3>

<table>
<thead>
<tr>
<th>NOMBRE</th>
<th>CODIGO</th>
<th>CATEGORIA</th>
<th>ESTADO</th>
<th>CANTIDAD</th>
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
          </html>
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