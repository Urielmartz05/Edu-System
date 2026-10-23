<?php

    class StudentCourses {

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
        public function getCourse(int $courseId) {

            $connection = $this->connection;
            $sql = "SELECT id, course_name, department_id, progress, status FROM course WHERE id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Usar el course_id de getEnrollment(); no recibe el ID del usuario.
        public function getSubjects(int $courseId) {

            $connection = $this->connection;
            $sql = "SELECT id, course_id, subject_name, teacher_id FROM subject WHERE course_id = ? ORDER BY id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $courseId);
            $stmt->execute();

            $result = $stmt->get_result();
            $subjects = [];

            while ($row = $result->fetch_assoc()) {
                $subjects[] = $row;
            }

            $stmt->close();

            return $subjects;
        }

    }
