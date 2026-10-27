<?php
    require_once __DIR__ . '/../auth/check_auth.php';
    requireValidPost();
    requireRole([1]);

    $action = $_POST['action'] ?? '';

    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    require_once __DIR__ . '/../../models/adminModels/adminUsersModel.php';

    $coursesModel = new AdminCourses();
    $usersModel = new AdminUsers();

    switch ($action) {
        case 'course':
            $courseName = trim($_POST['course_name'] ?? '');
            $departmentId = (int)($_POST['department_id'] ?? 0);
            $credits = (int)($_POST['credits'] ?? 4);
            $status = (int)($_POST['course_status'] ?? 1);
            $teacherId = (int)($_POST['teacher_id'] ?? 0);

            if (empty($courseName) || $departmentId <= 0) {
                $_SESSION['flash_error'] = "Course title and department are required.";
                header("Location: ../../views/admin/adminCreate.php?tab=course&status=error");
                exit;
            }

            $courseId = $coursesModel->createCourse($courseName, $departmentId, $credits, $status);
            if ($courseId) {
                if ($teacherId > 0) {
                    $coursesModel->createBlock($courseId, $courseName . ' - Core Unit', $teacherId);
                }
                $_SESSION['flash_success'] = "Course \"{$courseName}\" successfully created in catalog!";
                header("Location: ../../views/admin/adminCreate.php?tab=course&status=success");
                exit;
            } else {
                $_SESSION['flash_error'] = "Database error while creating the course. Please try again.";
                header("Location: ../../views/admin/adminCreate.php?tab=course&status=error");
                exit;
            }
            break;

        case 'student':
            $name = trim($_POST['name'] ?? '');
            $lastname = trim($_POST['lastname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? 'alumno123';
            $courseId = (int)($_POST['course_id'] ?? 0);

            if (empty($name) || empty($lastname) || empty($email) || empty($password)) {
                $_SESSION['flash_error'] = "First name, last name, email, and password are required.";
                header("Location: ../../views/admin/adminCreate.php?tab=student&status=error");
                exit;
            }

            $result = $usersModel->createStudent($name, $lastname, $email, $password, $courseId);
            if ($result['success']) {
                $_SESSION['flash_success'] = "Student {$name} {$lastname} registered and enrolled successfully!";
                header("Location: ../../views/admin/adminCreate.php?tab=student&status=success");
                exit;
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? "Failed to create student account.";
                header("Location: ../../views/admin/adminCreate.php?tab=student&status=error");
                exit;
            }
            break;

        case 'teacher':
            $name = trim($_POST['name'] ?? '');
            $lastname = trim($_POST['lastname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? 'password123';
            $courseId = (int)($_POST['course_id'] ?? 0);
            $specialty = trim($_POST['specialty'] ?? '');

            if (empty($name) || empty($lastname) || empty($email) || empty($password)) {
                $_SESSION['flash_error'] = "First name, last name, email, and password are required.";
                header("Location: ../../views/admin/adminCreate.php?tab=teacher&status=error");
                exit;
            }

            $result = $usersModel->createTeacher($name, $lastname, $email, $password);
            if ($result['success']) {
                $teacherId = (int)$result['id'];
                if ($courseId > 0) {
                    $coursesModel->assignTeacherToCourse($courseId, $teacherId, $specialty);
                }
                $_SESSION['flash_success'] = "Faculty member {$name} {$lastname} appointed successfully!";
                header("Location: ../../views/admin/adminCreate.php?tab=teacher&status=success");
                exit;
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? "Failed to create teacher account.";
                header("Location: ../../views/admin/adminCreate.php?tab=teacher&status=error");
                exit;
            }
            break;

        case 'admin':
            $name = trim($_POST['name'] ?? '');
            $lastname = trim($_POST['lastname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? 'admin123';

            if (empty($name) || empty($lastname) || empty($email) || empty($password)) {
                $_SESSION['flash_error'] = "First name, last name, email, and password are required.";
                header("Location: ../../views/admin/adminCreate.php?tab=admin&status=error");
                exit;
            }

            $result = $usersModel->createAdmin($name, $lastname, $email, $password);
            if ($result['success']) {
                $_SESSION['flash_success'] = "Administrator {$name} {$lastname} provisioned successfully!";
                header("Location: ../../views/admin/adminCreate.php?tab=admin&status=success");
                exit;
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? "Failed to create administrator account.";
                header("Location: ../../views/admin/adminCreate.php?tab=admin&status=error");
                exit;
            }
            break;

        case 'director':
            $name = trim($_POST['name'] ?? '');
            $lastname = trim($_POST['lastname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? 'password123';
            $departmentId = (int)($_POST['department_id'] ?? 0);

            if (empty($name) || empty($lastname) || empty($email) || empty($password) || $departmentId <= 0) {
                $_SESSION['flash_error'] = "First name, last name, email, password, and assigned section/department are required.";
                header("Location: ../../views/admin/adminCreate.php?tab=director&status=error");
                exit;
            }

            $result = $usersModel->createDirector($name, $lastname, $email, $password, $departmentId);
            if ($result['success']) {
                $_SESSION['flash_success'] = "Academic Director {$name} {$lastname} appointed and assigned successfully!";
                header("Location: ../../views/admin/adminCreate.php?tab=director&status=success");
                exit;
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? "Failed to create Academic Director.";
                header("Location: ../../views/admin/adminCreate.php?tab=director&status=error");
                exit;
            }
            break;

        case 'section':
        case 'department':
            $departmentName = trim($_POST['department_name'] ?? '');
            $directorUserId = (int)($_POST['director_id'] ?? 0);

            if (empty($departmentName)) {
                $_SESSION['flash_error'] = "Section / Department name is required.";
                header("Location: ../../views/admin/adminCreate.php?tab=section&status=error");
                exit;
            }

            $result = $coursesModel->createDepartment($departmentName, $directorUserId);
            if ($result['success']) {
                $_SESSION['flash_success'] = "Section / Department \"{$departmentName}\" created successfully!";
                header("Location: ../../views/admin/adminCreate.php?tab=section&status=success");
                exit;
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? "Failed to create section.";
                header("Location: ../../views/admin/adminCreate.php?tab=section&status=error");
                exit;
            }
            break;

        case 'assign_director':
            $departmentId = (int)($_POST['department_id'] ?? 0);
            $directorId = (int)($_POST['director_id'] ?? 0);

            if ($departmentId <= 0) {
                $_SESSION['flash_error'] = "Invalid section identifier.";
                header("Location: ../../views/admin/adminCreate.php?tab=section&status=error");
                exit;
            }

            $res = $coursesModel->assignDirectorToDepartment($departmentId, $directorId);
            if ($res['success']) {
                $_SESSION['flash_success'] = "Director assignment updated successfully.";
            } else {
                $_SESSION['flash_error'] = $res['error'] ?? "Failed to update director assignment.";
            }
            header("Location: ../../views/admin/adminCreate.php?tab=section");
            exit;

        case 'delete_section':
        case 'delete_department':
            $departmentId = (int)($_POST['department_id'] ?? 0);
            $deleteMode = trim($_POST['delete_mode'] ?? 'standard');
            $reassignToId = (int)($_POST['reassign_to_id'] ?? 0);

            if ($departmentId <= 0) {
                $_SESSION['flash_error'] = "Invalid section identifier.";
                header("Location: ../../views/admin/adminCreate.php?tab=section&status=error");
                exit;
            }

            $dept = $coursesModel->getDepartmentById($departmentId);
            $dName = $dept ? $dept['department'] : "#{$departmentId}";

            if ($deleteMode === 'reassign' && $reassignToId > 0) {
                $result = $coursesModel->deleteDepartment($departmentId, $reassignToId, false);
            } elseif ($deleteMode === 'cascade') {
                $result = $coursesModel->deleteDepartment($departmentId, 0, true);
            } else {
                $result = $coursesModel->deleteDepartment($departmentId, 0, false);
            }

            if ($result['success']) {
                $msg = $result['message'] ?? "Section / Department \"{$dName}\" deleted successfully.";
                $_SESSION['flash_success'] = $msg;
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? "Failed to delete section.";
            }
            header("Location: ../../views/admin/adminCreate.php?tab=section");
            exit;

        default:
            header("Location: ../../views/admin/adminCreate.php");
            exit;
    }
