<?php

    // ==========================================
    // CONTROLLER: MANAGE COURSE BLOCKS & FACULTY (ADMIN & DIRECTOR)
    // ==========================================

    require_once __DIR__ . '/../auth/check_auth.php';
    requireValidPost();
    requireRole([1, 2]);

    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    require_once __DIR__ . '/../../models/directorModels/directorDashboardModel.php';

    $coursesModel = new AdminCourses();
    $directorModel = new DirectorDashboard();
    $user = getLoggedUser();
    $isDirector = ((int)($user['role'] ?? 0) === 2);

    $action = $_POST['action'] ?? 'create_block';
    $courseId = (int)($_POST['course_id'] ?? 0);
    $blockName = trim($_POST['block_name'] ?? '');
    $teacherId = (int)($_POST['teacher_id'] ?? 0);
    $blockId = (int)($_POST['block_id'] ?? 0);
    $returnUrl = trim($_POST['return_url'] ?? '');

    // If Director, verify course belongs to their academic department
    if ($isDirector && $courseId > 0) {
        $dept = $directorModel->getDirectorDepartment((int)$user['id']);
        $deptId = (int)($dept['department_id'] ?? 0);
        $course = $coursesModel->getCourseDetails($courseId);
        if (!$course || (int)$course['department_id'] !== $deptId) {
            $_SESSION['flash_error'] = "You are not authorized to assign faculty to courses outside your department.";
            header("Location: ../../views/director/directorCourses.php");
            exit;
        }
    }

    switch ($action) {
        case 'assign_teacher':
            if ($courseId <= 0 || $teacherId <= 0) {
                $_SESSION['flash_error'] = "Both course and faculty instructor must be selected.";
            } else {
                $success = $coursesModel->assignTeacherToCourse($courseId, $teacherId, $blockName);
                if ($success) {
                    $_SESSION['flash_success'] = "Faculty instructor successfully assigned to course!";
                } else {
                    $_SESSION['flash_error'] = "Failed to assign faculty instructor to course.";
                }
            }
            break;

        case 'reassign_block':
            if ($blockId <= 0 || $teacherId <= 0) {
                $_SESSION['flash_error'] = "Module and new instructor are required for reassignment.";
            } else {
                if ($coursesModel->updateBlockTeacher($blockId, $teacherId)) {
                    $_SESSION['flash_success'] = "Module instructor successfully updated.";
                } else {
                    $_SESSION['flash_error'] = "Failed to update module instructor.";
                }
            }
            break;

        case 'create_block':
        default:
            if ($courseId <= 0 || empty($blockName) || $teacherId <= 0) {
                $_SESSION['flash_error'] = "Block title and assigned instructor are required.";
            } else {
                $newBlockId = $coursesModel->createBlock($courseId, $blockName, $teacherId);
                if ($newBlockId) {
                    $_SESSION['flash_success'] = "Module \"{$blockName}\" successfully added with appointed instructor.";
                } else {
                    $_SESSION['flash_error'] = "Database error while adding module.";
                }
            }
            break;
    }

    // Determine safe redirection target
    if (!empty($returnUrl) && !preg_match('/^https?:\/\//i', $returnUrl)) {
        header("Location: " . $returnUrl);
        exit;
    }

    if ($isDirector) {
        if ($courseId > 0) {
            header("Location: ../../views/director/directorCourseContent.php?course_id=" . urlencode($courseId));
        } else {
            header("Location: ../../views/director/directorCourses.php");
        }
    } else {
        header("Location: ../../views/admin/adminCourseContent.php?course_id=" . urlencode($courseId));
    }
    exit;
