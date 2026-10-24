<?php

    class StudentDashboard {

        private $connection;

        // Initialize MySQL database connection.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Resolve active course ID from argument or session.
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

        // Fetch general details of the student's active course.
        public function getActiveCourse($studentId = null, $courseId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $targetCourseId = $this->resolveCourseId($studentId, $courseId);
            $connection = $this->connection;

            $sql = "SELECT 
                        c.id, 
                        c.course_name, 
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

        // Calculate dynamic syllabus completion percentage for the student.
        public function getCourseProgress($studentId = null, $courseId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $targetCourseId = $this->resolveCourseId($studentId, $courseId);
            $connection = $this->connection;

            if ($studentId <= 0 || $targetCourseId <= 0) {
                return 0.0;
            }

            // Total modules in course
            $totalSql = "SELECT COUNT(id) AS total FROM blocks WHERE course_id = ?";
            $totalStmt = $connection->prepare($totalSql);
            $totalStmt->bind_param("i", $targetCourseId);
            $totalStmt->execute();
            $totalRow = $totalStmt->get_result()->fetch_assoc();
            $total = (int)($totalRow['total'] ?? 0);
            $totalStmt->close();

            if ($total <= 0) {
                return 0.0;
            }

            // Modules completed by student
            $compSql = "SELECT COUNT(sbp.block_id) AS completed 
                        FROM student_block_progress sbp
                        INNER JOIN blocks b ON b.id = sbp.block_id
                        WHERE sbp.student_id = ? AND b.course_id = ?";
            $compStmt = $connection->prepare($compSql);
            $compStmt->bind_param("ii", $studentId, $targetCourseId);
            $compStmt->execute();
            $compRow = $compStmt->get_result()->fetch_assoc();
            $completed = (int)($compRow['completed'] ?? 0);
            $compStmt->close();

            return round(($completed / $total) * 100, 1);
        }

        // Fetch academic weight in credits for active course.
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

        // Count syllabus modules belonging to active course.
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

        // Fetch syllabus blocks for active course with assigned instructor.
        public function getBlocks($studentId = null, $courseId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $targetCourseId = $this->resolveCourseId($studentId, $courseId);
            $connection = $this->connection;

            $sql = "SELECT 
                        b.id,
                        b.block_name,
                        b.course_id,
                        c.course_name,
                        c.status,
                        CONCAT(u.name, ' ', u.lastname) AS teacher_name
                    FROM student s
                    INNER JOIN course c ON c.id = s.course_id
                    INNER JOIN blocks b ON b.course_id = s.course_id
                    LEFT JOIN users u ON u.id = b.teacher_id
                    WHERE s.id = ? AND s.course_id = ?
                    ORDER BY b.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $targetCourseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $blocks = [];

            while ($row = $result->fetch_assoc()) {
                $blocks[] = $row;
            }

            $stmt->close();

            return $blocks;
        }

    }

?>
