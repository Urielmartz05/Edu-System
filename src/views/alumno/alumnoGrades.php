<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([4]);
    require_once __DIR__ . "/../../models/gradeModels/gradeModel.php";
    require_once __DIR__ . "/../../models/studentModels/studentEnrollmentModel.php";

    $user = getLoggedUser();
    $studentId = (int)($user['id'] ?? 0);

    $gradeModel = new GradeModel();
    $enrollmentModel = new StudentEnrollment();

    $courseFilter = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;
    $search = trim($_GET['search'] ?? '');

    $academicSummary = $gradeModel->getStudentAcademicSummary($studentId);
    $allGrades = $gradeModel->getStudentGrades($studentId, $courseFilter);
    $enrolledCourses = $enrollmentModel->getEnrolledCourses($studentId);

    if (!empty($search)) {
        $allGrades = array_filter($allGrades, function($g) use ($search) {
            $cName = $g['course_name'] ?? $g['course_title'] ?? '';
            return (stripos($g['assignment_title'], $search) !== false)
                || (stripos($cName, $search) !== false)
                || (stripos($g['teacher_name'], $search) !== false);
        });
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
    <title>Academic Grades & Performance · Edu-System</title>
    <link rel="stylesheet" href="alumnoGrades.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

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
                        <span class="eyebrow">ACADEMIC PERFORMANCE · STUDENT RECORD</span>
                        <h1>Academic Grades & Evaluations</h1>
                        <p class="subtitle">Review your coursework evaluations, academic scores, cumulative average, and instructor feedback.</p>
                    </div>
                </section>

                <!-- Academic Overview Metrics -->
                <div class="metrics-grid">
                    <div class="metric-card highlight-card">
                        <span class="metric-label">Cumulative Average</span>
                        <div class="average-val-row">
                            <span class="metric-value grade-average"><?php echo number_format($academicSummary['average_score'], 1); ?>%</span>
                        </div>
                        <span class="metric-desc">Overall score across graded tasks</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Assigned Deliverables</span>
                        <span class="metric-value"><?php echo $academicSummary['total_assignments']; ?></span>
                        <span class="metric-desc">Registered across all courses</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Submitted Work</span>
                        <span class="metric-value"><?php echo $academicSummary['submitted_count']; ?></span>
                        <span class="metric-desc">Handed in by you</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Graded & Audited</span>
                        <span class="metric-value"><?php echo $academicSummary['graded_count']; ?></span>
                        <span class="metric-desc">Fully scored by faculty</span>
                    </div>
                </div>

                <!-- Academic Gradebook Report Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Evaluation Breakdown</h2>
                            <p>Detailed list of assigned tasks and academic scores.</p>
                        </div>
                    </div>

                    <!-- Filter Toolbar (Pure PHP GET Form) -->
                    <div class="panel-toolbar">
                        <form method="GET" action="alumnoGrades.php" class="filter-form">
                            <select name="course_id" class="select-control">
                                <option value="">All Enrolled Courses</option>
                                <?php foreach ($enrolledCourses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo $courseFilter === (int)$course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search coursework title..." class="search-input">
                            <button type="submit" class="btn-filter">Filter Report</button>
                            <?php if (!empty($search) || !empty($courseFilter)): ?>
                                <a href="alumnoGrades.php" class="btn-clear">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Gradebook Table Wrap -->
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Course & Instructor</th>
                                    <th>Deliverable / Task</th>
                                    <th>Module Block</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Status</th>
                                    <th>Instructor Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allGrades)): ?>
                                    <tr>
                                        <td colspan="7" class="empty-state-cell">
                                            <p>No evaluation records found matching your selection.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allGrades as $row):
                                        $status = $row['submission_status'];
                                        $scoreText = $row['score'] !== null ? number_format($row['score'], 1) : '--';
                                        $percent = $row['percentage'];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="course-name-cell">
                                                    <strong><?php echo htmlspecialchars($row['course_name'] ?? $row['course_title'] ?? ''); ?></strong>
                                                    <span class="teacher-subtitle">Prof. <?php echo htmlspecialchars($row['teacher_name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="task-cell">
                                                    <strong><?php echo htmlspecialchars($row['assignment_title']); ?></strong>
                                                    <small class="due-hint">Due: <?php echo date('M d, Y', strtotime($row['due_date'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="block-tag"><?php echo htmlspecialchars($row['block_name'] ?? $row['block_title'] ?? ''); ?></span>
                                            </td>
                                            <td>
                                                <span class="score-pill">
                                                    <strong><?php echo $scoreText; ?></strong> / <?php echo number_format($row['max_score'], 1); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($percent !== null): ?>
                                                    <span class="percentage-badge <?php echo $percent >= 70 ? 'pass-badge' : 'fail-badge'; ?>">
                                                        <?php echo number_format($percent, 1); ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="na-label">--</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-pill status-<?php echo htmlspecialchars($status); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($status)); ?>
                                                </span>
                                            </td>
                                            <td class="feedback-col">
                                                <?php if (!empty($row['feedback'])): ?>
                                                    <p class="feedback-snippet"><?php echo htmlspecialchars($row['feedback']); ?></p>
                                                <?php elseif ($status === 'graded'): ?>
                                                    <span class="no-feedback-text">No written comments.</span>
                                                <?php else: ?>
                                                    <span class="no-feedback-text">Awaiting evaluation.</span>
                                                <?php endif; ?>
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
