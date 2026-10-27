<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 2]);
    require_once __DIR__ . "/../../models/directorModels/directorDashboardModel.php";
    require_once __DIR__ . "/../../models/gradeModels/gradeModel.php";

    $user = getLoggedUser();
    $directorId = (int)($user['id'] ?? 0);

    $directorModel = new DirectorDashboard();
    $gradeModel = new GradeModel();

    $dept = $directorModel->getDirectorDepartment($directorId);
    $deptId = (int)($dept['department_id'] ?? 0);
    $deptName = $dept['department_name'] ?? 'Academic Department';

    $coursesGrades = $gradeModel->getDirectorDepartmentGrades($deptId);

    // Pure PHP search filter
    $search = trim($_GET['search'] ?? '');
    if (!empty($search)) {
        $coursesGrades = array_filter($coursesGrades, function($c) use ($search) {
            return (stripos($c['course_name'], $search) !== false)
                || (stripos($c['teacher_name'], $search) !== false);
        });
    }

    // Divisional Metrics calculation
    $totalCourses = count($coursesGrades);
    $totalGradedSubmissions = 0;
    $sumAverages = 0;
    $sumPassingRates = 0;
    $activeCount = 0;

    foreach ($coursesGrades as $cg) {
        $totalGradedSubmissions += (int)$cg['graded_submissions'];
        if ((int)$cg['graded_submissions'] > 0) {
            $sumAverages += (float)$cg['course_average'];
            $sumPassingRates += (float)$cg['passing_rate'];
            $activeCount++;
        }
    }

    $deptAverage = $activeCount > 0 ? round($sumAverages / $activeCount, 1) : 0.0;
    $deptPassingRate = $activeCount > 0 ? round($sumPassingRates / $activeCount, 1) : 0.0;
    $tab = $_GET['tab'] ?? 'courses';
    $selectedStudentId = (int)($_GET['student_id'] ?? 0);
    if ($selectedStudentId > 0) {
        $tab = 'students';
    }

    $deptCourses = $directorModel->getDepartmentCourses($deptId);

    // Student Gradebook data resolution
    $studentsList = [];
    $selectedStudentProfile = null;
    $selectedStudentGrades = [];
    $selectedStudentSummary = null;
    $studentSearch = trim($_GET['student_search'] ?? '');
    $courseFilter = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;

    if ($tab === 'students') {
        $studentsList = $gradeModel->getStudentsGradesOverview($deptId, $courseFilter, $studentSearch);

        if ($selectedStudentId > 0) {
            $profile = $gradeModel->getStudentProfileWithCourse($selectedStudentId);
            if ($profile && ((int)($profile['department_id'] ?? 0) === $deptId || (int)($user['role'] ?? 0) === 1)) {
                $selectedStudentProfile = $profile;
                $selectedStudentGrades = $gradeModel->getStudentGrades($selectedStudentId);
                $selectedStudentSummary = $gradeModel->getStudentAcademicSummary($selectedStudentId);
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Academic Performance · Edu-System</title>
    <link rel="stylesheet" href="directorGrades.css">
</head>
<body>
    <?php include_once __DIR__ . "/../../../ui/header/header.php"; ?>
    <?php include_once __DIR__ . "/../../../components/directorSidebar/directorSidebar.php"; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Page Heading Hero (Strictly Free of Buttons) -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">DIVISIONAL ANALYTICS · <?php echo htmlspecialchars($deptName); ?></span>
                        <h1>Academic Performance & Evaluations</h1>
                        <p class="subtitle">Monitor curricular course averages, inspect faculty evaluation workloads, and audit individual student gradebooks across your division.</p>
                    </div>
                </section>

                <!-- Divisional Analytics Metrics -->
                <div class="metrics-grid">
                    <div class="metric-card highlight-card">
                        <span class="metric-label">Division Cumulative Average</span>
                        <span class="metric-value"><?php echo $deptAverage; ?>%</span>
                        <span class="metric-desc">Mean performance across graded subjects</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Division Passing Rate</span>
                        <span class="metric-value pass-val"><?php echo $deptPassingRate; ?>%</span>
                        <span class="metric-desc">Overall passing proportion (&ge; 70%)</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Curricular Courses</span>
                        <span class="metric-value"><?php echo $totalCourses; ?></span>
                        <span class="metric-desc">Programs registered in <?php echo htmlspecialchars($deptName); ?></span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Graded Deliverables</span>
                        <span class="metric-value"><?php echo $totalGradedSubmissions; ?></span>
                        <span class="metric-desc">Total submissions evaluated by faculty</span>
                    </div>
                </div>

                <!-- View Navigation Tabs -->
                <nav class="tabs-nav">
                    <a href="directorGrades.php?tab=courses" class="tab-btn <?php echo ($tab === 'courses') ? 'active' : ''; ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        Course Evaluations Quality
                    </a>
                    <a href="directorGrades.php?tab=students" class="tab-btn <?php echo ($tab === 'students') ? 'active' : ''; ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Student Gradebook & Records
                    </a>
                </nav>

                <?php if ($tab === 'courses'): ?>
                    <!-- Academic Evaluation Breakdown Panel -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Course Evaluation Quality Audit</h2>
                                <p>Aggregated grades, passing rates, and evaluation progress per program.</p>
                            </div>
                        </div>

                        <!-- Panel Toolbar with Filter -->
                        <div class="panel-toolbar">
                            <form method="GET" action="directorGrades.php" class="filter-form">
                                <input type="hidden" name="tab" value="courses">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search program or instructor..." class="search-input">
                                <button type="submit" class="btn-filter">Search</button>
                                <?php if (!empty($search)): ?>
                                    <a href="directorGrades.php?tab=courses" class="btn-clear">Reset</a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <!-- Table Wrap -->
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Curricular Course</th>
                                        <th>Instructing Faculty</th>
                                        <th>Enrolled Students</th>
                                        <th>Total Tasks</th>
                                        <th>Graded Submissions</th>
                                        <th>Course Average</th>
                                        <th>Passing Rate</th>
                                        <th>Curricular Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($coursesGrades)): ?>
                                        <tr>
                                            <td colspan="8" class="empty-state-cell">
                                                <p>No academic course performance data found matching your query.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($coursesGrades as $cg):
                                            $avg = (float)$cg['course_average'];
                                            $passRate = (float)$cg['passing_rate'];
                                            $hasGraded = (int)$cg['graded_submissions'] > 0;
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="course-title-cell">
                                                        <strong><?php echo htmlspecialchars($cg['course_name']); ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="faculty-name"><?php echo htmlspecialchars($cg['teacher_name']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="metric-pill"><?php echo (int)$cg['enrolled_students']; ?> Students</span>
                                                </td>
                                                <td>
                                                    <strong><?php echo (int)$cg['total_assignments']; ?></strong>
                                                </td>
                                                <td>
                                                    <span class="graded-pill"><?php echo (int)$cg['graded_submissions']; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($hasGraded): ?>
                                                        <span class="score-badge <?php echo $avg >= 70 ? 'score-pass' : 'score-fail'; ?>">
                                                            <?php echo number_format($avg, 1); ?>%
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="na-label">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($hasGraded): ?>
                                                        <span class="pass-rate-text <?php echo $passRate >= 70 ? 'rate-good' : 'rate-warn'; ?>">
                                                            <?php echo number_format($passRate, 1); ?>%
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="na-label">--</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="status-tag status-<?php echo htmlspecialchars($cg['course_status']); ?>">
                                                        <?php echo ucfirst(htmlspecialchars($cg['course_status'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                <?php else: ?>
                    <!-- Student Gradebook View -->
                    <?php if ($selectedStudentProfile): ?>
                        <!-- Individual Student Grade Breakdown Report -->
                        <div class="student-report-card">
                            <div class="student-report-info">
                                <span class="eyebrow" style="color:#8B1528;">INDIVIDUAL STUDENT ACADEMIC DOSSIER</span>
                                <h3><?php echo htmlspecialchars($selectedStudentProfile['full_name']); ?></h3>
                                <p><?php echo htmlspecialchars($selectedStudentProfile['email']); ?> · <strong>Course:</strong> <?php echo htmlspecialchars($selectedStudentProfile['course_name']); ?> (<?php echo htmlspecialchars($selectedStudentProfile['department_name']); ?>)</p>
                            </div>
                            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                                <div class="student-stats-badges">
                                    <div class="student-stat-pill">
                                        <span class="stat-label">Cumulative Avg</span>
                                        <span class="stat-num" style="color: <?php echo (($selectedStudentSummary['average_score'] ?? 0) >= 70) ? '#16a34a' : '#dc2626'; ?>;">
                                            <?php echo number_format($selectedStudentSummary['average_score'] ?? 0, 1); ?>%
                                        </span>
                                    </div>
                                    <div class="student-stat-pill">
                                        <span class="stat-label">Handed In</span>
                                        <span class="stat-num"><?php echo (int)($selectedStudentSummary['submitted_count'] ?? 0); ?> / <?php echo (int)($selectedStudentSummary['total_assignments'] ?? 0); ?></span>
                                    </div>
                                    <div class="student-stat-pill">
                                        <span class="stat-label">Graded Tasks</span>
                                        <span class="stat-num"><?php echo (int)($selectedStudentSummary['graded_count'] ?? 0); ?></span>
                                    </div>
                                </div>
                                <a href="directorGrades.php?tab=students" class="btn-clear" style="padding:10px 16px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                    All Department Students
                                </a>
                            </div>
                        </div>

                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Coursework Evaluation Breakdown</h2>
                                    <p>Individual task grades, evaluator commentary, and syllabus achievements for <?php echo htmlspecialchars($selectedStudentProfile['full_name']); ?>.</p>
                                </div>
                            </div>

                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Assignment Title</th>
                                            <th>Syllabus Block</th>
                                            <th>Instructor</th>
                                            <th>Due Date</th>
                                            <th>Score</th>
                                            <th>Percentage</th>
                                            <th>Status</th>
                                            <th>Teacher Feedback</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($selectedStudentGrades)): ?>
                                            <tr>
                                                <td colspan="8" class="empty-state-cell">
                                                    <p>No coursework assignments or evaluations registered for this student.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($selectedStudentGrades as $g):
                                                $hasScore = ($g['score'] !== null);
                                                $pct = $g['percentage'];
                                                $st = strtolower($g['submission_status']);
                                            ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($g['assignment_title']); ?></strong></td>
                                                    <td><span class="metric-pill"><?php echo htmlspecialchars($g['block_name']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($g['teacher_name']); ?></td>
                                                    <td><?php echo !empty($g['due_date']) ? date('M j, Y', strtotime($g['due_date'])) : '—'; ?></td>
                                                    <td>
                                                        <?php if ($hasScore): ?>
                                                            <strong><?php echo number_format($g['score'], 1); ?></strong> / <?php echo number_format($g['max_score'], 1); ?>
                                                        <?php else: ?>
                                                            <span class="na-label">-- / <?php echo number_format($g['max_score'], 1); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($pct !== null): ?>
                                                            <span class="score-badge <?php echo $pct >= 70 ? 'score-pass' : 'score-fail'; ?>">
                                                                <?php echo number_format($pct, 1); ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="na-label">Ungraded</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo htmlspecialchars($st); ?>">
                                                            <?php echo ucfirst(htmlspecialchars($st)); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($g['feedback'])): ?>
                                                            <p class="feedback-text"><?php echo nl2br(htmlspecialchars($g['feedback'])); ?></p>
                                                        <?php else: ?>
                                                            <span class="na-label">No remarks</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                    <?php else: ?>
                        <!-- Department Students List Table -->
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Department Students Gradebook</h2>
                                    <p>Individual student cumulative scores, delivered assignments, and academic evaluation records in <?php echo htmlspecialchars($deptName); ?>.</p>
                                </div>
                            </div>

                            <!-- Panel Toolbar with Filters -->
                            <div class="panel-toolbar" style="justify-content:space-between;">
                                <form method="GET" action="directorGrades.php" class="filter-form" style="flex-wrap:wrap;">
                                    <input type="hidden" name="tab" value="students">
                                    <select name="course_id" class="search-input" style="width:200px;" onchange="this.form.submit()">
                                        <option value="">All Department Courses</option>
                                        <?php foreach ($deptCourses as $dc): ?>
                                            <option value="<?php echo (int)$dc['id']; ?>" <?php echo ($courseFilter === (int)$dc['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dc['course_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="student_search" value="<?php echo htmlspecialchars($studentSearch); ?>" placeholder="Search student name or email..." class="search-input">
                                    <button type="submit" class="btn-filter">Search</button>
                                    <?php if (!empty($studentSearch) || !empty($courseFilter)): ?>
                                        <a href="directorGrades.php?tab=students" class="btn-clear">Reset</a>
                                    <?php endif; ?>
                                </form>
                            </div>

                            <!-- Table of Students -->
                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Enrolled Course</th>
                                            <th>Coursework Progress</th>
                                            <th>Graded Tasks</th>
                                            <th>Cumulative Average</th>
                                            <th>Performance Status</th>
                                            <th style="text-align:right;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($studentsList)): ?>
                                            <tr>
                                                <td colspan="7" class="empty-state-cell">
                                                    <p>No student evaluation records found in this division.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($studentsList as $st):
                                                $hasGraded = $st['has_graded'];
                                                $avg = $st['average_score'];
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div class="dept-title-cell">
                                                            <strong><?php echo htmlspecialchars($st['student_name']); ?></strong>
                                                            <span style="display:block; font-size:12px; color:#64748b;"><?php echo htmlspecialchars($st['email']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="metric-pill"><?php echo htmlspecialchars($st['course_name']); ?></span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo (int)$st['submitted_count']; ?></strong> / <?php echo (int)$st['total_assignments']; ?> Submitted
                                                    </td>
                                                    <td>
                                                        <span class="graded-pill"><?php echo (int)$st['graded_count']; ?> Graded</span>
                                                    </td>
                                                    <td>
                                                        <?php if ($hasGraded && $avg !== null): ?>
                                                            <span class="score-badge <?php echo $avg >= 70 ? 'score-pass' : 'score-fail'; ?>">
                                                                <?php echo number_format($avg, 1); ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="na-label">No grades yet</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!$hasGraded): ?>
                                                            <span class="perf-status perf-pending">Awaiting Tasks</span>
                                                        <?php elseif ($avg >= 85): ?>
                                                            <span class="perf-status perf-high">High Distinction</span>
                                                        <?php elseif ($avg >= 70): ?>
                                                            <span class="perf-status perf-satisfactory">Satisfactory</span>
                                                        <?php else: ?>
                                                            <span class="perf-status perf-review">Needs Attention</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align:right;">
                                                        <a href="directorGrades.php?tab=students&student_id=<?php echo (int)$st['student_id']; ?>" class="btn-filter" style="text-decoration:none; display:inline-block; font-size:12px; padding:6px 12px;">
                                                            View Grades
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>
