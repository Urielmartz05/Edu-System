<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Require active user session
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../../index.php");
        exit;
    }

    // Resolve human-readable role name from numerical identifier
    function getRoleName($role_id) {
        switch ((int)$role_id) {
            case 1:
                return "Administrator";
            case 2:
                return "Director";
            case 3:
                return "Teacher";
            case 4:
                return "Student";
            default:
                return "User";
        }
    }

    // Retrieve active authenticated user session data and populate profile if needed
    function getLoggedUser() {
        $user = [
            'id' => $_SESSION['user_id'] ?? '',
            'name' => $_SESSION['name'] ?? '',
            'lastname' => $_SESSION['lastname'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'role' => $_SESSION['role_id'] ?? 0,
            'role_name' => getRoleName($_SESSION['role_id'] ?? 0)
        ];

        if (empty($user['lastname']) && !empty($user['id'])) {
            try {
                include_once __DIR__ . '/../../config/connection.php';
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
                    $stmt->close();
                }
            } catch (Exception $e) {
                // Keep session fallback values
            }
        }

        return $user;
    }

?>