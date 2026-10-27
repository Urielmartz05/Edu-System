<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    requireRole([1]);
    require_once __DIR__ . '/../../models/adminModels/adminDashboardModel.php';

    $adminDashboard = new AdminDashboard();
    $studentCount = $adminDashboard->getStudentsCount();
    $courseCount = $adminDashboard->getCourseCount();
    $teacherCount = $adminDashboard->getTeachersCount();
    $pendingAccountsCount = $adminDashboard->getPendingAccountsCount();
    $coursesInReviewCount = $adminDashboard->getCoursesInReviewCount();
    $topCourses = $adminDashboard->getTopCourses();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard · EduX</title>
        <link rel="stylesheet" href="adminDashboard.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>
                <div id="page-content">
                    <!-- Page Heading Hero -->
                    <section class="page-heading">
                        <div>
                            <span class="eyebrow">EXECUTIVE COMMAND · ACADEMIC CYCLE 2026-B</span>
                            <h1>Administrative Command Center</h1>
                            <p class="subtitle">Institutional management dashboard. Monitor student enrollment, curricular publications, faculty staffing, and platform verification queues.</p>
                        </div>
                    </section>

                    <!-- KPI Metrics Grid -->
                    <section class="kpi-grid">
                        <a href="adminUsers.php?role=Student" class="kpi" style="text-decoration:none; color:inherit; display:block;" title="Inspect student directory">
                            <div class="kpi-header">
                                <h2 class="kpi-label">Registered Students</h2>
                                <div class="kpi-icon-wrap icon-emerald">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </div>
                            </div>
                            <strong class="kpi-value"><?php echo $studentCount; ?></strong>
                            <span class="kpi-hint">Active learners enrolled</span>
                        </a>

                        <a href="adminCourses.php" class="kpi" style="text-decoration:none; color:inherit; display:block;" title="Manage course catalog">
                            <div class="kpi-header">
                                <h2 class="kpi-label">Courses Published</h2>
                                <div class="kpi-icon-wrap icon-blue">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                </div>
                            </div>
                            <strong class="kpi-value"><?php echo $courseCount; ?></strong>
                            <span class="kpi-hint">Curricular programs</span>
                        </a>

                        <a href="adminUsers.php?role=Teacher" class="kpi" style="text-decoration:none; color:inherit; display:block;" title="Manage faculty members">
                            <div class="kpi-header">
                                <h2 class="kpi-label">Faculty Staff</h2>
                                <div class="kpi-icon-wrap icon-maroon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 21v-2a6.5 6.5 0 0 1 13 0v2"/></svg>
                                </div>
                            </div>
                            <strong class="kpi-value"><?php echo $teacherCount; ?></strong>
                            <span class="kpi-hint">Assigned instructors</span>
                        </a>

                        <a href="adminUsers.php?status=Pending" class="kpi" style="text-decoration:none; color:inherit; display:block;" title="Review pending accounts">
                            <div class="kpi-header">
                                <h2 class="kpi-label">Pending Verifications</h2>
                                <div class="kpi-icon-wrap icon-amber">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                </div>
                            </div>
                            <strong class="kpi-value"><?php echo $pendingAccountsCount; ?></strong>
                            <span class="kpi-hint">Awaiting activation</span>
                        </a>
                    </section>

                    <!-- Split Panels Grid -->
                    <div class="grid">
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Top Enrolled Courses</h2>
                                    <p>Student enrollment distribution for the active cycle</p>
                                </div>
                                <a class="btn-panel-action" href="adminCourses.php">
                                    <span>View catalog</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 5"/></svg>
                                </a>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 44%;">COURSE / INSTRUCTOR</th>
                                            <th scope="col" style="width: 20%;">ENROLLED</th>
                                            <th scope="col" style="width: 20%;">STATUS</th>
                                            <th scope="col" style="width: 16%; text-align: right;">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($topCourses)): ?>
                                        <?php foreach ($topCourses as $course):
                                            $isPublished = ((int)$course['status'] === 1);
                                        ?>
                                            <tr>
                                                <td class="col-course">
                                                    <span class="cell-title"><?php echo htmlspecialchars($course['course_name']); ?></span>
                                                    <span class="cell-sub"><?php echo htmlspecialchars($course['teacher_name'] ?: 'Unassigned'); ?></span>
                                                </td>
                                                <td class="col-students"><?php echo (int) $course['total_students']; ?> students</td>
                                                <td class="col-status">
                                                    <span class="badge <?php echo $isPublished ? 'active' : 'pending'; ?>">
                                                        <?php echo $isPublished ? 'Published' : 'In Review'; ?>
                                                    </span>
                                                </td>
                                                <td style="text-align: right;">
                                                    <a href="adminCourseContent.php?course_id=<?php echo urlencode($course['id']); ?>" class="btn-table-action" title="Inspect Course">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                        <span>Inspect</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: #64748b; padding: 32px;">No active courses recorded</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Operational Tasks & Follow-up</h2>
                                    <p>Action items requiring administrative attention</p>
                                </div>
                            </div>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <span class="timeline-mark mark-amber"></span>
                                    <div>
                                        <strong>Accounts awaiting verification: <?php echo $pendingAccountsCount; ?></strong>
                                        <p>Review new student and faculty account requests.</p>
                                        <a class="btn-text-action" href="adminUsers.php">Manage users →</a>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <span class="timeline-mark mark-blue"></span>
                                    <div>
                                        <strong>Courses in review: <?php echo $coursesInReviewCount; ?></strong>
                                        <p>Inspect course syllabi pending administrative publication.</p>
                                        <a class="btn-text-action" href="adminCourses.php">Review courses →</a>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <span class="timeline-mark mark-emerald"></span>
                                    <div>
                                        <strong>Syllabus Progress Insights</strong>
                                        <p>Track student performance across all academic divisions.</p>
                                        <a class="btn-text-action" href="adminStatistics.php">View statistics →</a>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                </div>
            </main>
        </div>
    </body>
</html>
