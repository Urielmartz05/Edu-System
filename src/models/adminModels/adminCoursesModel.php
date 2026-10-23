<?php

    class AdminCourses {

        private $connection;

        public function __construct() {

            require_once __DIR__ . '/../../config/connection.php';
            $this->connection = createConnection();

        }

        public function getTotalCoursesCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_courses FROM course";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_courses'] ?? 0);
        }

        public function getPublishedCoursesCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_published FROM course WHERE status = 1";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_published'] ?? 0);
        }

        public function getReviewCoursesCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_review FROM course WHERE status = 0";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_review'] ?? 0);
        }

        public function getTotalEnrollmentsCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_enrollments FROM student";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_enrollments'] ?? 0);
        }

        public function getAllCourses() {

            $connection = $this->connection;
            $sql = "SELECT id, course_name, department_id, progress, status FROM course ORDER BY id ASC";

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
