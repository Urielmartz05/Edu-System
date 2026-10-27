<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Require active user session
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../../index.php");
        exit;
    }

    function requireRole(array $roles) {
        $user = getLoggedUser();
        if (!in_array((int)$user['role'], $roles, true)) {
            http_response_code(403);
            exit('Access denied.');
        }
    }

    function csrfField() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
    }

    function resourceLinkAttributes($path, $resourceType, $resourceId) {
        $root = realpath(__DIR__ . '/../../../resources');
        $file = realpath(__DIR__ . '/../../../' . $path);
        if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) {
            return 'aria-disabled="true"';
        }
        if (!in_array($resourceType, ['assignment', 'submission'], true) || (int)$resourceId <= 0) {
            return 'aria-disabled="true"';
        }
        $url = '/src/controllers/files/downloadResource.php?' . http_build_query([
            'type' => $resourceType, 'id' => (int)$resourceId
        ]);
        return 'href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"';
    }

    function requireValidPost() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('POST required.');
        }
        $token = $_POST['csrf_token'] ?? null;
        if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            exit('Invalid form token. Reload the page and try again.');
        }
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

    // Refresh authorization once per request so role/account changes take effect immediately.
    function getLoggedUser() {
        static $user = null;
        if ($user !== null) {
            return $user;
        }
        require_once __DIR__ . '/../../config/connection.php';
        $connection = createConnection();
        $id = (int)($_SESSION['user_id'] ?? 0);
        $sql = "SELECT id, name, lastname, email, role, verified FROM users WHERE id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $connection->close();
        if (!$row || (int)$row['verified'] !== 1) {
            $_SESSION = [];
            session_destroy();
            header('Location: /index.php');
            exit;
        }
        $user = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'lastname' => $row['lastname'] ?? '',
            'email' => $row['email'],
            'role' => (int)$row['role'],
            'role_name' => getRoleName($row['role'])
        ];
        $_SESSION['name'] = $user['name'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role_id'] = $user['role'];
        return $user;
    }
