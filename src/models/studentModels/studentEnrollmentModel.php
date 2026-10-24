<?php

    class StudentEnrollment {

        private $connection;

        // Initialize MySQL database connection.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Fetch enrolled courses for student with live dynamic progress and module counts.
        public function getEnrolledCourses($studentId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $connection = $this->connection;
            $sql = "SELECT 
                        c.id,
                        c.course_name,
                        c.credits,
                        c.status,
                        d.department,
                        COUNT(DISTINCT b.id) AS total_blocks,
                        COALESCE((
                            SELECT COUNT(DISTINCT sbp.block_id) 
                            FROM student_block_progress sbp 
                            INNER JOIN blocks b2 ON b2.id = sbp.block_id 
                            WHERE sbp.student_id = s.id AND b2.course_id = c.id
                        ), 0) AS completed_blocks
                    FROM student s
                    INNER JOIN course c ON c.id = s.course_id
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN blocks b ON b.course_id = c.id
                    WHERE s.id = ?
                    GROUP BY c.id, c.course_name, c.credits, c.status, d.department, s.id
                    ORDER BY c.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $courses = [];

            while ($row = $result->fetch_assoc()) {
                $total = (int)($row['total_blocks'] ?? 0);
                $completed = (int)($row['completed_blocks'] ?? 0);
                $row['progress'] = ($total > 0) ? round(($completed / $total) * 100, 1) : 0.0;
                $courses[] = $row;
            }

            $stmt->close();

            return $courses;
        }

        // Fetch catalog of active courses available for new enrollment.
        public function getAvailableCourses($studentId = null) {

            $studentId = $studentId ?? ($_SESSION['user_id'] ?? 0);
            $connection = $this->connection;
            $sql = "SELECT 
                        c.id,
                        c.course_name,
                        c.credits,
                        c.status,
                        d.department,
                        COUNT(b.id) AS total_blocks
                    FROM course c
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN blocks b ON b.course_id = c.id
                    WHERE c.status = 1
                      AND c.id NOT IN (
                          SELECT course_id FROM student WHERE id = ?
                      )
                    GROUP BY c.id, c.course_name, c.credits, c.status, d.department
                    ORDER BY c.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $courses = [];

            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }

            $stmt->close();

            return $courses;
        }

        // Verify if a student is actively enrolled in a specific course.
        public function isEnrolled($studentId, $courseId) {

            $connection = $this->connection;
            $sql = "SELECT 1 FROM student WHERE id = ? AND course_id = ? LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $enrolled = ($result->num_rows > 0);
            $stmt->close();

            return $enrolled;
        }

        // Enroll student into the designated course.
        public function enroll($studentId, $courseId) {

            if ($this->isEnrolled($studentId, $courseId)) {
                return true;
            }

            $connection = $this->connection;
            $sql = "INSERT INTO student (id, course_id) VALUES (?, ?)";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $courseId);

            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Unenroll student from the designated course.
        public function unenroll($studentId, $courseId) {

            $connection = $this->connection;
            $sql = "DELETE FROM student WHERE id = ? AND course_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $courseId);

            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

    }

?>
