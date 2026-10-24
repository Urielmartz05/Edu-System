<?php

    class StudentStatistics {

        private $connection;

        // Initialize the database connection
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Retrieve course progress details for a specific student
        public function getCourseProgress($studentId = null) {

            $studentId = (int)($studentId ?? ($_SESSION['user_id'] ?? 0));
            $connection = $this->connection;

            $sql = "SELECT 
                        c.id, 
                        c.course_name, 
                        c.progress, 
                        c.status 
                    FROM student s
                    INNER JOIN course c ON c.id = s.course_id
                    WHERE s.id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Count total blocks/modules in the student's active course
        public function getBlocksCount($studentId = null) {

            $studentId = (int)($studentId ?? ($_SESSION['user_id'] ?? 0));
            $connection = $this->connection;

            $sql = "SELECT COUNT(b.id) AS total_blocks 
                    FROM student s
                    INNER JOIN blocks b ON b.course_id = s.course_id
                    WHERE s.id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_blocks'] ?? 0);
        }

        // Count total instructors teaching blocks in the student's active course
        public function getInstructorsCount($studentId = null) {

            $studentId = (int)($studentId ?? ($_SESSION['user_id'] ?? 0));
            $connection = $this->connection;

            $sql = "SELECT COUNT(DISTINCT b.teacher_id) AS total_instructors 
                    FROM student s
                    INNER JOIN blocks b ON b.course_id = s.course_id
                    WHERE s.id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_instructors'] ?? 0);
        }

        // Backward compatibility method for subjects count
        public function getSubjectsCount($studentId = null) {
            return $this->getBlocksCount($studentId);
        }

        // Backward compatibility method for teachers count
        public function getTeachersCount($studentId = null) {
            return $this->getInstructorsCount($studentId);
        }

    }

?>