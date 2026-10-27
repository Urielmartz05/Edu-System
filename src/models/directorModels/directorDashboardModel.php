<?php

    class DirectorDashboard {

        private $connection;

        // Initialize the MySQL database connection
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Retrieve the academic department assigned to the director
        public function getDirectorDepartment($directorId) {

            $directorId = (int)$directorId;
            $connection = $this->connection;

            $sql = "SELECT 
                        d.department_id, 
                        dep.department AS department_name
                    FROM director d
                    INNER JOIN departments dep ON dep.id = d.department_id
                    WHERE d.id = ?
                    LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $directorId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: ['department_id' => 0, 'department_name' => 'Unassigned Department'];
        }

        // Count total courses belonging to the academic department
        public function getCoursesCount($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;

            $sql = "SELECT COUNT(id) AS total_courses FROM course WHERE department_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_courses'] ?? 0);
        }

        // Count active faculty members teaching modules in the department
        public function getFacultyCount($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;

            $sql = "SELECT COUNT(DISTINCT b.teacher_id) AS total_faculty
                    FROM blocks b
                    INNER JOIN course c ON c.id = b.course_id
                    WHERE c.department_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_faculty'] ?? 0);
        }

        // Count total students enrolled in courses within the department
        public function getStudentsCount($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;

            $sql = "SELECT COUNT(DISTINCT s.id) AS total_students
                    FROM student s
                    INNER JOIN course c ON c.id = s.course_id
                    WHERE c.department_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_students'] ?? 0);
        }

        // Calculate average progress across department courses
        public function getAverageProgress($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;

            $sql = "SELECT AVG(progress) AS avg_progress FROM course WHERE department_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return round((float) ($row['avg_progress'] ?? 0), 1);
        }

        // Retrieve department courses with student enrollment and module statistics
        public function getDepartmentCourses($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;

            $sql = "SELECT 
                        c.id,
                        c.course_name,
                        c.progress,
                        c.status,
                        c.credits,
                        COUNT(DISTINCT s.id) AS student_count,
                        COUNT(DISTINCT b.id) AS block_count,
                        COALESCE(GROUP_CONCAT(DISTINCT CONCAT(u.name, ' ', u.lastname) SEPARATOR ', '), 'Unassigned') AS faculty_names
                    FROM course c
                    LEFT JOIN student s ON s.course_id = c.id
                    LEFT JOIN blocks b ON b.course_id = c.id
                    LEFT JOIN users u ON u.id = b.teacher_id
                    WHERE c.department_id = ?
                    GROUP BY c.id, c.course_name, c.progress, c.status, c.credits
                    ORDER BY c.id ASC";

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

        // Retrieve faculty roster assigned to the department along with their modules
        public function getDepartmentFaculty($departmentId) {

            $departmentId = (int)$departmentId;
            $connection = $this->connection;

            $sql = "SELECT 
                        u.id,
                        CONCAT(u.name, ' ', u.lastname) AS teacher_name,
                        u.email,
                        COUNT(DISTINCT b.id) AS assigned_blocks_count,
                        GROUP_CONCAT(DISTINCT b.block_name SEPARATOR ' • ') AS block_names
                    FROM blocks b
                    INNER JOIN course c ON c.id = b.course_id
                    INNER JOIN users u ON u.id = b.teacher_id
                    WHERE c.department_id = ?
                    GROUP BY u.id, u.name, u.lastname, u.email
                    ORDER BY teacher_name ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $departmentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $faculty = [];

            while ($row = $result->fetch_assoc()) {
                $faculty[] = $row;
            }

            $stmt->close();

            return $faculty;
        }

    }

?>
