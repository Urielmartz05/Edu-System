<?php
    require_once __DIR__ . '/../auth/check_auth.php';
    $user = getLoggedUser();

    $roleId = (int)($user['role'] ?? ($_SESSION['role_id'] ?? 0));
    if ($roleId !== 1) {
        header("Location: ../../views/general/403.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../../views/admin/adminCreate.php");
        exit;
    }

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

            if (empty($name) || empty($lastname) || empty($email) || empty($password)) {
                $_SESSION['flash_error'] = "First name, last name, email, and password are required.";
                header("Location: ../../views/admin/adminCreate.php?tab=teacher&status=error");
                exit;
            }

            $result = $usersModel->createTeacher($name, $lastname, $email, $password);
            if ($result['success']) {
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

        default:
            header("Location: ../../views/admin/adminCreate.php");
            exit;
    }
