<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1]);
    require_once __DIR__ . "/../../models/assignmentModels/assignmentModel.php";
    require_once __DIR__ . "/../../config/connection.php";

    $assignmentModel = new AssignmentModel();
    $conn = createConnection();

    // Fetch departments for filter
    $deptResult = $conn->query("SELECT id, department FROM departments ORDER BY department ASC");
    $departments = [];
    while ($d = $deptResult->fetch_assoc()) {
        $departments[] = $d;
    }

    $deptFilter = isset($_GET['dept_id']) && $_GET['dept_id'] !== '' ? (int)$_GET['dept_id'] : null;
    $courseFilter = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;
    $search = trim($_GET['search'] ?? '');

    $allAssignments = $assignmentModel->getAllAssignments($deptFilter, $courseFilter);

    if (!empty($search)) {
        $allAssignments = array_filter($allAssignments, function($a) use ($search) {
            return (stripos($a['title'], $search) !== false)
                || (stripos($a['course_name'] ?? '', $search) !== false)
                || (stripos($a['teacher_name'] ?? '', $search) !== false)
                || (stripos($a['department_name'] ?? '', $search) !== false);
        });
    }

    // Institutional KPIs
    $kpiTotalTasks = count($allAssignments);
    $kpiTotalSubmissions = 0;
    $kpiTotalGraded = 0;

    foreach ($allAssignments as $a) {
        $kpiTotalSubmissions += (int)($a['submission_count'] ?? 0);
        $kpiTotalGraded += (int)($a['graded_count'] ?? 0);
    }

    $kpiPending = $kpiTotalSubmissions - $kpiTotalGraded;

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institutional Assignments Repository · EduX</title>
    <link rel="stylesheet" href="adminAssignments.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

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
                        <span class="eyebrow">SYSTEM GOVERNANCE · ACADEMIC AUDIT</span>
                        <h1>Institutional Assignments Repository</h1>
                        <p class="subtitle">Global oversight of all academic tasks, prompts, deliverables, and submissions created across departments and curricula.</p>
                    </div>
                </section>

                <!-- Institutional Overview KPIs -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <span class="metric-label">Institutional Assignments</span>
                        <span class="metric-value"><?php echo $kpiTotalTasks; ?></span>
                        <span class="metric-desc">Curricular tasks on record</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Total Submissions</span>
                        <span class="metric-value submitted-val"><?php echo $kpiTotalSubmissions; ?></span>
                        <span class="metric-desc">Turned in across all departments</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Evaluated Deliverables</span>
                        <span class="metric-value graded-val"><?php echo $kpiTotalGraded; ?></span>
                        <span class="metric-desc">Scored with faculty feedback</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Pending Faculty Evaluation</span>
                        <span class="metric-value pending-val"><?php echo $kpiPending; ?></span>
                        <span class="metric-desc">Submissions awaiting review</span>
                    </div>
                </div>

                <!-- Global Assignments List Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Institutional Coursework Audit Directory</h2>
                            <p>Filter by academic department or search by keyword.</p>
                        </div>
                    </div>

                    <!-- Panel Toolbar with Filters -->
                    <div class="panel-toolbar">
                        <form method="GET" action="adminAssignments.php" class="filter-form">
                            <select name="dept_id" class="select-control">
                                <option value="">All Academic Departments</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?php echo $d['id']; ?>" <?php echo $deptFilter === (int)$d['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['department']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search task, course, or teacher..." class="search-input">
                            <button type="submit" class="btn-filter">Audit Filter</button>
                            <?php if (!empty($search) || !empty($deptFilter)): ?>
                                <a href="adminAssignments.php" class="btn-clear">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Table Wrap -->
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Assignment & Module</th>
                                    <th>Department</th>
                                    <th>Course Program</th>
                                    <th>Faculty Instructor</th>
                                    <th>Due Date</th>
                                    <th>Turned In / Enrolled</th>
                                    <th>Graded</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allAssignments)): ?>
                                    <tr>
                                        <td colspan="8" class="empty-state-cell">
                                            <p>No institutional coursework assignments found matching the criteria.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allAssignments as $a):
                                        $subCount = (int)$a['submission_count'];
                                        $gradCount = (int)$a['graded_count'];
                                        $pendingThis = $subCount - $gradCount;
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
                                                <span class="dept-badge"><?php echo htmlspecialchars($a['department_name'] ?? 'General'); ?></span>
                                            </td>
                                            <td>
                                                <span class="course-name-tag"><?php echo htmlspecialchars($a['course_name']); ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($a['teacher_name']); ?></strong>
                                            </td>
                                            <td>
                                                <div class="due-cell">
                                                    <span><?php echo date('M d, Y', strtotime($a['due_date'])); ?></span>
                                                    <small><?php echo date('h:i A', strtotime($a['due_date'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="roster-pill"><?php echo $subCount; ?> / <?php echo (int)$a['student_count']; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($pendingThis === 0 && $subCount > 0): ?>
                                                    <span class="badge-all-graded">100% Done</span>
                                                <?php elseif ($pendingThis > 0): ?>
                                                    <span class="badge-pending-grad"><?php echo $pendingThis; ?> Pending</span>
                                                <?php else: ?>
                                                    <span class="na-label">No Submissions</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: right; white-space: nowrap;">
                                                <a href="../ponente/ponenteAssignments.php?action=edit&edit_id=<?php echo (int)$a['id']; ?>" class="btn-review-link" style="display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:600; color:#0f766e; background:#f0fdfa; border:1px solid #99f6e4; padding:5px 10px; border-radius:6px; text-decoration:none; margin-right:6px;" title="Edit Assignment Parameters">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    Edit
                                                </a>
                                                <a href="../ponente/ponenteAssignments.php?review_id=<?php echo (int)$a['id']; ?>" class="btn-review-link" style="display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:600; color:#2563eb; background:#eff6ff; border:1px solid #bfdbfe; padding:5px 10px; border-radius:6px; text-decoration:none; margin-right:6px;" title="Review & Grade Submissions">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                    Review / Grade
                                                </a>
                                                <form method="POST" action="../../controllers/teacher/manageAssignments.php?action=delete&assignment_id=<?php echo $a['id']; ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this assignment?');"><?php echo csrfField(); ?><button type="submit" class="btn-delete-link">
                                                    Delete
                                                </button></form>
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
