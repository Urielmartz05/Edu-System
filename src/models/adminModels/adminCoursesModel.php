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

            $connection = $this->connection;
            $courseId = (int)$courseId;

            if ($courseId <= 0) {
                return false;
            }

            $connection->begin_transaction();

            try {
                // 1. Fetch block IDs belonging to course
                $blockStmt = $connection->prepare("SELECT id FROM blocks WHERE course_id = ?");
                $blockStmt->bind_param("i", $courseId);
                $blockStmt->execute();
                $bRes = $blockStmt->get_result();
                $blockIds = [];

                while ($bRow = $bRes->fetch_assoc()) {
                    $blockIds[] = (int)$bRow['id'];
                }
                $blockStmt->close();

                // 2. Unlink files on disk and remove records
                if (!empty($blockIds)) {
                    $inPlaceholders = implode(',', array_fill(0, count($blockIds), '?'));
                    $types = str_repeat('i', count($blockIds));
                    
                    $fileQuery = "SELECT id, file_path FROM files WHERE subject_id IN ($inPlaceholders)";
                    $fileStmt = $connection->prepare($fileQuery);
                    $fileStmt->bind_param($types, ...$blockIds);
                    $fileStmt->execute();
                    $fRes = $fileStmt->get_result();

                    while ($f = $fRes->fetch_assoc()) {
                        $fullPath = __DIR__ . '/../../../' . $f['file_path'];
                        if (file_exists($fullPath) && is_file($fullPath)) {
                            @unlink($fullPath);
                        }
                    }
                    $fileStmt->close();

                    // Delete file records
                    $delFilesStmt = $connection->prepare("DELETE FROM files WHERE subject_id IN ($inPlaceholders)");
                    $delFilesStmt->bind_param($types, ...$blockIds);
                    $delFilesStmt->execute();
                    $delFilesStmt->close();

                    // Delete student block progress records
                    $delProgStmt = $connection->prepare("DELETE FROM student_block_progress WHERE block_id IN ($inPlaceholders)");
                    $delProgStmt->bind_param($types, ...$blockIds);
                    $delProgStmt->execute();
                    $delProgStmt->close();
                }

                // 3. Delete student enrollments in this course
                $delEnrollStmt = $connection->prepare("DELETE FROM student WHERE course_id = ?");
                $delEnrollStmt->bind_param("i", $courseId);
                $delEnrollStmt->execute();
                $delEnrollStmt->close();

                // 4. Delete syllabus module blocks
                $delBlocksStmt = $connection->prepare("DELETE FROM blocks WHERE course_id = ?");
                $delBlocksStmt->bind_param("i", $courseId);
                $delBlocksStmt->execute();
                $delBlocksStmt->close();

                // 5. Delete course record
                $delCourseStmt = $connection->prepare("DELETE FROM course WHERE id = ?");
                $delCourseStmt->bind_param("i", $courseId);
                $delCourseStmt->execute();
                $delCourseStmt->close();

                $connection->commit();
                return true;
            } catch (Exception $e) {
                $connection->rollback();
                return false;
            }
        }

        // Delete a specific syllabus block and attached learning resources.
        public function deleteBlock($blockId) {

            $connection = $this->connection;
            $blockId = (int)$blockId;

            if ($blockId <= 0) {
                return false;
            }

            $connection->begin_transaction();

            try {
                // Unlink physical files
                $fileStmt = $connection->prepare("SELECT id, file_path FROM files WHERE subject_id = ?");
                $fileStmt->bind_param("i", $blockId);
                $fileStmt->execute();
                $fRes = $fileStmt->get_result();

                while ($f = $fRes->fetch_assoc()) {
                    $fullPath = __DIR__ . '/../../../' . $f['file_path'];
                    if (file_exists($fullPath) && is_file($fullPath)) {
                        @unlink($fullPath);
                    }
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
                return true;
            } catch (Exception $e) {
                $connection->rollback();
                return false;
            }
        }

    }

?>
