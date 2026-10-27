<?php

    // ==========================================
    // CONTROLLER: FACULTY FILE MANAGEMENT
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
    // ACTION: UPLOAD LEARNING RESOURCE
    // ==========================================
    if ($action === 'upload') {
        $blockId = (int)($_POST['block_id'] ?? 0);
        $fileTitle = trim($_POST['file_title'] ?? '');

        if ($courseId <= 0 || $blockId <= 0 || !isset($_FILES['file'])) {
            $_SESSION['flash_error'] = "Invalid upload parameters.";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        // Verify that the instructor owns this block
        if (!$teacherCoursesModel->isTeacherOfBlock($teacherId, $blockId)) {
            $_SESSION['flash_error'] = "You do not have permission to attach resources to this module.";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = "File upload failed (Code: {$file['error']}).";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        // Maximum allowed file size: 25 MB
        if ($file['size'] > 25 * 1024 * 1024) {
            $_SESSION['flash_error'] = "File exceeds maximum permitted size of 25 MB.";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        // Allowed file extensions for course materials
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'png', 'jpg', 'jpeg'];
        if (!in_array($ext, $allowedExts)) {
            $_SESSION['flash_error'] = "Extension .{$ext} is not allowed. Supported formats: PDF, Office, ZIP, and Images.";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        // Sanitize file title and create unique physical storage path
        $displayTitle = !empty($fileTitle) ? ($fileTitle . '.' . $ext) : $file['name'];
        $cleanBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($displayTitle, PATHINFO_FILENAME));
        $storedFileName = $cleanBaseName . '_' . bin2hex(random_bytes(12)) . '.' . $ext;

        $targetDir = __DIR__ . '/../../../resources/documents/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $targetPath = $targetDir . $storedFileName;
        $dbPath = 'resources/documents/' . $storedFileName;

        // Move uploaded file and persist record in database
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $inserted = $teacherCoursesModel->addFileToBlock($teacherId, $blockId, $displayTitle, $dbPath);
            if ($inserted) {
                $_SESSION['flash_success'] = "Resource '{$displayTitle}' has been uploaded successfully.";
            } else {
                @unlink($targetPath);
                $_SESSION['flash_error'] = "Database error while attaching resource.";
            }
        } else {
            $_SESSION['flash_error'] = "Failed to store the uploaded file in directory.";
        }

        header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
        exit;
    }

    // ==========================================
    // ACTION: DELETE ATTACHED RESOURCE
    // ==========================================
    if ($action === 'delete') {
        $fileId = (int)($_REQUEST['file_id'] ?? 0);
        if ($fileId <= 0 || $courseId <= 0) {
            $_SESSION['flash_error'] = "Invalid delete parameters.";
            header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
            exit;
        }

        $deleted = $teacherCoursesModel->deleteFileFromBlock($teacherId, $fileId, $userRole);
        if ($deleted) {
            $_SESSION['flash_success'] = "Learning resource has been removed successfully.";
        } else {
            $_SESSION['flash_error'] = "Could not delete file or permission denied.";
        }

        header("Location: ../../views/ponente/ponenteCourseContent.php?course_id={$courseId}");
        exit;
    }

    header("Location: ../../views/ponente/ponenteCourses.php");
    exit;

?>
