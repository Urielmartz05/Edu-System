<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([4]);
    require_once __DIR__ . "/../../models/studentModels/studentDashboardModel.php";
    $user = getLoggedUser();

    $studentDashboard = new StudentDashboard();
    $activeCourse = $studentDashboard->getActiveCourse();
    $courseProgress = $studentDashboard->getCourseProgress();
    $blocksCount = $studentDashboard->getBlocksCount();
    $courseCredits = $studentDashboard->getCourseCredits();
    $courseBlocks = $studentDashboard->getBlocks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard · EduX</title>
    <link rel="stylesheet" href="alumnoDashboard.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Page Heading Hero -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC PROGRAM · <?php echo htmlspecialchars($activeCourse['course_name'] ?? 'General Learning Portal'); ?></span>
                        <h1>Welcome back, <?php echo htmlspecialchars($user['name'] . ' ' . ($user['lastname'] ?? '')); ?></h1>
                        <p class="subtitle">Overview of your academic progress, syllabus units, and study materials for your enrolled program.</p>
                    </div>
                </section>

                <!-- KPI Cards Grid -->
                <section class="kpi-grid">
                    <a href="alumnoCourses.php" class="kpi" style="cursor: pointer; text-decoration:none; color:inherit; display:block;" title="View all course modules">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Course Modules</h2>
                            <div class="kpi-icon-wrap icon-maroon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo (int)$blocksCount; ?></strong>
                        <span class="kpi-hint">Syllabus modules scheduled</span>
                    </a>

                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Overall Progress</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo number_format((float)$courseProgress, 1); ?>%</strong>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width: <?php echo min(100, max(0, (float)$courseProgress)); ?>%;"></div>
                        </div>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Course Credits</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo (int)$courseCredits; ?></strong>
                        <span class="kpi-hint">Academic weight units</span>
                    </article>

                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Enrollment Status</h2>
                            <div class="kpi-icon-wrap icon-amber">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value" style="font-size: 20px;">Active</strong>
                        <span class="kpi-hint">Official registered student</span>
                    </article>
                </section>

                <!-- Dashboard Content Grid -->
                <div class="dashboard-split">
                    <!-- Course Modules Table -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Active Syllabus Modules</h2>
                                <p>Instructional units and study topics for <?php echo htmlspecialchars($activeCourse['course_name'] ?? 'your course'); ?>.</p>
                            </div>
                            <a href="alumnoCourses.php" class="btn-panel-action">
                                <span>View all modules</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 5"/></svg>
                            </a>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 18%;">BLOCK</th>
                                        <th style="width: 44%;">MODULE NAME</th>
                                        <th style="width: 26%;">INSTRUCTOR</th>
                                        <th style="width: 12%; text-align: right;">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($courseBlocks)): ?>
                                        <?php foreach ($courseBlocks as $block): ?>
                                            <tr>
                                                <td><span class="block-badge">Block #<?php echo (int)$block['id']; ?></span></td>
                                                <td><span class="module-title-cell"><?php echo htmlspecialchars($block['block_name']); ?></span></td>
                                                <td><span class="teacher-tag"><?php echo htmlspecialchars($block['teacher_name'] ?? 'Unassigned'); ?></span></td>
                                                <td style="text-align: right;">
                                                    <a href="blockContent.php?block_id=<?php echo (int)$block['id']; ?>" class="btn-row-action" style="text-decoration:none;">View →</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="table-empty">No syllabus modules recorded yet for this course.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Academic Highlights Summary -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Active Program Profile</h2>
                                <p>Summary of current course registration.</p>
                            </div>
                        </div>
                        <div class="program-summary-body">
                            <div class="summary-item">
                                <span class="summary-label">Course Title:</span>
                                <strong class="summary-value"><?php echo htmlspecialchars($activeCourse['course_name'] ?? 'General Course'); ?></strong>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Academic Status:</span>
                                <span class="badge active">Enrolled & Active</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Academic Weight:</span>
                                <strong class="summary-value"><?php echo (int)$courseCredits; ?> Credits</strong>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Syllabus Completion:</span>
                                <strong class="summary-value"><?php echo number_format((float)$courseProgress, 1); ?>% Completed</strong>
                            </div>

                            <div class="summary-actions">
                                <a href="alumnoCourses.php" class="btn-primary-block">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    <span>Access Course Syllabus</span>
                                </a>
                                <a href="misCursos.php" class="btn-outline-block">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                    <span>Switch to Another Course</span>
                                </a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
