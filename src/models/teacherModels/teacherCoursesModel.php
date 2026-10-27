<?php

    class TeacherCourses {

        private $connection;

        // Initialize MySQL database connection.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Fetch all courses assigned to instructor with enrollment and module metrics.
        public function getCoursesWithDetails($teacherId) {

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
                        COUNT(DISTINCT b.id) AS my_blocks_count,
                        GROUP_CONCAT(DISTINCT b.block_name SEPARATOR '   ') AS my_blocks_list
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


        // Fetch course specifications from the instructor's perspective.
        public function getCourseDetails($courseId, $teacherId) {

            $courseId = (int)$courseId;
            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT
                        c.id,
                        c.course_name,
                        c.department_id,
                        COALESCE(d.department, 'General') AS department_name,
                        c.progress,
                        c.status,
                        c.credits,
                        COUNT(DISTINCT s.id) AS total_students,
                        COUNT(DISTINCT b.id) AS total_blocks,
                        COUNT(DISTINCT CASE WHEN b.teacher_id = ? THEN b.id END) AS my_blocks_count
                    FROM course c
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN blocks b ON b.course_id = c.id
                    LEFT JOIN student s ON s.course_id = c.id
                    WHERE c.id = ?
                    GROUP BY c.id, c.course_name, c.department_id, d.department, c.progress, c.status, c.credits
                    HAVING my_blocks_count > 0";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $teacherId, $courseId);
            $stmt->execute();
            $result = $stmt->get_result();
            $details = $result->fetch_assoc() ?: null;
            $stmt->close();

            return $details;
        }

        // Fetch all course syllabus blocks with ownership flag and attached files.
        public function getCourseBlocksWithFiles($courseId, $teacherId) {

            $courseId = (int)$courseId;
            $teacherId = (int)$teacherId;
            $connection = $this->connection;

            $sql = "SELECT
                        b.id,
                        b.block_name,
                        b.course_id,
                        b.teacher_id,
                        CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.lastname, '')) AS teacher_name,
                        CASE WHEN b.teacher_id = ? THEN 1 ELSE 0 END AS is_my_block
                    FROM blocks b
                    LEFT JOIN users u ON u.id = b.teacher_id
                    WHERE b.course_id = ?
                    ORDER BY b.id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $teacherId, $courseId);
            $stmt->execute();
            $result = $stmt->get_result();
            $blocks = [];

            while ($row = $result->fetch_assoc()) {
                $blockId = (int)$row['id'];
                $row['files'] = $this->getBlockFiles($blockId);
                $row['links'] = $this->getBlockLinks($blockId);
                $blocks[] = $row;
            }

            $stmt->close();

            return $blocks;
        }

        // Fetch study files attached to a syllabus module block.
        public function getBlockFiles($blockId) {

            $blockId = (int)$blockId;
            $connection = $this->connection;

            $sql = "SELECT id, subject_id, file_name, file_path
                    FROM files
                    WHERE subject_id = ?
                    ORDER BY id ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $blockId);
            $stmt->execute();
            $result = $stmt->get_result();
            $files = [];

            while ($row = $result->fetch_assoc()) {
                $files[] = $row;
            }

            $stmt->close();

            return $files;
        }

        // Fetch course student roster with real dynamic individual progress.
        public function getCourseStudents($courseId) {

            $courseId = (int)$courseId;
            $connection = $this->connection;

            $sql = "SELECT DISTINCT
                        u.id,
                        u.name,
                        u.lastname,
                        u.email,
                        u.verified,
                        COALESCE((
                            SELECT COUNT(DISTINCT sbp.block_id)
                            FROM student_block_progress sbp
                            INNER JOIN blocks b2 ON b2.id = sbp.block_id
                            WHERE sbp.student_id = u.id AND b2.course_id = ?
                        ), 0) AS completed_blocks,
                        COALESCE((
                            SELECT COUNT(id) FROM blocks WHERE course_id = ?
                        ), 0) AS total_blocks
                    FROM student s
                    INNER JOIN users u ON u.id = s.id
                    WHERE s.course_id = ?
                    ORDER BY u.name ASC, u.lastname ASC";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("iii", $courseId, $courseId, $courseId);
            $stmt->execute();
            $result = $stmt->get_result();
            $students = [];

            while ($row = $result->fetch_assoc()) {
                $total = (int)($row['total_blocks'] ?? 0);
                $completed = (int)($row['completed_blocks'] ?? 0);
                $row['progress_percent'] = ($total > 0) ? round(($completed / $total) * 100) : 0;
                $students[] = $row;
            }

            $stmt->close();

            return $students;
        }

        // Validate whether an instructor is the designated teacher of a block.
        public function isTeacherOfBlock($teacherId, $blockId) {

            $teacherId = (int)$teacherId;
            $blockId = (int)$blockId;
            $connection = $this->connection;

            $sql = "SELECT id FROM blocks WHERE id = ? AND teacher_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $blockId, $teacherId);
            $stmt->execute();
            $result = $stmt->get_result();

            $isOwner = ($result->num_rows > 0);
            $stmt->close();

            return $isOwner;
        }

        // Fetch syllabus blocks taught by an instructor within a specific course.
        public function getTeacherBlocksForCourse($teacherId, $courseId) {

            $teacherId = (int)$teacherId;
            $courseId = (int)$courseId;
            $connection = $this->connection;

            $sql = "SELECT id, block_name FROM blocks WHERE course_id = ? AND teacher_id = ? ORDER BY id ASC";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $courseId, $teacherId);
            $stmt->execute();
            $result = $stmt->get_result();
            $blocks = [];

            while ($row = $result->fetch_assoc()) {
                $blocks[] = $row;
            }

            $stmt->close();

            return $blocks;
        }

        // Upload and attach digital learning resource to a block.
        public function addFileToBlock($teacherId, $blockId, $fileName, $filePath) {

            if (!$this->isTeacherOfBlock($teacherId, $blockId)) {
                return false;
            }

            $blockId = (int)$blockId;
            $connection = $this->connection;

            $sql = "INSERT INTO files (subject_id, file_name, file_path) VALUES (?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("iss", $blockId, $fileName, $filePath);

            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Delete learning resource from physical disk and database.
        public function deleteFileFromBlock($teacherId, $fileId, $userRole = 3) {
            $teacherId = (int)$teacherId;
            $fileId = (int)$fileId;
            $userRole = (int)$userRole;
            if ($fileId <= 0 || !in_array($userRole, [1, 2, 3], true)) { return false; }
            $connection = $this->connection;
            $connection->begin_transaction();
            try {
                if ($userRole === 1) {
                    $sql = 'SELECT f.id, f.file_path FROM files f WHERE f.id = ?';
                } elseif ($userRole === 2) {
                    $sql = 'SELECT f.id, f.file_path FROM files f
                        INNER JOIN blocks b ON b.id = f.subject_id
                        INNER JOIN course c ON c.id = b.course_id
                        INNER JOIN director d ON d.department_id = c.department_id
                        WHERE f.id = ? AND d.id = ?';
                } else {
                    $sql = 'SELECT f.id, f.file_path FROM files f
                        INNER JOIN blocks b ON b.id = f.subject_id
                        WHERE f.id = ? AND b.teacher_id = ?';
                }
                $stmt = $connection->prepare($sql . ' FOR UPDATE');
                if ($userRole === 1) { $stmt->bind_param('i', $fileId); }
                else { $stmt->bind_param('ii', $fileId, $teacherId); }
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$row) { $connection->rollback(); return false; }
                $delete = $connection->prepare('DELETE FROM files WHERE id = ?');
                $delete->bind_param('i', $fileId);
                if (!$delete->execute() || $delete->affected_rows !== 1) { throw new RuntimeException('Cannot delete file record.'); }
                $delete->close();
                if (!$connection->commit()) { throw new RuntimeException('Cannot commit file deletion.'); }
            } catch (Throwable $e) {
                $connection->rollback();
                error_log('Resource deletion failed: ' . $e->getMessage());
                return false;
            }
            $root = realpath(__DIR__ . '/../../../resources');
            $path = realpath(__DIR__ . '/../../../' . $row['file_path']);
            if ($root && $path && str_starts_with($path, $root . DIRECTORY_SEPARATOR) && is_file($path)) {
                if (!@unlink($path)) { error_log('Could not clean up deleted resource: ' . $row['file_path']); }
            }
            return true;
        }

        // Add external multimedia or web reference link to a block.
        public function addLinkToBlock($teacherId, $blockId, $title, $url, $linkType = 'external') {
            if (!$this->isTeacherOfBlock($teacherId, $blockId)) {
                return false;
            }

            $blockId = (int)$blockId;
            $connection = $this->connection;

            $sql = "INSERT INTO block_links (block_id, title, url, link_type) VALUES (?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("isss", $blockId, $title, $url, $linkType);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        }

        // Delete external reference link from block.
        public function deleteLinkFromBlock($teacherId, $linkId, $userRole = 3) {
            $teacherId = (int)$teacherId;
            $linkId = (int)$linkId;
            $userRole = (int)$userRole;
            $connection = $this->connection;

            if ($userRole === 1) { // Admin
                $sql = "DELETE FROM block_links WHERE id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("i", $linkId);
            } elseif ($userRole === 2) { // Director
                $sql = "DELETE bl FROM block_links bl
                        INNER JOIN blocks b ON b.id = bl.block_id
                        INNER JOIN course c ON c.id = b.course_id
                        INNER JOIN director d ON d.department_id = c.department_id
                        WHERE bl.id = ? AND d.id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("ii", $linkId, $teacherId);
            } else { // Teacher
                $sql = "DELETE bl FROM block_links bl
                        INNER JOIN blocks b ON b.id = bl.block_id
                        WHERE bl.id = ? AND b.teacher_id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("ii", $linkId, $teacherId);
            }
            $success = $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            return $success && ($affected > 0);
        }

        // Fetch external links for a block.
        public function getBlockLinks($blockId) {
            $blockId = (int)$blockId;
            $connection = $this->connection;

            $sql = "SELECT id, block_id, title, url, link_type, created_at
                    FROM block_links
                    WHERE block_id = ?
                    ORDER BY id ASC";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $blockId);
            $stmt->execute();
            $result = $stmt->get_result();

            $links = [];
            while ($row = $result->fetch_assoc()) {
                $links[] = $row;
            }
            $stmt->close();

            return $links;
        }

    }

?>
