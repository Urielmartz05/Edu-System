<?php
    require_once("../../controllers/auth/check_auth.php");
    $user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Director</title>
    <link rel="stylesheet" href="/ui/dashboard.css">
</head>
<body>
    <?php include_once("../../../ui/header/header.php"); ?>

    <main class="dashboard-main">
        <div class="user-card">
            <div class="user-card-header">
                <h1 class="user-card-title">Panel de Director</h1>
                <p class="user-card-subtitle">Datos del usuario</p>
            </div>

            <div class="user-info-list">
                <div class="user-info-item">
                    <span class="user-info-label">Número de control / ID</span>
                    <span class="user-info-value"><?php echo htmlspecialchars($user['id']); ?></span>
                </div>

                <div class="user-info-item">
                    <span class="user-info-label">Nombre</span>
                    <span class="user-info-value"><?php echo htmlspecialchars($user['name']); ?></span>
                </div>

                <div class="user-info-item">
                    <span class="user-info-label">Apellidos</span>
                    <span class="user-info-value"><?php echo htmlspecialchars($user['lastname']); ?></span>
                </div>

                <div class="user-info-item">
                    <span class="user-info-label">Correo electrónico</span>
                    <span class="user-info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>

                <div class="user-info-item">
                    <span class="user-info-label">Tipo de usuario</span>
                    <span class="user-info-value">
                        <span class="badge-role"><?php echo htmlspecialchars($user['role_name']); ?></span>
                    </span>
                </div>
            </div>
        </div>
    </main>
</body>
</html>