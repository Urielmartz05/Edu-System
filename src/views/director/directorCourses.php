<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 2]);
    require_once __DIR__ . "/../../models/directorModels/directorDashboardModel.php";
    $user = getLoggedUser();

    $directorModel = new DirectorDashboard();
    $dept = $directorModel->getDirectorDepartment($user['id']);
    $deptId = (int)$dept['department_id'];

    $coursesCount = $directorModel->getCoursesCount($deptId);
    $facultyCount = $directorModel->getFacultyCount($deptId);
    $studentsCount = $directorModel->getStudentsCount($deptId);
    $avgProgress = $directorModel->getAverageProgress($deptId);

    $departmentCourses = $directorModel->getDepartmentCourses($deptId);
    $allDeptCourses = $departmentCourses;

    require_once __DIR__ . "/../../models/adminModels/adminCoursesModel.php";
    $coursesModel = new AdminCourses();
    $teachersList = $coursesModel->getTeachersList();

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);

    $assignCourseId = (int)($_GET['assign_course_id'] ?? 0);
    $assignTeacherId = (int)($_GET['assign_teacher_id'] ?? 0);
    $showAssignModal = !empty($_GET['assign_modal']) || $assignCourseId > 0 || $assignTeacherId > 0;

    // Search and Status filtering
    $search = trim($_GET['search'] ?? '');
    $statusFilter = trim($_GET['status'] ?? '');

    if (!empty($search)) {
        $departmentCourses = array_filter($departmentCourses, function($c) use ($search) {
            return stripos($c['course_name'] ?? '', $search) !== false
                || stripos($c['faculty_names'] ?? '', $search) !== false;
        });
    }

    if ($statusFilter !== '') {
        $departmentCourses = array_filter($departmentCourses, function($c) use ($statusFilter) {
            return (string)$c['status'] === $statusFilter;
        });
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Division Courses & Curricula · EduX</title>
    <link rel="stylesheet" href="directorCourses.css">
</head>
<body>
    <?php include_once __DIR__ . "/../../../ui/header/header.php"; ?>
    <?php include_once __DIR__ . "/../../../components/directorSidebar/directorSidebar.php"; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Page Heading -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC DIVISION CURRICULA · DEPARTMENT OF <?php echo htmlspecialchars(strtoupper($dept['department_name'])); ?></span>
                        <h1>Division Courses & Syllabi</h1>
                        <p class="subtitle">Complete catalog of educational programs, active curricula, syllabus progress, and course management within your academic department.</p>
                    </div>
                </section>

                <!-- KPI Grid -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <span class="kpi-label">Total Programs</span>
                        <strong class="kpi-value"><?php echo $coursesCount; ?></strong>
                        <span class="kpi-hint">Curricular offerings</span>
                    </article>

                    <article class="kpi">
                        <span class="kpi-label">Active Faculty</span>
                        <strong class="kpi-value"><?php echo $facultyCount; ?></strong>
                        <span class="kpi-hint">Instructors in division</span>
                    </article>

                    <article class="kpi">
                        <span class="kpi-label">Enrolled Students</span>
                        <strong class="kpi-value"><?php echo $studentsCount; ?></strong>
                        <span class="kpi-hint">Active learners</span>
                    </article>

                    <article class="kpi">
                        <span class="kpi-label">Average Progress</span>
                        <strong class="kpi-value"><?php echo $avgProgress; ?>%</strong>
                        <span class="kpi-hint">Syllabus completion rate</span>
                    </article>
                </section>

                <?php if ($flashSuccess): ?>
                    <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; font-size: 13px; font-weight: 500; display:flex; align-items:center; gap:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span><?php echo htmlspecialchars($flashSuccess); ?></span>
                    </div>
                <?php elseif ($flashError): ?>
                    <div style="background-color: #fef2f2; border: 1px solid #fecdd3; color: #b91c1c; padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; font-size: 13px; font-weight: 500; display:flex; align-items:center; gap:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($flashError); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Main Courses Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Division Courses & Offerings</h2>
                            <p>Academic programs registered under <?php echo htmlspecialchars($dept['department_name']); ?>.</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span class="badge-tag"><?php echo count($departmentCourses); ?> Course(s)</span>
                            <a href="directorCourses.php?assign_modal=1" class="btn-primary" style="display:inline-flex; align-items:center; gap:6px; background:#8B1528; color:#ffffff; padding:8px 16px; border-radius:8px; font-weight:600; font-size:12px; text-decoration:none;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Assign Faculty to Course</span>
                            </a>
                        </div>
                    </div>

                    <div class="filters-bar">
                        <form method="GET" action="directorCourses.php" class="filter-form">
                            <input type="search" name="search" class="search-input" placeholder="Search course title or instructor name..." value="<?php echo htmlspecialchars($search); ?>">
                            <select name="status" class="select-filter" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="1" <?php echo ($statusFilter === '1') ? 'selected' : ''; ?>>Active / Published</option>
                                <option value="0" <?php echo ($statusFilter === '0') ? 'selected' : ''; ?>>In Review / Draft</option>
                            </select>
                            <button type="submit" class="btn-filter">Search</button>
                            <?php if (!empty($search) || $statusFilter !== ''): ?>
                                <a href="directorCourses.php" class="btn-clear">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 24%;">COURSE PROGRAM</th>
                                    <th style="width: 9%;">CREDITS</th>
                                    <th style="width: 9%;">MODULES</th>
                                    <th style="width: 18%;">ASSIGNED FACULTY</th>
                                    <th style="width: 10%;">STUDENTS</th>
                                    <th style="width: 14%;">SYLLABUS PROGRESS</th>
                                    <th style="width: 8%;">STATUS</th>
                                    <th style="width: 18%; text-align: right;">ACADEMIC AUDIT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($departmentCourses)): ?>
                                    <?php foreach ($departmentCourses as $c):
                                        $prog = (float)($c['progress'] ?? 0);
                                        $isPublished = ((int)$c['status'] === 1);
                                    ?>
                                        <tr>
                                            <td>
                                                <strong style="color: #0f172a; font-size: 14px;"><?php echo htmlspecialchars($c['course_name']); ?></strong>
                                            </td>
                                            <td><?php echo (int)($c['credits'] ?? 0); ?> Credits</td>
                                            <td><?php echo (int)$c['block_count']; ?> modules</td>
                                            <td><span class="faculty-tag"><?php echo htmlspecialchars($c['faculty_names']); ?></span></td>
                                            <td><?php echo (int)$c['student_count']; ?> enrolled</td>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:8px;">
                                                    <span style="font-weight:600; min-width:38px; font-size:12px;"><?php echo $prog; ?>%</span>
                                                    <div style="flex:1; background:#e2e8f0; border-radius:6px; height:8px; overflow:hidden; min-width:45px;">
                                                        <div style="width:<?php echo min(100, max(0, $prog)); ?>%; background:#10b981; height:100%;"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $isPublished ? 'active' : 'pending'; ?>">
                                                    <?php echo $isPublished ? 'Active' : 'In Review'; ?>
                                                </span>
                                            </td>
                                             <td style="text-align: right;">
                                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                                    <a href="directorCourses.php?assign_course_id=<?php echo $c['id']; ?>" class="btn-action" style="background:#8B1528; color:#ffffff; text-decoration:none;" title="Assign Faculty to this Course">Assign</a>
                                                    <a href="directorCourseContent.php?course_id=<?php echo $c['id']; ?>" class="btn-action btn-material" title="Inspect Syllabus & Study Materials">Material</a>
                                                    <a href="directorAssignments.php?course_id=<?php echo $c['id']; ?>" class="btn-action btn-tasks" title="Supervise Tasks">Tasks</a>
                                                    <a href="directorGrades.php?search=<?php echo urlencode($c['course_name']); ?>" class="btn-action btn-grades" title="Audit Grades">Grades</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; padding:32px; color:#64748b;">No courses found matching your criteria.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Assign Faculty Modal (Pure PHP) -->
    <?php if ($showAssignModal): ?>
        <div id="assignFacultyModal" class="modal-overlay" style="display:flex;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Assign Faculty to Course</h3>
                        <p style="font-size:12px; color:#64748b; margin-top:2px;">Appoint an instructional faculty member to teach in a division curriculum.</p>
                    </div>
                    <a href="directorCourses.php" class="btn-modal-close" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/admin/manageBlock.php" method="POST" style="margin-top:18px;"><?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="assign_teacher">
                    <input type="hidden" name="return_url" value="directorCourses.php">

                    <div style="margin-bottom:16px;">
                        <label for="modal_course_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Division Course Program *</label>
                        <select id="modal_course_id" name="course_id" class="modal-field" required>
                            <option value="">Select course program...</option>
                            <?php foreach ($allDeptCourses as $dc): ?>
                                <option value="<?php echo (int)$dc['id']; ?>" <?php echo ($assignCourseId === (int)$dc['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dc['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label for="modal_teacher_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Appointed Faculty Member *</label>
                        <select id="modal_teacher_id" name="teacher_id" class="modal-field" required>
                            <option value="">Select faculty instructor...</option>
                            <?php foreach ($teachersList as $t): ?>
                                <option value="<?php echo (int)$t['id']; ?>" <?php echo ($assignTeacherId === (int)$t['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['name'] . ' ' . $t['lastname'] . ' (' . $t['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label for="modal_block_name" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Instructional Module / Syllabus Unit (Optional)</label>
                        <input type="text" id="modal_block_name" name="block_name" class="modal-field" placeholder="e.g. Core Unit / Advanced Topics (optional)" maxlength="60">
                        <span style="font-size:11.5px; color:#64748b; margin-top:4px; display:block;">If left blank, an unassigned module will be linked or a core unit will be created automatically.</span>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <a href="directorCourses.php" class="btn-modal-cancel">Cancel</a>
                        <button type="submit" class="btn-modal-submit">Confirm Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
