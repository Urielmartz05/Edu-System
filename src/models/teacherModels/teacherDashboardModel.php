<?php

    class TeacherDashboard {

        private $connection;

        // Initialize the MySQL database connection
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Count distinct courses where the teacher has assigned blocks/modules
        public function getAssignedCoursesCount($teacherId) {

            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT COUNT(DISTINCT course_id) AS total_courses FROM blocks WHERE teacher_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_courses'] ?? 0);
        }

        // Count total modules/blocks taught by the teacher
        public function getAssignedBlocksCount($teacherId) {

            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT COUNT(id) AS total_blocks FROM blocks WHERE teacher_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_blocks'] ?? 0);
        }

        // Count total distinct students enrolled in courses taught by the teacher
        public function getEnrolledStudentsCount($teacherId) {

            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT COUNT(DISTINCT s.id) AS total_students 
                    FROM student s 
                    INNER JOIN blocks b ON b.course_id = s.course_id 
                    WHERE b.teacher_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_students'] ?? 0);
        }

        // Calculate average completion progress across the teacher's courses
        public function getAverageProgress($teacherId) {

            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT AVG(c.progress) AS avg_progress 
                    FROM course c 
                    INNER JOIN blocks b ON b.course_id = c.id 
                    WHERE b.teacher_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return round((float) ($row['avg_progress'] ?? 0), 1);
        }

        // Retrieve full list of assigned courses with enrollment metrics and modules
        public function getAssignedCourses($teacherId) {

            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT 
                        c.id,
                        c.course_name,
                        COALESCE(d.department, 'General') AS department_name,
                        c.progress,
                        c.status,
                        c.credits,
                        COUNT(DISTINCT s.id) AS student_count,
                        COUNT(DISTINCT b.id) AS block_count,
                        GROUP_CONCAT(DISTINCT b.block_name ORDER BY b.id ASC SEPARATOR '   ') AS my_blocks_list
                    FROM blocks b
                    INNER JOIN course c ON c.id = b.course_id
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN student s ON s.course_id = c.id
                    WHERE b.teacher_id = ?
                    GROUP BY c.id, c.course_name, d.department, c.progress, c.status, c.credits
                    ORDER BY c.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();

            $result = $stmt->get_result();
            $courses = [];

            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }

            $stmt->close();

            return $courses;
        }

        // Retrieve modules assigned to the teacher along with uploaded resource counts
        public function getAssignedBlocks($teacherId) {

            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT 
                        b.id,
                        b.block_name,
                        b.course_id,
                        c.course_name,
                        COUNT(f.id) AS files_count
                    FROM blocks b
                    INNER JOIN course c ON c.id = b.course_id
                    LEFT JOIN files f ON f.subject_id = b.id
                    WHERE b.teacher_id = ?
                    GROUP BY b.id, b.block_name, b.course_id, c.course_name
                    ORDER BY c.id ASC, b.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();

            $result = $stmt->get_result();
            $blocks = [];

            while ($row = $result->fetch_assoc()) {
                $blocks[] = $row;
            }

            $stmt->close();

            return $blocks;
        }

        // Retrieve recently enrolled students in courses taught by the teacher
        public function getRecentEnrolledStudents($teacherId, $limit = 6) {

            $teacherId = (int)$teacherId;
            $limit = (int)$limit;
            $connection = $this->connection;

            $sql = "SELECT DISTINCT 
                        u.id,
                        u.name,
                        u.lastname,
                        u.email,
                        u.verified,
                        c.id AS course_id,
                        c.course_name
                    FROM student s
                    INNER JOIN users u ON u.id = s.id
                    INNER JOIN course c ON c.id = s.course_id
                    INNER JOIN blocks b ON b.course_id = c.id
                    WHERE b.teacher_id = ?
                    ORDER BY u.id DESC
                    LIMIT ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $teacherId, $limit);
            $stmt->execute();

            $result = $stmt->get_result();
            $students = [];

            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }

            $stmt->close();

            return $students;
        }

    }

?>