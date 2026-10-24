<?php

    class AdminUsers {

        private $connection;

        // Initialize MySQL database connection.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Count total registered users in the platform.
        public function getTotalUsersCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_users FROM users";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_users'] ?? 0);
        }

        // Count total user accounts with student role (role = 4).
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

        // Count faculty and academic leadership accounts (Directors and Teachers: roles 2 and 3).
        public function getAcademicStaffCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_staff FROM users WHERE role IN (2, 3)";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_staff'] ?? 0);
        }

        // Count user accounts pending email confirmation (verified = 0).
        public function getPendingUsersCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_pending FROM users WHERE verified = 0";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_pending'] ?? 0);
        }

        // Fetch complete community directory with role type and department metadata.
        public function getAllUsers() {

            $connection = $this->connection;
            $sql = "SELECT 
                        u.id, 
                        u.name, 
                        u.lastname, 
                        u.email, 
                        u.role, 
                        u.verified,
                        COALESCE(r.roleType, 'User') AS role_name,
                        dir.department_id,
                        dept.department AS department_name
                    FROM users u
                    LEFT JOIN role r ON r.id = u.role
                    LEFT JOIN director dir ON dir.id = u.id
                    LEFT JOIN departments dept ON dept.id = dir.department_id
                    ORDER BY u.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $users = [];

            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            $stmt->close();

            return $users;
        }

        // Check if an email address is already registered to another user account.
        public function isEmailTaken($email, $excludeUserId = 0) {

            $connection = $this->connection;
            $email = trim(strtolower($email));
            $excludeUserId = (int) $excludeUserId;

            $sql = "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("si", $email, $excludeUserId);
            $stmt->execute();

            $result = $stmt->get_result();
            $isTaken = ($result->num_rows > 0);
            $stmt->close();

            return $isTaken;
        }

        // Fetch detailed profile metadata of a specific user.
        public function getUserById($userId) {

            $connection = $this->connection;
            $userId = (int)$userId;

            $sql = "SELECT 
                        u.id, 
                        u.name, 
                        u.lastname, 
                        u.email, 
                        u.role, 
                        u.verified,
                        COALESCE(r.roleType, 'User') AS role_name,
                        dir.department_id,
                        dept.department AS department_name
                    FROM users u
                    LEFT JOIN role r ON r.id = u.role
                    LEFT JOIN director dir ON dir.id = u.id
                    LEFT JOIN departments dept ON dept.id = dir.department_id
                    WHERE u.id = ?
                    LIMIT 1";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row ?: null;
        }

        // Create new student account and record optional course enrollment.
        public function createStudent($name, $lastname, $email, $password, $courseId = null) {

            $connection = $this->connection;
            $name = trim($name);
            $lastname = trim($lastname);
            $email = trim(strtolower($email));

            if ($this->isEmailTaken($email)) {
                return ['success' => false, 'error' => 'Email address is already registered in the system.'];
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 4; // Student
            $verified = 1;

            $sqlUser = "INSERT INTO users (name, lastname, email, password, role, verified) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sqlUser);
            $stmt->bind_param("ssssii", $name, $lastname, $email, $hashedPassword, $role, $verified);

            if (!$stmt->execute()) {
                $stmt->close();
                return ['success' => false, 'error' => 'Database error creating user account.'];
            }

            $userId = $connection->insert_id;
            $stmt->close();

            // Record initial course enrollment if specified
            if ($courseId && (int)$courseId > 0) {
                $cId = (int)$courseId;
                $sqlStudent = "INSERT INTO student (id, course_id) VALUES (?, ?)";
                $stmtStudent = $connection->prepare($sqlStudent);
                $stmtStudent->bind_param("ii", $userId, $cId);
                $stmtStudent->execute();
                $stmtStudent->close();
            }

            return ['success' => true, 'id' => $userId];
        }

        // Create new teacher account and synchronize entry in teacher table.
        public function createTeacher($name, $lastname, $email, $password) {

            $connection = $this->connection;
            $name = trim($name);
            $lastname = trim($lastname);
            $email = trim(strtolower($email));

            if ($this->isEmailTaken($email)) {
                return ['success' => false, 'error' => 'Email address is already registered in the system.'];
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 3; // Teacher
            $verified = 1;

            $sqlUser = "INSERT INTO users (name, lastname, email, password, role, verified) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sqlUser);
            $stmt->bind_param("ssssii", $name, $lastname, $email, $hashedPassword, $role, $verified);

            if (!$stmt->execute()) {
                $stmt->close();
                return ['success' => false, 'error' => 'Database error creating teacher account.'];
            }

            $userId = $connection->insert_id;
            $stmt->close();

            $sqlTeacher = "INSERT INTO teacher (id) VALUES (?)";
            $stmtTeacher = $connection->prepare($sqlTeacher);
            $stmtTeacher->bind_param("i", $userId);
            $stmtTeacher->execute();
            $stmtTeacher->close();

            return ['success' => true, 'id' => $userId];
        }

        // Create new administrator account and synchronize entry in admin table.
        public function createAdmin($name, $lastname, $email, $password) {

            $connection = $this->connection;
            $name = trim($name);
            $lastname = trim($lastname);
            $email = trim(strtolower($email));

            if ($this->isEmailTaken($email)) {
                return ['success' => false, 'error' => 'Email address is already registered in the system.'];
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 1; // Admin
            $verified = 1;

            $sqlUser = "INSERT INTO users (name, lastname, email, password, role, verified) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sqlUser);
            $stmt->bind_param("ssssii", $name, $lastname, $email, $hashedPassword, $role, $verified);

            if (!$stmt->execute()) {
                $stmt->close();
                return ['success' => false, 'error' => 'Database error creating administrator account.'];
            }

            $userId = $connection->insert_id;
            $stmt->close();

            $sqlAdmin = "INSERT INTO admin (id) VALUES (?)";
            $stmtAdmin = $connection->prepare($sqlAdmin);
            $stmtAdmin->bind_param("i", $userId);
            $stmtAdmin->execute();
            $stmtAdmin->close();

            return ['success' => true, 'id' => $userId];
        }

        // Manually activate and verify a user account.
        public function verifyUser($userId) {

            $connection = $this->connection;
            $userId = (int) $userId;

            $sql = "UPDATE users SET verified = 1 WHERE id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $userId);

            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Reset account access password.
        public function resetPassword($userId, $newPassword) {

            $connection = $this->connection;
            $userId = (int) $userId;
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userId);

            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Update user profile metadata and synchronize role child-tables.
        public function updateUser($userId, $name, $lastname, $email, $role, $verified, $departmentId = null) {

            $connection = $this->connection;
            $userId = (int)$userId;
            $name = trim($name);
            $lastname = trim($lastname);
            $email = trim(strtolower($email));
            $role = (int)$role;
            $verified = (int)$verified;

            if ($this->isEmailTaken($email, $userId)) {
                return ['success' => false, 'error' => 'Email is already taken by another account.'];
            }

            $current = $this->getUserById($userId);
            if (!$current) {
                return ['success' => false, 'error' => 'User account does not exist.'];
            }
            $oldRole = (int)$current['role'];

            $connection->begin_transaction();

            try {
                $sql = "UPDATE users SET name = ?, lastname = ?, email = ?, role = ?, verified = ? WHERE id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("sssiii", $name, $lastname, $email, $role, $verified, $userId);
                $stmt->execute();
                $stmt->close();

                // If role changed, validate constraints and synchronize child-tables
                if ($oldRole !== $role) {
                    if ($oldRole === 3) {
                        $chk = $connection->prepare("SELECT COUNT(id) AS cnt FROM blocks WHERE teacher_id = ?");
                        $chk->bind_param("i", $userId);
                        $chk->execute();
                        $cnt = (int)($chk->get_result()->fetch_assoc()['cnt'] ?? 0);
                        $chk->close();

                        if ($cnt > 0) {
                            $connection->rollback();
                            return ['success' => false, 'error' => "Cannot change role of teacher #{$userId} while they are assigned to {$cnt} course block(s). Please reassign blocks first."];
                        }
                    }

                    // Remove legacy role records
                    $connection->query("DELETE FROM admin WHERE id = {$userId}");
                    $connection->query("DELETE FROM director WHERE id = {$userId}");
                    $connection->query("DELETE FROM teacher WHERE id = {$userId}");
                    $connection->query("DELETE FROM student WHERE id = {$userId}");

                    // Insert into new role child-table
                    if ($role === 1) {
                        $sAdmin = $connection->prepare("INSERT INTO admin (id) VALUES (?) ON DUPLICATE KEY UPDATE id=id");
                        $sAdmin->bind_param("i", $userId);
                        $sAdmin->execute();
                        $sAdmin->close();
                    } elseif ($role === 2) {
                        $dept = !empty($departmentId) ? (int)$departmentId : 1;
                        $sDir = $connection->prepare("INSERT INTO director (id, department_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE department_id = VALUES(department_id)");
                        $sDir->bind_param("ii", $userId, $dept);
                        $sDir->execute();
                        $sDir->close();
                    } elseif ($role === 3) {
                        $sTeacher = $connection->prepare("INSERT INTO teacher (id) VALUES (?) ON DUPLICATE KEY UPDATE id=id");
                        $sTeacher->bind_param("i", $userId);
                        $sTeacher->execute();
                        $sTeacher->close();
                    }
                } elseif ($role === 2 && !empty($departmentId)) {
                    // Update department if director role remains unchanged
                    $dept = (int)$departmentId;
                    $sDir = $connection->prepare("INSERT INTO director (id, department_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE department_id = VALUES(department_id)");
                    $sDir->bind_param("ii", $userId, $dept);
                    $sDir->execute();
                    $sDir->close();
                }

                $connection->commit();
                return ['success' => true];
            } catch (Exception $e) {
                $connection->rollback();
                return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
            }
        }

        // Safely delete user account preventing self-deletion and orphan states.
        public function deleteUser($userId, $currentAdminId = 0) {

            $connection = $this->connection;
            $userId = (int)$userId;
            $currentAdminId = (int)$currentAdminId;

            if ($userId <= 0) {
                return ['success' => false, 'error' => 'Invalid user ID.'];
            }

            if ($userId === $currentAdminId) {
                return ['success' => false, 'error' => 'You cannot delete your own active administrator account.'];
            }

            $user = $this->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'error' => 'User does not exist.'];
            }

            // Prevent deleting the system's sole administrator
            if ((int)$user['role'] === 1) {
                $adminCountRes = $connection->query("SELECT COUNT(id) AS cnt FROM users WHERE role = 1");
                $adminCnt = (int)($adminCountRes->fetch_assoc()['cnt'] ?? 0);
                if ($adminCnt <= 1) {
                    return ['success' => false, 'error' => 'Cannot delete the system\'s only administrator account.'];
                }
            }

            // Ensure teacher has no active course blocks before deletion
            if ((int)$user['role'] === 3) {
                $chk = $connection->prepare("SELECT COUNT(id) AS cnt FROM blocks WHERE teacher_id = ?");
                $chk->bind_param("i", $userId);
                $chk->execute();
                $bCnt = (int)($chk->get_result()->fetch_assoc()['cnt'] ?? 0);
                $chk->close();

                if ($bCnt > 0) {
                    return ['success' => false, 'error' => "Cannot delete teacher {$user['name']} because they are assigned to {$bCnt} module(s). Please reassign the modules first."];
                }
            }

            $connection->begin_transaction();

            try {
                // Delete dependent child-table records
                $connection->query("DELETE FROM student_block_progress WHERE student_id = {$userId}");
                $connection->query("DELETE FROM student WHERE id = {$userId}");
                $connection->query("DELETE FROM director WHERE id = {$userId}");
                $connection->query("DELETE FROM admin WHERE id = {$userId}");
                $connection->query("DELETE FROM teacher WHERE id = {$userId}");

                // Delete root user record
                $stmt = $connection->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();

                $connection->commit();
                return ['success' => true];
            } catch (Exception $e) {
                $connection->rollback();
                return ['success' => false, 'error' => 'Database deletion error: ' . $e->getMessage()];
            }
        }

    }

?>
