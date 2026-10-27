<?php

    // ==========================================
    // CONTROLLER: MANAGE COURSE BLOCKS & FACULTY (ADMIN, DIRECTOR, TEACHER)
    // ==========================================

    require_once __DIR__ . '/../auth/check_auth.php';
    requireValidPost();
    requireRole([1, 2, 3]);

    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    require_once __DIR__ . '/../../models/directorModels/directorDashboardModel.php';
    require_once __DIR__ . '/../../models/teacherModels/teacherCoursesModel.php';

    $coursesModel = new AdminCourses();
    $directorModel = new DirectorDashboard();
    $teacherModel = new TeacherCourses();

    $user = getLoggedUser();
    $userRole = (int)($user['role'] ?? 0);
    $isDirector = ($userRole === 2);
    $isTeacher = ($userRole === 3);

    $action = $_POST['action'] ?? 'create_block';
    $courseId = (int)($_POST['course_id'] ?? 0);
    $blockName = trim($_POST['block_name'] ?? '');
    $teacherId = (int)($_POST['teacher_id'] ?? 0);
    $blockId = (int)($_POST['block_id'] ?? 0);
    $returnUrl = trim($_POST['return_url'] ?? '');

    // Resolve ownership from the actual module before trusting a posted course ID.
    if ($action === 'reassign_block' || $action === 'rename_block') {
        $block = $coursesModel->getBlockManagementDetails($blockId);
        if (!$block || ($courseId > 0 && $courseId !== (int)$block['course_id'])) {
            $_SESSION['flash_error'] = 'Invalid module or course selection.';
            header('Location: /src/views/' . ($isDirector ? 'director/directorCourses.php' : ($isTeacher ? 'ponente/ponenteCourses.php' : 'admin/adminCourses.php')));
            exit;
        }
        $courseId = (int)$block['course_id'];
    }

    // Teachers can only create blocks for courses they instruct, or rename blocks assigned to them
    if ($isTeacher) {
        if ($action === 'create_block') {
            $courseCheck = $teacherModel->getCourseDetails($courseId, (int)$user['id']);
            if (!$courseCheck) {
                $_SESSION['flash_error'] = "You are not authorized to add modules to a course you do not instruct.";
                header("Location: ../../views/ponente/ponenteCourses.php");
                exit;
            }
            $teacherId = (int)$user['id'];
        } elseif ($action === 'rename_block') {
            if (!$teacherModel->isTeacherOfBlock((int)$user['id'], $blockId)) {
                http_response_code(403);
                $_SESSION['flash_error'] = "You are not authorized to modify a module that is not assigned to you.";
                header("Location: /src/views/ponente/ponenteCourseContent.php" . ($courseId > 0 ? "?course_id={$courseId}" : ""));
                exit;
            }
        } else {
            http_response_code(403);
            exit('Access denied.');
        }
    }

    // If Director, verify course belongs to their academic department
    if ($isDirector) {
        $dept = $directorModel->getDirectorDepartment((int)$user['id']);
        $deptId = (int)($dept['department_id'] ?? 0);
        $course = $coursesModel->getCourseDetails($courseId);
        if (!$course || (int)$course['department_id'] !== $deptId) {
            $_SESSION['flash_error'] = "You are not authorized to manage courses outside your department.";
            header("Location: ../../views/director/directorCourses.php");
            exit;
        }
    }

    switch ($action) {
        case 'rename_block':
            if ($blockId <= 0 || empty($blockName)) {
                $_SESSION['flash_error'] = "Module title cannot be empty.";
            } else {
                if ($coursesModel->updateBlockName($blockId, $blockName)) {
                    $_SESSION['flash_success'] = "Module title successfully updated to \"{$blockName}\".";
                } else {
                    $_SESSION['flash_error'] = "Failed to update module title.";
                }
            }
            break;

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
                    $_SESSION['flash_success'] = "Module \"{$blockName}\" successfully added.";
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
    } elseif ($isTeacher) {
        if (basename(parse_url($returnUrl, PHP_URL_PATH) ?: '') === 'ponenteCourses.php') {
            header('Location: /src/views/ponente/ponenteCourses.php');
        } else {
            header('Location: /src/views/ponente/ponenteCourseContent.php?course_id=' . $courseId);
        }
    } else {
        header('Location: /src/views/admin/' . ($courseId > 0 ? 'adminCourseContent.php?course_id=' . $courseId : 'adminCourses.php'));
    }
    exit;
