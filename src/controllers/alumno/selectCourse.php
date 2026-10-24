<?php

    // ==========================================
    // CONTROLLER: ACTIVE COURSE SELECTION (STUDENT)
    // ==========================================

    require_once __DIR__ . '/../auth/check_auth.php';
    require_once __DIR__ . '/../../models/studentModels/studentEnrollmentModel.php';

    $user = getLoggedUser();
    $studentId = (int) ($user['id'] ?? 0);
    $courseId = (int) ($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
    $target = $_GET['target'] ?? 'courses';

    // Validate that the student is genuinely enrolled in the requested course
    if ($studentId > 0 && $courseId > 0) {
        $enrollmentModel = new StudentEnrollment();
        if ($enrollmentModel->isEnrolled($studentId, $courseId)) {
            $_SESSION['active_course_id'] = $courseId;

            // Redirect according to requested target view
            $redirect = ($target === 'dashboard') 
                ? '../../views/alumno/alumnoDashboard.php' 
                : '../../views/alumno/alumnoCourses.php';

            header("Location: " . $redirect);
            exit;
        }
    }

    header("Location: ../../views/alumno/misCursos.php?error=invalid_course");
    exit;

?>