<?php
    // ==========================================
    // CONTROLLER: SECURE BLOCK FILE DOWNLOAD
    // ==========================================

    require_once __DIR__ . "/../auth/check_auth.php";
    requireRole([1, 2, 3, 4]);
    $user = getLoggedUser();
    $userId = (int)($user['id'] ?? 0);
    $roleId = (int)($_SESSION['role_id'] ?? 0);

    $fileId = (int)($_GET['file_id'] ?? 0);
    $mode = ($_GET['mode'] ?? 'download') === 'view' ? 'inline' : 'attachment';

    if ($fileId <= 0) {
        http_response_code(400);
        exit("Invalid file request.");
    }

    require_once __DIR__ . "/../../config/connection.php";
    $connection = createConnection();

    // Query file metadata and associated block/course
    $sql = "SELECT f.id, f.file_name, f.file_path, f.subject_id, b.course_id, b.block_name
            FROM files f
            INNER JOIN blocks b ON b.id = f.subject_id
            WHERE f.id = ?
            LIMIT 1";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $result = $stmt->get_result();
    $fileData = $result->fetch_assoc();
    $stmt->close();

    if (!$fileData) {
        http_response_code(404);
        exit("Requested learning resource does not exist.");
    }

    $courseId = (int)$fileData['course_id'];

    // Authorization check: Students must be enrolled in the course
    if ($roleId === 4) {
        $checkSql = "SELECT id FROM student WHERE id = ? AND course_id = ? LIMIT 1";
        $checkStmt = $connection->prepare($checkSql);
        $checkStmt->bind_param("ii", $userId, $courseId);
        $checkStmt->execute();
        $isEnrolled = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if (!$isEnrolled) {
            http_response_code(403);
            exit("Access denied: You are not enrolled in this course program.");
        }
    }

    // Resolve and validate physical storage path
    $resourcesRoot = realpath(__DIR__ . '/../../../resources');
    $fullPath = realpath(__DIR__ . '/../../../' . $fileData['file_path']);

    if (!$resourcesRoot || !$fullPath || !str_starts_with($fullPath, $resourcesRoot . DIRECTORY_SEPARATOR) || !is_file($fullPath)) {
        http_response_code(404);
        exit("Physical document not found on server.");
    }

    // Resolve clean display filename and MIME type
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $cleanFilename = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', $fileData['file_name']);
    if (!str_ends_with(strtolower($cleanFilename), '.' . $ext)) {
        $cleanFilename .= '.' . $ext;
    }

    $mimeMap = [
        'pdf'  => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc'  => 'application/msword',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls'  => 'application/vnd.ms-excel',
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'txt'  => 'text/plain'
    ];

    $contentType = $mimeMap[$ext] ?? 'application/octet-stream';
    $fileSize = filesize($fullPath);

    // Clean any prior output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Send HTTP stream headers
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: ' . $mode . '; filename="' . $cleanFilename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $fileSize);

    readfile($fullPath);
    exit;
?>
