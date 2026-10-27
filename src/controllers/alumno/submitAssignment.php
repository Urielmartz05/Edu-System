<?php

    // ==========================================
    // CONTROLLER: STUDENT ASSIGNMENT SUBMISSION
    // ==========================================

    require_once __DIR__ . "/../auth/check_auth.php";
    requireValidPost();
    require_once __DIR__ . "/../../models/assignmentModels/assignmentModel.php";
    require_once __DIR__ . "/../../config/connection.php";

    $user = getLoggedUser();
    $studentId = (int)($user['id'] ?? 0);
    $userRole = (int)($user['role'] ?? 0);

    // Only students (role 4) may submit assignments
    if ($studentId <= 0 || $userRole !== 4) {
        header("Location: ../../views/alumno/alumnoCourses.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../../views/alumno/alumnoAssignments.php");
        exit;
    }

    $assignmentId = (int)($_POST['assignment_id'] ?? 0);
    $submissionText = trim($_POST['submission_text'] ?? '');

    if ($assignmentId <= 0) {
        $_SESSION['flash_error'] = "Invalid assignment specified.";
        header("Location: ../../views/alumno/alumnoAssignments.php");
        exit;
    }

    $assignmentModel = new AssignmentModel();
    $assignment = $assignmentModel->getAssignmentDetails($assignmentId);

    if (!$assignment) {
        $_SESSION['flash_error'] = "The specified assignment does not exist.";
        header("Location: ../../views/alumno/alumnoAssignments.php");
        exit;
    }

    // Verify enrollment in the assignment's course
    $conn = createConnection();
    $enrollmentCheck = $conn->prepare("SELECT id FROM student WHERE id = ? AND course_id = ?");
    $enrollmentCheck->bind_param("ii", $studentId, $assignment['course_id']);
    $enrollmentCheck->execute();
    $isEnrolled = $enrollmentCheck->get_result()->fetch_assoc();
    $enrollmentCheck->close();

    if (!$isEnrolled) {
        $_SESSION['flash_error'] = "You are not enrolled in the course associated with this task.";
        header("Location: ../../views/alumno/alumnoAssignments.php");
        exit;
    }

    $filePath = null;

    if (isset($_FILES['submission_file']) && !in_array($_FILES['submission_file']['error'], [UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE], true)) {
        $_SESSION['flash_error'] = "The file upload failed. Please try again.";
        header("Location: ../../views/alumno/alumnoAssignments.php?task_id={$assignmentId}");
        exit;
    }

    // Handle student submission file upload
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['submission_file'];
        $maxFileSize = 25 * 1024 * 1024; // 25 MB

        if ($file['size'] > $maxFileSize) {
            $_SESSION['flash_error'] = "The uploaded file exceeds the 25 MB size limit.";
            header("Location: ../../views/alumno/alumnoAssignments.php?task_id={$assignmentId}");
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'png', 'jpg', 'jpeg'];

        if (!in_array($ext, $allowedExts)) {
            $_SESSION['flash_error'] = "File extension .{$ext} is not allowed. Please submit PDF, Office, ZIP, or image formats.";
            header("Location: ../../views/alumno/alumnoAssignments.php?task_id={$assignmentId}");
            exit;
        }

        $uploadDir = __DIR__ . '/../../../resources/submissions/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'sub_' . $studentId . '_' . $assignmentId . '_' . bin2hex(random_bytes(12)) . '.' . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $filePath = 'resources/submissions/' . $fileName;
        } else {
            $_SESSION['flash_error'] = "Failed to store the uploaded deliverable.";
            header("Location: ../../views/alumno/alumnoAssignments.php?task_id={$assignmentId}");
            exit;
        }
    }

    if (empty($submissionText) && empty($filePath)) {
        $_SESSION['flash_error'] = "Please provide submission text notes or upload a deliverable file.";
        header("Location: ../../views/alumno/alumnoAssignments.php?task_id={$assignmentId}");
        exit;
    }

    $success = $assignmentModel->submitAssignment($assignmentId, $studentId, $submissionText, $filePath);

    if ($success) {
        $_SESSION['flash_success'] = "Your coursework for '{$assignment['title']}' was submitted successfully!";
    } else {
        $_SESSION['flash_error'] = "Could not record your submission. Please try again.";
    }

    header("Location: ../../views/alumno/alumnoAssignments.php?task_id={$assignmentId}");
    exit;
