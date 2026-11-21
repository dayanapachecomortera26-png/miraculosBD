<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear Cuenta</title>
  <style>
      body {
          display: flex;
          justify-content: center;
          align-items: center;
          height: 100vh;
          margin: 0;
          background-color: #fce4ec; /* tono rosita suave */
      }
      .contenedor {
          background-color: white;
          padding: 25px;
          border-radius: 12px;
          box-shadow: 0 4px 15px rgba(233, 30, 99, 0.15);
          width: 320px;
          text-align: center;
      }
      h2 {
          color: #ad1457;
          margin-bottom: 20px;
      }
      .campo {
          margin-bottom: 15px;
          width: 100%;
          text-align: left;
      }
      .campo label {
          display: block;
          margin-bottom: 5px;
          color: #880e4f;
          font-weight: bold;
      }
      .campo input {
          width: 100%;
          padding: 10px;
          border: 1px solid #f8bbd0;
          border-radius: 8px;
          box-sizing: border-box;
          outline: none;
      }
      .campo input:focus {
          border-color: #ec407a;
      }
      .boton {
          padding: 11px 20px;
          border: none;
          border-radius: 8px;
          background-color: #ec407a;
          color: white;
          cursor: pointer;
          font-size: 16px;
          width: 100%;
          font-weight: bold;
      }
      .boton:hover {
          background-color: #d81b60;
      }
      .boton-regresar {
          margin-top: 10px;
          background-color: #f48fb1;
      }
      .boton-regresar:hover {
          background-color: #f06292;
      }
      .mensaje {
          margin-top: 12px;
          font-weight: bold;
      }
      .exito {
          color: #2e7d32;
      }
      .error {
          color: #c62828;
      }
  </style>
</head>
<body>
<div class="contenedor">
  <h2>Crear Cuenta</h2>
  <form method="post" action="">
      <div class="campo">
          <label>Correo Electrónico</label>
          <input type="email" name="correo" placeholder="tu@correo.com" required>
      </div>
      <div class="campo">
          <label>Contraseña</label>
          <input type="password" name="contrasena" placeholder="Crea tu contraseña" required>
      </div>
      
      <input type="submit" name="register" class="boton" value="Crear Cuenta">
  </form>
  
  <?php
  session_start();
  $servidor = "localhost";
  $usuario_bd = "root";
  $pass_bd = "";
  $nombre_bd = "miraculos_cafe";

  $conexion = new mysqli($servidor, $usuario_bd, $pass_bd, $nombre_bd);
  if ($conexion->connect_error) {
      die("<p class='mensaje error'>Error de conexión: " . $conexion->connect_error . "</p>");
  }
  $conexion->set_charset("utf8");

  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
      $usuario = trim($_POST["correo"] ?? "");
      $contrasena = trim($_POST["contrasena"] ?? "");

      if (empty($usuario) || empty($contrasena)) {
          echo "<script>alert('Completa todos los campos'); history.back();</script>";
          exit;
      }

      // Verificar si el correo ya existe en la tabla cuenta
      $consulta = $conexion->prepare("SELECT usuario FROM cuenta WHERE usuario = ? LIMIT 1");
      $consulta->bind_param("s", $usuario);
      $consulta->execute();
      $consulta->store_result();

      if ($consulta->num_rows > 0) {
          echo "<script>alert('Ya estás registrado ✅ Inicia sesión'); window.location='login.html';</script>";
          $consulta->close();
          exit;
      }
      $consulta->close();

      // Insertar en la tabla cliente
      $insertar = $conexion->prepare("INSERT INTO cliente (usuario, contrasena) VALUES (?, ?)");
      $insertar->bind_param("ss", $usuario, $contrasena);

      if ($insertar->execute()) {
          echo "<script>alert('✅ Registro exitoso! Ahora inicia sesión'); window.location='login.html';</script>";
      } else {
          echo "<script>alert('❌ Error: " . $conexion->error . "'); history.back();</script>";
      }

      $insertar->close();
  }

  $conexion->close();
  ?>

  <button class="boton boton-regresar" onclick="window.location.href='login.html'">Regresar</button>
</div>
</body>
</html>