<?php
    // ==========================================
    // CONTROLLER: FACULTY COURSE LINKS MANAGEMENT
    // ==========================================

    require_once __DIR__ . "/../auth/check_auth.php";
    requireValidPost();
    requireRole([1, 2, 3]);
    require_once __DIR__ . "/../../models/teacherModels/teacherCoursesModel.php";

    $user = getLoggedUser();
    $teacherId = (int)($user['id'] ?? 0);
    $userRole = (int)($user['role'] ?? 0);

    $teacherCoursesModel = new TeacherCourses();
    $action = $_POST['action'] ?? ($_REQUEST['action'] ?? '');
    $courseId = (int)($_POST['course_id'] ?? ($_REQUEST['course_id'] ?? 0));

    // ==========================================
    // ACTION: ADD EXTERNAL REFERENCE LINK
    // ==========================================
    if ($action === 'add') {
        $blockId = (int)($_POST['block_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $linkType = trim($_POST['link_type'] ?? 'external');

        if ($courseId <= 0 || $blockId <= 0 || empty($title) || empty($url)) {
            $_SESSION['flash_error'] = "Module, title, and valid URL are required.";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL) || (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://'))) {
            $_SESSION['flash_error'] = "Please provide a valid web URL starting with http:// or https://";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        $allowedTypes = ['video', 'document', 'external', 'other'];
        if (!in_array($linkType, $allowedTypes)) {
            $linkType = 'external';
        }

        $inserted = $teacherCoursesModel->addLinkToBlock($teacherId, $blockId, $title, $url, $linkType);
        if ($inserted) {
            $_SESSION['flash_success'] = "Resource link '{$title}' added successfully.";
        } else {
            $_SESSION['flash_error'] = "Could not attach link or you do not have permission for this module.";
        }

        header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
        exit;
    }

    // ==========================================
    // ACTION: DELETE EXTERNAL LINK
    // ==========================================
    if ($action === 'delete') {
        $linkId = (int)($_REQUEST['link_id'] ?? 0);
        if ($linkId <= 0 || $courseId <= 0) {
            $_SESSION['flash_error'] = "Invalid link deletion parameters.";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        $deleted = $teacherCoursesModel->deleteLinkFromBlock($teacherId, $linkId, $userRole);
        if ($deleted) {
            $_SESSION['flash_success'] = "External resource link has been removed.";
        } else {
            $_SESSION['flash_error'] = "Could not remove link or permission denied.";
        }

        header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
        exit;
    }

    header("Location: ../../views/ponente/ponenteCourses.php");
    exit;
?>
