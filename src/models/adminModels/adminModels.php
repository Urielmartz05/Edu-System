<?php

    class AdminDashboard {

        private $connection;

        public function __construct() {

            require_once __DIR__ . '/../../config/connection.php';
            $this->connection = createConnection();

        }

        public function getStudentsCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_students FROM student";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_students'] ?? 0);
        }

        public function getCourseCount() {
            
            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_courses FROM course";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_courses'] ?? 0);
        }

        public function getTeachersCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_teachers FROM teacher";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_teachers'] ?? 0);
        }

        public function getPendingAccountsCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_pending FROM users WHERE verified = 0";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_pending'] ?? 0);
        }

        public function getCoursesInReviewCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_review FROM course WHERE status = 0";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_review'] ?? 0);
        }

        public function getTopCourses($limit = 5) {

            $connection = $this->connection;
            $sql = "SELECT 
                        c.id,
                        c.course_name,
                        c.status,
                        COUNT(DISTINCT s.id) AS total_students,
                        GROUP_CONCAT(DISTINCT CONCAT(u.name, ' ', u.lastname) SEPARATOR ', ') AS teacher_name
                    FROM course c
                    INNER JOIN student s ON s.course_id = c.id
                    INNER JOIN subject sub ON sub.course_id = c.id
                    INNER JOIN users u ON u.id = sub.teacher_id
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

            return $courses;
        }

    }

?>