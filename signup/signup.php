<?php
    include_once "../connection.php";
    $conexion = createConnection();

    $mensaje = "";
    $tokenConfirmacion = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = (int)trim($_POST["id"]);
        $nombre = trim($_POST["nombre"]);
        $lastname = trim($_POST["lastname"] ?? "");
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $role = (int) trim($_POST["role"]);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $tokenConfirmacion = bin2hex(random_bytes(16));

        $sql = "INSERT INTO users 
            (id, name, lastname, email, password, role, token_verificacion, email_verificado) VALUES 
            (?,?,?,?,?,?,?,0)";

        try {
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("issssis", $id, $nombre, $lastname, $email, $passwordHash, $role, $tokenConfirmacion);
            
            if ($stmt->execute()) {
                $mensaje = "Usuario registrado correctamente. Confirma tu correo para activar tu cuenta.";
            } else {
                $mensaje = "No se pudo registrar usuario";
                $tokenConfirmacion = "";
            }
        } catch (mysqli_sql_exception $e) {
            $tokenConfirmacion = "";
            if ($e->getCode() === 1062) {
                $mensaje = "El número de control o correo electrónico ya está registrado.";
            } else {
                $mensaje = "No se pudo registrar usuario: " . $e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de alumno</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>

    <?php include_once('../ui/header/header.php') ?>
    
    <main class="registro-main">
        <div class="registro-main-container">
            <div class="registro-columna-izquierda">
                <h1 class="registro-title">Registro de alumno</h1>
                <form class="registro-form" method="POST">
                    <label>Numero de control:</label>
                    <input type="number" name="id" placeholder="Número de control" required>

                    <label>Nombre:</label>
                    <input type="text" name="nombre" placeholder="Nombre" required>

                    <label>Apellidos:</label>
                    <input type="text" name="lastname" placeholder="Apellidos" required>

                    <label>Email:</label>
                    <input type="email" name="email" placeholder="Correo electrónico" required>

                    <label>Rol:</label>
                    <select name="role" required>
                        <option value="">Selecciona un rol</option>
                        <option value="2">Alumno</option>
                        <option value="3">Director</option>
                        <option value="4">Ponente</option>
                    </select>

                    <label>Password:</label>
                    <input type="password" name="password" placeholder="Contraseña" required>

                    <button class="registro-button" type="submit">Registrarme</button>
                </form>

                <?php if (!empty($mensaje)): ?>
                    <p class="registro-message"><?php echo htmlspecialchars($mensaje); ?></p>
                <?php endif; ?>

                <?php if (!empty($tokenConfirmacion)): ?>
                    <p class="registro-message">
                        Para confirmar tu correo:
                        <a class="registro-link" href="../verificar/verificar.php?token=<?php echo urlencode($tokenConfirmacion); ?>">
                            Confirmar email
                        </a>
                    </p>
                <?php endif; ?>

                <a class="registro-link" href="../index.php">Iniciar sesión</a>
            </div>

            <div class="registro-columna-derecha"></div>
        </div>
    </main>

</body>
</html>
