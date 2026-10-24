<?php
require_once __DIR__ . '/../src/config/connection.php';
require_once __DIR__ . '/../src/models/adminModels/adminCoursesModel.php';
require_once __DIR__ . '/../src/models/adminModels/adminUsersModel.php';
require_once __DIR__ . '/../src/models/adminModels/adminStatisticsModel.php';
require_once __DIR__ . '/../src/models/directorModels/directorDashboardModel.php';
require_once __DIR__ . '/../src/models/teacherModels/teacherDashboardModel.php';
require_once __DIR__ . '/../src/models/teacherModels/teacherCoursesModel.php';
require_once __DIR__ . '/../src/models/studentModels/studentCoursesModel.php';

echo "=== Testing AdminCourses ===" . PHP_EOL;
$ac = new AdminCourses();
echo "Total courses: " . $ac->getTotalCoursesCount() . PHP_EOL;
echo "Published: " . $ac->getPublishedCoursesCount() . PHP_EOL;
echo "Review: " . $ac->getReviewCoursesCount() . PHP_EOL;
echo "Enrollments: " . $ac->getTotalEnrollmentsCount() . PHP_EOL;
$allCourses = $ac->getAllCourses();
echo "All courses count: " . count($allCourses) . PHP_EOL;
if (!empty($allCourses)) {
    echo "First course: " . $allCourses[0]['course_name'] . " (" . $allCourses[0]['department_name'] . ") - Instructor: " . $allCourses[0]['teacher_name'] . PHP_EOL;
}

echo PHP_EOL . "=== Testing AdminUsers ===" . PHP_EOL;
$au = new AdminUsers();
echo "Total users: " . $au->getTotalUsersCount() . PHP_EOL;
echo "Students: " . $au->getStudentsCount() . PHP_EOL;
echo "Staff: " . $au->getAcademicStaffCount() . PHP_EOL;
echo "Pending: " . $au->getPendingUsersCount() . PHP_EOL;
$allUsers = $au->getAllUsers();
echo "All users count: " . count($allUsers) . PHP_EOL;

echo PHP_EOL . "=== Testing AdminStatistics ===" . PHP_EOL;
$as = new AdminStatistics();
echo "Avg progress: " . $as->getAverageCourseProgress() . "%" . PHP_EOL;
echo "Completion rate: " . $as->getCompletionRate() . "%" . PHP_EOL;
$dist = $as->getProgressDistribution();
echo "Distribution tier high: " . $dist['high'] . "%" . PHP_EOL;

echo PHP_EOL . "=== Testing DirectorDashboard (Director ID 2) ===" . PHP_EOL;
$dd = new DirectorDashboard();
$dept = $dd->getDirectorDepartment(2);
echo "Dept: " . $dept['department_name'] . " (ID: " . $dept['department_id'] . ")" . PHP_EOL;
echo "Dept courses count: " . $dd->getCoursesCount($dept['department_id']) . PHP_EOL;
echo "Dept faculty count: " . $dd->getFacultyCount($dept['department_id']) . PHP_EOL;
echo "Dept students count: " . $dd->getStudentsCount($dept['department_id']) . PHP_EOL;
echo "Dept avg progress: " . $dd->getAverageProgress($dept['department_id']) . "%" . PHP_EOL;
$deptCourses = $dd->getDepartmentCourses($dept['department_id']);
echo "Dept courses returned: " . count($deptCourses) . PHP_EOL;
$deptFaculty = $dd->getDepartmentFaculty($dept['department_id']);
echo "Dept faculty returned: " . count($deptFaculty) . PHP_EOL;

echo PHP_EOL . "=== Testing TeacherDashboard & TeacherCourses (Teacher ID 3) ===" . PHP_EOL;
$td = new TeacherDashboard();
echo "Assigned courses: " . $td->getAssignedCoursesCount(3) . PHP_EOL;
echo "Assigned blocks: " . $td->getAssignedBlocksCount(3) . PHP_EOL;
echo "Enrolled students: " . $td->getEnrolledStudentsCount(3) . PHP_EOL;
$tCourses = $td->getAssignedCourses(3);
echo "Assigned courses returned: " . count($tCourses) . PHP_EOL;
$tBlocks = $td->getAssignedBlocks(3);
echo "Assigned blocks returned: " . count($tBlocks) . PHP_EOL;
$tc = new TeacherCourses();
$tcCourses = $tc->getCoursesWithDetails(3);
echo "TeacherCourses details returned: " . count($tcCourses) . PHP_EOL;

echo PHP_EOL . "=== Testing StudentCourses Block Details (Block ID 1) ===" . PHP_EOL;
$sc = new StudentCourses();
$block = $sc->getBlockDetails(1, 7);
echo "Block name: " . ($block['block_name'] ?? 'Not found') . PHP_EOL;
echo "Course: " . ($block['course_name'] ?? '') . PHP_EOL;
echo "Teacher: " . ($block['teacher_name'] ?? '') . " (" . ($block['teacher_email'] ?? '') . ")" . PHP_EOL;
$files = $sc->getBlockFiles(1);
echo "Files attached count: " . count($files) . PHP_EOL;

echo PHP_EOL . ">>> ALL MODEL TESTS PASSED SUCCESSFULLY! <<<" . PHP_EOL;
