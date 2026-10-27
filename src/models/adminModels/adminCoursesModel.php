<?php

    class AdminCourses {

        private $connection;

        // Initialize MySQL database connection.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Count total courses in catalog.
        public function getTotalCoursesCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_courses FROM course";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_courses'] ?? 0);
        }

        // Count published courses with active status (status = 1).
        public function getPublishedCoursesCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_published FROM course WHERE status = 1";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_published'] ?? 0);
        }

        // Count paused courses under review (status = 0).
        public function getReviewCoursesCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_review FROM course WHERE status = 0";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_review'] ?? 0);
        }

        // Count total active student enrollments across all courses.
        public function getTotalEnrollmentsCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_enrollments FROM student";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_enrollments'] ?? 0);
        }

        // Fetch all catalog courses with department, enrollment count, and faculty.
        public function getAllCourses() {

            $connection = $this->connection;
            $sql = "SELECT 
                        c.id, 
                        c.course_name, 
                        c.department_id, 
                        COALESCE(d.department, 'General') AS department_name,
                        c.progress, 
                        c.status,
                        c.credits,
                        COUNT(DISTINCT s.id) AS student_count,
                        COALESCE(GROUP_CONCAT(DISTINCT CONCAT(u.name, ' ', u.lastname) SEPARATOR ', '), 'Unassigned') AS teacher_name
                    FROM course c
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN student s ON s.course_id = c.id
                    LEFT JOIN blocks b ON b.course_id = c.id
                    LEFT JOIN users u ON u.id = b.teacher_id
                    GROUP BY c.id, c.course_name, c.department_id, d.department, c.progress, c.status, c.credits
                    ORDER BY c.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $courses = [];

            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }

            $stmt->close();

            return $courses;
        }

        // Fetch course technical metadata and enrollment metrics.
        public function getCourseDetails($courseId) {

            $courseId = (int) $courseId;
            $connection = $this->connection;
            $sql = "SELECT 
                        c.id, 
                        c.course_name, 
                        c.department_id, 
                        COALESCE(d.department, 'General') AS department_name,
                        c.progress, 
                        c.status, 
                        c.credits,
                        COUNT(DISTINCT s.id) AS student_count
                    FROM course c
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN student s ON s.course_id = c.id
                    WHERE c.id = ?
                    GROUP BY c.id, c.course_name, c.department_id, d.department, c.progress, c.status, c.credits
                    LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Fetch all syllabus modules assigned to a course.
        public function getCourseBlocks($courseId) {

            $courseId = (int) $courseId;
            $connection = $this->connection;
            $sql = "SELECT 
                        b.id,
                        b.course_id,
                        b.block_name,
                        b.teacher_id,
                        COALESCE(CONCAT(u.name, ' ', u.lastname), 'Unassigned') AS teacher_name,
                        COALESCE(u.email, '') AS teacher_email
                    FROM blocks b
                    LEFT JOIN users u ON u.id = b.teacher_id
                    WHERE b.course_id = ?
                    ORDER BY b.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $blocks = [];

            while ($row = $result->fetch_assoc()) {
                $blocks[] = $row;
            }

            $stmt->close();

            return $blocks;
        }

        // Fetch study files attached to a specific course module.
        public function getBlockFiles($blockId) {

            $blockId = (int) $blockId;
            $connection = $this->connection;
            $sql = "SELECT id, subject_id, file_name, file_path 
                    FROM files 
                    WHERE subject_id = ?
                    ORDER BY id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $blockId);
            $stmt->execute();

            $result = $stmt->get_result();
            $files = [];

            while ($row = $result->fetch_assoc()) {
                $files[] = $row;
            }

            $stmt->close();

            return $files;
        }

        // Fetch roster of students enrolled in a specific course.
        public function getCourseStudents($courseId) {

            $courseId = (int) $courseId;
            $connection = $this->connection;
            $sql = "SELECT 
                        u.id,
                        u.name,
                        u.lastname,
                        u.email,
                        u.verified
                    FROM student s
                    INNER JOIN users u ON u.id = s.id
                    WHERE s.course_id = ?
                    ORDER BY u.lastname ASC, u.name ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $students = [];

            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }

            $stmt->close();

            return $students;
        }

        // Count total attached files across all modules of a course.
        public function getCourseFilesCount($courseId) {

            $courseId = (int) $courseId;
            $connection = $this->connection;
            $sql = "SELECT COUNT(f.id) AS total_files
                    FROM files f
                    INNER JOIN blocks b ON b.id = f.subject_id
                    WHERE b.course_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_files'] ?? 0);
        }

        // Insert new course record into database and return its generated ID.
        public function createCourse($courseName, $departmentId, $credits, $status = 1, $progress = 0) {

            $connection = $this->connection;
            $courseName = trim($courseName);
            $departmentId = (int) $departmentId;
            $credits = (int) $credits;
            $status = (int) $status;
            $progress = (float) $progress;

            $sql = "INSERT INTO course (course_name, department_id, credits, status, progress) 
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("siiid", $courseName, $departmentId, $credits, $status, $progress);

            $success = $stmt->execute();
            $newId = $success ? $connection->insert_id : false;
            $stmt->close();

            return $newId;
        }

        // Toggle publication status of course (Published <-> Under Review).
        public function toggleCourseStatus($courseId) {

            $courseId = (int) $courseId;
            $connection = $this->connection;

            $sql = "UPDATE course SET status = IF(status = 1, 0, 1) WHERE id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);

            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Add a new module block to a course syllabus.
        public function createBlock($courseId, $blockName, $teacherId) {

            $courseId = (int) $courseId;
            $blockName = trim($blockName);
            $teacherId = (int) $teacherId;
            $connection = $this->connection;

            $sql = "INSERT INTO blocks (course_id, block_name, teacher_id) VALUES (?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("isi", $courseId, $blockName, $teacherId);

            $success = $stmt->execute();
            $newId = $success ? $connection->insert_id : false;
            $stmt->close();

            return $newId;
        }

        // Assign a teacher to a course by linking an existing unassigned block or creating a new module.
        public function assignTeacherToCourse($courseId, $teacherId, $blockTitle = '') {
            $courseId = (int) $courseId;
            $teacherId = (int) $teacherId;
            if ($courseId <= 0 || $teacherId <= 0) {
                return false;
            }

            $connection = $this->connection;

            // Check if teacher already has a block in this course
            $chk = $connection->prepare("SELECT id FROM blocks WHERE course_id = ? AND teacher_id = ? LIMIT 1");
            $chk->bind_param("ii", $courseId, $teacherId);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $chk->close();
                return true;
            }
            $chk->close();

            // Check if there is an unassigned block in this course
            $un = $connection->prepare("SELECT id FROM blocks WHERE course_id = ? AND (teacher_id IS NULL OR teacher_id = 0) LIMIT 1");
            $un->bind_param("i", $courseId);
            $un->execute();
            $unRes = $un->get_result()->fetch_assoc();
            $un->close();

            if ($unRes) {
                $up = $connection->prepare("UPDATE blocks SET teacher_id = ? WHERE id = ?");
                $up->bind_param("ii", $teacherId, $unRes['id']);
                $res = $up->execute();
                $up->close();
                return $res;
            } else {
                $courseData = $this->getCourseDetails($courseId);
                $cName = $courseData['course_name'] ?? 'Curso';
                $finalTitle = !empty($blockTitle) ? trim($blockTitle) : ($cName . ' - Módulo Docente');
                return (bool)$this->createBlock($courseId, $finalTitle, $teacherId);
            }
        }

        // Update assigned instructor for a syllabus module block.
        public function getBlockManagementDetails($blockId) {
            $stmt = $this->connection->prepare('SELECT b.id, b.course_id, c.department_id FROM blocks b INNER JOIN course c ON c.id = b.course_id WHERE b.id = ?');
            $stmt->bind_param('i', $blockId);
            $stmt->execute();
            $block = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $block ?: null;
        }

        public function updateBlockTeacher($blockId, $teacherId, $courseId) {
            $blockId = (int)$blockId;
            $teacherId = (int)$teacherId;
            $courseId = (int)$courseId;
            if ($blockId <= 0 || $teacherId <= 0 || $courseId <= 0) {
                return false;
            }

            $connection = $this->connection;
            $stmt = $connection->prepare("UPDATE blocks b INNER JOIN users u ON u.id = ? AND u.role = 3
                SET b.teacher_id = u.id WHERE b.id = ? AND b.course_id = ?");
            $stmt->bind_param("iii", $teacherId, $blockId, $courseId);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Fetch list of teachers available for module assignment.
        public function getTeachersList() {

            $connection = $this->connection;
            $sql = "SELECT u.id, u.name, u.lastname, u.email 
                    FROM teacher t 
                    INNER JOIN users u ON u.id = t.id 
                    ORDER BY u.name ASC, u.lastname ASC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $teachers = [];

            while ($row = $result->fetch_assoc()) {
                $teachers[] = $row;
            }

            $stmt->close();

            return $teachers;
        }

        // Fetch all academic departments in the institution.
        public function getDepartmentsList() {

            $connection = $this->connection;
            $sql = "SELECT id, department FROM departments ORDER BY department ASC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $departments = [];

            while ($row = $result->fetch_assoc()) {
                $departments[] = $row;
            }

            $stmt->close();

            return $departments;
        }

        // Fetch department details by ID.
        public function getDepartmentById($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;
            $sql = "SELECT id, department, created_at, updated_at FROM departments WHERE id = ? LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Fetch all departments/sections with associated course counts, director counts, and appointed director names.
        public function getDepartmentsWithStats() {

            $connection = $this->connection;
            $sql = "SELECT 
                        d.id, 
                        d.department, 
                        d.created_at, 
                        COUNT(DISTINCT c.id) AS course_count,
                        COUNT(DISTINCT dir.id) AS director_count,
                        COALESCE(GROUP_CONCAT(DISTINCT CONCAT(u.name, ' ', u.lastname) SEPARATOR ', '), 'Unassigned') AS director_name,
                        MIN(dir.id) AS director_user_id
                    FROM departments d
                    LEFT JOIN course c ON c.department_id = d.id
                    LEFT JOIN director dir ON dir.department_id = d.id
                    LEFT JOIN users u ON u.id = dir.id
                    GROUP BY d.id, d.department, d.created_at
                    ORDER BY d.department ASC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $departments = [];

            while ($row = $result->fetch_assoc()) {
                $departments[] = $row;
            }

            $stmt->close();

            return $departments;
        }

        // Fetch courses specifically assigned to a department/section.
        public function getCoursesByDepartmentId($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;
            $sql = "SELECT id, course_name, credits, status FROM course WHERE department_id = ? ORDER BY course_name ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $courses = [];

            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }

            $stmt->close();

            return $courses;
        }

        // Fetch all users with Director role.
        public function getAllDirectors() {

            $connection = $this->connection;
            $sql = "SELECT 
                        u.id, 
                        u.name, 
                        u.lastname, 
                        u.email,
                        d.department_id,
                        dept.department AS department_name
                    FROM users u
                    LEFT JOIN director d ON d.id = u.id
                    LEFT JOIN departments dept ON dept.id = d.department_id
                    WHERE u.role = 2
                    ORDER BY u.name ASC, u.lastname ASC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $directors = [];

            while ($row = $result->fetch_assoc()) {
                $directors[] = $row;
            }

            $stmt->close();

            return $directors;
        }

        // Assign or reassign a Director to an academic department/section.
        public function assignDirectorToDepartment($departmentId, $directorUserId) {

            $connection = $this->connection;
            $departmentId = (int)$departmentId;
            $directorUserId = (int)$directorUserId;

            if ($departmentId <= 0) {
                return ['success' => false, 'error' => 'Invalid section identifier.'];
            }

            $connection->begin_transaction();
            try {
                if ($directorUserId <= 0) {
                    // Unassign director from this department
                    $stmt = $connection->prepare("DELETE FROM director WHERE department_id = ?");
                    $stmt->bind_param("i", $departmentId);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Verify user exists and has Director role (role = 2)
                    $chk = $connection->prepare("SELECT id FROM users WHERE id = ? AND role = 2");
                    $chk->bind_param("i", $directorUserId);
                    $chk->execute();
                    $res = $chk->get_result();

                    if ($res->num_rows === 0) {
                        $chk->close();
                        $connection->rollback();
                        return ['success' => false, 'error' => 'Selected user is not registered as an Academic Director.'];
                    }
                    $chk->close();

                    // Upsert into director table
                    $stmt = $connection->prepare("INSERT INTO director (id, department_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE department_id = VALUES(department_id)");
                    $stmt->bind_param("ii", $directorUserId, $departmentId);
                    $stmt->execute();
                    $stmt->close();
                }

                $connection->commit();
                return ['success' => true];
            } catch (\Throwable $e) {
                $connection->rollback();
                return ['success' => false, 'error' => 'Database exception: ' . $e->getMessage()];
            }
        }

        // Create a new department/section for course classification.
        public function createDepartment($departmentName, $directorUserId = 0) {

            $connection = $this->connection;
            $name = trim($departmentName);
            $directorUserId = (int)$directorUserId;

            if (empty($name)) {
                return ['success' => false, 'error' => 'Section / Department name cannot be empty.'];
            }

            if (mb_strlen($name) > 30) {
                return ['success' => false, 'error' => 'Section / Department name cannot exceed 30 characters.'];
            }

            // Case-insensitive duplicate check
            $checkSql = "SELECT id FROM departments WHERE LOWER(department) = LOWER(?) LIMIT 1";
            $checkStmt = $connection->prepare($checkSql);
            $checkStmt->bind_param("s", $name);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRes->num_rows > 0) {
                $checkStmt->close();
                return ['success' => false, 'error' => "A section named \"{$name}\" already exists."];
            }
            $checkStmt->close();

            $connection->begin_transaction();
            try {
                $sql = "INSERT INTO departments (department) VALUES (?)";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $newId = $connection->insert_id;
                $stmt->close();

                // If a director was chosen, assign them immediately
                if ($directorUserId > 0) {
                    $stmtDir = $connection->prepare("INSERT INTO director (id, department_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE department_id = VALUES(department_id)");
                    $stmtDir->bind_param("ii", $directorUserId, $newId);
                    $stmtDir->execute();
                    $stmtDir->close();
                }

                $connection->commit();
                return ['success' => true, 'id' => $newId];
            } catch (\Throwable $e) {
                $connection->rollback();
                return ['success' => false, 'error' => 'Database error while registering section: ' . $e->getMessage()];
            }
        }

        // Delete department/section with support for reassigning courses or cascading deletion.
        public function deleteDepartment($departmentId, $reassignToDeptId = 0, $cascadeDeleteCourses = false) {

            $connection = $this->connection;
            $departmentId = (int)$departmentId;
            $reassignToDeptId = (int)$reassignToDeptId;

            if ($departmentId <= 0) {
                return ['success' => false, 'error' => 'Invalid section identifier.'];
            }

            // Retrieve all courses assigned to this department
            $cStmt = $connection->prepare("SELECT id FROM course WHERE department_id = ?");
            $cStmt->bind_param("i", $departmentId);
            $cStmt->execute();
            $cRes = $cStmt->get_result();
            $courseIds = [];
            while ($cRow = $cRes->fetch_assoc()) {
                $courseIds[] = (int)$cRow['id'];
            }
            $cStmt->close();
            $courseCount = count($courseIds);

            // Reassign scenario
            if ($reassignToDeptId > 0) {
                if ($reassignToDeptId === $departmentId) {
                    return ['success' => false, 'error' => 'Cannot reassign courses to the section being deleted.'];
                }

                $connection->begin_transaction();
                try {
                    // Move courses
                    if ($courseCount > 0) {
                        $updCourses = $connection->prepare("UPDATE course SET department_id = ? WHERE department_id = ?");
                        $updCourses->bind_param("ii", $reassignToDeptId, $departmentId);
                        $updCourses->execute();
                        $updCourses->close();
                    }

                    // Move directors to target department
                    $updDir = $connection->prepare("UPDATE director SET department_id = ? WHERE department_id = ?");
                    $updDir->bind_param("ii", $reassignToDeptId, $departmentId);
                    $updDir->execute();
                    $updDir->close();

                    // Delete the old department
                    $delDept = $connection->prepare("DELETE FROM departments WHERE id = ?");
                    $delDept->bind_param("i", $departmentId);
                    $delDept->execute();
                    $delDept->close();

                    $connection->commit();
                    return ['success' => true, 'message' => "Section deleted and {$courseCount} course(s) successfully reassigned."];
                } catch (\Throwable $e) {
                    $connection->rollback();
                    return ['success' => false, 'error' => 'Database error during reassignment: ' . $e->getMessage()];
                }
            }

            // Own one transaction for the whole section and every course.
            if ($cascadeDeleteCourses) {
                $pathsToDelete = [];
                $connection->begin_transaction();
                try {
                    $courses = $connection->prepare('SELECT id FROM course WHERE department_id = ? ORDER BY id FOR UPDATE');
                    $courses->bind_param('i', $departmentId);
                    if (!$courses->execute()) { throw new RuntimeException('Cannot lock section courses.'); }
                    $result = $courses->get_result();
                    $courseIds = [];
                    while ($row = $result->fetch_assoc()) { $courseIds[] = (int)$row['id']; }
                    $courses->close();
                    foreach ($courseIds as $cId) {
                        $this->deleteCourseRecords($cId, $pathsToDelete);
                    }
                    $delDir = $connection->prepare('DELETE FROM director WHERE department_id = ?');
                    $delDir->bind_param('i', $departmentId);
                    if (!$delDir->execute()) { throw new RuntimeException('Cannot remove director assignment.'); }
                    $delDir->close();
                    $delDept = $connection->prepare('DELETE FROM departments WHERE id = ?');
                    $delDept->bind_param('i', $departmentId);
                    if (!$delDept->execute()) { throw new RuntimeException('Cannot delete section.'); }
                    $delDept->close();
                    if (!$connection->commit()) { throw new RuntimeException('Section deletion commit failed.'); }
                } catch (Throwable $e) {
                    $connection->rollback();
                    error_log('Section deletion failed: ' . $e->getMessage());
                    return ['success' => false, 'error' => 'Could not delete the section. No changes were saved.'];
                }
                $this->removeCommittedFiles($pathsToDelete);
                return ['success' => true, 'message' => 'Section and ' . count($courseIds) . ' associated course(s) deleted.'];
            }

            // Standard delete (if unassigned or 0 courses)
            $connection->begin_transaction();
            try {
                // If any director is linked, remove director record before deleting department
                $delDir = $connection->prepare("DELETE FROM director WHERE department_id = ?");
                $delDir->bind_param("i", $departmentId);
                $delDir->execute();
                $delDir->close();

                if ($courseCount > 0) {
                    $connection->rollback();
                    return [
                        'success' => false, 
                        'requires_reassignment' => true,
                        'course_count' => $courseCount,
                        'error' => "This section contains {$courseCount} course(s). Please choose to reassign them to another section or delete them."
                    ];
                }

                $delDept = $connection->prepare("DELETE FROM departments WHERE id = ?");
                $delDept->bind_param("i", $departmentId);
                $delDept->execute();
                $delDept->close();

                $connection->commit();
                return ['success' => true];
            } catch (\Throwable $e) {
                $connection->rollback();
                return ['success' => false, 'error' => 'Database error while removing section: ' . $e->getMessage()];
            }
        }

        // Update curricular specifications of course (title, department, credits, status).
        public function updateCourse($courseId, $courseName, $departmentId, $credits, $status = null) {

            $connection = $this->connection;
            $courseId = (int)$courseId;
            $courseName = trim($courseName);
            $departmentId = (int)$departmentId;
            $credits = (int)$credits;

            if ($status !== null) {
                $status = (int)$status;
                $sql = "UPDATE course SET course_name = ?, department_id = ?, credits = ?, status = ? WHERE id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("siiii", $courseName, $departmentId, $credits, $status, $courseId);
            } else {
                $sql = "UPDATE course SET course_name = ?, department_id = ?, credits = ? WHERE id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("siii", $courseName, $departmentId, $credits, $courseId);
            }

            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Delete course and perform cascading cleanup of files, blocks, and enrollments.
        public function deleteCourse($courseId) {
            $courseId = (int)$courseId;
            if ($courseId <= 0) { return false; }
            $connection = $this->connection;
            $pathsToDelete = [];
            $connection->begin_transaction();
            try {
                $this->deleteCourseRecords($courseId, $pathsToDelete);
                if (!$connection->commit()) { throw new RuntimeException('Course deletion commit failed.'); }
            } catch (Throwable $e) {
                $connection->rollback();
                return false;
            }
            $this->removeCommittedFiles($pathsToDelete);
            return true;
        }

        // Uses the caller's transaction. Never commits or removes files itself.
        private function deleteCourseRecords($courseId, array &$pathsToDelete) {
            $connection = $this->connection;
            $this->deleteCourseworkRecords('course_id', $courseId, $pathsToDelete);
            $links = $connection->prepare('DELETE bl FROM block_links bl INNER JOIN blocks b ON b.id = bl.block_id WHERE b.course_id = ?');
            $links->bind_param('i', $courseId);
            if (!$links->execute()) { throw new RuntimeException('Cannot delete course links.'); }
            $links->close();
            // 1. Fetch block IDs belonging to course
            $blockStmt = $connection->prepare("SELECT id FROM blocks WHERE course_id = ?");
            $blockStmt->bind_param("i", $courseId);
            if (!$blockStmt->execute()) { throw new RuntimeException('Course deletion query failed.'); }
            $bRes = $blockStmt->get_result();
            $blockIds = [];

            while ($bRow = $bRes->fetch_assoc()) {
                $blockIds[] = (int)$bRow['id'];
            }
            $blockStmt->close();

            // 2. Collect paths; physical deletion must wait until commit.
            if (!empty($blockIds)) {
                $inPlaceholders = implode(',', array_fill(0, count($blockIds), '?'));
                $types = str_repeat('i', count($blockIds));

                $fileQuery = "SELECT id, file_path FROM files WHERE subject_id IN ($inPlaceholders)";
                $fileStmt = $connection->prepare($fileQuery);
                $fileStmt->bind_param($types, ...$blockIds);
                if (!$fileStmt->execute()) { throw new RuntimeException('Course deletion query failed.'); }
                $fRes = $fileStmt->get_result();

                while ($f = $fRes->fetch_assoc()) {
                    $pathsToDelete[] = $f['file_path'];
                }
                $fileStmt->close();

                // Delete file records
                $delFilesStmt = $connection->prepare("DELETE FROM files WHERE subject_id IN ($inPlaceholders)");
                $delFilesStmt->bind_param($types, ...$blockIds);
                if (!$delFilesStmt->execute()) { throw new RuntimeException('Course deletion query failed.'); }
                $delFilesStmt->close();

                // Delete student block progress records
                $delProgStmt = $connection->prepare("DELETE FROM student_block_progress WHERE block_id IN ($inPlaceholders)");
                $delProgStmt->bind_param($types, ...$blockIds);
                if (!$delProgStmt->execute()) { throw new RuntimeException('Course deletion query failed.'); }
                $delProgStmt->close();
            }

            // 3. Delete student enrollments in this course
            $delEnrollStmt = $connection->prepare("DELETE FROM student WHERE course_id = ?");
            $delEnrollStmt->bind_param("i", $courseId);
            if (!$delEnrollStmt->execute()) { throw new RuntimeException('Course deletion query failed.'); }
            $delEnrollStmt->close();

            // 4. Delete syllabus module blocks
            $delBlocksStmt = $connection->prepare("DELETE FROM blocks WHERE course_id = ?");
            $delBlocksStmt->bind_param("i", $courseId);
            if (!$delBlocksStmt->execute()) { throw new RuntimeException('Course deletion query failed.'); }
            $delBlocksStmt->close();

            // 5. Delete course record
            $delCourseStmt = $connection->prepare("DELETE FROM course WHERE id = ?");
            $delCourseStmt->bind_param("i", $courseId);
            if (!$delCourseStmt->execute()) { throw new RuntimeException('Course deletion query failed.'); }
            $delCourseStmt->close();

        }

        // Delete a specific syllabus block and attached learning resources.
        public function deleteBlock($blockId) {

            $connection = $this->connection;
            $blockId = (int)$blockId;

            if ($blockId <= 0) {
                return false;
            }

            $connection->begin_transaction();
            $pathsToDelete = [];

            try {
                $this->deleteCourseworkRecords('block_id', $blockId, $pathsToDelete);
                $links = $connection->prepare('DELETE FROM block_links WHERE block_id = ?');
                $links->bind_param('i', $blockId);
                if (!$links->execute()) { throw new RuntimeException('Cannot delete module links.'); }
                $links->close();
                // Collect paths without changing the filesystem before commit.
                $fileStmt = $connection->prepare("SELECT id, file_path FROM files WHERE subject_id = ?");
                $fileStmt->bind_param("i", $blockId);
                $fileStmt->execute();
                $fRes = $fileStmt->get_result();

                while ($f = $fRes->fetch_assoc()) {
                    $pathsToDelete[] = $f['file_path'];
                }
                $fileStmt->close();

                // Delete database records
                $delFiles = $connection->prepare("DELETE FROM files WHERE subject_id = ?");
                $delFiles->bind_param("i", $blockId);
                $delFiles->execute();
                $delFiles->close();

                $delProg = $connection->prepare("DELETE FROM student_block_progress WHERE block_id = ?");
                $delProg->bind_param("i", $blockId);
                $delProg->execute();
                $delProg->close();

                $delBlock = $connection->prepare("DELETE FROM blocks WHERE id = ?");
                $delBlock->bind_param("i", $blockId);
                $delBlock->execute();
                $delBlock->close();

                $connection->commit();
            } catch (Throwable $e) {
                $connection->rollback();
                return false;
            }
            $this->removeCommittedFiles($pathsToDelete);
            return true;
        }

        private function deleteCourseworkRecords($column, $id, array &$paths) {
            if (!in_array($column, ['course_id', 'block_id'], true)) {
                throw new InvalidArgumentException('Invalid coursework deletion scope.');
            }
            $connection = $this->connection;
            $files = $connection->prepare("SELECT file_path FROM assignments WHERE {$column} = ?
                UNION ALL SELECT sub.file_path FROM assignment_submissions sub
                INNER JOIN assignments a ON a.id = sub.assignment_id WHERE a.{$column} = ?");
            $files->bind_param('ii', $id, $id);
            if (!$files->execute()) { throw new RuntimeException('Cannot collect coursework files.'); }
            $result = $files->get_result();
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['file_path'])) { $paths[] = $row['file_path']; }
            }
            $files->close();
            $submissions = $connection->prepare("DELETE sub FROM assignment_submissions sub
                INNER JOIN assignments a ON a.id = sub.assignment_id WHERE a.{$column} = ?");
            $submissions->bind_param('i', $id);
            if (!$submissions->execute()) { throw new RuntimeException('Cannot delete coursework submissions.'); }
            $submissions->close();
            $assignments = $connection->prepare("DELETE FROM assignments WHERE {$column} = ?");
            $assignments->bind_param('i', $id);
            if (!$assignments->execute()) { throw new RuntimeException('Cannot delete coursework assignments.'); }
            $assignments->close();
        }

        private function removeCommittedFiles(array $paths) {
            $root = realpath(__DIR__ . '/../../../resources');
            if (!$root) {
                return;
            }
            foreach (array_unique($paths) as $path) {
                $file = realpath(__DIR__ . '/../../../' . $path);
                if ($file && str_starts_with($file, $root . DIRECTORY_SEPARATOR) && is_file($file)) {
                    if (!@unlink($file)) {
                        error_log('Could not clean up a resource file after database commit: ' . $path);
                    }
                }
            }
        }

    }

?>
