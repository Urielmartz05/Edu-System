<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    require_once __DIR__ . "/../../models/teacherModels/teacherDashboardModel.php";
    $user = getLoggedUser();

    $teacherModel = new TeacherDashboard();
    $teacherId = (int)$user['id'];

    $assignedCoursesCount = $teacherModel->getAssignedCoursesCount($teacherId);
    $assignedBlocksCount = $teacherModel->getAssignedBlocksCount($teacherId);
    $enrolledStudentsCount = $teacherModel->getEnrolledStudentsCount($teacherId);
    $averageProgress = $teacherModel->getAverageProgress($teacherId);

    $assignedCourses = $teacherModel->getAssignedCourses($teacherId);
    $recentStudents = $teacherModel->getRecentEnrolledStudents($teacherId, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard · Edu-System</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <?php include_once __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include_once __DIR__ . '/../../../components/teacherSidebar/teacherSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Page Heading Hero -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">INSTRUCTOR PORTAL · ACADEMIC CYCLE 2026-B</span>
                        <h1>Welcome back, Prof. <?php echo htmlspecialchars($user['name'] . ' ' . ($user['lastname'] ?? '')); ?></h1>
                        <p class="subtitle">Overview of your active teaching assignments, syllabus progress, and newly enrolled students.</p>
                    </div>
                </section>

                <!-- KPI Cards Grid -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Active Courses</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $assignedCoursesCount; ?></strong>
                        <span class="kpi-hint">Assigned curricular programs</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Total Students</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $enrolledStudentsCount; ?></strong>
                        <span class="kpi-hint">Enrolled in your groups</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Teaching Modules</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $assignedBlocksCount; ?></strong>
                        <span class="kpi-hint">Syllabus units instructed</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Syllabus Progress</h2>
                            <div class="kpi-icon-wrap icon-amber">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $averageProgress; ?>%</strong>
                        <span class="kpi-hint">Average course completion</span>
                    </article>
                </section>

                <!-- Dashboard Split Layout -->
                <div class="dashboard-split">
                    <!-- Left Section: Active Teaching Classes -->
                    <section class="classes-section">
                        <div class="section-head">
                            <h2>My Active Teaching Classes</h2>
                            <span class="panel-counter"><?php echo count($assignedCourses); ?> Active Course(s)</span>
                        </div>

                        <?php if (!empty($assignedCourses)): ?>
                            <?php foreach ($assignedCourses as $c): 
                                $prog = (float)($c['progress'] ?? 0);
                                $isPublished = ((int)$c['status'] === 1);
                                $statusText = $isPublished ? 'Active' : 'In Review';
                                $statusClass = $isPublished ? 'active' : 'pending';
                                $modulesArray = !empty($c['my_blocks_list']) ? explode(' • ', $c['my_blocks_list']) : [];
                            ?>
                                <article class="class-card" onclick="window.location.href='ponenteCourseContent.php?course_id=<?php echo (int)$c['id']; ?>'" style="cursor: pointer;" title="Open <?php echo htmlspecialchars($c['course_name']); ?> workspace">
                                    <div class="class-card-top">
                                        <span class="badge-dept"><?php echo htmlspecialchars($c['department_name'] ?? 'General'); ?></span>
                                        <span class="badge-status <?php echo $statusClass; ?>">
                                            <span class="status-dot"></span>
                                            <?php echo $statusText; ?>
                                        </span>
                                    </div>

                                    <h3 class="class-title"><?php echo htmlspecialchars($c['course_name']); ?></h3>

                                    <!-- Progress Bar -->
                                    <div class="progress-container">
                                        <div class="progress-labels">
                                            <span>Syllabus Progress</span>
                                            <strong><?php echo number_format($prog, 1); ?>%</strong>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo min(100, max(0, $prog)); ?>%;"></div>
                                        </div>
                                    </div>

                                    <!-- Modules Assigned inside this Course -->
                                    <?php if (!empty($modulesArray)): ?>
                                        <div class="modules-assignment-wrap">
                                            <div class="modules-assignment-label">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                                <span>Your Assigned Module(s):</span>
                                            </div>
                                            <div class="module-pills-list">
                                                <?php foreach ($modulesArray as $modName): ?>
                                                    <span class="module-pill"><?php echo htmlspecialchars($modName); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Footer -->
                                    <div class="class-card-footer">
                                        <div class="class-card-meta">
                                            <div class="meta-item">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                                <span><?php echo (int)$c['student_count']; ?> students</span>
                                            </div>
                                            <div class="meta-item">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                                <span><?php echo (int)($c['credits'] ?? 0); ?> credits</span>
                                            </div>
                                        </div>
                                        <a href="ponenteCourseContent.php?course_id=<?php echo (int)$c['id']; ?>" class="btn-open-workspace" onclick="event.stopPropagation();">
                                            <span>Open Course Workspace</span>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="class-card" style="text-align: center; padding: 48px 24px; color: #64748b;">
                                <p>No active courses assigned to your instructor profile for this cycle.</p>
                            </div>
                        <?php endif; ?>
                    </section>

                    <!-- Right Section: Enrolled Students Snapshot -->
                    <section class="panel">
                        <div class="panel-head">
                            <h2>Enrolled Students Snapshot</h2>
                            <span class="panel-counter"><?php echo count($recentStudents); ?> Enrolled</span>
                        </div>

                        <?php if (!empty($recentStudents)): ?>
                            <div class="students-snapshot-list">
                                <?php foreach ($recentStudents as $s): ?>
                                    <?php
                                        $initials = strtoupper(substr($s['name'] ?? 'S', 0, 1) . substr($s['lastname'] ?? '', 0, 1));
                                        $isVerified = ((int)($s['verified'] ?? 0) === 1);
                                    ?>
                                    <div class="student-row-item">
                                        <div class="student-profile">
                                            <div class="student-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                            <div class="student-names">
                                                <div class="student-fullname"><?php echo htmlspecialchars(($s['name'] ?? '') . ' ' . ($s['lastname'] ?? '')); ?></div>
                                                <a href="mailto:<?php echo htmlspecialchars($s['email']); ?>" class="student-email"><?php echo htmlspecialchars($s['email']); ?></a>
                                            </div>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span class="student-course-badge" title="<?php echo htmlspecialchars($s['course_name']); ?>">
                                                <?php echo htmlspecialchars(mb_strimwidth($s['course_name'], 0, 18, '...')); ?>
                                            </span>
                                            <span class="badge <?php echo $isVerified ? 'active' : 'pending'; ?>" style="min-width: 80px; height: 26px; font-size: 11px;">
                                                <?php echo $isVerified ? 'Verified' : 'Pending'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state-card">
                                <p>No students enrolled in your teaching groups yet.</p>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
