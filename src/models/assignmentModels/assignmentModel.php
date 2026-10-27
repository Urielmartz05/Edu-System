<?php

    class AssignmentModel {

        private $connection;

        // Initialize MySQL database connection
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Create a new assignment linked to a course and syllabus block
        public function createAssignment($courseId, $blockId, $teacherId, $title, $description, $maxScore, $dueDate, $filePath = null) {

            $courseId = (int)$courseId;
            $blockId = (int)$blockId;
            $teacherId = (int)$teacherId;
            $title = trim($title);
            $description = trim($description);
            $maxScore = (float)$maxScore;
            $connection = $this->connection;

            $sql = "SELECT id FROM blocks WHERE id = ? AND course_id = ?";

            $blockCheck = $connection->prepare($sql);
            $blockCheck->bind_param("ii", $blockId, $courseId);
            $blockCheck->execute();
            $result = $blockCheck->get_result();
            $validBlock = ($result->num_rows === 1);
            $blockCheck->close();

            if (!$validBlock || $title === '' || $maxScore <= 0 || $maxScore > 999.99 || !is_finite($maxScore) || strtotime($dueDate) === false) {
                return false;
            }

            $sql = "INSERT INTO assignments
                    (course_id, block_id, teacher_id, title, description, max_score, due_date, file_path)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("iiissdss", $courseId, $blockId, $teacherId, $title, $description, $maxScore, $dueDate, $filePath);

            $success = $stmt->execute();
            $insertId = $stmt->insert_id;
            $stmt->close();

            return $success ? $insertId : false;
        }

        // Retrieve assignments created by a specific teacher with submission counters
        public function getTeacherAssignments($teacherId, $courseId = null) {

            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT
                        a.id,
                        a.course_id,
                        a.block_id,
                        a.teacher_id,
                        a.title,
                        a.description,
                        a.max_score,
                        a.due_date,
                        a.file_path,
                        a.created_at,
                        c.course_name,
                        b.block_name,
                        COUNT(DISTINCT s.id) AS total_enrolled_students,
                        COUNT(DISTINCT sub.id) AS total_submissions,
                        COUNT(DISTINCT CASE WHEN sub.score IS NOT NULL THEN sub.id END) AS graded_submissions
                    FROM assignments a
                    INNER JOIN course c ON c.id = a.course_id
                    INNER JOIN blocks b ON b.id = a.block_id
                    LEFT JOIN student s ON s.course_id = a.course_id
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id
                    WHERE a.teacher_id = ?";

            if ($courseId !== null && (int)$courseId > 0) {
                $sql .= " AND a.course_id = " . (int)$courseId;
            }

            $sql .= " GROUP BY a.id, a.course_id, a.block_id, a.teacher_id, a.title, a.description, a.max_score, a.due_date, a.file_path, a.created_at, c.course_name, b.block_name
                      ORDER BY a.due_date DESC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();

            $result = $stmt->get_result();
            $assignments = [];

            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }

            $stmt->close();

            return $assignments;
        }

        // Retrieve detailed record for an assignment
        public function getAssignmentDetails($assignmentId) {

            $assignmentId = (int)$assignmentId;
            $connection = $this->connection;

            $sql = "SELECT
                        a.*,
                        c.course_name,
                        b.block_name,
                        u.name AS teacher_name,
                        u.lastname AS teacher_lastname,
                        u.email AS teacher_email
                    FROM assignments a
                    INNER JOIN course c ON c.id = a.course_id
                    INNER JOIN blocks b ON b.id = a.block_id
                    INNER JOIN users u ON u.id = a.teacher_id
                    WHERE a.id = ?
                    LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $assignmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Retrieve all submissions for a specific assignment for teacher review
        public function getAssignmentSubmissions($assignmentId, $teacherId = null) {

            $assignmentId = (int)$assignmentId;
            $connection = $this->connection;

            $sql = "SELECT
                        s.id AS student_id,
                        u.name AS student_name,
                        u.lastname AS student_lastname,
                        u.email AS student_email,
                        sub.id AS submission_id,
                        sub.submission_text,
                        sub.file_path,
                        sub.submitted_at,
                        sub.score,
                        sub.feedback,
                        sub.graded_at,
                        sub.status
                    FROM assignments a
                    INNER JOIN student s ON s.course_id = a.course_id
                    INNER JOIN users u ON u.id = s.id
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.student_id = s.id
                    WHERE a.id = ?";

            if ($teacherId !== null && (int)$teacherId > 0) {
                $sql .= " AND a.teacher_id = " . (int)$teacherId;
            }

            $sql .= " ORDER BY u.lastname ASC, u.name ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $assignmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $submissions = [];

            while ($row = $result->fetch_assoc()) {
                $submissions[] = $row;
            }

            $stmt->close();

            return $submissions;
        }

        // Evaluate and record score and feedback for a student submission or student assignment
        public function gradeSubmission($submissionId, $teacherId, $score, $feedback, $assignmentId = null, $studentId = null) {

            $submissionId = (int)$submissionId;
            $assignmentId = (int)$assignmentId;
            $studentId = (int)$studentId;
            $score = (float)$score;
            $feedback = trim($feedback);
            $connection = $this->connection;

            $assignment = null;
            if ($assignmentId > 0) {
                $check = $connection->prepare("SELECT id, teacher_id, max_score FROM assignments WHERE id = ?");
                $check->bind_param("i", $assignmentId);
                $check->execute();
                $assignment = $check->get_result()->fetch_assoc();
                $check->close();
            } else if ($submissionId > 0) {
                $sql = "SELECT a.id, a.teacher_id, a.max_score
                        FROM assignment_submissions sub
                        INNER JOIN assignments a ON a.id = sub.assignment_id
                        WHERE sub.id = ?";
                $check = $connection->prepare($sql);
                $check->bind_param("i", $submissionId);
                $check->execute();
                $assignment = $check->get_result()->fetch_assoc();
                $check->close();
                if ($assignment) {
                    $assignmentId = (int)$assignment['id'];
                }
            } else {
                return false;
            }

            if (!$assignment || !is_finite($score) || $score < 0 || $score > (float)$assignment['max_score']
                || ($teacherId !== null && (int)$assignment['teacher_id'] !== (int)$teacherId)) {
                return false;
            }

            if ($submissionId > 0) {
                $sql = "UPDATE assignment_submissions
                        SET score = ?, feedback = ?, graded_at = NOW(), status = 'graded'
                        WHERE id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("dsi", $score, $feedback, $submissionId);
                $updated = $stmt->execute();
                $stmt->close();
                return $updated;
            }

            if ($studentId > 0 && $assignmentId > 0) {
                $sql = "INSERT INTO assignment_submissions (assignment_id, student_id, score, feedback, graded_at, status)
                        VALUES (?, ?, ?, ?, NOW(), 'graded')
                        ON DUPLICATE KEY UPDATE
                            score = VALUES(score),
                            feedback = VALUES(feedback),
                            graded_at = NOW(),
                            status = 'graded'";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("iids", $assignmentId, $studentId, $score, $feedback);
                $updated = $stmt->execute();
                $stmt->close();
                return $updated;
            }

            return false;
        }

        // Delete an assignment if the requesting teacher is the owner
        public function deleteAssignment($assignmentId, $teacherId) {

            $assignmentId = (int)$assignmentId;
            $teacherId = $teacherId === null ? null : (int)$teacherId;
            $connection = $this->connection;

            $details = $this->getAssignmentDetails($assignmentId);
            if (!$details || ($teacherId !== null && (int)$details['teacher_id'] !== $teacherId)) {
                return false;
            }

            $sql = "DELETE FROM assignments WHERE id = ? AND (? IS NULL OR teacher_id = ?)";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("iii", $assignmentId, $teacherId, $teacherId);
            $deleted = $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($deleted && $affected > 0 && !empty($details['file_path'])) {
                $resourceRoot = realpath(__DIR__ . '/../../../resources/assignments');
                $fullPath = realpath(__DIR__ . '/../../../' . $details['file_path']);
                if ($resourceRoot && $fullPath && str_starts_with($fullPath, $resourceRoot . DIRECTORY_SEPARATOR) && is_file($fullPath)) {
                    unlink($fullPath);
                }
            }
            return $deleted && ($affected > 0);
        }

        // Retrieve assignments visible to a student with submission status
        public function getStudentAssignments($studentId, $courseId = null, $statusFilter = null) {

            $studentId = (int)$studentId;
            $connection = $this->connection;

            $sql = "SELECT
                        a.id,
                        a.course_id,
                        a.block_id,
                        a.title,
                        a.description,
                        a.max_score,
                        a.due_date,
                        a.file_path,
                        a.created_at,
                        c.course_name,
                        b.block_name,
                        CONCAT(u.name, ' ', u.lastname) AS teacher_name,
                        sub.id AS submission_id,
                        sub.submitted_at,
                        sub.score,
                        sub.feedback,
                        sub.file_path AS submission_file_path,
                        CASE
                            WHEN sub.id IS NULL AND NOW() > a.due_date THEN 'overdue'
                            WHEN sub.id IS NULL THEN 'pending'
                            WHEN sub.score IS NOT NULL THEN 'graded'
                            ELSE 'submitted'
                        END AS task_state
                    FROM assignments a
                    INNER JOIN course c ON c.id = a.course_id
                    INNER JOIN blocks b ON b.id = a.block_id
                    INNER JOIN users u ON u.id = a.teacher_id
                    INNER JOIN student s ON s.course_id = a.course_id AND s.id = ?
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.student_id = s.id
                    WHERE 1 = 1";

            if ($courseId !== null && (int)$courseId > 0) {
                $sql .= " AND a.course_id = " . (int)$courseId;
            }

            $sql .= " ORDER BY a.due_date ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $assignments = [];

            while ($row = $result->fetch_assoc()) {
                if ($statusFilter !== null && !empty($statusFilter) && $statusFilter !== 'all') {
                    if ($row['task_state'] !== $statusFilter) {
                        continue;
                    }
                }
                $assignments[] = $row;
            }

            $stmt->close();

            return $assignments;
        }

        // Submit student deliverable work
        public function submitAssignment($assignmentId, $studentId, $submissionText, $filePath = null) {

            $assignmentId = (int)$assignmentId;
            $studentId = (int)$studentId;
            $submissionText = trim($submissionText);
            $connection = $this->connection;

            // Check if submitted past deadline
            $details = $this->getAssignmentDetails($assignmentId);
            $enrollment = $connection->prepare("SELECT 1 FROM student WHERE id = ? AND course_id = ?");
            $courseId = (int)($details['course_id'] ?? 0);
            $enrollment->bind_param("ii", $studentId, $courseId);
            $enrollment->execute();
            $enrolled = $enrollment->get_result()->num_rows > 0;
            $enrollment->close();
            if (!$details || !$enrolled) {
                return false;
            }
            $isLate = false;
            if ($details && !empty($details['due_date'])) {
                $isLate = (strtotime('now') > strtotime($details['due_date']));
            }
            $status = $isLate ? 'late' : 'submitted';

            $sql = "INSERT INTO assignment_submissions
                    (assignment_id, student_id, submission_text, file_path, submitted_at, status)
                    VALUES (?, ?, ?, ?, NOW(), ?)
                    ON DUPLICATE KEY UPDATE
                        submission_text = VALUES(submission_text),
                        file_path = COALESCE(VALUES(file_path), file_path),
                        submitted_at = NOW(),
                        score = NULL,
                        feedback = NULL,
                        graded_at = NULL,
                        status = VALUES(status)";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("iisss", $assignmentId, $studentId, $submissionText, $filePath, $status);

            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Retrieve existing student submission for a specific assignment
        public function getStudentSubmission($assignmentId, $studentId) {

            $assignmentId = (int)$assignmentId;
            $studentId = (int)$studentId;
            $connection = $this->connection;

            $sql = "SELECT * FROM assignment_submissions WHERE assignment_id = ? AND student_id = ? LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $assignmentId, $studentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Retrieve department assignments for director supervision
        public function getDepartmentAssignments($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;

            $sql = "SELECT
                        a.id,
                        a.title,
                        a.max_score,
                        a.due_date,
                        c.id AS course_id,
                        c.course_name,
                        b.block_name,
                        CONCAT(u.name, ' ', u.lastname) AS teacher_name,
                        COUNT(DISTINCT s.id) AS student_count,
                        COUNT(DISTINCT sub.id) AS submission_count,
                        COUNT(DISTINCT CASE WHEN sub.score IS NOT NULL THEN sub.id END) AS graded_count
                    FROM assignments a
                    INNER JOIN course c ON c.id = a.course_id
                    INNER JOIN blocks b ON b.id = a.block_id
                    INNER JOIN users u ON u.id = a.teacher_id
                    LEFT JOIN student s ON s.course_id = c.id
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id
                    WHERE c.department_id = ?
                    GROUP BY a.id, a.title, a.max_score, a.due_date, c.id, c.course_name, b.block_name, u.name, u.lastname
                    ORDER BY a.due_date DESC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $assignments = [];

            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }

            $stmt->close();

            return $assignments;
        }

        // Retrieve all institution assignments for administrator directory
        public function getAllAssignments($departmentId = null, $courseId = null) {

            $connection = $this->connection;

            $sql = "SELECT
                        a.id,
                        a.title,
                        a.max_score,
                        a.due_date,
                        a.created_at,
                        c.id AS course_id,
                        c.course_name,
                        d.department AS department_name,
                        b.block_name,
                        CONCAT(u.name, ' ', u.lastname) AS teacher_name,
                        COUNT(DISTINCT s.id) AS student_count,
                        COUNT(DISTINCT sub.id) AS submission_count,
                        COUNT(DISTINCT CASE WHEN sub.score IS NOT NULL THEN sub.id END) AS graded_count
                    FROM assignments a
                    INNER JOIN course c ON c.id = a.course_id
                    LEFT JOIN departments d ON d.id = c.department_id
                    INNER JOIN blocks b ON b.id = a.block_id
                    INNER JOIN users u ON u.id = a.teacher_id
                    LEFT JOIN student s ON s.course_id = c.id
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id
                    WHERE 1 = 1";

            if ($departmentId !== null && (int)$departmentId > 0) {
                $sql .= " AND c.department_id = " . (int)$departmentId;
            }

            if ($courseId !== null && (int)$courseId > 0) {
                $sql .= " AND a.course_id = " . (int)$courseId;
            }

            $sql .= " GROUP BY a.id, a.title, a.max_score, a.due_date, a.created_at, c.id, c.course_name, d.department, b.block_name, u.name, u.lastname
                      ORDER BY a.id DESC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $assignments = [];

            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }

            $stmt->close();

            return $assignments;
        }

    }

?>
