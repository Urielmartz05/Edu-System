<?php
    include_once __DIR__ . "/../../config/connection.php";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email · EduX</title>
    <link rel="stylesheet" href="verificar.css">
</head>
<body>
 
    <?php include_once __DIR__ . '/../../../ui/header/header.php'; ?>

    <main>
        <div class="verify-card">
            <?php
                if ($resultado && $resultado->num_rows === 1) {
                    $usuario = $resultado->fetch_assoc();
                    $sql = "UPDATE users 
                            SET verified = 1,
                            token_verificacion = NULL
                            WHERE id = ?";
                    
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param("i", $usuario["id"]);
                    $stmt->execute();

                    echo "<h1>Email Confirmed</h1>";
                    echo "<p style='color:#64748b; margin:10px 0 20px;'>Your account has been activated successfully.</p>";
                    echo "<a href='../../../index.php' class='verify-link'>Sign In to Your Account</a>";
                    
                } else {

                    echo "<div class='error-box'>";
                    echo "<h1>Invalid or Expired Token</h1>";
                    echo "<p style='color:#64748b; margin:10px 0 20px;'>This verification link is invalid or has already been used.</p>";
                    echo "<a href='../../../index.php'>Return to Home</a>";
                    echo "</div>";
                }
            ?>
        </div>
    </main>

</body>
</html>
