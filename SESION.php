<?php
session_start();
$mensaje_error = "";

if (isset($_SESSION['error'])) {
    $mensaje_error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SESION</title>
  <link rel="stylesheet" href="CSS/SESION.css">
</head>
<body>
<header>
  <a href="INDEX.php" class="btn-volver">← Inicio</a>
  <img class="logo" src="IMG/tipo.png" alt="">
</header>

<form action="CONEXION.php" method="post" id="formulario">
 <div class="formi">
  <div class="campis">
    <label for="nombreapellido">Nombre y Apellido:</label><br>
    <input class="campos" type="text" id="nombreapellido" name="NOMBRE_APELLIDO" required><br><br>
  </div>

  <div class="campis">
    <label for="telefonocorreo">Correo electrónico o Teléfono:</label><br>
    <input class="campos" type="text" id="telefonocorreo" name="TELEFONO_CORREO" required><br><br>
  </div>

  <div class="campis">
    <label for="dui">DUI:</label><br>
    <input class="campos" type="text" id="ide" name="DUI" required><br><br>
  </div>

  <div class="campis">
    <label for="contraseña">Contraseña:</label><br>
    <input class="campos" type="password" id="contraseña" name="CONTRASEÑA" required><br><br>
  </div>
</div>

<div class="boton-centro">
  <input id="boton" type="submit" name="ingresar" value="INGRESAR">
</div>

</form>
<a href="REGISTRO.php">
<p >¿no tienes cuenta? REGISTRATE</p></button></a>
<?php if (!empty($mensaje_error)) : ?>
  <div class="error" style="color: red; text-align: center; margin-top: 10px;">
    <?= $mensaje_error ?>
  </div>
<?php endif; ?>

</body>

</html>
