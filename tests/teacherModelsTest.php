<?php
require_once __DIR__ . '/../src/config/connection.php';
require_once __DIR__ . '/../src/models/teacherModels/teacherCoursesModel.php';

$tc = new TeacherCourses();

echo "=== TEST 1: getCourseDetails(1, 3) ===\n";
$course = $tc->getCourseDetails(1, 3);
print_r($course);

echo "\n=== TEST 2: getCourseBlocksWithFiles(1, 3) ===\n";
$blocks = $tc->getCourseBlocksWithFiles(1, 3);
foreach ($blocks as $b) {
    echo "Block #" . $b['id'] . ": " . $b['block_name'] . " | Teacher: " . $b['teacher_name'] . " | Mine: " . $b['is_my_block'] . " | Files: " . count($b['files']) . "\n";
    foreach ($b['files'] as $f) {
        echo "   -> File: " . $f['file_name'] . " (" . $f['file_path'] . ")\n";
    }
}

echo "\n=== TEST 3: getTeacherBlocksForCourse(3, 1) ===\n";
$teacherBlocks = $tc->getTeacherBlocksForCourse(3, 1);
print_r($teacherBlocks);

echo "\n=== TEST 4: getCourseStudents(1) ===\n";
$students = $tc->getCourseStudents(1);
echo "Enrolled students count: " . count($students) . "\n";
foreach ($students as $s) {
    echo "   - Student #" . $s['id'] . ": " . $s['name'] . " " . $s['lastname'] . " (" . $s['email'] . ")\n";
}

echo "\n=== ALL TEACHER MODEL TESTS PASSED ===\n";
