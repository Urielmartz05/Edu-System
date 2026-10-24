<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $mensaje = $_SESSION['error_login'] ?? '';
    unset($_SESSION['error_login']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In · Edu-System</title>
</head>
<body>

    <?php include_once __DIR__ . "/ui/header/header.php"; ?>

    <main class="login-main">
        <div class="login-main-container">
            <h1 class="login-title">Sign In</h1>
            <form class="login-form" action="./src/controllers/auth/loginProcess.php" method="POST">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>

                <button class="login-button" type="submit">Log In</button>
            </form>

            <p class="login-message"><?php echo htmlspecialchars($mensaje); ?></p>
            <a class="login-link" href="./src/views/signup/signup.php">Create an account</a>
        </div>
    </main>
</body>
</html>
