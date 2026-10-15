<?php
    include_once "../connection.php";
    $conexion = createConnection();

    $token = trim($_GET["token"] ?? "");
    $resultado = null;

    if (!empty($token)) {
        $sql = "SELECT id FROM users WHERE token_verificacion = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Email</title>
    <link rel="stylesheet" href="verificar.css">
</head>
<body>
 
    <?php include_once('../ui/header/header.php') ?>

    <main>
        <div class="verify-card">
            <?php
                if ($resultado && $resultado->num_rows === 1) {
                    $usuario = $resultado->fetch_assoc();
                    $sql = "UPDATE users 
                            SET email_verificado = 1,
                            token_verificacion = NULL
                            WHERE id = ?";
                    
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param("i", $usuario["id"]);
                    $stmt->execute();

                    echo "<h1>Email confirmado</h1>";
                    echo "<a href='../index.php' class='verify-link'>Iniciar Sesión</a>";
                    
                } else {

                    echo "<div class='error-box'>";
                    echo "<h1>Token inválido o expirado</h1>";
                    echo "<a href='../index.php'>Volver al inicio</a>";
                    echo "</div>";
                }
            ?>
        </div>
    </main>

</body>
</html> 
