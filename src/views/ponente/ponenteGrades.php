<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 3]);
    require_once __DIR__ . "/../../models/gradeModels/gradeModel.php";
    require_once __DIR__ . "/../../models/teacherModels/teacherCoursesModel.php";

    $user = getLoggedUser();
    $teacherId = (int)($user['id'] ?? 0);

    $gradeModel = new GradeModel();
    $teacherCoursesModel = new TeacherCourses();

    $teacherCourses = $teacherCoursesModel->getCoursesWithDetails($teacherId);
    $selectedCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : ($teacherCourses[0]['id'] ?? 0);

    $gradebookData = [
        'assignments' => [],
        'students' => []
    ];

    if ($selectedCourseId > 0) {
        $gradebookData = $gradeModel->getTeacherCourseGradebook($teacherId, $selectedCourseId);
    }

    $assignments = $gradebookData['assignments'];
    $students = $gradebookData['students'];

    // Calculate aggregated metrics
    $totalStudents = count($students);
    $totalAssignments = count($assignments);
    $overallSum = 0;
    $passingStudents = 0;

    foreach ($students as $st) {
        $overallSum += $st['average'];
        if ($st['average'] >= 70) {
            $passingStudents++;
        }
    }

    $courseAverage = $totalStudents > 0 ? round($overallSum / $totalStudents, 1) : 0.0;
    $passingRate = $totalStudents > 0 ? round(($passingStudents / $totalStudents) * 100, 1) : 0.0;

    // Selected course details
    $selectedCourseName = 'Select a Course';
    foreach ($teacherCourses as $c) {
        if ((int)$c['id'] === $selectedCourseId) {
            $selectedCourseName = $c['course_name'];
            break;
        }
    }

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Gradebook Matrix · Edu-System</title>
    <link rel="stylesheet" href="ponenteGrades.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/teacherSidebar/teacherSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Flash Alerts -->
                <?php if (!empty($flashSuccess)): ?>
                    <div class="alert-banner alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span><?php echo htmlspecialchars($flashSuccess); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flashError)): ?>
                    <div class="alert-banner alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($flashError); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Page Heading Hero (Strictly Free of Buttons) -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC EVALUATION · COURSE GRADEBOOK</span>
                        <h1>Gradebook Matrix & Cumulative Records</h1>
                        <p class="subtitle">Examine comprehensive student scores across all course deliverables, analyze group performance, and track passing rates.</p>
                    </div>
                </section>

                <!-- Gradebook Course Performance Metrics -->
                <div class="metrics-grid">
                    <div class="metric-card highlight-card">
                        <span class="metric-label">Course Average</span>
                        <span class="metric-value"><?php echo $courseAverage; ?>%</span>
                        <span class="metric-desc">Group score across evaluated tasks</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Passing Rate</span>
                        <span class="metric-value pass-val"><?php echo $passingRate; ?>%</span>
                        <span class="metric-desc"><?php echo $passingStudents; ?> of <?php echo $totalStudents; ?> students passing (&ge; 70%)</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Enrolled Students</span>
                        <span class="metric-value"><?php echo $totalStudents; ?></span>
                        <span class="metric-desc">Registered in <?php echo htmlspecialchars($selectedCourseName); ?></span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Evaluated Deliverables</span>
                        <span class="metric-value"><?php echo $totalAssignments; ?></span>
                        <span class="metric-desc">Coursework items on record</span>
                    </div>
                </div>

                <!-- Main Gradebook Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2><?php echo htmlspecialchars($selectedCourseName); ?> — Gradebook Matrix</h2>
                            <p>Cross-referenced student scores per assignment.</p>
                        </div>
                    </div>

                    <!-- Panel Toolbar with Course Selector (Native PHP Form) -->
                    <div class="panel-toolbar">
                        <form method="GET" action="ponenteGrades.php" class="filter-form">
                            <label for="course_id" class="toolbar-label">Select Course Program:</label>
                            <select id="course_id" name="course_id" class="select-control">
                                <?php foreach ($teacherCourses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $selectedCourseId === (int)$c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-filter">Switch Course</button>
                        </form>
                    </div>

                    <!-- Matrix Table Wrap -->
                    <div class="table-wrap">
                        <table class="data-table matrix-table">
                            <thead>
                                <tr>
                                    <th class="sticky-col">Enrolled Student</th>
                                    <?php foreach ($assignments as $a): ?>
                                        <th class="matrix-task-header" title="<?php echo htmlspecialchars($a['title']); ?>">
                                            <div class="task-head-content">
                                                <span class="task-head-title"><?php echo htmlspecialchars($a['title']); ?></span>
                                                <span class="task-head-max"><?php echo number_format($a['max_score'], 1); ?> pts</span>
                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                    <th style="text-align: right;">Total Points</th>
                                    <th style="text-align: right;">Final Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="<?php echo count($assignments) + 3; ?>" class="empty-state-cell">
                                            <p>No enrolled students found in this course program.</p>
                                        </td>
                                    </tr>
                                <?php elseif (empty($assignments)): ?>
                                    <tr>
                                        <td colspan="3" class="empty-state-cell">
                                            <p>No coursework assignments have been created yet for this course. Visit the Assignments section to publish tasks.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $st):
                                        $avg = $st['average'];
                                        $isPass = ($avg >= 70);
                                    ?>
                                        <tr>
                                            <td class="sticky-col">
                                                <div class="student-name-wrap">
                                                    <strong><?php echo htmlspecialchars($st['name']); ?></strong>
                                                    <span class="student-email"><?php echo htmlspecialchars($st['email']); ?></span>
                                                </div>
                                            </td>
                                            <?php foreach ($assignments as $a):
                                                $aId = $a['id'];
                                                $gradeItem = $st['grades'][$aId] ?? null;
                                                $hasScore = ($gradeItem !== null && $gradeItem['score'] !== null);
                                                $status = $gradeItem['status'] ?? 'pending';
                                            ?>
                                                <td class="matrix-cell">
                                                    <?php if ($hasScore): ?>
                                                        <span class="matrix-score-val"><?php echo number_format($gradeItem['score'], 1); ?></span>
                                                    <?php elseif ($status === 'submitted'): ?>
                                                        <span class="cell-status-submitted" title="Submitted, awaiting review">In Review</span>
                                                    <?php else: ?>
                                                        <span class="cell-status-pending" title="Not submitted">--</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td style="text-align: right;">
                                                <strong><?php echo number_format($st['total_points'], 1); ?></strong> / <?php echo number_format($st['max_possible_points'], 1); ?>
                                            </td>
                                            <td style="text-align: right;">
                                                <span class="avg-badge <?php echo $isPass ? 'avg-pass' : 'avg-fail'; ?>">
                                                    <?php echo number_format($avg, 1); ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </main>
    </div>
</body>
</html>
