<?php

class ResourceDownload {
    public function __construct(private $connection) {}

    public function getAuthorizedResource($type, $id, $userId, $roleId) {
        if (!in_array($type, ['assignment', 'submission'], true) || $id <= 0 || $userId <= 0) {
            return null;
        }
        if ($type === 'assignment') {
            $sql = "SELECT a.file_path, a.title AS file_name, a.course_id, a.teacher_id,
                           c.department_id, NULL AS student_id
                    FROM assignments a INNER JOIN course c ON c.id = a.course_id WHERE a.id = ?";
        } else {
            $sql = "SELECT sub.file_path, a.title AS file_name, a.course_id, a.teacher_id,
                           c.department_id, sub.student_id
                    FROM assignment_submissions sub
                    INNER JOIN assignments a ON a.id = sub.assignment_id
                    INNER JOIN course c ON c.id = a.course_id WHERE sub.id = ?";
        }
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resource = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$resource || empty($resource['file_path'])) {
            return null;
        }
        if ($roleId === 1 || ($roleId === 3 && (int)$resource['teacher_id'] === $userId)) {
            return $resource;
        }
        if ($roleId === 4) {
            if ($type === 'submission' && (int)$resource['student_id'] !== $userId) {
                return null;
            }
            $access = $this->connection->prepare('SELECT 1 FROM student WHERE id = ? AND course_id = ? LIMIT 1');
            $access->bind_param('ii', $userId, $resource['course_id']);
        } elseif ($roleId === 2) {
            $access = $this->connection->prepare('SELECT 1 FROM director WHERE id = ? AND department_id = ? LIMIT 1');
            $access->bind_param('ii', $userId, $resource['department_id']);
        } else {
            return null;
        }
        $access->execute();
        $allowed = $access->get_result()->num_rows > 0;
        $access->close();
        return $allowed ? $resource : null;
    }
}
