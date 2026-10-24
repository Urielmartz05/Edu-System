<?php
    require_once __DIR__ . '/../auth/check_auth.php';
    $user = getLoggedUser();

    $roleId = (int)($user['role'] ?? ($_SESSION['role_id'] ?? 0));
    if ($roleId !== 1) {
        header("Location: ../../views/general/403.php");
        exit;
    }

    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    $coursesModel = new AdminCourses();

    $courseId = (int)($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
    $returnUrl = $_GET['return_url'] ?? $_POST['return_url'] ?? '../../views/admin/adminCourses.php';

    if ($courseId > 0) {
        if ($coursesModel->toggleCourseStatus($courseId)) {
            $_SESSION['flash_success'] = "Course publication status updated successfully!";
        } else {
            $_SESSION['flash_error'] = "Failed to update course status.";
        }
    }

    // Clean safe redirect within admin views
    if (strpos($returnUrl, 'adminCourseContent.php') !== false) {
        header("Location: ../../views/admin/adminCourseContent.php?course_id=" . urlencode($courseId));
    } else {
        header("Location: ../../views/admin/adminCourses.php");
    }
    exit;
