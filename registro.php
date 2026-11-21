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
          background-color: #f0f0f0;
      }
      .contenedor {
          background-color: white;
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
          width: 300px;
          text-align: center;
      }
      .campo {
          margin-bottom: 15px;
          width: 100%;
          text-align: left;
      }
      .campo label {
          display: block;
          margin-bottom: 5px;
          color: #555;
          font-weight: bold;
      }
      .campo input {
          width: 100%;
          padding: 10px;
          border: 1px solid #ccc;
          border-radius: 5px;
          box-sizing: border-box;
      }
      .boton {
          padding: 10px 20px;
          border: none;
          border-radius: 5px;
          background-color: #9333EA;
          color: white;
          cursor: pointer;
          font-size: 16px;
          width: 100%;
      }
      .boton:hover {
          background-color: #7E22CE;
      }
      .boton-regresar {
          margin-top: 10px;
          background-color: #6c757d;
      }
      .exito {
          color: green;
          margin-top: 10px;
      }
      .error {
          color: red;
          margin-top: 10px;
      }
  </style>
</head>
<body>
<div class="contenedor">
  <h2>Crear Cuenta</h2>
  <form method="post" action="">
      <div class="campo">
          <label>Nombre</label>
          <input type="text" name="nombre" placeholder="Tu nombre completo" required>
      </div>
      <div class="campo">
          <label>Correo Electrónico</label>
          <input type="email" name="correo" placeholder="tu@correo.com" required>
      </div>
      <div class="campo">
          <label>Contraseña</label>
          <input type="password" name="contrasena" placeholder="Crea tu contraseña" required>
      </div>
      <div class="campo">
          <label>Fecha y Hora de Registro</label>
          <input type="datetime-local" name="fecha_registro" required>
      </div>
      
      <input type="submit" name="register" class="boton" value="Crear Cuenta">
  </form>
  
  <?php 
      $servidor = "localhost";
      $usuario_bd = "root";
      $pass_bd = "";
      $base_bd = "miraculos_cafe";
      
      $conex = mysqli_connect($servidor, $usuario_bd, $pass_bd, $base_bd);
      
      if (!$conex) {
          echo "<p class='error'>Error de conexión: " . mysqli_connect_error() . "</p>";
      } else {
          mysqli_set_charset($conex, "utf8");
      }
      if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
          $nombre = trim($_POST['nombre']);
          $correo = trim($_POST['correo']);
          $contrasena = trim($_POST['contrasena']);
          $fecha_registro = trim($_POST['fecha_registro']);
          
          $verificar = mysqli_prepare($conex, "SELECT id FROM cuenta WHERE correo = ?");
          mysqli_stmt_bind_param($verificar, "s", $correo);
          mysqli_stmt_execute($verificar);
          mysqli_stmt_store_result($verificar);
          
          if (mysqli_stmt_num_rows($verificar) > 0) {
              echo "<p class='error'>Ese correo ya está registrado.</p>";
              mysqli_stmt_close($verificar);
          } else {
              mysqli_stmt_close($verificar);
              
              $insertar = mysqli_prepare($conex, "INSERT INTO cuenta (nombre, correo, contrasena, fecha_registro) VALUES (?, ?, ?, ?)");
              mysqli_stmt_bind_param($insertar, "ssss", $nombre, $correo, $contrasena, $fecha_registro);
              
              if (mysqli_stmt_execute($insertar)) {
                  // ✅ Redirige a login.html tras crear la cuenta
                  echo "<p class='exito'>¡Cuenta creada exitosamente! Iniciando sesión...</p>";
                  echo "<script>setTimeout(function(){window.location.href='login.html';}, 2000);</script>";
              } else {
                  echo "<p class='error'>Error al guardar: " . mysqli_error($conex) . "</p>";
              }
              mysqli_stmt_close($insertar);
          }
      }
  ?>
  <button class="boton boton-regresar" onclick="window.location.href='login.html'">Regresar</button>
</div>
</body>
</html>