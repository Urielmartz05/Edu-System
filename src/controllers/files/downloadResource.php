<?php
require_once __DIR__ . '/../auth/check_auth.php';
requireRole([1, 2, 3, 4]);
$user = getLoggedUser();
$type = $_GET['type'] ?? '';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!is_string($type) || !in_array($type, ['assignment', 'submission'], true) || !$id) {
    http_response_code(400);
    exit('Invalid resource request.');
}
require_once __DIR__ . '/../../models/fileModels/resourceDownloadModel.php';
$model = new ResourceDownload(createConnection());
$resource = $model->getAuthorizedResource($type, $id, (int)$user['id'], (int)$user['role']);
if (!$resource) {
    http_response_code(404);
    exit('Resource not found.');
}
$root = realpath(__DIR__ . '/../../../resources/' . ($type === 'assignment' ? 'assignments' : 'submissions'));
$path = realpath(__DIR__ . '/../../../' . $resource['file_path']);
if (!$root || !$path || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
    http_response_code(404);
    exit('Resource file not found.');
}
$stream = fopen($path, 'rb');
if ($stream === false) {
    http_response_code(404);
    exit('Resource file unavailable.');
}
$name = preg_replace('/[^a-zA-Z0-9_. -]/', '_', $resource['file_name']);
$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$name = ($name ?: 'download') . ($extension !== '' ? '.' . $extension : '');
session_write_close();
while (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Cache-Control: private, no-store');
header('Content-Length: ' . fstat($stream)['size']);
fpassthru($stream);
fclose($stream);
exit;
