<?php

    // ==========================================
    // CONTROLLER: TOGGLE BLOCK COMPLETION (STUDENT)
    // ==========================================

    require_once __DIR__ . '/../auth/check_auth.php';
    requireValidPost();
    requireRole([4]);
    $user = getLoggedUser();

    // Verify authenticated student session
    if (!$user) {
        header("Location: ../../../index.php");
        exit;
    }

    $studentId = (int)$user['id'];
    $blockId = (int)($_POST['block_id'] ?? $_GET['block_id'] ?? 0);

    if ($blockId <= 0) {
        header("Location: ../../views/alumno/alumnoCourses.php?error=invalid_block");
        exit;
    }

    require_once __DIR__ . '/../../models/studentModels/studentCoursesModel.php';
    $studentModel = new StudentCourses();

    // Validate that the block exists and retrieve course data
    $block = $studentModel->getBlockDetails($blockId, $studentId);
    if (!$block) {
        header("Location: ../../views/alumno/alumnoCourses.php?error=not_found");
        exit;
    }

    // Toggle block completion status in database
    $studentModel->toggleBlockCompletion($studentId, $blockId);

    // Standard pure PHP redirect back to the module page
    header("Location: ../../views/alumno/blockContent.php?block_id=" . urlencode($blockId));
    exit;

?>