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

    // Resolve ownership from the actual module before trusting a posted course ID.
    if ($action === 'reassign_block') {
        $block = $coursesModel->getBlockManagementDetails($blockId);
        if (!$block || ($courseId > 0 && $courseId !== (int)$block['course_id'])) {
            $_SESSION['flash_error'] = 'Invalid module or course selection.';
            header('Location: /src/views/' . ($isDirector ? 'director/directorCourses.php' : 'admin/adminCourses.php'));
            exit;
        }
        $courseId = (int)$block['course_id'];
    }

    // If Director, verify course belongs to their academic department
    if ($isDirector) {
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
                if ($coursesModel->updateBlockTeacher($blockId, $teacherId, $courseId)) {
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

    // Only return to known view paths, never to a path relative to this controller.
    if ($isDirector) {
        if (basename(parse_url($returnUrl, PHP_URL_PATH) ?: '') === 'directorCourses.php' || $courseId <= 0) {
            header('Location: /src/views/director/directorCourses.php');
        } else {
            header('Location: /src/views/director/directorCourseContent.php?course_id=' . $courseId);
        }
    } else {
        header('Location: /src/views/admin/' . ($courseId > 0 ? 'adminCourseContent.php?course_id=' . $courseId : 'adminCourses.php'));
    }
    exit;
