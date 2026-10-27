<?php

    class GradeModel {

        private $connection;

        // Initialize MySQL database connection
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Retrieve graded and pending assignments for a student across all courses or for a specific course
        public function getStudentGrades($studentId, $courseId = null) {

            $studentId = (int)$studentId;
            $connection = $this->connection;

            $sql = "SELECT
                        a.id AS assignment_id,
                        a.title AS assignment_title,
                        a.max_score,
                        a.due_date,
                        c.id AS course_id,
                        c.course_name,
                        b.id AS block_id,
                        b.block_name,
                        CONCAT(u_teacher.name, ' ', COALESCE(u_teacher.lastname, '')) AS teacher_name,
                        sub.id AS submission_id,
                        sub.score,
                        sub.feedback,
                        sub.submitted_at,
                        sub.graded_at,
                        sub.file_path AS submission_file,
                        COALESCE(sub.status, 'pending') AS submission_status
                    FROM assignments a
                    INNER JOIN course c ON a.course_id = c.id
                    INNER JOIN blocks b ON a.block_id = b.id
                    INNER JOIN student s ON s.course_id = c.id AND s.id = ?
                    INNER JOIN users u_teacher ON a.teacher_id = u_teacher.id
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
                    WHERE 1=1";

            if ($courseId !== null && (int)$courseId > 0) {
                $courseId = (int)$courseId;
                $sql .= " AND a.course_id = ?";
                $sql .= " ORDER BY c.course_name ASC, a.due_date DESC";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("iii", $studentId, $studentId, $courseId);
            } else {
                $sql .= " ORDER BY c.course_name ASC, a.due_date DESC";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("ii", $studentId, $studentId);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $grades = [];

            while ($row = $result->fetch_assoc()) {
                $row['max_score'] = (float)$row['max_score'];
                $row['score'] = $row['score'] !== null ? (float)$row['score'] : null;
                $row['percentage'] = ($row['score'] !== null && $row['max_score'] > 0)
                    ? round(($row['score'] / $row['max_score']) * 100, 2)
                    : null;
                $grades[] = $row;
            }

            $stmt->close();
            return $grades;
        }

        // Calculate student academic summary statistics (average score, completed, pending)
        public function getStudentAcademicSummary($studentId) {

            $studentId = (int)$studentId;
            $connection = $this->connection;

            $sql = "SELECT
                        COUNT(DISTINCT a.id) AS total_assignments,
                        COUNT(DISTINCT CASE WHEN sub.status IN ('submitted', 'graded', 'late') THEN sub.id END) AS submitted_count,
                        COUNT(DISTINCT CASE WHEN sub.status = 'graded' THEN sub.id END) AS graded_count,
                        AVG(CASE WHEN sub.status = 'graded' AND a.max_score > 0 THEN (sub.score / a.max_score) * 100 END) AS average_score
                    FROM assignments a
                    INNER JOIN student s ON s.course_id = a.course_id AND s.id = ?
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $studentId, $studentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $summary = $result->fetch_assoc();
            $stmt->close();

            if (!$summary) {
                return [
                    'total_assignments' => 0,
                    'submitted_count' => 0,
                    'graded_count' => 0,
                    'average_score' => 0.00
                ];
            }

            $summary['total_assignments'] = (int)$summary['total_assignments'];
            $summary['submitted_count'] = (int)$summary['submitted_count'];
            $summary['graded_count'] = (int)$summary['graded_count'];
            $summary['average_score'] = $summary['average_score'] !== null ? round((float)$summary['average_score'], 2) : 0.00;

            return $summary;
        }

        // Retrieve full gradebook matrix for a course taught by a specific teacher
        public function getTeacherCourseGradebook($teacherId, $courseId) {

            $teacherId = (int)$teacherId;
            $courseId = (int)$courseId;
            $connection = $this->connection;

            $access = $connection->prepare("SELECT 1 FROM blocks WHERE course_id = ? AND teacher_id = ? LIMIT 1");
            $access->bind_param("ii", $courseId, $teacherId);
            $access->execute();
            $authorized = $access->get_result()->num_rows > 0;
            $access->close();
            if (!$authorized) {
                return ['assignments' => [], 'students' => []];
            }

            // 1. Fetch assignments for this course
            $assignmentsSql = "SELECT id, title, max_score, due_date
                               FROM assignments
                               WHERE course_id = ? AND teacher_id = ?
                               ORDER BY due_date ASC, id ASC";
            $stmt = $connection->prepare($assignmentsSql);
            $stmt->bind_param("ii", $courseId, $teacherId);
            $stmt->execute();
            $assignmentsResult = $stmt->get_result();
            $assignments = [];
            while ($row = $assignmentsResult->fetch_assoc()) {
                $row['max_score'] = (float)$row['max_score'];
                $assignments[] = $row;
            }
            $stmt->close();

            // 2. Fetch enrolled students in this course
            $studentsSql = "SELECT u.id, CONCAT(u.name, ' ', COALESCE(u.lastname, '')) AS name, u.email
                            FROM student s
                            INNER JOIN users u ON s.id = u.id
                            WHERE s.course_id = ?
                            ORDER BY u.name ASC";
            $stmt = $connection->prepare($studentsSql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();
            $studentsResult = $stmt->get_result();
            $students = [];
            while ($row = $studentsResult->fetch_assoc()) {
                $students[$row['id']] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'grades' => [],
                    'total_points' => 0.00,
                    'max_possible_points' => 0.00,
                    'average' => 0.00
                ];
            }
            $stmt->close();

            // 3. Fetch all submissions for these assignments
            if (!empty($assignments) && !empty($students)) {
                $assignmentIds = array_column($assignments, 'id');
                $placeholders = implode(',', array_fill(0, count($assignmentIds), '?'));
                $types = str_repeat('i', count($assignmentIds));

                $submissionsSql = "SELECT assignment_id, student_id, score, status, graded_at
                                   FROM assignment_submissions
                                   WHERE assignment_id IN ($placeholders)";
                $stmt = $connection->prepare($submissionsSql);
                $stmt->bind_param($types, ...$assignmentIds);
                $stmt->execute();
                $submissionsResult = $stmt->get_result();

                while ($row = $submissionsResult->fetch_assoc()) {
                    $studentId = (int)$row['student_id'];
                    $assignmentId = (int)$row['assignment_id'];
                    if (isset($students[$studentId])) {
                        $students[$studentId]['grades'][$assignmentId] = [
                            'score' => $row['score'] !== null ? (float)$row['score'] : null,
                            'status' => $row['status']
                        ];
                    }
                }
                $stmt->close();

                // Compute student averages
                foreach ($students as $sId => &$studentData) {
                    $earnedPoints = 0.00;
                    $possiblePoints = 0.00;
                    foreach ($assignments as $assignment) {
                        $aId = $assignment['id'];
                        $max = $assignment['max_score'];
                        $possiblePoints += $max;

                        if (isset($studentData['grades'][$aId]) && $studentData['grades'][$aId]['score'] !== null) {
                            $earnedPoints += $studentData['grades'][$aId]['score'];
                        }
                    }

                    $studentData['total_points'] = round($earnedPoints, 2);
                    $studentData['max_possible_points'] = round($possiblePoints, 2);
                    $studentData['average'] = ($possiblePoints > 0) ? round(($earnedPoints / $possiblePoints) * 100, 2) : 0.00;
                }
                unset($studentData);
            }

            return [
                'assignments' => $assignments,
                'students' => array_values($students)
            ];
        }

        // Retrieve department courses academic performance summary for Director
        public function getDirectorDepartmentGrades($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;

            $sql = "SELECT
                        c.id AS course_id,
                        c.course_name,
                        c.status AS course_status,
                        COALESCE(teachers.teacher_name, 'Unassigned') AS teacher_name,
                        COALESCE(enrollment.enrolled_students, 0) AS enrolled_students,
                        COUNT(DISTINCT a.id) AS total_assignments,
                        COUNT(DISTINCT CASE WHEN sub.status = 'graded' THEN sub.id END) AS graded_submissions,
                        AVG(CASE WHEN sub.status = 'graded' AND a.max_score > 0 THEN (sub.score / a.max_score) * 100 END) AS course_average,
                        COUNT(DISTINCT CASE WHEN sub.status = 'graded' AND a.max_score > 0 AND (sub.score / a.max_score) * 100 >= 70 THEN sub.id END) AS passing_count
                    FROM course c
                    LEFT JOIN (SELECT course_id, COUNT(DISTINCT id) AS enrolled_students FROM student GROUP BY course_id) enrollment ON enrollment.course_id = c.id
                    LEFT JOIN (
                        SELECT b.course_id, GROUP_CONCAT(DISTINCT CONCAT(u.name, ' ', COALESCE(u.lastname, '')) ORDER BY u.name SEPARATOR ', ') AS teacher_name
                        FROM blocks b LEFT JOIN users u ON u.id = b.teacher_id GROUP BY b.course_id
                    ) teachers ON teachers.course_id = c.id
                    LEFT JOIN assignments a ON a.course_id = c.id
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id
                    WHERE c.department_id = ?
                    GROUP BY c.id, c.course_name, c.status, teachers.teacher_name, enrollment.enrolled_students
                    ORDER BY c.course_name ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $courses = [];

            while ($row = $result->fetch_assoc()) {
                $row['enrolled_students'] = (int)$row['enrolled_students'];
                $row['total_assignments'] = (int)$row['total_assignments'];
                $row['graded_submissions'] = (int)$row['graded_submissions'];
                $row['course_average'] = $row['course_average'] !== null ? round((float)$row['course_average'], 2) : 0.00;
                $row['passing_rate'] = ($row['graded_submissions'] > 0)
                    ? round(($row['passing_count'] / $row['graded_submissions']) * 100, 2)
                    : 0.00;
                $courses[] = $row;
            }

            $stmt->close();
            return $courses;
        }

        // Retrieve global institutional academic grades overview for Administrator
        public function getAdminGlobalGradesSummary() {

            $connection = $this->connection;

            // Overview metrics
            $overviewSql = "SELECT
                                COUNT(DISTINCT a.id) AS total_assignments,
                                COUNT(DISTINCT sub.id) AS total_submissions,
                                COUNT(DISTINCT CASE WHEN sub.status = 'graded' THEN sub.id END) AS graded_submissions,
                                AVG(CASE WHEN sub.status = 'graded' AND a.max_score > 0 THEN (sub.score / a.max_score) * 100 END) AS institutional_average
                            FROM assignments a
                            LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id";

            $overviewResult = $connection->query($overviewSql);
            $overview = $overviewResult->fetch_assoc();

            $overview['total_assignments'] = (int)$overview['total_assignments'];
            $overview['total_submissions'] = (int)$overview['total_submissions'];
            $overview['graded_submissions'] = (int)$overview['graded_submissions'];
            $overview['institutional_average'] = $overview['institutional_average'] !== null ? round((float)$overview['institutional_average'], 2) : 0.00;

            // Department summaries
            $departmentsSql = "SELECT
                                   d.id AS department_id,
                                   d.department AS department_name,
                                   COUNT(DISTINCT c.id) AS courses_count,
                                   COUNT(DISTINCT a.id) AS assignments_count,
                                   AVG(CASE WHEN sub.status = 'graded' AND a.max_score > 0 THEN (sub.score / a.max_score) * 100 END) AS department_average
                               FROM departments d
                               LEFT JOIN course c ON c.department_id = d.id
                               LEFT JOIN assignments a ON a.course_id = c.id
                               LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id
                               GROUP BY d.id, d.department
                               ORDER BY department_average DESC, d.department ASC";

            $deptResult = $connection->query($departmentsSql);
            $departments = [];
            while ($row = $deptResult->fetch_assoc()) {
                $row['courses_count'] = (int)$row['courses_count'];
                $row['assignments_count'] = (int)$row['assignments_count'];
                $row['department_average'] = $row['department_average'] !== null ? round((float)$row['department_average'], 2) : 0.00;
                $departments[] = $row;
            }

            return [
                'overview' => $overview,
                'departments' => $departments
            ];
        }

        // Retrieve individual student grades overview list with optional department/course/search filters
        public function getStudentsGradesOverview($departmentId = null, $courseId = null, $search = null) {

            $connection = $this->connection;

            $sql = "SELECT
                        s.id AS student_id,
                        CONCAT(u.name, ' ', COALESCE(u.lastname, '')) AS student_name,
                        u.email,
                        c.id AS course_id,
                        c.course_name,
                        d.id AS department_id,
                        COALESCE(d.department, 'General') AS department_name,
                        COUNT(DISTINCT a.id) AS total_assignments,
                        COUNT(DISTINCT CASE WHEN sub.status IN ('submitted', 'graded', 'late') THEN sub.id END) AS submitted_count,
                        COUNT(DISTINCT CASE WHEN sub.status = 'graded' THEN sub.id END) AS graded_count,
                        AVG(CASE WHEN sub.status = 'graded' AND a.max_score > 0 THEN (sub.score / a.max_score) * 100 END) AS average_score
                    FROM student s
                    INNER JOIN users u ON s.id = u.id
                    INNER JOIN course c ON s.course_id = c.id
                    LEFT JOIN departments d ON c.department_id = d.id
                    LEFT JOIN assignments a ON a.course_id = c.id
                    LEFT JOIN assignment_submissions sub ON sub.assignment_id = a.id AND sub.student_id = s.id
                    WHERE 1=1";

            $params = [];
            $types = "";

            if ($departmentId !== null && (int)$departmentId > 0) {
                $sql .= " AND c.department_id = ?";
                $params[] = (int)$departmentId;
                $types .= "i";
            }

            if ($courseId !== null && (int)$courseId > 0) {
                $sql .= " AND c.id = ?";
                $params[] = (int)$courseId;
                $types .= "i";
            }

            if (!empty($search)) {
                $sql .= " AND (u.name LIKE ? OR u.lastname LIKE ? OR u.email LIKE ? OR c.course_name LIKE ?)";
                $searchWildcard = "%" . trim($search) . "%";
                $params[] = $searchWildcard;
                $params[] = $searchWildcard;
                $params[] = $searchWildcard;
                $params[] = $searchWildcard;
                $types .= "ssss";
            }

            $sql .= " GROUP BY s.id, u.name, u.lastname, u.email, c.id, c.course_name, d.id, d.department
                      ORDER BY c.course_name ASC, u.name ASC";

            $stmt = $connection->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $students = [];

            while ($row = $result->fetch_assoc()) {
                $row['student_id'] = (int)$row['student_id'];
                $row['course_id'] = (int)$row['course_id'];
                $row['department_id'] = (int)($row['department_id'] ?? 0);
                $row['total_assignments'] = (int)$row['total_assignments'];
                $row['submitted_count'] = (int)$row['submitted_count'];
                $row['graded_count'] = (int)$row['graded_count'];
                $row['has_graded'] = ($row['graded_count'] > 0);
                $row['average_score'] = $row['average_score'] !== null ? round((float)$row['average_score'], 1) : null;
                $students[] = $row;
            }

            $stmt->close();
            return $students;
        }

        // Retrieve single student details with enrolled course and department
        public function getStudentProfileWithCourse($studentId) {

            $studentId = (int)$studentId;
            $connection = $this->connection;

            $sql = "SELECT
                        s.id AS student_id,
                        u.name,
                        u.lastname,
                        CONCAT(u.name, ' ', COALESCE(u.lastname, '')) AS full_name,
                        u.email,
                        c.id AS course_id,
                        c.course_name,
                        c.department_id,
                        COALESCE(d.department, 'General') AS department_name
                    FROM student s
                    INNER JOIN users u ON s.id = u.id
                    INNER JOIN course c ON s.course_id = c.id
                    LEFT JOIN departments d ON c.department_id = d.id
                    WHERE s.id = ?
                    LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $profile = $result->fetch_assoc();
            $stmt->close();

            return $profile;
        }

    }
