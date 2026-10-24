<?php

    // ==========================================
    // CONTROLLER: CREATE COURSE BLOCK (ADMIN)
    // ==========================================

    require_once __DIR__ . '/../auth/check_auth.php';
    $user = getLoggedUser();

    // Verify Administrator role authorization (Role 1)
    $roleId = (int)($user['role'] ?? ($_SESSION['role_id'] ?? 0));
    if ($roleId !== 1) {
        header("Location: ../../views/general/403.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../../views/admin/adminCourses.php");
        exit;
    }

    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    $coursesModel = new AdminCourses();

    $courseId = (int)($_POST['course_id'] ?? 0);
    $blockName = trim($_POST['block_name'] ?? '');
    $teacherId = (int)($_POST['teacher_id'] ?? 0);

    if ($courseId <= 0 || empty($blockName) || $teacherId <= 0) {
        $_SESSION['flash_error'] = "Block title and assigned instructor are required.";
        header("Location: ../../views/admin/adminCourseContent.php?course_id=" . urlencode($courseId) . "&error=missing_fields");
        exit;
    }

    // Register the new curricular block
    $blockId = $coursesModel->createBlock($courseId, $blockName, $teacherId);
    if ($blockId) {
        $_SESSION['flash_success'] = "Module \"{$blockName}\" successfully added to course.";
    } else {
        $_SESSION['flash_error'] = "Database error while adding module.";
    }

    header("Location: ../../views/admin/adminCourseContent.php?course_id=" . urlencode($courseId));
    exit;

?>