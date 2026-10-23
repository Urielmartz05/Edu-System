<?php

    class AdminUsers {

        private $connection;

        public function __construct() {

            require_once __DIR__ . '/../../config/connection.php';
            $this->connection = createConnection();

        }

        public function getTotalUsersCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_users FROM users";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_users'] ?? 0);
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

        public function getAcademicStaffCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_staff FROM users WHERE role IN (2, 3)";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_staff'] ?? 0);
        }

        public function getPendingUsersCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_pending FROM users WHERE verified = 0";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int) ($row['total_pending'] ?? 0);
        }

        public function getAllUsers() {

            $connection = $this->connection;
            $sql = "SELECT id, name, lastname, email, role, verified FROM users ORDER BY id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $users = [];

            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            return $users;
        }

    }

?>
