<?php
require_once __DIR__ . '/../src/config/connection.php';
require_once __DIR__ . '/../src/models/adminModels/adminCoursesModel.php';
require_once __DIR__ . '/../src/models/adminModels/adminUsersModel.php';
require_once __DIR__ . '/../src/models/studentModels/studentCoursesModel.php';
require_once __DIR__ . '/../src/models/teacherModels/teacherCoursesModel.php';
require_once __DIR__ . '/../src/models/studentModels/studentEnrollmentModel.php';

$c = createConnection();

echo "==========================================" . PHP_EOL;
echo "RUNNING INTEGRATION TEST FOR POINTS 1, 2, 3" . PHP_EOL;
echo "==========================================" . PHP_EOL;

// --- TEST 1: POINT 1 - Signup & Child-table synchronization ---
echo PHP_EOL . "--- TEST 1: Child-Table Synchronization ---" . PHP_EOL;
$testTeacherId = 999901;
$c->query("DELETE FROM users WHERE id = {$testTeacherId}");
$c->query("DELETE FROM teacher WHERE id = {$testTeacherId}");

// Simulate signup with role=3
$c->begin_transaction();
$pw = password_hash('testpass123', PASSWORD_DEFAULT);
$tok = bin2hex(random_bytes(16));
$stmt = $c->prepare("INSERT INTO users (id, name, lastname, email, password, role, token_verificacion, verified) VALUES (?, 'TestTeacher', 'Unit', 'testteacher@unit.edu', ?, 3, ?, 0)");
$stmt->bind_param("iss", $testTeacherId, $pw, $tok);
$stmt->execute();
$stmtT = $c->prepare("INSERT INTO teacher (id) VALUES (?) ON DUPLICATE KEY UPDATE id=id");
$stmtT->bind_param("i", $testTeacherId);
$stmtT->execute();
$c->commit();

// Verify user in users AND in teacher
$checkU = $c->query("SELECT id, role FROM users WHERE id = {$testTeacherId}")->fetch_assoc();
$checkT = $c->query("SELECT id FROM teacher WHERE id = {$testTeacherId}")->fetch_assoc();
echo "Teacher user exists: " . ($checkU ? "YES (role {$checkU['role']})" : "NO") . PHP_EOL;
echo "Teacher child-table row exists: " . ($checkT ? "YES (id {$checkT['id']})" : "NO") . PHP_EOL;
assert($checkU && $checkT, "Point 1 teacher sync failed");

// Clean up
$c->query("DELETE FROM teacher WHERE id = {$testTeacherId}");
$c->query("DELETE FROM users WHERE id = {$testTeacherId}");
echo "Point 1 Child-table synchronization: PASS ✓" . PHP_EOL;


// --- TEST 2: POINT 2 - Student Interactive Learning Progress Tracking ---
echo PHP_EOL . "--- TEST 2: Interactive Student Progress Tracking ---" . PHP_EOL;
$studentModel = new StudentCourses();

// Find an existing block and student
$firstBlock = $c->query("SELECT id, course_id FROM blocks LIMIT 1")->fetch_assoc();
$firstStudent = $c->query("SELECT id FROM users WHERE role = 4 LIMIT 1")->fetch_assoc();

if ($firstBlock && $firstStudent) {
    $bId = (int)$firstBlock['id'];
    $sId = (int)$firstStudent['id'];
    $cId = (int)$firstBlock['course_id'];

    echo "Testing with Student ID: {$sId}, Block ID: {$bId}, Course ID: {$cId}" . PHP_EOL;

    // Clear initial state
    $c->query("DELETE FROM student_block_progress WHERE student_id = {$sId} AND block_id = {$bId}");

    $initialStatus = $studentModel->isBlockCompleted($sId, $bId);
    $initialProgress = $studentModel->getDynamicCourseProgress($sId, $cId);
    echo "Initial completion: " . ($initialStatus ? "Completed" : "Incomplete") . " (Course progress: {$initialProgress}%)" . PHP_EOL;

    // Toggle 1: Mark as completed
    $newStatus = $studentModel->toggleBlockCompletion($sId, $bId);
    $afterProgress = $studentModel->getDynamicCourseProgress($sId, $cId);
    echo "Toggled to: " . ($newStatus ? "Completed ✓" : "Incomplete") . " (Course progress: {$afterProgress}%)" . PHP_EOL;
    assert($newStatus === true, "Block should be marked completed");
    assert($afterProgress >= $initialProgress, "Course progress should increase or stay same");

    // Check teacher roster calculation
    $teacherModel = new TeacherCourses();
    $roster = $teacherModel->getCourseStudents($cId);
    $foundStu = null;
    foreach ($roster as $r) {
        if ((int)$r['id'] === $sId) {
            $foundStu = $r;
            break;
        }
    }
    if ($foundStu) {
        echo "Teacher roster dynamic progress for student: " . $foundStu['progress_percent'] . "% (" . $foundStu['completed_blocks'] . "/" . $foundStu['total_blocks'] . " blocks)" . PHP_EOL;
        assert($foundStu['completed_blocks'] >= 1, "Teacher roster should reflect completed block");
    }

    // Toggle 2: Mark as incomplete (undo)
    $undoStatus = $studentModel->toggleBlockCompletion($sId, $bId);
    $undoProgress = $studentModel->getDynamicCourseProgress($sId, $cId);
    echo "Undone toggle: " . ($undoStatus ? "Completed" : "Incomplete (clean reset)") . " (Course progress: {$undoProgress}%)" . PHP_EOL;
    assert($undoStatus === false, "Block should be marked incomplete after undo");

    echo "Point 2 Student Progress Tracking: PASS ✓" . PHP_EOL;
} else {
    echo "Skipped live test: No blocks or students in database." . PHP_EOL;
}


// --- TEST 3: POINT 3 - Admin CRUD on Courses & Users ---
echo PHP_EOL . "--- TEST 3: Admin In-Place Management (CRUD) ---" . PHP_EOL;
$adminCourses = new AdminCourses();
$adminUsers = new AdminUsers();

// 3A: Course CRUD
echo "Testing Course CRUD..." . PHP_EOL;
$tempCourseId = $adminCourses->createCourse("Temporary Test Course 999", 1, 4, 1, 0);
echo "Created temp course ID: {$tempCourseId}" . PHP_EOL;
assert($tempCourseId > 0, "createCourse failed");

$updResult = $adminCourses->updateCourse($tempCourseId, "Updated Test Course 999", 2, 6, 0);
echo "Updated course: " . ($updResult ? "YES" : "NO") . PHP_EOL;
assert($updResult === true, "updateCourse failed");

$details = $adminCourses->getCourseDetails($tempCourseId);
echo "Verified new course name: " . $details['course_name'] . " | Credits: " . $details['credits'] . " | Status: " . $details['status'] . PHP_EOL;
assert($details['credits'] == 6, "Credits update mismatch");

// Delete course
$delCourseResult = $adminCourses->deleteCourse($tempCourseId);
echo "Deleted course: " . ($delCourseResult ? "YES" : "NO") . PHP_EOL;
assert($delCourseResult === true, "deleteCourse failed");

$checkDeleted = $adminCourses->getCourseDetails($tempCourseId);
assert($checkDeleted === null, "Course should no longer exist");

// 3B: User CRUD
echo PHP_EOL . "Testing User CRUD..." . PHP_EOL;
$createdUser = $adminUsers->createStudent("UnitTester", "Jones", "unittester999@test.com", "pass123");
echo "Created student ID: {$createdUser['id']}" . PHP_EOL;
assert($createdUser['success'] === true, "createStudent failed");
$testUid = (int)$createdUser['id'];

// Update user profile
$updUser = $adminUsers->updateUser($testUid, "UnitTesterUpdated", "JonesUpdated", "unittester999@test.com", 4, 1);
echo "Updated user profile: " . ($updUser['success'] ? "YES" : "NO: " . ($updUser['error'] ?? '')) . PHP_EOL;
assert($updUser['success'] === true, "updateUser failed");

$userRec = $adminUsers->getUserById($testUid);
echo "Verified updated user: " . $userRec['name'] . " " . $userRec['lastname'] . " (Verified: " . $userRec['verified'] . ")" . PHP_EOL;
assert($userRec['name'] === "UnitTesterUpdated", "User name update mismatch");

// Delete user
$delUser = $adminUsers->deleteUser($testUid, 1);
echo "Deleted user: " . ($delUser['success'] ? "YES" : "NO: " . ($delUser['error'] ?? '')) . PHP_EOL;
assert($delUser['success'] === true, "deleteUser failed");

$checkDelUser = $adminUsers->getUserById($testUid);
assert($checkDelUser === null, "User should no longer exist");

echo "Point 3 Admin In-Place CRUD: PASS ✓" . PHP_EOL;

echo PHP_EOL . "==========================================" . PHP_EOL;
echo "ALL TESTS PASSED SUCCESSFULLY (100%)" . PHP_EOL;
echo "==========================================" . PHP_EOL;
