<?php
    require_once __DIR__ . '/../auth/check_auth.php';
    require_once __DIR__ . '/../../models/studentModels/studentEnrollmentModel.php';

    $user = getLoggedUser();
    $studentId = (int) ($user['id'] ?? 0);
    $courseId = (int) ($_POST['course_id'] ?? $_GET['course_id'] ?? 0);

    if ($studentId > 0 && $courseId > 0) {
        $enrollmentModel = new StudentEnrollment();
        if ($enrollmentModel->enroll($studentId, $courseId)) {
            $_SESSION['active_course_id'] = $courseId;
            $_SESSION['flash_success'] = "Successfully enrolled in the course!";
            header("Location: ../../views/alumno/misCursos.php?enrolled=success");
            exit;
        }
    }

    header("Location: ../../views/alumno/cursosDisponibles.php?error=enrollment_failed");
    exit;
?>
