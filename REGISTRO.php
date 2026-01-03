<?php
$servidor = "localhost:3309";
$usuario = "root";
$clave = "";
$basededatos = "sistema_de_invenario";
$enlace = mysqli_connect($servidor, $usuario, $clave, $basededatos);
$enlace->set_charset("utf8");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>REGISTRO DE USUARIO - AGROAMIGO</title>
  <link rel="stylesheet" href="CSS/REGISTRO.CSS">
  
</head>
<body>

  <header>
    <a href="INDEX.php" class="btn-volver">← Inicio</a>
    <div class="logo">
      <img src="IMG/tipo.png" alt="Logo Agroamigo">
    </div>
  </header>

  <main>
    <section class="container">
      <h2>REGISTRO DE USUARIO</h2>

      <form action="#" method="POST">
        <div class="form-group">
          <label for="NOMBRE_APELLIDO">NOMBRE Y APELLIDO:</label>
          <input type="text" id="NOMBRE_APELLIDO" name="NOMBRE_APELLIDO" class="campos" required>
        </div>

        <div class="form-group">
          <label for="DUI">DUI:</label>
          <input type="text" id="DUI" name="DUI" class="campos" placeholder="00000000-0" required>
        </div>

        <div class="form-group">
          <label for="EDAD">EDAD:</label>
          <input type="number" id="EDAD" name="EDAD" class="campos" min="18" max="99" required>
        </div>

        <div class="form-group">
          <label for="TELEFONO_CORREO">CORREO ELECTRONICO O TELEFONO:</label>
          <input type="text" id="TELEFONO_CORREO" name="TELEFONO_CORREO" class="campos" required>
        </div>

        <div class="form-group">
          <label for="FECHA_INGRESO">FECHA DE INGRESO:</label>
          <input type="date" id="FECHA_INGRESO" name="FECHA_INGRESO" class="campos" required>
        </div>

        <div class="form-group">
          <label for="CONTRASEÑA">CONTRASEÑA:</label>
          <input type="password" id="CONTRASEÑA" name="CONTRASEÑA" class="campos" required>
        </div>

        <div class="boton1">
          <button type="submit" name="REGISTRARSE">REGISTRAR</button>
          <input type="reset" value="LIMPIAR">
        </div>
      </form>
      <br>
      

      <nav class="login-link">
        ¿Ya tienes cuenta? <a href="SESION.PHP">Inicia sesión aquí</a>
      </nav>
    </section>
  </main>

  <footer>
    <p class="footer-text">
      © 2025 Agroamigo. Todos los derechos reservados.
    </p>
  </footer>

</body>
</html>

<?php
if (isset($_POST ["REGISTRARSE"] )){
$NOMBRE = $_POST ["NOMBRE_APELLIDO"];
$DUI = $_POST ["DUI"];
$EDAD = $_POST ["EDAD"];
$TELEFONO_CORREO = $_POST ["TELEFONO_CORREO"];
$FECHA = $_POST ["FECHA_INGRESO"];
$ROL = $_POST ["ROLES"];
$CONTRA =$_POST ["CONTRASEÑA"];

$insertarDatos = "INSERT INTO empleados (NOMBRE_APELLIDO, DUI, EDAD, TELEFONO_CORREO, FECHA_DE_INGRESO, ROL, CONTRASEÑA) VALUES ('$NOMBRE', '$DUI', '$EDAD', '$TELEFONO_CORREO', '$FECHA', '$ROL', '$CONTRA')";
$ejecutar = mysqli_query ($enlace,$insertarDatos);

}
?>