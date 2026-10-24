<?php

    // ==========================================
    // CONTROLLER: USER MANAGEMENT ACTIONS (ADMIN)
    // ==========================================

    require_once __DIR__ . '/../auth/check_auth.php';
    $user = getLoggedUser();

    // Verify Administrator role authorization (Role 1)
    $roleId = (int)($user['role'] ?? ($_SESSION['role_id'] ?? 0));
    if ($roleId !== 1) {
        header("Location: ../../views/general/403.php");
        exit;
    }

    require_once __DIR__ . '/../../models/adminModels/adminUsersModel.php';
    $usersModel = new AdminUsers();

    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? $_GET['user_id'] ?? 0);

    if ($userId <= 0) {
        $_SESSION['flash_error'] = "Invalid user identifier.";
        header("Location: ../../views/admin/adminUsers.php");
        exit;
    }

    switch ($action) {

        // Manually activate and verify user account
        case 'verify':
            if ($usersModel->verifyUser($userId)) {
                $_SESSION['flash_success'] = "User account #{$userId} verified and activated successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to update verification status.";
            }
            break;

        // Reset account password to standard temporary password
        case 'reset_password':
            $defaultPassword = 'password123';
            if ($usersModel->resetPassword($userId, $defaultPassword)) {
                $_SESSION['flash_success'] = "Password for user #{$userId} reset to '{$defaultPassword}'.";
            } else {
                $_SESSION['flash_error'] = "Failed to reset password for user #{$userId}.";
            }
            break;

        // Update profile data, academic role, and department assignment
        case 'update_user':
            $name = trim($_POST['name'] ?? '');
            $lastname = trim($_POST['lastname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = (int)($_POST['role'] ?? 0);
            $verified = isset($_POST['verified']) ? (int)$_POST['verified'] : 1;
            $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;

            if (empty($name) || empty($email) || $role <= 0) {
                $_SESSION['flash_error'] = "First name, institutional email, and academic role are required.";
                header("Location: ../../views/admin/adminUsers.php?error=missing_fields");
                exit;
            }

            $result = $usersModel->updateUser($userId, $name, $lastname, $email, $role, $verified, $departmentId);
            if ($result['success']) {
                $_SESSION['flash_success'] = "Profile for user \"{$name} {$lastname}\" (#{$userId}) updated successfully.";
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? "Failed to update user profile.";
            }
            break;

        // Delete user account with foreign key and safety validation
        case 'delete_user':
            $result = $usersModel->deleteUser($userId, (int)$user['id']);
            if ($result['success']) {
                $_SESSION['flash_success'] = "User account #{$userId} deleted successfully.";
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? "Failed to delete user account.";
            }
            break;

        default:
            break;
    }

    header("Location: ../../views/admin/adminUsers.php");
    exit;

?>