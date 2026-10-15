<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

function getRoleName($role_id) {
    switch ((int)$role_id) {
        case 1:
            return "Administrador";
        case 2:
            return "Alumno";
        case 3:
            return "Director";
        case 4:
            return "Ponente";
        default:
            return "Usuario";
    }
}

function getLoggedUser() {
    $user = [
        'id' => $_SESSION['user_id'] ?? '',
        'name' => $_SESSION['name'] ?? '',
        'lastname' => $_SESSION['lastname'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role_id'] ?? 0,
        'role_name' => getRoleName($_SESSION['role_id'] ?? 0)
    ];

    // Si falta el apellido u otros datos en la sesión, consultamos a la base de datos
    if (empty($user['lastname']) && !empty($user['id'])) {
        try {
            include_once __DIR__ . '/../connection.php';
            $conn = createConnection();
            $stmt = $conn->prepare("SELECT id, name, lastname, email, role FROM users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $user['name'] = $row['name'];
                    $user['lastname'] = $row['lastname'];
                    $user['email'] = $row['email'];
                    $user['role'] = $row['role'];
                    $user['role_name'] = getRoleName($row['role']);
                    $_SESSION['lastname'] = $row['lastname'];
                }
            }
        } catch (Exception $e) {
            // Continuar con los datos en sesión
        }
    }

    return $user;
}
?>
