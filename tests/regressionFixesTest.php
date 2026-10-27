<?php
// Isolated regression checks: no live database and no real email transport.
session_set_save_handler(new class implements SessionHandlerInterface {
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }
    public function read(string $id): string|false { return ''; }
    public function write(string $id, string $data): bool { return true; }
    public function destroy(string $id): bool { return true; }
    public function gc(int $max_lifetime): int|false { return 0; }
});
session_start();
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) { return false; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
$_SESSION['user_id'] = 1;
require_once __DIR__ . '/../src/models/adminModels/adminCoursesModel.php';
require_once __DIR__ . '/../src/models/adminModels/adminUsersModel.php';
require_once __DIR__ . '/../src/models/fileModels/resourceDownloadModel.php';
require_once __DIR__ . '/../src/config/verificationEmail.php';
require_once __DIR__ . '/../src/controllers/auth/check_auth.php';

function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

class TestRows {
    public int $num_rows;
    public function __construct(private array $rows) { $this->num_rows = count($rows); }
    public function fetch_assoc() { return array_shift($this->rows); }
}

class TestStatement {
    private array $values = [];
    private array $rows = [];
    public function __construct(private TestDatabase $db, private string $sql) {}
    public function bind_param($types, &...$values) { $this->values = $values; }
    public function execute() {
        $this->db->queries[] = $this->sql;
        if (preg_match('/^(UPDATE|DELETE|INSERT)/', $this->sql)) {
            $this->db->writes++;
        }
        $this->rows = ($this->db->respond)($this->sql, $this->values, $this->db);
        return true;
    }
    public function get_result() { return new TestRows($this->rows); }
    public function close() {}
}

class TestDatabase {
    public array $queries = [];
    public int $writes = 0;
    public bool $inTransaction = false;
    public bool $rolledBack = false;
    public bool $committed = false;
    public $onCommit = null;
    public function __construct(public $respond) {}
    public function prepare($sql) { return new TestStatement($this, $sql); }
    public function query($sql) {
        $stmt = $this->prepare($sql);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function begin_transaction() { $this->inTransaction = true; }
    public function commit() {
        if ($this->onCommit) { ($this->onCommit)(); }
        $this->committed = true;
        $this->inTransaction = false;
    }
    public function rollback() { $this->rolledBack = true; $this->inTransaction = false; }
}

// Rollbacks, commit failures and successful deletes must preserve the ordering
// between database changes and physical file removal.
foreach (['deleteCourse', 'deleteBlock'] as $method) {
    foreach (['delete failure', 'commit failure', 'success'] as $scenario) {
        $fixture = tempnam(__DIR__ . '/../resources/documents', 'regression-');
        file_put_contents($fixture, 'Disposable fixture');
        $relative = 'resources/documents/' . basename($fixture);
        try {
            $db = new TestDatabase(function ($sql) use ($scenario, $relative) {
                if ($scenario === 'delete failure' && str_starts_with($sql, 'DELETE FROM blocks')) {
                    throw new RuntimeException('Simulated delete failure');
                }
                if (str_starts_with($sql, 'SELECT id FROM blocks')) { return [['id' => 1]]; }
                if (str_starts_with($sql, 'SELECT id, file_path FROM files')) {
                    return [['id' => 1, 'file_path' => $relative]];
                }
                return [];
            });
            $db->onCommit = function () use ($fixture, $scenario) {
                check(is_file($fixture), 'File was removed before commit');
                if ($scenario === 'commit failure') { throw new RuntimeException('Simulated commit failure'); }
            };
            $result = (new AdminCourses($db))->$method(1);
            clearstatcache(true, $fixture);
            check($result === ($scenario === 'success'), "$method returned the wrong result: $scenario");
            check(is_file($fixture) === ($scenario !== 'success'), "$method lost or retained a file incorrectly: $scenario");
            check($scenario === 'success' ? $db->committed : $db->rolledBack, 'Transaction outcome mismatch');
        } finally {
            if (is_file($fixture)) { unlink($fixture); }
        }
    }
}
echo "PASS: course/module deletion ordering and rollback (6 cases)\n";

class TestAdminUsers extends AdminUsers {
    public function isEmailTaken($email, $excludeUserId = 0) { return false; }
    public function getUserById($userId) { return ['id' => $userId, 'role' => 1, 'verified' => 1, 'name' => 'Admin']; }
}
foreach ([[[1], 4, 1, false], [[1], 1, 0, false], [[1], 1, 1, true], [[1, 2], 4, 1, true]] as [$admins, $role, $verified, $allowed]) {
    $db = new TestDatabase(function ($sql, $values, $db) use ($admins) {
        if (str_contains($sql, 'FOR UPDATE')) {
            check($db->inTransaction, 'Admin rows must be locked inside the write transaction');
            return array_map(fn($id) => ['id' => $id], $admins);
        }
        return [];
    });
    $result = (new TestAdminUsers($db))->updateUser(1, 'Admin', 'One', 'admin@example.com', $role, $verified);
    check($result['success'] === $allowed, 'Last active admin update guard failed');
    if (!$allowed) { check($db->writes === 0 && $db->rolledBack, 'Rejected update must not write'); }
}
foreach ([[[1], 2, false], [[1, 2], 1, false], [[1, 2], 2, true]] as [$admins, $currentId, $allowed]) {
    $db = new TestDatabase(function ($sql, $values, $db) use ($admins) {
        if (str_contains($sql, 'FOR UPDATE')) {
            check($db->inTransaction, 'Admin deletion lock is outside the transaction');
            return array_map(fn($id) => ['id' => $id], $admins);
        }
        return [];
    });
    $result = (new TestAdminUsers($db))->deleteUser(1, $currentId);
    check($result['success'] === $allowed, 'Admin self-deletion or last active admin guard failed');
    if (!$allowed) { check($db->writes === 0, 'Rejected deletion must not write'); }
}
echo "PASS: last active administrator and self-deletion guards (7 cases)\n";

$cases = [
    ['assignment', 1, 1, false, true], ['submission', 1, 1, false, true],
    ['assignment', 30, 3, false, true], ['submission', 30, 3, false, true],
    ['assignment', 31, 3, false, false], ['submission', 31, 3, false, false],
    ['assignment', 40, 4, true, true], ['assignment', 40, 4, false, false],
    ['submission', 40, 4, true, true], ['submission', 40, 4, false, false],
    ['submission', 41, 4, true, false],
    ['assignment', 20, 2, true, true], ['assignment', 20, 2, false, false],
    ['submission', 20, 2, true, true], ['submission', 20, 2, false, false],
    ['other', 1, 1, true, false], ['submission', 1, 0, true, false]
];
foreach ($cases as [$type, $userId, $roleId, $access, $allowed]) {
    $db = new TestDatabase(function ($sql, $values) use ($access) {
        if (str_starts_with($sql, 'SELECT 1')) {
            check(in_array($values[1], [5, 7], true), 'Incorrect enrollment/department scope');
            return $access ? [['allowed' => 1]] : [];
        }
        return [['file_path' => 'resources/submissions/example.pdf', 'file_name' => 'Task',
            'course_id' => 5, 'department_id' => 7, 'teacher_id' => 30, 'student_id' => 40]];
    });
    $resource = (new ResourceDownload($db))->getAuthorizedResource($type, 9, $userId, $roleId);
    check(($resource !== null) === $allowed, "Wrong download permission: $type / role $roleId / user $userId");
}
echo "PASS: attachment permissions across all roles (17 cases)\n";

$fixture = tempnam(__DIR__ . '/../resources/assignments', 'regression-');
try {
    $link = resourceLinkAttributes('resources/assignments/' . basename($fixture), 'assignment', 12);
    check(str_contains($link, 'downloadResource.php?type=assignment&amp;id=12'), 'Attachment bypasses the authenticated download controller');
    check(resourceLinkAttributes('README.md', 'assignment', 12) === 'aria-disabled="true"', 'Non-resource path was accepted');
} finally { unlink($fixture); }
echo "PASS: protected attachment links\n";

$viewSource = file_get_contents(__DIR__ . '/../src/views/ponente/ponenteAssignments.php');
$start = strpos($viewSource, '<?php foreach ($submissions as $sub):');
check($start !== false, 'Review template loop is missing');
$end = strpos($viewSource, '<?php endforeach; ?>', $start);
check($end !== false, 'Review template loop is incomplete');
$reviewAssignment = ['id' => 12, 'max_score' => 100];
$row = ['student_id' => 40, 'student_name' => 'Student', 'student_lastname' => 'One',
    'student_email' => 'student@example.com', 'submission_id' => 501,
    'submission_text' => 'Work', 'file_path' => null, 'submitted_at' => '2026-10-26 12:00:00',
    'score' => 80, 'feedback' => '', 'status' => 'graded'];
$pending = array_replace($row, ['student_id' => 41, 'submission_id' => null,
    'submitted_at' => null, 'score' => null, 'status' => null]);
$submissions = [$row, $pending];
ob_start();
try {
    eval('?>' . substr($viewSource, $start, $end - $start + strlen('<?php endforeach; ?>')));
    $rendered = ob_get_contents();
} finally { ob_end_clean(); }
check(str_contains($rendered, 'name="submission_id" value="501"'), 'Grading form lost the submission identifier');
check(substr_count($rendered, '<form ') === 1, 'Students without a submission must not have grading forms');
check(str_contains($rendered, 'id="sub_501"') && str_contains($rendered, 'id="student_41"'), 'Review row identifiers are incorrect');
check(str_contains($rendered, 'Awaiting submission'), 'Pending submission state was not rendered');
echo "PASS: actual grading template with graded and missing submissions\n";

$oldBase = getenv('APP_BASE_URL');
$oldFrom = getenv('MAIL_FROM');
try {
    putenv('APP_BASE_URL=https://campus.example.com');
    putenv('MAIL_FROM=noreply@example.com');
    $token = str_repeat('a', 32);
    $calls = 0;
    $transport = function ($to, $subject, $body, $headers) use ($token, &$calls) {
        $calls++;
        check($to === 'student@example.com', 'Verification was sent to the wrong recipient');
        check(str_contains($body, 'https://campus.example.com/src/controllers/verificar/verificar.php?token=' . $token), 'Activation URL is wrong');
        check($headers['From'] === 'noreply@example.com', 'Sender is wrong');
        return true;
    };
    check(sendVerificationEmail('student@example.com', $token, $transport), 'Email transport failed');
    check($calls === 1, 'Unexpected email calls');
    check(!sendVerificationEmail('student@example.com', $token, fn() => false), 'Mail failure was hidden');
    putenv('APP_BASE_URL');
    check(!sendVerificationEmail('student@example.com', $token, $transport), 'Missing configuration must not send email');
    check($calls === 1, 'Mail was sent without configuration');
    $signup = file_get_contents(__DIR__ . '/../src/views/signup/signup.php');
    $html = substr($signup, strpos($signup, '<!DOCTYPE'));
    check(!str_contains($html, 'tokenConfirmacion') && !str_contains($html, 'verificar.php?token='), 'Signup exposes the activation token');
} finally {
    putenv($oldBase === false ? 'APP_BASE_URL' : 'APP_BASE_URL=' . $oldBase);
    putenv($oldFrom === false ? 'MAIL_FROM' : 'MAIL_FROM=' . $oldFrom);
}
echo "PASS: email activation, delivery failure, and token privacy\n";
echo "All regression checks passed.\n";
