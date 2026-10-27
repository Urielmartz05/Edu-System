<?php

    class StudentCourses {

        private $connection;

        // Initialize MySQL database connection.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Resolve student's active course ID from argument or session.
        private function resolveCourseId($studentId, $courseId = null) {

            if (!empty($courseId)) {
                return (int) $courseId;
            }

            if (!empty($_SESSION['active_course_id'])) {
                return (int) $_SESSION['active_course_id'];
            }

            $sql = "SELECT course_id FROM student WHERE id = ? ORDER BY course_id ASC LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $resolved = (int) ($row['course_id'] ?? 0);
            if ($resolved > 0) {
                $_SESSION['active_course_id'] = $resolved;
            }

            return $resolved;
        }

        // Fetch general course metadata for the enrolled student.
        public function getCourse($studentId = null, $courseId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $targetCourseId = $this->resolveCourseId($studentId, $courseId);
            $connection = $this->connection;

            $sql = "SELECT
                        c.id,
                        c.course_name,
                        c.department_id,
                        c.progress,
                        c.status,
                        c.credits
                    FROM student s
                    INNER JOIN course c ON c.id = s.course_id
                    WHERE s.id = ? AND s.course_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $targetCourseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Calculate and return the student's dynamic syllabus completion percentage.
        public function getCourseProgress($studentId = null, $courseId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $targetCourseId = $this->resolveCourseId($studentId, $courseId);

            if ($targetCourseId <= 0 || $studentId <= 0) {
                return 0.0;
            }

            return $this->getDynamicCourseProgress($studentId, $targetCourseId);
        }

        // Check whether a specific curriculum module has been marked as completed.
        public function isBlockCompleted($studentId, $blockId) {

            $studentId = (int)$studentId;
            $blockId = (int)$blockId;
            $connection = $this->connection;

            $sql = "SELECT block_id FROM student_block_progress WHERE student_id = ? AND block_id = ? LIMIT 1";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $blockId);
            $stmt->execute();

            $result = $stmt->get_result();
            $isDone = ($result->num_rows > 0);
            $stmt->close();

            return $isDone;
        }

        // Toggle module completion status (completed <-> in progress).
        public function toggleBlockCompletion($studentId, $blockId) {

            $studentId = (int)$studentId;
            $blockId = (int)$blockId;
            $connection = $this->connection;

            if ($this->isBlockCompleted($studentId, $blockId)) {
                $sql = "DELETE FROM student_block_progress WHERE student_id = ? AND block_id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("ii", $studentId, $blockId);
                $stmt->execute();
                $stmt->close();
                return false; // Now incomplete
            } else {
                $sql = "INSERT INTO student_block_progress (student_id, block_id) VALUES (?, ?)";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("ii", $studentId, $blockId);
                $stmt->execute();
                $stmt->close();
                return true; // Now completed
            }
        }

        // Perform mathematical progress calculation: (completed blocks / total blocks) * 100.
        public function getDynamicCourseProgress($studentId, $courseId) {

            $studentId = (int)$studentId;
            $courseId = (int)$courseId;
            $connection = $this->connection;

            // Total modules in course
            $totalSql = "SELECT COUNT(id) AS total FROM blocks WHERE course_id = ?";
            $totalStmt = $connection->prepare($totalSql);
            $totalStmt->bind_param("i", $courseId);
            $totalStmt->execute();
            $totalRow = $totalStmt->get_result()->fetch_assoc();
            $total = (int)($totalRow['total'] ?? 0);
            $totalStmt->close();

            if ($total <= 0) {
                return 0.0;
            }

            // Modules completed by this student
            $compSql = "SELECT COUNT(sbp.block_id) AS completed
                        FROM student_block_progress sbp
                        INNER JOIN blocks b ON b.id = sbp.block_id
                        WHERE sbp.student_id = ? AND b.course_id = ?";
            $compStmt = $connection->prepare($compSql);
            $compStmt->bind_param("ii", $studentId, $courseId);
            $compStmt->execute();
            $compRow = $compStmt->get_result()->fetch_assoc();
            $completed = (int)($compRow['completed'] ?? 0);
            $compStmt->close();

            $progress = round(($completed / $total) * 100, 1);
            return $progress;
        }

        // Fetch course academic weight in credits.
        public function getCourseCredits($studentId = null, $courseId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $targetCourseId = $this->resolveCourseId($studentId, $courseId);
            $connection = $this->connection;

            $sql = "SELECT c.credits
                    FROM student s
                    INNER JOIN course c ON c.id = s.course_id
                    WHERE s.id = ? AND s.course_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $targetCourseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['credits'] ?? 0);
        }

        // Fetch all course modules with instructor info and completion status.
        public function getBlocks($studentId = null, $courseId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $targetCourseId = $this->resolveCourseId($studentId, $courseId);
            $connection = $this->connection;

            $sql = "SELECT
                        b.id,
                        b.block_name,
                        b.course_id,
                        u.name,
                        u.lastname,
                        CONCAT(u.name, ' ', u.lastname) AS teacher_name,
                        CASE WHEN sbp.block_id IS NOT NULL THEN 1 ELSE 0 END AS is_completed
                    FROM student s
                    INNER JOIN blocks b ON b.course_id = s.course_id
                    INNER JOIN teacher t ON t.id = b.teacher_id
                    INNER JOIN users u ON u.id = t.id
                    LEFT JOIN student_block_progress sbp ON sbp.block_id = b.id AND sbp.student_id = ?
                    WHERE s.id = ? AND s.course_id = ?
                    ORDER BY b.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("iii", $studentId, $studentId, $targetCourseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $blocks = [];

            while ($row = $result->fetch_assoc()) {
                $blockId = (int)$row['id'];
                $row['files'] = $this->getBlockFiles($blockId);
                $row['links'] = $this->getBlockLinks($blockId);
                $blocks[] = $row;
            }

            $stmt->close();

            return $blocks;
        }

        // Count total syllabus modules in the course.
        public function getBlocksCount($studentId = null, $courseId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $targetCourseId = $this->resolveCourseId($studentId, $courseId);
            $connection = $this->connection;

            $sql = "SELECT COUNT(b.id) AS total_blocks
                    FROM student s
                    INNER JOIN blocks b ON b.course_id = s.course_id
                    WHERE s.id = ? AND s.course_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $targetCourseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_blocks'] ?? 0);
        }

        // Fetch full technical specifications and assigned instructor for a specific module.
        public function getBlockDetails($blockId, $studentId = null) {

            $blockId = (int) $blockId;
            $studentId = (int) ($studentId ?? ($_SESSION['user_id'] ?? 0));
            $connection = $this->connection;

            $sql = "SELECT
                        b.id,
                        b.block_name,
                        b.course_id,
                        c.course_name,
                        c.credits,
                        c.status,
                        c.progress,
                        COALESCE(d.department, 'General') AS department_name,
                        u.id AS teacher_id,
                        CONCAT(u.name, ' ', u.lastname) AS teacher_name,
                        u.email AS teacher_email,
                        CASE WHEN sbp.block_id IS NOT NULL THEN 1 ELSE 0 END AS is_completed
                    FROM blocks b
                    INNER JOIN course c ON c.id = b.course_id
                    INNER JOIN student enrollment ON enrollment.course_id = c.id AND enrollment.id = ?
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN users u ON u.id = b.teacher_id
                    LEFT JOIN student_block_progress sbp ON sbp.block_id = b.id AND sbp.student_id = ?
                    WHERE b.id = ?
                    LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("iii", $studentId, $studentId, $blockId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Fetch attached files and study resources for a module block.
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

        // Fetch external links and multimedia resources attached to a block.
        public function getBlockLinks($blockId) {
            $blockId = (int)$blockId;
            $connection = $this->connection;

            $sql = "SELECT id, block_id, title, url, link_type, created_at
                    FROM block_links
                    WHERE block_id = ?
                    ORDER BY id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $blockId);
            $stmt->execute();
            $result = $stmt->get_result();
            $links = [];

            while ($row = $result->fetch_assoc()) {
                $links[] = $row;
            }

            $stmt->close();

            return $links;
        }

    }

?>
