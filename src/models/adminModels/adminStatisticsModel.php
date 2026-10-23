<?php

    class AdminStatistics {

        private $connection;

        public function __construct() {

            require_once __DIR__ . '/../../config/connection.php';
            $this->connection = createConnection();

        }

        public function getTotalEnrollments() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_enrollments FROM student";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_enrollments'] ?? 0);
        }

        public function getAverageCourseProgress() {

            $connection = $this->connection;
            $sql = "SELECT AVG(progress) AS avg_progress FROM course";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return round((float) ($row['avg_progress'] ?? 0), 1);
        }

        public function getActiveCoursesCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS active_courses FROM course WHERE status = 1";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['active_courses'] ?? 0);
        }

        public function getCoursesProgress() {

            $connection = $this->connection;
            $sql = "SELECT id, course_name, progress, status FROM course ORDER BY progress DESC";

            $stmt = $connection->prepare($sql);
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
