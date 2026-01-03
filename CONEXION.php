<?php
session_start(); 
$servidor = "localhost:3309";
$usuario = "root";
$clave = "";
$basededatos = "sistema_de_invenario";
$enlace = mysqli_connect($servidor, $usuario, $clave, $basededatos);
$enlace->set_charset("utf8");

if (isset($_POST["ingresar"])) {
    $dui = $_POST["DUI"];
    $clave = $_POST["CONTRASEÑA"];
    $usuario = $_POST["NOMBRE_APELLIDO"];
    $correonumero = $_POST["TELEFONO_CORREO"];

    if (!empty($dui) && !empty($clave) && !empty($usuario) && !empty($correonumero)) {
        $sql = $enlace->query("SELECT * FROM empleados 
                               WHERE DUI='$dui' 
                               AND CONTRASEÑA='$clave' 
                               AND NOMBRE_APELLIDO='$usuario' 
                               AND TELEFONO_CORREO='$correonumero'");
        if ($sql->num_rows > 0) {
            header("Location: CENTRO.php");
            exit;
        } else {
            
            $_SESSION['error'] = "Datos incorrectos, intenta de nuevo.";
            header("Location: SESION.php");
            exit;
        }
    }
}
?>
