<?php
// Configuración de la base de datos ✅ YA AJUSTADO
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "miraculos_cafe"; // ✅ Tu BD correcta
$tabla = "donaciones";

// Conectar a la BD
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

// Solo procesar si la solicitud es por POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Obtener y limpiar datos del formulario
    $tarjeta = trim($_POST['tarjeta'] ?? '');
    $clave = trim($_POST['clave'] ?? '');
    $cantidad = trim($_POST['cantidad'] ?? '');

    // Validaciones
    $errores = [];
    
    if (!preg_match('/^\d{16}$/', preg_replace('/\s+/', '', $tarjeta))) {
        $errores[] = "Número de tarjeta debe tener 16 dígitos";
    }
    if (!preg_match('/^\d{3}$/', $clave)) {
        $errores[] = "La clave CVV debe tener 3 dígitos";
    }
    if (!is_numeric($cantidad) || $cantidad < 1) {
        $errores[] = "La cantidad debe ser mayor a 1 MXN";
    }

    if (!empty($errores)) {
        foreach ($errores as $e) {
            echo "<p style='color:red;'>$e</p>";
        }
        echo "<a href='javascript:history.back()'>← Volver</a>";
        exit;
    }

    // Preparar consulta
    $stmt = $conexion->prepare("INSERT INTO $tabla (numero_tarjeta, cvv, cantidad, fecha) VALUES (?, ?, ?, NOW())");
    
    // Enmascarar tarjeta
    $ultimos_4 = substr(preg_replace('/\s+/', '', $tarjeta), -4);
    $tarjeta_mascara = "**** **** **** " . $ultimos_4;
    
    $stmt->bind_param("ssd", $tarjeta_mascara, $clave, $cantidad);

    if ($stmt->execute()) {
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Donación Exitosa</title>
    <link href='https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;600&display=swap' rel='stylesheet'>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #fff0f5; text-align: center; padding: 3rem; }
        h1 { font-family: 'Pacifico', cursive; color: #d63384; }
        .exito { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: inline-block; }
        .boton { display: inline-block; margin-top: 1rem; padding: 0.7rem 1.5rem; background: #d63384; color: white; border-radius: 10px; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class='exito'>
        <h1>✅ ¡Gracias por tu donación!</h1>
        <p>Tu aporte de <strong>\$" . number_format($cantidad, 2) . " MXN</strong> ha sido registrado.</p>
        <p>Apoyas a los animales 🐶🐱</p>
        <a href='donaciones.html' class='boton'>Volver a donaciones</a>
    </div>
</body>
</html>";
    } else {
        echo "<p style='color:red;'>❌ Error al guardar: " . $stmt->error . "</p>";
    }

    $stmt->close();
} else {
    echo "<p>Acceso no permitido</p>";
}

$conexion->close();
?>