<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    include_once __DIR__ . '/../../config/connection.php';
    $connection = createConnection();

    // Process authentication credentials upon POST submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();
            $stmt->close();

            $autenticado = false;
            if ($usuario) {
                if (password_verify($password, $usuario['password'])) {
                    $autenticado = true;
                } elseif (!password_get_info($usuario['password'])['algo'] && $password === $usuario['password']) {
                    $autenticado = true;
                    // Automatically migrate legacy plaintext passwords to secure hash
                    $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upd->bind_param("si", $nuevoHash, $usuario['id']);
                    $upd->execute();
                    $upd->close();
                }
            }

            if ($usuario && $autenticado) {
                $estaVerificado = ((int)($usuario['verified'] ?? 0) === 1);

                if (!$estaVerificado) {
                    $_SESSION['error_login'] = "Your account is pending verification and approval by an administrator.";
                    header("Location: ../../../index.php");
                    exit;
                }

                session_regenerate_id(true);
                unset($_SESSION['active_course_id']);
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['name'] = $usuario['name'];
                $_SESSION['lastname'] = $usuario['lastname'] ?? '';
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['role_id'] = (int)$usuario['role'];

                include_once __DIR__ . "/../role/getRoleType.php";
                $roleType = strtolower(trim(getRoleType($usuario["role"]) ?? ''));

                // Role-based destination routing
                switch ($roleType) {
                    case 'admin':
                        header("Location: ../../views/admin/adminDashboard.php");
                        break;

                    case 'director':
                        header("Location: ../../views/director/director.php");
                        break;

                    case 'teacher':
                        header("Location: ../../views/ponente/ponente.php");
                        break;

                    case 'student':
                        header("Location: ../../views/alumno/misCursos.php");
                        break;

                    default:
                        header("Location: ../../views/general/403.php");
                        break;
                }
                exit;

            } else {
                $_SESSION['error_login'] = "Invalid email or password.";
                header("Location: ../../../index.php");
                exit;
            }

        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['error_login'] = "Unable to sign in. Please try again.";
            header("Location: ../../../index.php");
            exit;
        }
    } else {
        header("Location: ../../../index.php");
        exit;
    }

?>
