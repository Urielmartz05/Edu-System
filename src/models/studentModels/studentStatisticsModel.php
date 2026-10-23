<?php

    class StudentStatistics {

        private $connection;

        // La conexión opcional permite probar las lecturas sin cambiar la configuración.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // ID del usuario de la sesión; devuelve su inscripción o null.
        public function getEnrollment(int $studentId) {

            $connection = $this->connection;
            $sql = "SELECT id, course_id FROM student WHERE id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Usar el course_id de getEnrollment(); no recibe el ID del usuario.
        public function getCourseProgress(int $courseId) {

            $connection = $this->connection;
            $sql = "SELECT id, progress, status FROM course WHERE id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Usar el course_id de getEnrollment(); no recibe el ID del usuario.
        public function getSubjectsCount(int $courseId) {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_subjects FROM subject WHERE course_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_subjects'] ?? 0);
        }

        // Usar el course_id de getEnrollment(); no recibe el ID del usuario.
        public function getTeachersCount(int $courseId) {

            $connection = $this->connection;
            $sql = "SELECT COUNT(DISTINCT teacher_id) AS total_teachers FROM subject WHERE course_id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_teachers'] ?? 0);
        }

    }
