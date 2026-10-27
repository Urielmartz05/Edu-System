<?php
    require_once __DIR__ . '/../auth/check_auth.php';
    requireValidPost();
    requireRole([1]);

    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    $coursesModel = new AdminCourses();

    $courseId = (int)($_POST['course_id'] ?? $_GET['course_id'] ?? 0);
    $returnUrl = $_POST['return_url'] ?? $_GET['return_url'] ?? '../../views/admin/adminCourses.php';

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
