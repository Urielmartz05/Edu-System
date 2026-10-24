<?php

    class AdminDashboard {

        private $connection;

        // Initialize MySQL database connection.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Count total student user accounts in the platform.
        public function getStudentsCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_students FROM users WHERE role = 4";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_students'] ?? 0);
        }

        // Count total course offerings in the catalog.
        public function getCourseCount() {
            
            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_courses FROM course";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_courses'] ?? 0);
        }

        // Count registered faculty members in teacher table.
        public function getTeachersCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_teachers FROM teacher";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_teachers'] ?? 0);
        }

        // Count user accounts pending email confirmation.
        public function getPendingAccountsCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_pending FROM users WHERE verified = 0";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_pending'] ?? 0);
        }

        // Count courses paused or currently under review.
        public function getCoursesInReviewCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_review FROM course WHERE status = 0";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_review'] ?? 0);
        }

        // Fetch top courses ranked by highest student enrollment.
        public function getTopCourses($limit = 5) {

            $connection = $this->connection;
            $sql = "SELECT 
                        c.id,
                        c.course_name,
                        c.status,
                        COUNT(DISTINCT s.id) AS total_students,
                        COALESCE(GROUP_CONCAT(DISTINCT CONCAT(u.name, ' ', u.lastname) SEPARATOR ', '), 'Unassigned') AS teacher_name
                    FROM course c
                    LEFT JOIN student s ON s.course_id = c.id
                    LEFT JOIN blocks b ON b.course_id = c.id
                    LEFT JOIN users u ON u.id = b.teacher_id
                    GROUP BY c.id, c.course_name, c.status
                    ORDER BY total_students DESC
                    LIMIT ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();

            $result = $stmt->get_result();
            $courses = [];

            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }

            $stmt->close();

            return $courses;
        }

    }

?>
