<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 2]);
    require_once __DIR__ . "/../../models/directorModels/directorDashboardModel.php";
    require_once __DIR__ . "/../../models/assignmentModels/assignmentModel.php";

    $user = getLoggedUser();
    $directorId = (int)($user['id'] ?? 0);

    $directorModel = new DirectorDashboard();
    $assignmentModel = new AssignmentModel();

    $dept = $directorModel->getDirectorDepartment($directorId);
    $deptId = (int)($dept['department_id'] ?? 0);
    $deptName = $dept['department_name'] ?? 'Academic Department';

    $departmentAssignments = $assignmentModel->getDepartmentAssignments($deptId);

    // Filter by course or search
    $courseFilter = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;
    $search = trim($_GET['search'] ?? '');

    $departmentCourses = $directorModel->getDepartmentCourses($deptId);

    if ($courseFilter > 0) {
        $departmentAssignments = array_filter($departmentAssignments, function($a) use ($courseFilter) {
            return (int)$a['course_id'] === $courseFilter;
        });
    }

    if (!empty($search)) {
        $departmentAssignments = array_filter($departmentAssignments, function($a) use ($search) {
            return (stripos($a['title'], $search) !== false)
                || (stripos($a['course_name'] ?? '', $search) !== false)
                || (stripos($a['teacher_name'] ?? '', $search) !== false);
        });
    }

    // KPI Metrics calculation
    $kpiTotalTasks = count($departmentAssignments);
    $kpiTotalSubmissions = 0;
    $kpiTotalGraded = 0;

    foreach ($departmentAssignments as $a) {
        $kpiTotalSubmissions += (int)($a['submission_count'] ?? 0);
        $kpiTotalGraded += (int)($a['graded_count'] ?? 0);
    }

    $complianceRate = $kpiTotalSubmissions > 0 ? round(($kpiTotalGraded / $kpiTotalSubmissions) * 100, 1) : 100.0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Assignments Supervision · Edu-System</title>
    <link rel="stylesheet" href="directorAssignments.css">
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
                        <span class="eyebrow">DIVISIONAL SUPERVISION · <?php echo htmlspecialchars($deptName); ?></span>
                        <h1>Department Coursework & Deliverables</h1>
                        <p class="subtitle">Monitor assignments published by faculty across all division courses, review submission volumes, and audit grading compliance.</p>
                    </div>
                </section>

                <!-- Divisional KPI Metrics -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <span class="metric-label">Division Assignments</span>
                        <span class="metric-value"><?php echo $kpiTotalTasks; ?></span>
                        <span class="metric-desc">Registered in <?php echo htmlspecialchars($deptName); ?></span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Student Submissions</span>
                        <span class="metric-value submitted-val"><?php echo $kpiTotalSubmissions; ?></span>
                        <span class="metric-desc">Handed in across division courses</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Graded by Faculty</span>
                        <span class="metric-value graded-val"><?php echo $kpiTotalGraded; ?></span>
                        <span class="metric-desc">Evaluations completed</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Faculty Grading Compliance</span>
                        <span class="metric-value"><?php echo $complianceRate; ?>%</span>
                        <span class="metric-desc">Submissions evaluated to date</span>
                    </div>
                </div>

                <!-- Main Supervision Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Curricular Deliverables Directory</h2>
                            <p>Overview of academic tasks published by division faculty.</p>
                        </div>
                    </div>

                    <!-- Panel Toolbar with Filters -->
                    <div class="panel-toolbar">
                        <form method="GET" action="directorAssignments.php" class="filter-form">
                            <select name="course_id" class="select-control">
                                <option value="">All Department Courses</option>
                                <?php foreach ($departmentCourses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $courseFilter === (int)$c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search task or teacher..." class="search-input">
                            <button type="submit" class="btn-filter">Filter</button>
                            <?php if (!empty($search) || !empty($courseFilter)): ?>
                                <a href="directorAssignments.php" class="btn-clear">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Table Wrap -->
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Assignment & Module</th>
                                    <th>Course Program</th>
                                    <th>Instructing Faculty</th>
                                    <th>Due Date</th>
                                    <th>Student Roster</th>
                                    <th>Submissions</th>
                                    <th>Graded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($departmentAssignments)): ?>
                                    <tr>
                                        <td colspan="7" class="empty-state-cell">
                                            <p>No coursework assignments registered for this department.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($departmentAssignments as $a):
                                        $pending = (int)$a['submission_count'] - (int)$a['graded_count'];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="task-title-cell">
                                                    <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                                                    <span class="block-subtitle"><?php echo htmlspecialchars($a['block_name']); ?></span>
                                                    <?php if (!empty($a['file_path'])): ?>
                                                        <a <?php echo resourceLinkAttributes($a['file_path'], 'assignment', $a['id']); ?> target="_blank" download style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; color:#8B1528; text-decoration:none; margin-top:4px; padding:2px 8px; background:#fdf2f4; border:1px solid #fecdd3; border-radius:4px;" title="Download attached prompt">
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                            <span>Prompt (<?php echo htmlspecialchars(strtoupper(pathinfo($a['file_path'], PATHINFO_EXTENSION)) ?: 'PDF'); ?>)</span>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="course-name-tag"><?php echo htmlspecialchars($a['course_name']); ?></span>
                                            </td>
                                            <td>
                                                <div class="faculty-name-wrap">
                                                    <strong><?php echo htmlspecialchars($a['teacher_name']); ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="due-cell">
                                                    <span><?php echo date('M d, Y', strtotime($a['due_date'])); ?></span>
                                                    <small><?php echo date('h:i A', strtotime($a['due_date'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="roster-pill"><?php echo (int)$a['student_count']; ?> Enrolled</span>
                                            </td>
                                            <td>
                                                <span class="submission-count-pill"><?php echo (int)$a['submission_count']; ?> Delivered</span>
                                            </td>
                                            <td>
                                                <?php if ($pending === 0 && (int)$a['submission_count'] > 0): ?>
                                                    <span class="badge-all-graded">100% Graded</span>
                                                <?php elseif ($pending > 0): ?>
                                                    <span class="badge-pending-grad"><?php echo $pending; ?> Pending</span>
                                                <?php else: ?>
                                                    <span class="na-label">No entries</span>
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
