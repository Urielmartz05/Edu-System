<?php
    // ==========================================
    // CONTROLLER: UPDATE USER PASSWORD & SECURITY
    // ==========================================

    require_once __DIR__ . "/../auth/check_auth.php";
    requireValidPost();
    requireRole([1, 2, 3, 4]);

    $loggedUser = getLoggedUser();
    $userId = (int)($loggedUser['id'] ?? 0);

    if ($userId <= 0) {
        header("Location: /index.php");
        exit;
    }

    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    $returnUrl = "../../views/profile/profile.php";

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['flash_profile_error'] = "All password fields are required.";
        header("Location: " . $returnUrl);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        $_SESSION['flash_profile_error'] = "New passwords do not match. Please verify and try again.";
        header("Location: " . $returnUrl);
        exit;
    }

    if (strlen($newPassword) < 6) {
        $_SESSION['flash_profile_error'] = "New password must be at least 6 characters long.";
        header("Location: " . $returnUrl);
        exit;
    }

    require_once __DIR__ . "/../../config/connection.php";
    $connection = createConnection();

    // Query existing password hash
    $stmt = $connection->prepare("SELECT id, password FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$userData) {
        $_SESSION['flash_profile_error'] = "User account not found.";
        header("Location: " . $returnUrl);
        exit;
    }

    $storedPassword = $userData['password'];
    $isValid = false;

    // Standard hash verification or legacy plaintext match
    $isLegacyPassword = !password_get_info($storedPassword)['algo'];
    if (password_verify($currentPassword, $storedPassword)
        || ($isLegacyPassword && hash_equals($storedPassword, $currentPassword))) {
        $isValid = true;
    }

    if (!$isValid) {
        $_SESSION['flash_profile_error'] = "Current password is incorrect.";
        header("Location: " . $returnUrl);
        exit;
    }

    // Encrypt new password using default secure algorithm (Bcrypt/Argon2)
    $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->bind_param("si", $newHashed, $userId);
    $success = $updateStmt->execute();
    $updateStmt->close();
    $connection->close();

    if ($success) {
        $_SESSION['flash_profile_success'] = "Password updated successfully. Your new password is now active.";
    } else {
        $_SESSION['flash_profile_error'] = "Database error while updating password. Please try again.";
    }

    header("Location: " . $returnUrl);
    exit;
?>
