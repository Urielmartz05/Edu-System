<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    requireRole([1, 2]);
    $user = getLoggedUser();
    $directorId = (int)$user['id'];

    require_once __DIR__ . '/../../models/directorModels/directorDashboardModel.php';
    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';

    $directorModel = new DirectorDashboard();
    $dept = $directorModel->getDirectorDepartment($directorId);
    $deptId = (int)($dept['department_id'] ?? 0);

    $coursesModel = new AdminCourses();
    $courseId = (int)($_GET['course_id'] ?? 0);

    if ($courseId <= 0) {
        header("Location: director.php#coursesSection");
        exit;
    }

    $course = $coursesModel->getCourseDetails($courseId);
    if (!$course) {
        header("Location: director.php?error=course_not_found");
        exit;
    }

    // Verify department ownership (unless role 1)
    if ((int)($course['department_id'] ?? 0) !== $deptId && (int)($user['role'] ?? 0) !== 1) {
        header("Location: director.php?error=unauthorized_department");
        exit;
    }

    $blocks = $coursesModel->getCourseBlocks($courseId);
    $students = $coursesModel->getCourseStudents($courseId);
    $totalFiles = $coursesModel->getCourseFilesCount($courseId);
    $teachersList = $coursesModel->getTeachersList();

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);

    $reassignBlockId = (int)($_GET['reassign_block_id'] ?? 0);
    $blockToReassign = null;
    if ($reassignBlockId > 0) {
        foreach ($blocks as $b) {
            if ((int)$b['id'] === $reassignBlockId) {
                $blockToReassign = $b;
                break;
            }
        }
    }

    $renameBlockId = (int)($_GET['rename_block_id'] ?? 0);
    $blockToRename = null;
    if ($renameBlockId > 0) {
        foreach ($blocks as $b) {
            if ((int)$b['id'] === $renameBlockId) {
                $blockToRename = $b;
                break;
            }
        }
    }

    // Fetch attached study files for each module/block
    $blockFiles = [];
    foreach ($blocks as $b) {
        $blockFiles[$b['id']] = $coursesModel->getBlockFiles($b['id']);
    }

    $isPublished = ((int)$course['status'] === 1);
    $statusText = $isPublished ? 'Published' : 'Under Review';
    $statusClass = $isPublished ? 'active' : 'pending';
    $progress = (float)($course['progress'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['course_name']); ?> · Course Syllabus & Materials · EduX</title>
    <link rel="stylesheet" href="directorCourseContent.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/directorSidebar/directorSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Page Header Hero -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC DIVISION · <?php echo htmlspecialchars($dept['department_name']); ?></span>
                        <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
                        <p class="subtitle">Supervise curricular syllabus modules, appointed faculty instructors, digital study materials, and enrolled students.</p>
                    </div>
                </section>

                <!-- Panel Toolbar for Course Actions -->
                <div class="panel-toolbar" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <a href="directorCourses.php" class="btn btn-secondary" style="text-decoration:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        <span>Back to Department Courses</span>
                    </a>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <a href="directorAssignments.php?course_id=<?php echo (int)$courseId; ?>" class="btn btn-secondary" style="text-decoration:none;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>Supervise Tasks</span>
                        </a>
                        <a href="directorGrades.php?search=<?php echo urlencode($course['course_name']); ?>" class="btn btn-secondary" style="text-decoration:none;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
                            <span>Audit Grades</span>
                        </a>
                    </div>
                </div>

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

                <!-- Executive KPI Metrics -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <div class="kpi-header">
                            <span class="kpi-label">Syllabus Progress</span>
                            <div class="kpi-icon-wrap icon-status">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $progress; ?>%</strong>
                        <div style="width:100%; background:#e2e8f0; border-radius:6px; height:8px; overflow:hidden; margin-top:8px;">
                            <div style="width:<?php echo min(100, max(0, $progress)); ?>%; background:#10b981; height:100%;"></div>
                        </div>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <span class="kpi-label">Enrolled Students</span>
                            <div class="kpi-icon-wrap icon-students">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($students); ?></strong>
                        <span class="kpi-hint">Learners active in roster</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <span class="kpi-label">Syllabus Modules</span>
                            <div class="kpi-icon-wrap icon-modules">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($blocks); ?></strong>
                        <span class="kpi-hint">Instructional blocks configured</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <span class="kpi-label">Study Materials</span>
                            <div class="kpi-icon-wrap icon-files">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $totalFiles; ?></strong>
                        <span class="kpi-hint">Files, slides & documents</span>
                    </article>
                </section>

                <!-- Two-Column Layout -->
                <div class="two-column-layout">
                    <!-- Left Column: Syllabus Modules & Attached Learning Materials -->
                    <div class="primary-column">
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Syllabus Units & Learning Materials</h2>
                                    <p>Inspect instructional modules and download all learning resources uploaded by faculty.</p>
                                </div>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span class="director-badge-tag"><?php echo count($blocks); ?> Module(s)</span>
                                    <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>&add_module=1" class="btn btn-primary" style="padding:6px 14px; font-size:12px; text-decoration:none; background:#8B1528; border-color:#8B1528; color:#ffffff; border-radius:8px; font-weight:600;">+ Add Module & Faculty</a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <?php if (!empty($blocks)): ?>
                                    <div class="accordion">
                                        <?php foreach ($blocks as $index => $b):
                                            $files = $blockFiles[$b['id']] ?? [];
                                        ?>
                                            <div class="accordion-item" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 14px; overflow: hidden; background:#ffffff;">
                                                <div class="accordion-header" style="background:#f8fafc; padding: 16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-bottom: 1px solid #e2e8f0;">
                                                    <div>
                                                        <span style="font-size: 11px; font-weight: 700; color: #8B1528; text-transform: uppercase; letter-spacing: 0.05em; display:block; margin-bottom:2px;">Module #<?php echo ($index + 1); ?></span>
                                                        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;"><?php echo htmlspecialchars($b['block_name']); ?></h3>
                                                    </div>
                                                    <div style="display:flex; align-items:center; gap:10px;">
                                                        <span class="faculty-tag" style="background:#eff6ff; color:#1d4ed8; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600;">
                                                            Instructor: <?php echo htmlspecialchars($b['teacher_name'] ?? 'Unassigned'); ?>
                                                            <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>&reassign_block_id=<?php echo (int)$b['id']; ?>" style="color:#8B1528; text-decoration:none; margin-left:6px; font-weight:700;" title="Reassign Faculty Instructor">✎ Change</a>
                                                        </span>
                                                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                                                            <?php echo count($files); ?> file(s)
                                                        </span>
                                                        <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>&rename_block_id=<?php echo (int)$b['id']; ?>" style="display:inline-flex; align-items:center; gap:4px; padding:4px 9px; font-size:12px; font-weight:600; color:#0f766e; background:#f0fdfa; border:1px solid #99f6e4; border-radius:6px; text-decoration:none;" title="Edit Module Title">
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                            <span>Edit Name</span>
                                                        </a>
                                                        <form method="POST" action="../../controllers/admin/manageCourseActions.php" style="display:inline; margin:0;" onsubmit="return confirm('Delete this module and all its attached resources permanently?');">
                                                            <?php echo csrfField(); ?>
                                                            <input type="hidden" name="action" value="delete_block">
                                                            <input type="hidden" name="block_id" value="<?php echo (int)$b['id']; ?>">
                                                            <input type="hidden" name="course_id" value="<?php echo (int)$courseId; ?>">
                                                            <button type="submit" style="display:inline-flex; align-items:center; gap:4px; padding:4px 9px; font-size:12px; font-weight:600; color:#dc2626; background:#fef2f2; border:1px solid #fecdd3; border-radius:6px; cursor:pointer;" title="Delete Module Block">
                                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                                <span>Remove</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                <div class="accordion-body" style="padding: 18px 20px;">
                                                    <h4 style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">Attached Study Materials & Resources</h4>

                                                    <?php if (!empty($files)): ?>
                                                        <div class="files-list">
                                                            <?php foreach ($files as $f): ?>
                                                                <div class="file-item">
                                                                    <div class="file-main">
                                                                        <div class="file-type-icon">
                                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                                        </div>
                                                                        <div class="file-info">
                                                                            <span class="file-name"><?php echo htmlspecialchars($f['file_name']); ?></span>
                                                                            <span class="file-meta">Resource #<?php echo (int)$f['id']; ?> · Syllabus Unit #<?php echo ($index + 1); ?></span>
                                                                        </div>
                                                                    </div>
                                                                    <div style="display:flex; align-items:center; gap:6px;">
                                                                        <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$f['id']; ?>&mode=view" target="_blank" class="btn-file-action" title="Preview Resource">
                                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                                            <span>View</span>
                                                                        </a>
                                                                        <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$f['id']; ?>&mode=download" class="btn-file-action" title="Download Document">
                                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                                            <span>Download</span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="empty-files-alert">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                            <span>No digital files or readings uploaded for this module yet. Instructors can attach materials via their portal.</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state-panel">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                        <h3>No Modules Configured</h3>
                                        <p>This course currently has no syllabus block units configured in the system.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>

                    <!-- Right Column: Course Specifications & Enrolled Students -->
                    <div class="secondary-column">
                        <!-- Course Specification Card -->
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Program Overview</h2>
                                    <p>Curricular division data</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="spec-row" style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9; font-size:13px;">
                                    <span style="color:#64748b; font-weight:500;">Academic Division</span>
                                    <strong style="color:#0f172a;"><?php echo htmlspecialchars($course['department_name'] ?? $dept['department_name']); ?></strong>
                                </div>
                                <div class="spec-row" style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9; font-size:13px;">
                                    <span style="color:#64748b; font-weight:500;">Academic Credits</span>
                                    <strong style="color:#0f172a;"><?php echo (int)($course['credits'] ?? 0); ?> Credits</strong>
                                </div>
                                <div class="spec-row" style="display:flex; justify-content:space-between; padding:10px 0; font-size:13px;">
                                    <span style="color:#64748b; font-weight:500;">Curricular Status</span>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </div>
                            </div>
                        </section>

                        <!-- Enrolled Students Panel -->
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Enrolled Learners</h2>
                                    <p>Active student roster (<?php echo count($students); ?>)</p>
                                </div>
                            </div>
                            <div class="panel-body" style="padding:0;">
                                <div class="table-wrap" style="border:none; border-radius:0;">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th style="text-align:right;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($students)): ?>
                                                <tr>
                                                    <td colspan="2" class="empty-state-cell" style="padding:20px !important;">
                                                        <p>No learners currently enrolled.</p>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($students as $s): ?>
                                                    <tr>
                                                        <td>
                                                            <strong style="display:block; font-size:13px; color:#0f172a;"><?php echo htmlspecialchars($s['name'] . ' ' . ($s['lastname'] ?? '')); ?></strong>
                                                            <span style="font-size:11px; color:#64748b;"><?php echo htmlspecialchars($s['email']); ?></span>
                                                        </td>
                                                        <td style="text-align:right;">
                                                            <a href="directorGrades.php?tab=students&student_id=<?php echo (int)$s['id']; ?>" class="btn-file-action" style="font-size:11px; padding:4px 8px; text-decoration:none;" title="View Student Academic Grades">
                                                                Grades
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Add Module & Faculty Modal (Pure PHP) -->
    <?php if (!empty($_GET['add_module'])): ?>
        <div id="moduleModal" class="modal-overlay" style="display:flex;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Add Syllabus Module & Appoint Faculty</h3>
                        <p style="font-size:12px; color:#64748b; margin-top:2px;">Create an instructional block unit and assign an instructor.</p>
                    </div>
                    <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>" class="btn-modal-close" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/admin/manageBlock.php" method="POST" style="margin-top:18px;"><?php echo csrfField(); ?>
                    <input type="hidden" name="course_id" value="<?php echo (int)$courseId; ?>">
                    <input type="hidden" name="action" value="create_block">
                    <input type="hidden" name="return_url" value="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>">

                    <div style="margin-bottom:16px;">
                        <label for="block_name" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Module / Block Title *</label>
                        <input type="text" id="block_name" name="block_name" class="modal-field" placeholder="e.g. Distributed Database Architecture" maxlength="60" required>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label for="teacher_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Appointed Faculty Instructor *</label>
                        <select id="teacher_id" name="teacher_id" class="modal-field" required>
                            <option value="">Select instructor...</option>
                            <?php foreach ($teachersList as $t): ?>
                                <option value="<?php echo (int)$t['id']; ?>">
                                    <?php echo htmlspecialchars($t['name'] . ' ' . $t['lastname'] . ' (' . $t['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>" class="btn-modal-cancel">Cancel</a>
                        <button type="submit" class="btn-modal-submit">Create Module</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Reassign Module Instructor Modal (Pure PHP) -->
    <?php if ($blockToReassign): ?>
        <div id="reassignModal" class="modal-overlay" style="display:flex;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Reassign Module Instructor</h3>
                        <p style="font-size:12px; color:#64748b; margin-top:2px;">Change faculty member responsible for "<?php echo htmlspecialchars($blockToReassign['block_name']); ?>".</p>
                    </div>
                    <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>" class="btn-modal-close" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/admin/manageBlock.php" method="POST" style="margin-top:18px;"><?php echo csrfField(); ?>
                    <input type="hidden" name="course_id" value="<?php echo (int)$courseId; ?>">
                    <input type="hidden" name="block_id" value="<?php echo (int)$blockToReassign['id']; ?>">
                    <input type="hidden" name="action" value="reassign_block">
                    <input type="hidden" name="return_url" value="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>">

                    <div style="margin-bottom:20px;">
                        <label for="reassign_teacher_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Select New Faculty Instructor *</label>
                        <select id="reassign_teacher_id" name="teacher_id" class="modal-field" required>
                            <option value="">Select instructor...</option>
                            <?php foreach ($teachersList as $t): ?>
                                <option value="<?php echo (int)$t['id']; ?>" <?php echo ((int)($blockToReassign['teacher_id'] ?? 0) === (int)$t['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['name'] . ' ' . $t['lastname'] . ' (' . $t['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>" class="btn-modal-cancel">Cancel</a>
                        <button type="submit" class="btn-modal-submit">Update Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Rename Module Modal (Pure PHP) -->
    <?php if ($blockToRename): ?>
        <div id="renameModal" class="modal-overlay" style="display:flex;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Edit Module Title</h3>
                        <p style="font-size:12px; color:#64748b; margin-top:2px;">Update the syllabus title for this curricular unit.</p>
                    </div>
                    <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>" class="btn-modal-close" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/admin/manageBlock.php" method="POST" style="margin-top:18px;"><?php echo csrfField(); ?>
                    <input type="hidden" name="course_id" value="<?php echo (int)$courseId; ?>">
                    <input type="hidden" name="block_id" value="<?php echo (int)$blockToRename['id']; ?>">
                    <input type="hidden" name="action" value="rename_block">
                    <input type="hidden" name="return_url" value="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>">

                    <div style="margin-bottom:20px;">
                        <label for="rename_block_name" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Module Title *</label>
                        <input type="text" id="rename_block_name" name="block_name" value="<?php echo htmlspecialchars($blockToRename['block_name']); ?>" class="modal-field" maxlength="60" required autofocus>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <a href="directorCourseContent.php?course_id=<?php echo (int)$courseId; ?>" class="btn-modal-cancel">Cancel</a>
                        <button type="submit" class="btn-modal-submit" style="background:#0f766e; border-color:#0f766e;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
