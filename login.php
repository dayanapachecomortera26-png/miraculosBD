<?php
session_start();

$servidor = "localhost";
$usuario_bd = "root";
$pass_bd = "";
$nombre_bd = "miraculos_cafe";

$conexion = new mysqli($servidor, $usuario_bd, $pass_bd, $nombre_bd);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$correo = trim($_POST['correo'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

if (empty($correo) || empty($contrasena)) {
    echo "<script>alert('Completa todos los campos'); history.back();</script>";
    exit;
}

$consulta = $conexion->prepare("SELECT contrasena FROM cuenta WHERE correo = ? LIMIT 1");
$consulta->bind_param("s", $correo);
$consulta->execute();
$consulta->store_result();

if ($consulta->num_rows > 0) {
    // ✅ SÍ existe el usuario
    $consulta->bind_result($contrasena_guardada);
    $consulta->fetch();

    if ($contrasena === $contrasena_guardada) {
        // ✅ Todo correcto → va a menu.html
        $_SESSION['correo'] = $usuario;
        header("Location: menu.html");
        exit;
    } else {
        echo "<script>alert('Contraseña incorrecta ❌'); history.back();</script>";
    }
} else {
    // ❌ NO está registrado → avisa y vuelve
    echo "<script>alert('Este usuario no está registrado 📝'); history.back();</script>";
}

$consulta->close();
$conexion->close();
?>