<?php

    // ==========================================
    // CONTROLLER: COURSE AND MODULE ACTIONS (ADMIN)
    // ==========================================

    require_once __DIR__ . '/../auth/check_auth.php';
    $user = getLoggedUser();

    // Verify Administrator role authorization (Role 1)
    $roleId = (int)($user['role'] ?? ($_SESSION['role_id'] ?? 0));
    if ($roleId !== 1) {
        header("Location: ../../views/general/403.php");
        exit;
    }

    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    $coursesModel = new AdminCourses();

    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $courseId = (int)($_POST['course_id'] ?? $_GET['course_id'] ?? 0);

    switch ($action) {

        // Update curricular specifications for a course
        case 'update_course':
            $courseName = trim($_POST['course_name'] ?? '');
            $departmentId = (int)($_POST['department_id'] ?? 0);
            $credits = (int)($_POST['credits'] ?? 0);
            $status = isset($_POST['status']) ? (int)$_POST['status'] : null;

            if ($courseId <= 0 || empty($courseName) || $departmentId <= 0 || $credits <= 0) {
                $_SESSION['flash_error'] = "All course fields are required.";
                header("Location: ../../views/admin/adminCourses.php?error=missing_fields");
                exit;
            }

            if ($coursesModel->updateCourse($courseId, $courseName, $departmentId, $credits, $status)) {
                $_SESSION['flash_success'] = "Course \"{$courseName}\" successfully updated.";
            } else {
                $_SESSION['flash_error'] = "Failed to update course specifications.";
            }

            $returnUrl = !empty($_POST['return_url']) ? $_POST['return_url'] : '../../views/admin/adminCourses.php';
            header("Location: " . $returnUrl);
            exit;

        // Cascade delete course and all associated blocks/enrollments
        case 'delete_course':
            if ($courseId <= 0) {
                $_SESSION['flash_error'] = "Invalid course identifier.";
                header("Location: ../../views/admin/adminCourses.php");
                exit;
            }

            $course = $coursesModel->getCourseDetails($courseId);
            $cName = $course ? $course['course_name'] : "#{$courseId}";

            if ($coursesModel->deleteCourse($courseId)) {
                $_SESSION['flash_success'] = "Course \"{$cName}\" and all associated records have been removed.";
            } else {
                $_SESSION['flash_error'] = "Could not delete course #{$courseId}.";
            }

            header("Location: ../../views/admin/adminCourses.php");
            exit;

        // Delete an individual module/block from the curriculum
        case 'delete_block':
            $blockId = (int)($_POST['block_id'] ?? $_GET['block_id'] ?? 0);
            if ($blockId <= 0) {
                $_SESSION['flash_error'] = "Invalid module identifier.";
                header("Location: ../../views/admin/adminCourseContent.php?course_id=" . urlencode($courseId));
                exit;
            }

            if ($coursesModel->deleteBlock($blockId)) {
                $_SESSION['flash_success'] = "Module #{$blockId} successfully deleted.";
            } else {
                $_SESSION['flash_error'] = "Failed to remove course module.";
            }

            header("Location: ../../views/admin/adminCourseContent.php?course_id=" . urlencode($courseId));
            exit;

        default:
            header("Location: ../../views/admin/adminCourses.php");
            exit;
    }

?>