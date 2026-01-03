<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agroservicio Los Llanos</title>
  <link rel="stylesheet" href="CSS/MENU.CSS" />
</head>
<body>

  <div class="sidebar">
    <h2>Agroservicio<br>Los Llanos</h2>
    <a href="#" class="menu-link active" data-section="venta">🛒 Venta</a>
    <a href="#" class="menu-link" data-section="compra">📦 Compra</a>
    <a href="#" class="menu-link" data-section="inventario">📊 Inventario</a>
    <a href="#" class="menu-link" data-section="usuarios">👥 Ingreso de usuarios</a>
  </div>

  <div class="content">
    <div id="venta" class="section active">
      <h1>🛒 Módulo de Venta</h1>
      <p>Aquí puedes registrar ventas de productos agrícolas.</p>
    </div>
    <div id="compra" class="section">
      <h1>📦 Módulo de Compra</h1>
      <p>Registra compras de fertilizantes, semillas, etc.</p>
    </div>
    <div id="inventario" class="section">
      <h1>📊 Inventario</h1>
      <p>Consulta y actualiza el stock disponible.</p>
    </div>
    <div id="usuarios" class="section">
      <h1>👥 Ingreso de Usuarios</h1>
      <p>Administra usuarios y accesos al sistema.</p>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>