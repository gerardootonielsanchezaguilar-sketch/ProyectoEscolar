<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventario</title>
  <link rel="stylesheet" href="CSS/INVENTARIO.css">
  <style>
    /* Posicionar el enlace VOLVER a la izquierda dentro del header */
    header { position: relative; }
    .volver-link {
      position: absolute;
      left: 20px;
      top: 18px;
      color: #000000ff;
      text-decoration: none;
    font-weight: 600;
      padding: 6px 10px;
      border-radius: 6px;
    }
    .volver-link:hover { opacity: 0.9; }
    @media (max-width: 600px) {
      .volver-link { left: 10px; top: 12px; }
    }
  </style>
</head>
<body>

  <header>
    <a href="CENTRO.php" class="volver-link">VOLVER</a>
    <h1>INVENTARIO</h1>
    <img src="IMG/tipo.png" alt="Logo Agroamigo" class="logo">
  
  </header>

  <main>
    <section class="contenedor">

      <article class="item aves izquierda" onclick="location.href='AVES_POSTURA.php'">
        <img src="https://img.icons8.com/ios-filled/50/000000/chicken.png" alt="Aves">
        LINEA AVES DE <br> POSTURA
      </article>

      <article class="item ganado derecha" onclick="location.href='GANADO.php'">
        LINEA DE GANADO<br>LECHERO
        <img src="https://img.icons8.com/ios-filled/50/000000/cow.png" alt="Ganado">
      </article>

      <article class="item cerdos izquierda" onclick="location.href='CERDOS.php'">
        <img src="https://img.icons8.com/ios-filled/50/000000/pig.png" alt="Cerdos">
        LINEA DE CERDOS
      </article>

      <article class="item equinos derecha" onclick="location.href='EQUINOS.php'">
        LINEA DE EQUINOS
        <img src="https://img.icons8.com/ios-filled/50/000000/horse.png" alt="Equinos">
      </article>

      <article class="item tilapia izquierda" onclick="location.href='TILAPIA.php'">
        <img src="https://img.icons8.com/ios-filled/50/000000/fish.png" alt="Tilapia">
        LINEA DE TILAPIA
      </article>

    </section>
  </main>

</body>
</html>