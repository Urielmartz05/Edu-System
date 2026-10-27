<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    requireRole([1]);
    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    $coursesModel = new AdminCourses();

    $courseId = (int)($_GET['course_id'] ?? 0);
    if ($courseId <= 0) {
        header("Location: adminCourses.php");
        exit;
    }

    $course = $coursesModel->getCourseDetails($courseId);
    if (!$course) {
        header("Location: adminCourses.php?error=course_not_found");
        exit;
    }

    $blocks = $coursesModel->getCourseBlocks($courseId);
    $students = $coursesModel->getCourseStudents($courseId);
    $totalFiles = $coursesModel->getCourseFilesCount($courseId);

    // Fetch attached files for each module/block
    $blockFiles = [];
    foreach ($blocks as $b) {
        $blockFiles[$b['id']] = $coursesModel->getBlockFiles($b['id']);
    }

    $teachersList = $coursesModel->getTeachersList();

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);

    $isPublished = ((int)$course['status'] === 1);
    $statusText = $isPublished ? 'Published' : 'Under Review';
    $statusClass = $isPublished ? 'active' : 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['course_name']); ?> · Course Inspector · Edu-System</title>
    <link rel="stylesheet" href="adminCourseContent.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Page Header Hero -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow"><?php echo htmlspecialchars($course['department_name']); ?> · Course Content Inspector</span>
                        <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
                        <p class="subtitle">Administrative oversight for course modules, assigned faculty, digital learning resources, and enrolled student rosters.</p>
                    </div>
                </section>

                <!-- Panel Toolbar for Course Actions -->
                <div class="panel-toolbar" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <a href="adminCourses.php" class="btn btn-secondary" style="text-decoration:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        <span>Back to Catalog</span>
                    </a>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <form method="POST" action="../../controllers/admin/toggleCourseStatus.php?course_id=<?php echo urlencode($course['id']); ?>&return_url=adminCourseContent.php" style="display:inline;"><?php echo csrfField(); ?><button type="submit"  class="btn <?php echo $isPublished ? 'btn-secondary' : 'btn-primary'; ?>" title="Toggle publication status">
                            <?php if ($isPublished): ?>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/></svg>
                                <span>Set to Review</span>
                            <?php else: ?>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span>Publish Course</span>
                            <?php endif; ?>
                        </button></form>
                        <a href="adminCreate.php?tab=course" class="btn btn-secondary" style="text-decoration:none;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Creation Hub</span>
                        </a>
                    </div>
                </div>

                <?php if ($flashSuccess): ?>
                    <div style="background-color: #f0fdf4; border: 1px solid #86efac; color: #15803d; padding: 14px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; font-weight: 500;">
                        <strong>Success:</strong> <?php echo htmlspecialchars($flashSuccess); ?>
                    </div>
                <?php elseif ($flashError): ?>
                    <div style="background-color: #fef2f2; border: 1px solid #fecdd3; color: #b91c1c; padding: 14px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; font-weight: 500;">
                        <strong>Error:</strong> <?php echo htmlspecialchars($flashError); ?>
                    </div>
                <?php endif; ?>

                <!-- Executive KPI Metrics -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <div class="kpi-header">
                            <span class="kpi-label">Course Status</span>
                            <div class="kpi-icon-wrap icon-status">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            </div>
                        </div>
                        <div class="kpi-status-container">
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </div>
                        <span class="kpi-hint">Catalog publication state</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <span class="kpi-label">Enrolled Students</span>
                            <div class="kpi-icon-wrap icon-students">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($students); ?></strong>
                        <span class="kpi-hint">Active class participants</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <span class="kpi-label">Instructional Modules</span>
                            <div class="kpi-icon-wrap icon-blocks">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($blocks); ?></strong>
                        <span class="kpi-hint">Syllabus block units</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <span class="kpi-label">Digital Resources</span>
                            <div class="kpi-icon-wrap icon-files">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo (int)$totalFiles; ?></strong>
                        <span class="kpi-hint">Attached study files & assets</span>
                    </article>
                </section>

                <!-- Inspector Content Grid -->
                <div class="content-grid">
                    <!-- Left Column: Instructional Modules & Attached Files -->
                    <div class="primary-column">
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Instructional Modules & Learning Materials</h2>
                                    <p>Comprehensive curriculum breakdown with assigned faculty instructors and attached files.</p>
                                </div>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span class="counter-badge"><?php echo count($blocks); ?> Module(s)</span>
                                    <a href="adminCourseContent.php?course_id=<?php echo (int)$course['id']; ?>&add_module=1" class="btn btn-primary" style="padding:6px 14px; font-size:12px; text-decoration:none;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span>Add Module</span>
                                    </a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <?php if (!empty($blocks)): ?>
                                    <div class="blocks-container">
                                        <?php foreach ($blocks as $index => $b):
                                            $files = $blockFiles[$b['id']] ?? [];
                                        ?>
                                            <div class="module-card">
                                                <div class="module-header">
                                                    <div class="module-title-wrap">
                                                        <span class="module-pill">Unit <?php echo $index + 1; ?> · Block #<?php echo (int)$b['id']; ?></span>
                                                        <h3 class="module-title"><?php echo htmlspecialchars($b['block_name']); ?></h3>
                                                    </div>
                                                    <div class="module-instructor">
                                                        <div class="instructor-avatar">
                                                            <?php echo strtoupper(substr($b['teacher_name'] ?? 'T', 0, 1)); ?>
                                                        </div>
                                                        <div class="instructor-details">
                                                            <span class="instructor-name"><?php echo htmlspecialchars($b['teacher_name']); ?></span>
                                                            <span class="instructor-role">Module Instructor</span>
                                                        </div>
                                                        <form method="POST" action="../../controllers/admin/manageCourseActions.php?action=delete_block&block_id=<?php echo (int)$b['id']; ?>&course_id=<?php echo (int)$course['id']; ?>" style="display:inline;"><?php echo csrfField(); ?><button type="submit"  class="btn-file-action" style="color:#dc2626; border-color:#fecdd3; margin-left:10px; text-decoration:none;" title="Delete Module Block">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                            <span>Remove</span>
                                                        </button></form>
                                                    </div>
                                                </div>

                                                <!-- Attached Files Section -->
                                                <div class="module-files-wrap">
                                                    <h4 class="files-heading">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                                        <span>Learning Resources & Attachments (<?php echo count($files); ?>)</span>
                                                    </h4>

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
                                                                            <span class="file-meta">Resource ID #<?php echo (int)$f['id']; ?> · Subject Module #<?php echo (int)$b['id']; ?></span>
                                                                        </div>
                                                                    </div>
                                                                    <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$f['id']; ?>&mode=download" class="btn-file-action" title="Download Resource">
                                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                                        <span>Download</span>
                                                                    </a>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="empty-files-alert">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                            <span>No digital files uploaded for this module yet. Instructors can attach materials via their portal.</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state-panel">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                        <h3>No Modules Defined</h3>
                                        <p>This course currently has no syllabus block units associated in the system.</p>
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
                                    <h2>Course Specifications</h2>
                                    <p>Curricular and institutional data</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <dl class="meta-list">
                                    <div class="meta-item">
                                        <dt>Department</dt>
                                        <dd><?php echo htmlspecialchars($course['department_name']); ?></dd>
                                    </div>
                                    <div class="meta-item">
                                        <dt>Academic Weight</dt>
                                        <dd><?php echo (int)($course['credits'] ?? 0); ?> Credit Units</dd>
                                    </div>
                                    <div class="meta-item">
                                        <dt>Course ID</dt>
                                        <dd>#<?php echo (int)$course['id']; ?></dd>
                                    </div>
                                    <div class="meta-item">
                                        <dt>Progress Indicator</dt>
                                        <dd><?php echo htmlspecialchars($course['progress'] ?? '0'); ?>%</dd>
                                    </div>
                                    <div class="meta-item">
                                        <dt>Catalog Publication</dt>
                                        <dd>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </section>

                        <!-- Enrolled Students Roster Card -->
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Enrolled Students</h2>
                                    <p>Active participants registered in this course</p>
                                </div>
                                <span class="counter-badge"><?php echo count($students); ?> Student(s)</span>
                            </div>
                            <div class="panel-body">
                                <?php if (!empty($students)): ?>
                                    <div class="students-list">
                                        <?php foreach ($students as $s):
                                            $isVerified = ((int)$s['verified'] === 1);
                                        ?>
                                            <div class="student-item">
                                                <div class="student-avatar">
                                                    <?php echo strtoupper(substr($s['name'] ?? 'S', 0, 1)); ?>
                                                </div>
                                                <div class="student-info">
                                                    <span class="student-name"><?php echo htmlspecialchars($s['name'] . ' ' . $s['lastname']); ?></span>
                                                    <span class="student-email"><?php echo htmlspecialchars($s['email']); ?></span>
                                                    <span class="student-id">Student ID #<?php echo (int)$s['id']; ?></span>
                                                </div>
                                                <div class="student-badge-wrap">
                                                    <span class="pill <?php echo $isVerified ? 'verified' : 'unverified'; ?>">
                                                        <?php echo $isVerified ? 'Verified' : 'Pending'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state-panel">
                                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        <h3>No Enrolled Students</h3>
                                        <p>No students have enrolled in this course yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Module Modal (Pure PHP) -->
    <?php if (!empty($_GET['add_module'])): ?>
        <div id="moduleModal" class="modal-overlay" style="display:flex;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Add Syllabus Module</h3>
                        <p style="font-size:12px; color:#64748b; margin-top:2px;">Create an instructional block unit and assign an instructor.</p>
                    </div>
                    <a href="adminCourseContent.php?course_id=<?php echo (int)$course['id']; ?>" class="btn-modal-close" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/admin/manageBlock.php" method="POST" style="margin-top:18px;"><?php echo csrfField(); ?>
                    <input type="hidden" name="course_id" value="<?php echo (int)$course['id']; ?>">
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
                        <a href="adminCourseContent.php?course_id=<?php echo (int)$course['id']; ?>" class="btn btn-secondary" style="text-decoration:none;">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Module</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>