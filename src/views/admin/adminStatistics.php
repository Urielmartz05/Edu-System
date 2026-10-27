<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    requireRole([1]);
    require_once __DIR__ . '/../../models/adminModels/adminStatisticsModel.php';

    $adminStats = new AdminStatistics();
    $totalEnrollments = $adminStats->getTotalEnrollments();
    $completionRate = $adminStats->getCompletionRate();
    $avgProgress = $adminStats->getAverageCourseProgress();
    $activeCourses = $adminStats->getActiveCoursesCount();
    $distribution = $adminStats->getProgressDistribution();
    $coursePerformance = $adminStats->getCoursePerformanceList();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Statistics · EduX</title>
        <link rel="stylesheet" href="adminStatistics.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>
                <div id="page-content">
                    <!-- Heading -->
                    <section class="page-heading">
                        <div>
                            <p class="eyebrow">ACADEMIC MANAGEMENT</p>
                            <h1>Academic Statistics</h1>
                            <p class="subtitle">Insights to support student learning, track completion milestones, and drive institutional decisions.</p>
                        </div>
                    </section>

                    <!-- KPI Grid -->
                    <section class="kpi-grid">
                        <article class="kpi">
                            <h2 class="kpi-label">Total Enrollments</h2>
                            <strong class="kpi-value"><?php echo $totalEnrollments; ?></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Completion Rate</h2>
                            <strong class="kpi-value"><?php echo $completionRate; ?>%</strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Average Progress</h2>
                            <strong class="kpi-value"><?php echo $avgProgress; ?>%</strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Active Courses</h2>
                            <strong class="kpi-value"><?php echo $activeCourses; ?></strong>
                        </article>
                    </section>

                    <div class="grid">
                        <!-- Progress Distribution Panel -->
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Syllabus Progress Breakdown</h2>
                                    <p>Percentage of courses across completion tiers</p>
                                </div>
                                <span class="pill">Current Academic Cycle</span>
                            </div>
                            <div class="progress-list">
                                <div>
                                    <div class="progress-title">
                                        <span>Advanced (&gt; 80% Completed)</span>
                                        <strong><?php echo $distribution['high']; ?>% (<?php echo $distribution['counts']['high']; ?> courses)</strong>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width:<?php echo $distribution['high']; ?>%; background:#10b981;"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress-title">
                                        <span>Intermediate (50% – 79%)</span>
                                        <strong><?php echo $distribution['mid']; ?>% (<?php echo $distribution['counts']['mid']; ?> courses)</strong>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width:<?php echo $distribution['mid']; ?>%; background:#3b82f6;"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress-title">
                                        <span>Beginning (1% – 49%)</span>
                                        <strong><?php echo $distribution['low']; ?>% (<?php echo $distribution['counts']['low']; ?> courses)</strong>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width:<?php echo $distribution['low']; ?>%; background:#f59e0b;"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress-title">
                                        <span>Not Started (0%)</span>
                                        <strong><?php echo $distribution['none']; ?>% (<?php echo $distribution['counts']['none']; ?> courses)</strong>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width:<?php echo $distribution['none']; ?>%; background:#94a3b8;"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Academic Guidance Insight -->
                        <div class="insight">
                            <span class="insight-badge">Advisory Notice</span>
                            <h3>Academic Advisory Summary</h3>
                            <p>
                                Overall institutional learning pace is currently maintaining an average completion rate of <strong><?php echo $avgProgress; ?>%</strong>. Most courses have active syllabus progression. Ensure courses in the beginning tier receive scheduled faculty check-ins and module updates.
                            </p>
                        </div>
                    </div>

                    <!-- Performance Table -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Course Performance Details</h2>
                                <p>Syllabus completion, enrolled students, and module counts per course.</p>
                            </div>
                        </div>
                        <div class="table-wrap" role="region" aria-label="Course statistics" tabindex="0">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col" class="col-course">COURSE</th>
                                        <th scope="col" class="col-students">ENROLLED STUDENTS</th>
                                        <th scope="col" class="col-modules">SYLLABUS MODULES</th>
                                        <th scope="col" class="col-progress">AVERAGE PROGRESS</th>
                                        <th scope="col" class="col-status">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($coursePerformance)): ?>
                                        <?php foreach ($coursePerformance as $item):
                                            $prog = (float)($item['progress'] ?? 0);
                                            $isPublished = ((int)$item['status'] === 1);
                                        ?>
                                            <tr>
                                                <td class="col-course"><strong><?php echo htmlspecialchars($item['course_name']); ?></strong></td>
                                                <td class="col-students"><?php echo (int)$item['enrolled_count']; ?> students</td>
                                                <td class="col-modules"><?php echo (int)$item['block_count']; ?> modules</td>
                                                <td class="col-progress">
                                                    <div style="display:flex; align-items:center; gap:10px;">
                                                        <span style="font-weight:600; min-width:45px;"><?php echo $prog; ?>%</span>
                                                        <div style="flex:1; background:#e2e8f0; border-radius:6px; height:8px; overflow:hidden; min-width:80px;">
                                                            <div style="width:<?php echo min(100, max(0, $prog)); ?>%; background:#10b981; height:100%;"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="col-status">
                                                    <span class="badge <?php echo $isPublished ? 'active' : 'pending'; ?>">
                                                        <?php echo $isPublished ? 'Active' : 'In Review'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align:center; padding:24px; color:#64748b;">No course statistics available.</td>
                                        </tr>
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
