<?php

    include_once('../../config/connection.php');
    $connection = createConnection();
    session_start();
    $mensaje = '';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        try {
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();

            $autenticado = false;
            if ($usuario) {
                if (password_verify($password, $usuario['password'])) {
                    $autenticado = true;
                } elseif ($password === $usuario['password']) {
                    $autenticado = true;
                    $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upd->bind_param("si", $nuevoHash, $usuario['id']);
                    $upd->execute();
                }
            }

            if ($usuario && $autenticado) {
                if (isset($usuario['email_verificado']) && (int)$usuario['email_verificado'] === 0) {
                    $_SESSION['error_login'] = "Debes confirmar tu correo antes de iniciar sesión.";
                    header("Location: ../../../index.php");
                    exit;
                }

                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['name'] = $usuario['name'];
                $_SESSION['lastname'] = $usuario['lastname'] ?? '';
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['role_id'] = $usuario['role'];

                switch ($usuario["role"]) {

                    // Admin
                    case 1:
                        header("Location: ../../views/admin/adminDashboard.php");
                        break;

                    // alumno
                    case 2:
                        header("Location: ../../views/alumno/alumno.php");
                        break;

                    // director
                    case 3:
                        header("Location: ../../views/director/director.php");
                        break;

                    // ponente
                    case 4:
                        header("Location: ../../views/ponente/ponente.php");
                        break;

                    default:
                        $_SESSION['error_login'] = "Rol de usuario no asignado o inválido.";
                        header("Location: ../../../index.php");
                        break;
                }

                exit;

            } else {
                $_SESSION['error_login'] = "Correo o contraseña incorrectos";
                header("Location: ../../../index.php");
                exit;
            }

        } catch (Exception $e) {
            $_SESSION['error_login'] = "Error al iniciar sesión: " . $e->getMessage();
            header("Location: ../../../index.php");
            exit;
        }
    } else {
        header("Location: ../../../index.php");
        exit;
    }
?>