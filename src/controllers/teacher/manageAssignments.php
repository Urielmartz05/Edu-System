<?php

    // ==========================================
    // CONTROLLER: FACULTY ASSIGNMENTS MANAGEMENT
    // ==========================================

    require_once __DIR__ . "/../auth/check_auth.php";
    requireValidPost();
    require_once __DIR__ . "/../../models/assignmentModels/assignmentModel.php";
    require_once __DIR__ . "/../../models/teacherModels/teacherCoursesModel.php";

    $user = getLoggedUser();
    $teacherId = (int)($user['id'] ?? 0);
    $userRole = (int)($user['role'] ?? 0);

    // Only teachers (role 3) and administrators (role 1) may manage assignments
    if ($teacherId <= 0 || ($userRole !== 3 && $userRole !== 1)) {
        header("Location: ../../views/ponente/ponente.php");
        exit;
    }

    $assignmentModel = new AssignmentModel();
    $teacherCoursesModel = new TeacherCourses();
    $action = $_REQUEST['action'] ?? '';

    // ==========================================
    // ACTION: CREATE ASSIGNMENT
    // ==========================================
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $courseId = (int)($_POST['course_id'] ?? 0);
        $blockId = (int)($_POST['block_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $maxScore = (float)($_POST['max_score'] ?? 100.00);
        $dueDate = trim($_POST['due_date'] ?? '');

        if ($courseId <= 0 || $blockId <= 0 || empty($title) || empty($dueDate) || strtotime($dueDate) === false || !is_finite($maxScore) || $maxScore <= 0 || $maxScore > 999.99) {
            $_SESSION['flash_error'] = "Course, syllabus block, title, and due date are mandatory.";
            header("Location: ../../views/ponente/ponenteAssignments.php?action=new" . ($courseId > 0 ? "&course_id={$courseId}" : ""));
            exit;
        }

        // Verify that the teacher is assigned to this block (unless admin)
        if ($userRole === 3 && !$teacherCoursesModel->isTeacherOfBlock($teacherId, $blockId)) {
            $_SESSION['flash_error'] = "You are not authorized to create assignments for this course module.";
            header("Location: ../../views/ponente/ponenteAssignments.php");
            exit;
        }

        if ($userRole === 3) {
            $block = $teacherCoursesModel->getCourseBlocksWithFiles($courseId, $teacherId);
            if (!in_array($blockId, array_map('intval', array_column($block, 'id')), true)) {
                $_SESSION['flash_error'] = "The module does not belong to this course.";
                header("Location: ../../views/ponente/ponenteAssignments.php");
                exit;
            }
        } else {
            require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
            $adminCoursesModel = new AdminCourses();
            $blockDetails = $adminCoursesModel->getBlockManagementDetails($blockId);
            if (!$blockDetails || (int)$blockDetails['course_id'] !== $courseId) {
                $_SESSION['flash_error'] = "The module does not belong to this course.";
                header("Location: ../../views/ponente/ponenteAssignments.php");
                exit;
            }
            if (!empty($blockDetails['teacher_id'])) {
                $teacherId = (int)$blockDetails['teacher_id'];
            }
        }
        if (isset($_FILES['attachment']) && !in_array($_FILES['attachment']['error'], [UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE], true)) {
            $_SESSION['flash_error'] = "The attachment upload failed. Please try again.";
            header("Location: ../../views/ponente/ponenteAssignments.php?action=new&course_id={$courseId}");
            exit;
        }
        $filePath = null;

        // Handle prompt attachment file upload
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $maxFileSize = 25 * 1024 * 1024; // 25 MB

            if ($file['size'] > $maxFileSize) {
                $_SESSION['flash_error'] = "Attached file exceeds the 25 MB limit.";
                header("Location: ../../views/ponente/ponenteAssignments.php?action=new&course_id={$courseId}");
                exit;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'png', 'jpg', 'jpeg'];

            if (!in_array($ext, $allowedExts)) {
                $_SESSION['flash_error'] = "File type .{$ext} is not supported.";
                header("Location: ../../views/ponente/ponenteAssignments.php?action=new&course_id={$courseId}");
                exit;
            }

            $uploadDir = __DIR__ . '/../../../resources/assignments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = 'assignment_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $filePath = 'resources/assignments/' . $fileName;
            } else {
                $_SESSION['flash_error'] = "Could not store the attached resource file.";
                header("Location: ../../views/ponente/ponenteAssignments.php?action=new&course_id={$courseId}");
                exit;
            }
        }

        // Convert due date format if needed
        $dueDateFormatted = date('Y-m-d H:i:s', strtotime($dueDate));

        $createdId = $assignmentModel->createAssignment($courseId, $blockId, $teacherId, $title, $description, $maxScore, $dueDateFormatted, $filePath);

        if ($createdId) {
            $_SESSION['flash_success'] = "Assignment '{$title}' created successfully.";
        } else {
            $_SESSION['flash_error'] = "Database error while creating the assignment.";
        }

        header("Location: ../../views/ponente/ponenteAssignments.php?course_id={$courseId}");
        exit;
    }

    // ==========================================
    // ACTION: UPDATE ASSIGNMENT
    // ==========================================
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $blockId = (int)($_POST['block_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $maxScore = (float)($_POST['max_score'] ?? 100.00);
        $dueDate = trim($_POST['due_date'] ?? '');
        $clearAttachment = !empty($_POST['clear_attachment']);

        if ($assignmentId <= 0 || $blockId <= 0 || empty($title) || empty($dueDate) || strtotime($dueDate) === false || !is_finite($maxScore) || $maxScore <= 0 || $maxScore > 999.99) {
            $_SESSION['flash_error'] = "Syllabus block, title, maximum score, and due date are mandatory.";
            header("Location: ../../views/ponente/ponenteAssignments.php?action=edit&edit_id={$assignmentId}");
            exit;
        }

        $assignment = $assignmentModel->getAssignmentDetails($assignmentId);
        if (!$assignment || ($userRole === 3 && (int)$assignment['teacher_id'] !== $teacherId)) {
            $_SESSION['flash_error'] = "You are not authorized to edit this assignment.";
            header("Location: ../../views/ponente/ponenteAssignments.php");
            exit;
        }

        $courseId = (int)$assignment['course_id'];

        // Validate that the new maximum score is not lower than any already awarded grade
        $highestGradedScore = $assignmentModel->getMaxGradedScore($assignmentId);
        if ($highestGradedScore !== null && $maxScore < $highestGradedScore) {
            $_SESSION['flash_error'] = "Cannot reduce maximum score to " . number_format($maxScore, 1) . " pts because a student has already been graded with " . number_format($highestGradedScore, 1) . " pts. Please set a maximum score of at least " . number_format($highestGradedScore, 1) . " pts.";
            header("Location: ../../views/ponente/ponenteAssignments.php?action=edit&edit_id={$assignmentId}");
            exit;
        }

        // Verify teacher authorization for the target block (unless admin)
        if ($userRole === 3 && !$teacherCoursesModel->isTeacherOfBlock($teacherId, $blockId)) {
            $_SESSION['flash_error'] = "You are not authorized to assign coursework to this course module.";
            header("Location: ../../views/ponente/ponenteAssignments.php?action=edit&edit_id={$assignmentId}");
            exit;
        }

        if (isset($_FILES['attachment']) && !in_array($_FILES['attachment']['error'], [UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE], true)) {
            $_SESSION['flash_error'] = "The attachment upload failed. Please try again.";
            header("Location: ../../views/ponente/ponenteAssignments.php?action=edit&edit_id={$assignmentId}");
            exit;
        }

        $filePath = null;

        // Handle prompt attachment file replacement
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $maxFileSize = 25 * 1024 * 1024; // 25 MB

            if ($file['size'] > $maxFileSize) {
                $_SESSION['flash_error'] = "Attached file exceeds the 25 MB limit.";
                header("Location: ../../views/ponente/ponenteAssignments.php?action=edit&edit_id={$assignmentId}");
                exit;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'png', 'jpg', 'jpeg'];

            if (!in_array($ext, $allowedExts)) {
                $_SESSION['flash_error'] = "File type .{$ext} is not supported.";
                header("Location: ../../views/ponente/ponenteAssignments.php?action=edit&edit_id={$assignmentId}");
                exit;
            }

            $uploadDir = __DIR__ . '/../../../resources/assignments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = 'assignment_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $filePath = 'resources/assignments/' . $fileName;
            } else {
                $_SESSION['flash_error'] = "Could not store the attached resource file.";
                header("Location: ../../views/ponente/ponenteAssignments.php?action=edit&edit_id={$assignmentId}");
                exit;
            }
        }

        $updated = $assignmentModel->updateAssignment($assignmentId, $blockId, $title, $description, $maxScore, $dueDate, $filePath, ($userRole === 1 ? null : $teacherId), $clearAttachment);

        if ($updated) {
            $_SESSION['flash_success'] = "Assignment '{$title}' updated successfully.";
        } else {
            $_SESSION['flash_error'] = "Database error while updating the assignment.";
        }

        $redirectUrl = ($userRole === 1) ? "../../views/admin/adminAssignments.php" : "../../views/ponente/ponenteAssignments.php?course_id={$courseId}";
        header("Location: " . $redirectUrl);
        exit;
    }

    // ==========================================
    // ACTION: GRADE SUBMISSION
    // ==========================================
    if ($action === 'grade' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $submissionId = (int)($_POST['submission_id'] ?? 0);
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $score = (isset($_POST['score']) && $_POST['score'] !== '') ? (float)$_POST['score'] : null;
        $feedback = trim($_POST['feedback'] ?? '');

        if (($submissionId <= 0 && $studentId <= 0) || $assignmentId <= 0 || $score === null) {
            $_SESSION['flash_error'] = "Missing submission or student identifier, or score value.";
            header("Location: ../../views/ponente/ponenteAssignments.php" . ($assignmentId > 0 ? "?review_id={$assignmentId}" : ""));
            exit;
        }

        // Validate assignment ownership
        $assignment = $assignmentModel->getAssignmentDetails($assignmentId);
        if (!$assignment || ($userRole === 3 && (int)$assignment['teacher_id'] !== $teacherId)) {
            $_SESSION['flash_error'] = "You are not authorized to grade this submission.";
            header("Location: ../../views/ponente/ponenteAssignments.php");
            exit;
        }

        if ($score < 0 || $score > (float)$assignment['max_score']) {
            $_SESSION['flash_error'] = "Score must be between 0 and {$assignment['max_score']}.";
            header("Location: ../../views/ponente/ponenteAssignments.php?review_id={$assignmentId}");
            exit;
        }

        $graded = $assignmentModel->gradeSubmission($submissionId, ($userRole === 1 ? null : $teacherId), $score, $feedback, $assignmentId, $studentId);

        if ($graded) {
            $_SESSION['flash_success'] = "Grade recorded successfully ({$score} / {$assignment['max_score']}).";
        } else {
            $_SESSION['flash_error'] = "Failed to update grade.";
        }

        $anchor = $submissionId > 0 ? "sub_{$submissionId}" : "student_{$studentId}";
        header("Location: ../../views/ponente/ponenteAssignments.php?review_id={$assignmentId}#{$anchor}");
        exit;
    }

    // ==========================================
    // ACTION: DELETE ASSIGNMENT
    // ==========================================
    if ($action === 'delete') {
        $assignmentId = (int)($_POST['assignment_id'] ?? $_GET['assignment_id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? $_GET['course_id'] ?? 0);

        if ($assignmentId <= 0) {
            $_SESSION['flash_error'] = "Invalid assignment identifier.";
            header("Location: ../../views/ponente/ponenteAssignments.php");
            exit;
        }

        $assignment = $assignmentModel->getAssignmentDetails($assignmentId);
        if (!$assignment || ($userRole === 3 && (int)$assignment['teacher_id'] !== $teacherId)) {
            $_SESSION['flash_error'] = "You do not have permission to delete this assignment.";
            header("Location: ../../views/ponente/ponenteAssignments.php");
            exit;
        }

        $deleted = $assignmentModel->deleteAssignment($assignmentId, ($userRole === 1 ? null : $teacherId));

        if ($deleted) {
            $_SESSION['flash_success'] = "Assignment deleted successfully.";
        } else {
            $_SESSION['flash_error'] = "Could not delete assignment.";
        }

        header("Location: ../../views/ponente/ponenteAssignments.php" . ($courseId > 0 ? "?course_id={$courseId}" : ""));
        exit;
    }

    // Default redirect if action is unrecognized
    header("Location: ../../views/ponente/ponenteAssignments.php");
    exit;
