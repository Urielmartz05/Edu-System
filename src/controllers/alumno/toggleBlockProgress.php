<?php

    // ==========================================
    // CONTROLLER: TOGGLE BLOCK COMPLETION (STUDENT)
    // ==========================================

    require_once __DIR__ . '/../auth/check_auth.php';
    $user = getLoggedUser();

    // Verify authenticated student session
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required.']);
        exit;
    }

    $studentId = (int)$user['id'];
    $blockId = (int)($_POST['block_id'] ?? $_GET['block_id'] ?? 0);

    // Detect if the incoming request is via AJAX
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
              || (!empty($_POST['ajax']) || !empty($_GET['ajax']))
              || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($blockId <= 0) {
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid module identifier.']);
            exit;
        }
        header("Location: ../../views/alumno/alumnoCourses.php?error=invalid_block");
        exit;
    }

    require_once __DIR__ . '/../../models/studentModels/studentCoursesModel.php';
    $studentModel = new StudentCourses();

    // Validate that the block exists and retrieve course data
    $block = $studentModel->getBlockDetails($blockId, $studentId);
    if (!$block) {
        if ($isAjax) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Module not found.']);
            exit;
        }
        header("Location: ../../views/alumno/alumnoCourses.php?error=not_found");
        exit;
    }

    // Toggle block completion status in database
    $isCompleted = $studentModel->toggleBlockCompletion($studentId, $blockId);
    $courseId = (int)$block['course_id'];
    $updatedProgress = $studentModel->getDynamicCourseProgress($studentId, $courseId);

    // Return JSON response for seamless asynchronous UI updates
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'completed' => $isCompleted,
            'course_progress' => $updatedProgress,
            'block_id' => $blockId,
            'message' => $isCompleted ? 'Module marked as completed.' : 'Module marked in progress.'
        ]);
        exit;
    }

    // Fallback standard redirection if JavaScript is not enabled
    header("Location: ../../views/alumno/blockContent.php?block_id=" . urlencode($blockId));
    exit;

?>