<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1]);
    require_once __DIR__ . "/../../models/gradeModels/gradeModel.php";

    $gradeModel = new GradeModel();
    $globalData = $gradeModel->getAdminGlobalGradesSummary();

    $overview = $globalData['overview'];
    $departments = $globalData['departments'];

    // Search filter across departments
    $search = trim($_GET['search'] ?? '');
    if (!empty($search)) {
        $departments = array_filter($departments, function($d) use ($search) {
            return (stripos($d['department_name'], $search) !== false);
        });
    }

    // Tab resolution
    $tab = $_GET['tab'] ?? 'departments';
    $selectedStudentId = (int)($_GET['student_id'] ?? 0);
    if ($selectedStudentId > 0) {
        $tab = 'students';
    }

    // Departments for dropdown filter
    require_once __DIR__ . '/../../config/connection.php';
    $conn = createConnection();
    $deptResult = $conn->query("SELECT id, department FROM departments ORDER BY department ASC");
    $allDepartments = [];
    while ($d = $deptResult->fetch_assoc()) {
        $allDepartments[] = $d;
    }

    // Courses for dropdown filter
    $courseResult = $conn->query("SELECT id, course_name, department_id FROM course ORDER BY course_name ASC");
    $allCourses = [];
    while ($c = $courseResult->fetch_assoc()) {
        $allCourses[] = $c;
    }

    // Student Gradebook data resolution
    $studentsList = [];
    $selectedStudentProfile = null;
    $selectedStudentGrades = [];
    $selectedStudentSummary = null;
    $deptFilter = isset($_GET['dept_id']) && $_GET['dept_id'] !== '' ? (int)$_GET['dept_id'] : null;
    $courseFilter = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;
    $studentSearch = trim($_GET['student_search'] ?? '');

    if ($tab === 'students') {
        $studentsList = $gradeModel->getStudentsGradesOverview($deptFilter, $courseFilter, $studentSearch);

        if ($selectedStudentId > 0) {
            $selectedStudentProfile = $gradeModel->getStudentProfileWithCourse($selectedStudentId);
            if ($selectedStudentProfile) {
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
    <title>Institutional Academic Grade Audit · Edu-System</title>
    <link rel="stylesheet" href="adminGrades.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Page Heading Hero (Strictly Free of Buttons) -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">INSTITUTIONAL PERFORMANCE · ACADEMIC AUDIT</span>
                        <h1>Institutional Grades & Academic Records</h1>
                        <p class="subtitle">Global audit of academic evaluations, divisional cumulative scores, institutional grade distributions, and individual student dossiers.</p>
                    </div>
                </section>

                <!-- Global Overview Metrics -->
                <div class="metrics-grid">
                    <div class="metric-card highlight-card">
                        <span class="metric-label">Institutional Cumulative Average</span>
                        <span class="metric-value"><?php echo number_format($overview['institutional_average'], 1); ?>%</span>
                        <span class="metric-desc">Mean academic score across all evaluated coursework</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Institutional Assignments</span>
                        <span class="metric-value"><?php echo $overview['total_assignments']; ?></span>
                        <span class="metric-desc">Published across all curricula</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Student Deliverables Handed In</span>
                        <span class="metric-value submitted-val"><?php echo $overview['total_submissions']; ?></span>
                        <span class="metric-desc">Submissions logged in system</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Graded & Audited Submissions</span>
                        <span class="metric-value graded-val"><?php echo $overview['graded_submissions']; ?></span>
                        <span class="metric-desc">With final evaluation on record</span>
                    </div>
                </div>

                <!-- View Navigation Tabs -->
                <nav class="tabs-nav">
                    <a href="adminGrades.php?tab=departments" class="tab-btn <?php echo ($tab === 'departments') ? 'active' : ''; ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        Department Rankings
                    </a>
                    <a href="adminGrades.php?tab=students" class="tab-btn <?php echo ($tab === 'students') ? 'active' : ''; ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Student Gradebook & Records
                    </a>
                </nav>

                <?php if ($tab === 'departments'): ?>
                    <!-- Department Rankings Panel -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Academic Division Evaluation Ranking</h2>
                                <p>Aggregated academic scores and curricular workload by department.</p>
                            </div>
                        </div>

                        <!-- Panel Toolbar with Search -->
                        <div class="panel-toolbar">
                            <form method="GET" action="adminGrades.php" class="filter-form">
                                <input type="hidden" name="tab" value="departments">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search department..." class="search-input">
                                <button type="submit" class="btn-filter">Search</button>
                                <?php if (!empty($search)): ?>
                                    <a href="adminGrades.php?tab=departments" class="btn-clear">Reset</a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <!-- Table Wrap -->
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Academic Division</th>
                                        <th>Curricular Courses</th>
                                        <th>Total Coursework Items</th>
                                        <th>Cumulative Average</th>
                                        <th>Performance Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($departments)): ?>
                                        <tr>
                                            <td colspan="5" class="empty-state-cell">
                                                <p>No departmental performance records found.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($departments as $d):
                                            $avg = (float)$d['department_average'];
                                            $hasData = ($d['assignments_count'] > 0);
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="dept-title-cell">
                                                        <strong><?php echo htmlspecialchars($d['department_name']); ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="metric-pill"><?php echo (int)$d['courses_count']; ?> Courses</span>
                                                </td>
                                                <td>
                                                    <strong><?php echo (int)$d['assignments_count']; ?></strong> Tasks
                                                </td>
                                                <td>
                                                    <?php if ($hasData): ?>
                                                        <span class="score-badge <?php echo $avg >= 70 ? 'score-pass' : 'score-fail'; ?>">
                                                            <?php echo number_format($avg, 1); ?>%
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="na-label">No evaluations yet</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!$hasData): ?>
                                                        <span class="perf-status perf-pending">Awaiting Tasks</span>
                                                    <?php elseif ($avg >= 85): ?>
                                                        <span class="perf-status perf-high">High Distinction</span>
                                                    <?php elseif ($avg >= 70): ?>
                                                        <span class="perf-status perf-satisfactory">Satisfactory</span>
                                                    <?php else: ?>
                                                        <span class="perf-status perf-review">Needs Review</span>
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
                                <a href="adminGrades.php?tab=students" class="btn-clear" style="padding:10px 16px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                    All Students
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
                        <!-- Institutional Students List Table -->
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Student Gradebook Directory</h2>
                                    <p>Individual student cumulative scores, delivered assignments, and academic evaluation records across all programs.</p>
                                </div>
                            </div>

                            <!-- Panel Toolbar with Filters -->
                            <div class="panel-toolbar" style="justify-content:space-between;">
                                <form method="GET" action="adminGrades.php" class="filter-form" style="flex-wrap:wrap;">
                                    <input type="hidden" name="tab" value="students">
                                    <select name="dept_id" class="search-input" style="width:180px;" onchange="this.form.submit()">
                                        <option value="">All Departments</option>
                                        <?php foreach ($allDepartments as $d): ?>
                                            <option value="<?php echo (int)$d['id']; ?>" <?php echo ($deptFilter === (int)$d['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($d['department']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="course_id" class="search-input" style="width:180px;" onchange="this.form.submit()">
                                        <option value="">All Courses</option>
                                        <?php foreach ($allCourses as $c): ?>
                                            <option value="<?php echo (int)$c['id']; ?>" <?php echo ($courseFilter === (int)$c['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($c['course_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="student_search" value="<?php echo htmlspecialchars($studentSearch); ?>" placeholder="Search student name or email..." class="search-input">
                                    <button type="submit" class="btn-filter">Search</button>
                                    <?php if (!empty($studentSearch) || !empty($deptFilter) || !empty($courseFilter)): ?>
                                        <a href="adminGrades.php?tab=students" class="btn-clear">Reset</a>
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
                                            <th>Academic Division</th>
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
                                                <td colspan="8" class="empty-state-cell">
                                                    <p>No student evaluation records found matching your filters.</p>
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
                                                        <span style="font-size:13px; color:#475569;"><?php echo htmlspecialchars($st['department_name']); ?></span>
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
                                                        <a href="adminGrades.php?tab=students&student_id=<?php echo (int)$st['student_id']; ?>" class="btn-filter" style="text-decoration:none; display:inline-block; font-size:12px; padding:6px 12px;">
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
