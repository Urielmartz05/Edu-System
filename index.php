<?php
    session_start();
    $mensaje = $_SESSION['error_login'] ?? '';
    unset($_SESSION['error_login']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <?php include_once("./ui/header/header.php"); ?>

    <main class="login-main">
        <div class="login-main-container">
            <h1 class="login-title">Inicio de Sesión</h1>
            <form class="login-form" action="./src/controllers/auth/loginProcess.php" method="POST">
                <label>Email</label>
                <input type="email" name="email" placeholder="Ingresa tu correo" required>

                <label>Contraseña</label>
                <input type="password" name="password" placeholder="Ingresa tu contraseña" required>

                <button class="login-button" type="submit">Entrar</button>
            </form>

            <p class="login-message"><?php echo htmlspecialchars($mensaje); ?></p>
            <a class="login-link" href="./src/views/signup/signup.php">Crear cuenta</a>
        </div>
    </main>
</body>
</html>